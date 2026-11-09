<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentStatus;
use App\Models\User;
use App\Services\DocumentFamily;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentFamilyTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        DocumentCategory::create(['name' => 'Procedury']);
        DocumentStatus::create(['name' => 'Opublikowany']);
    }

    private function document(string $title, ?Document $source = null, string $type = Document::TYPE_DOCUMENT, array $attributes = []): Document
    {
        return Document::create(array_merge([
            'title' => $title,
            'document_number' => $title,
            'description' => 'Opis dokumentu',
            'category' => 'Procedury',
            'status' => 'Opublikowany',
            'type' => $type,
            'source_document_id' => $source?->id,
            'active' => true,
            'file_path' => 'documents/source.pdf',
            'original_filename' => 'source.pdf',
            'created_by' => $this->manager->id,
        ], $attributes));
    }

    public function test_family_includes_ancestors_descendants_and_siblings_in_chronological_order(): void
    {
        $root = $this->document('Początek', attributes: ['created_at' => now()->subDays(4)]);
        $first = $this->document('Pierwsza zmiana', $root, Document::TYPE_CHANGE, ['created_at' => now()->subDays(3)]);
        $sibling = $this->document('Druga zmiana', $root, Document::TYPE_CHANGE, ['created_at' => now()->subDays(2)]);
        $nested = $this->document('Trzecia zmiana', $first, Document::TYPE_CHANGE, ['created_at' => now()->subDay()]);
        $other = $this->document('Osobny dokument');

        foreach ([$root, $first, $sibling, $nested] as $member) {
            $this->assertSame([$root->id, $first->id, $sibling->id, $nested->id], $member->connectedDocuments()->pluck('id')->all());
            $this->assertSame(Document::RELATION_STATE_CHANGED, $member->relationState());
        }
        $this->assertSame(Document::RELATION_STATE_ACTIVE, $other->relationState());
    }

    public function test_creating_a_repeal_deactivates_the_entire_family_and_records_history(): void
    {
        Storage::fake();
        Storage::put('documents/source.pdf', 'source');
        $root = $this->document('Główny');
        $first = $this->document('Zmiana 1', $root, Document::TYPE_CHANGE);
        $sibling = $this->document('Zmiana 2', $root, Document::TYPE_CHANGE);
        $other = $this->document('Niezależny');

        $this->actingAs($this->manager)->post(route('documents.store'), [
            'title' => 'Uchylenie', 'document_number' => 'UCH-1', 'description' => 'Opis',
            'category' => 'Procedury', 'status' => 'Opublikowany', 'active' => '1',
            'type' => Document::TYPE_REPEAL, 'source_document_id' => $first->id,
        ])->assertRedirect();

        $repeal = Document::where('document_number', 'UCH-1')->firstOrFail();
        foreach ([$root, $first, $sibling, $repeal] as $member) {
            $this->assertFalse($member->fresh()->active);
            $this->assertSame(Document::RELATION_STATE_REPEALED, $member->relationState());
            $this->assertDatabaseHas('document_histories', ['document_id' => $member->id]);
        }
        $this->assertTrue($other->fresh()->active);
        $first->update(['active' => true]);
        $this->assertFalse($first->fresh()->active);
        $later = $this->document('Późniejsza zmiana', $root, Document::TYPE_CHANGE);
        $this->assertFalse($later->fresh()->active);
        $this->assertSame(Document::RELATION_STATE_REPEALED, $later->relationState());

        $response = $this->actingAs($this->manager)->get(route('documents.index', ['relation_state' => 'repealed']));
        $response->assertOk()->assertSee($root->title)->assertSee($sibling->title)->assertSee($repeal->title)->assertDontSee($other->title);
    }

    public function test_every_card_shows_the_same_ordered_clickable_chain(): void
    {
        $root = $this->document('Dokument Alfa');
        $change = $this->document('Dokument Beta', $root, Document::TYPE_CHANGE);
        $last = $this->document('Dokument Gamma', $change, Document::TYPE_CHANGE);

        foreach ([$root, $change, $last] as $member) {
            foreach (['documents.preview', 'documents.edit'] as $route) {
                $response = $this->actingAs($this->manager)->get(route($route, $member));
                $response->assertOk()->assertSee('aria-current="page"', false)
                    ->assertSee('Otwarty dokument');
                $chain = explode('<ol', $response->getContent(), 2)[1];
                $this->assertLessThan(strpos($chain, 'Dokument Beta'), strpos($chain, 'Dokument Alfa'));
                $this->assertLessThan(strpos($chain, 'Dokument Gamma'), strpos($chain, 'Dokument Beta'));
                foreach ([$root, $change, $last] as $linked) {
                    $response->assertSee(route($route, $linked), false);
                }
            }
        }
    }

    public function test_list_summary_and_state_filter_include_the_entire_family(): void
    {
        $root = $this->document('Bazowy');
        $first = $this->document('Zmiana pierwsza', $root, Document::TYPE_CHANGE);
        $second = $this->document('Zmiana druga', $first, Document::TYPE_CHANGE);
        $other = $this->document('Poza zestawem');

        $response = $this->actingAs($this->manager)->get(route('documents.index', ['relation_state' => 'changed']));
        $response->assertOk()->assertSee('Dotyczy całego zestawu:')->assertDontSee($other->title);
        foreach ([$root, $first, $second] as $member) {
            $response->assertSee(route('documents.preview', $member), false);
        }
    }

    public function test_public_chain_and_summary_show_inactive_and_future_related_documents(): void
    {
        $root = $this->document('Jawny dokument');
        $change = $this->document('Jawna zmiana', $root, Document::TYPE_CHANGE);
        $this->document('Ukryta zmiana', $change, Document::TYPE_CHANGE, ['active' => false]);
        $this->document('Przyszła zmiana', $root, Document::TYPE_CHANGE, ['valid_from' => now()->addWeek()]);

        $this->get(route('documents.preview_public', $change))->assertOk()
            ->assertSee($root->title)->assertSee('Ukryta zmiana')->assertSee('Przyszła zmiana');
        $this->get(route('documents.index'))->assertOk()
            ->assertSee('Dotyczy całego zestawu:')->assertSee('Ukryta zmiana')->assertSee('Przyszła zmiana');
    }

    public function test_legacy_repeals_remain_publicly_readable(): void
    {
        Storage::fake();
        Storage::put('documents/source.pdf', '%PDF-1.4 legacy');
        $root = $this->document('Dawny dokument');
        Document::withoutEvents(fn () => $this->document('Dawne uchylenie', $root, Document::TYPE_REPEAL, ['system_identifier' => 'LEGACY-REPEAL']));
        app(DocumentFamily::class)->forget();

        foreach (['documents.preview_public', 'documents.file_public', 'documents.download_public'] as $route) {
            $this->get(route($route, $root))->assertOk();
        }
        $this->get(route('documents.index'))->assertOk()->assertSee($root->title);
        $this->get(route('dashboard'))->assertOk()->assertSee($root->title)->assertSee('Dawne uchylenie');
        $this->actingAs($this->manager)->get(route('dashboard'))->assertOk()->assertSee($root->title)->assertSee('Dawne uchylenie');
    }

    public function test_guests_can_read_every_document_and_its_pdf_but_cannot_modify_documents(): void
    {
        Storage::fake();
        $pdf = '%PDF-1.4 public preview';
        Storage::put('documents/source.pdf', $pdf);
        $root = $this->document('Dokument nieaktywny', attributes: ['active' => false]);
        $future = $this->document('Dokument przyszły', attributes: ['valid_from' => now()->addYear()]);
        $expired = $this->document('Dokument wygasły', attributes: ['valid_to' => now()->subYear()]);
        $repeal = $this->document('Dokument uchylający', $root, Document::TYPE_REPEAL);

        $list = $this->get(route('documents.index'))->assertOk();
        foreach ([$root, $future, $expired, $repeal] as $member) {
            $list->assertSee($member->title)->assertSee(route('documents.preview_public', $member), false)
                ->assertDontSee(route('documents.edit', $member), false);
            $this->get(route('documents.preview_public', $member))->assertOk()
                ->assertSee($member->title)
                ->assertSee('<iframe', false)
                ->assertSee(route('documents.file_public', $member), false)
                ->assertDontSee('<form', false);
            $this->get(route('documents.file_public', $member))->assertOk()
                ->assertHeader('Content-Type', 'application/pdf');
            $this->get(route('documents.download_public', $member))->assertOk()->assertDownload('source.pdf');
        }

        $this->get(route('documents.create'))->assertRedirect(route('login'));
        $this->get(route('documents.edit', $root))->assertRedirect(route('login'));
        $this->post(route('documents.store'), [])->assertRedirect(route('login'));
        $this->put(route('documents.update', $root), ['title' => 'Zmieniony tytuł'])->assertRedirect(route('login'));
        $this->delete(route('documents.destroy', $root))->assertRedirect(route('login'));
        $this->patch(route('documents.move', $root), ['folder_id' => null])->assertRedirect(route('login'));
        $this->assertSame('Dokument nieaktywny', $root->fresh()->title);
        $this->assertDatabaseCount('documents', 4);
    }

    public function test_cycles_do_not_loop_and_deleted_links_invalidate_the_family_cache(): void
    {
        $root = $this->document('A');
        $child = $this->document('B', $root, Document::TYPE_CHANGE);
        $root->update(['source_document_id' => $child->id]);
        $this->assertCount(2, $root->connectedDocuments());
        $child->delete();
        $this->assertSame([$root->id], $root->connectedDocuments()->pluck('id')->all());
    }
}
