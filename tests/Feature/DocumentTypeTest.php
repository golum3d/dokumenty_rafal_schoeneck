<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_document_is_saved_with_default_document_type(): void
    {
        Storage::fake();

        $user = User::factory()->create([
            'roles' => [User::ROLE_DOCUMENT_STAFF],
        ]);
        DocumentCategory::create(['name' => 'Procedury']);
        DocumentStatus::create(['name' => 'Opublikowany']);

        $response = $this->actingAs($user)->post(route('documents.store'), [
            'title' => 'Instrukcja kancelaryjna',
            'document_number' => 'DOC-001',
            'description' => 'Opis dokumentu',
            'category' => 'Procedury',
            'status' => 'Opublikowany',
            'active' => '1',
            'pdf' => UploadedFile::fake()->create('instrukcja.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('documents.index'));

        $this->assertDatabaseHas('documents', [
            'document_number' => 'DOC-001',
            'type' => Document::TYPE_DOCUMENT,
            'source_document_id' => null,
        ]);
    }

    public function test_change_create_form_can_be_opened_from_existing_document_card(): void
    {
        $user = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
        ]);
        DocumentCategory::create(['name' => 'Procedury']);
        DocumentStatus::create(['name' => 'Opublikowany']);

        $sourceDocument = Document::create([
            'title' => 'Instrukcja bazowa',
            'document_number' => 'DOC-BASE',
            'description' => 'Opis bazowy',
            'category' => 'Procedury',
            'status' => 'Opublikowany',
            'type' => Document::TYPE_DOCUMENT,
            'source_document_id' => null,
            'active' => true,
            'file_path' => 'documents/source.pdf',
            'original_filename' => 'source.pdf',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('documents.create', [
            'type' => Document::TYPE_CHANGE,
            'source_document_id' => $sourceDocument->id,
        ]));

        $response->assertOk();
        $response->assertSee('value="' . Document::TYPE_CHANGE . '"', false);
        $response->assertSee('value="' . $sourceDocument->id . '"', false);
        $response->assertSee('Instrukcja bazowa');
        $response->assertSee('source.pdf');
    }

    public function test_change_can_be_saved_from_create_form(): void
    {
        Storage::fake();

        $user = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
        ]);
        DocumentCategory::create(['name' => 'Procedury']);
        DocumentStatus::create(['name' => 'Opublikowany']);

        $sourceDocument = Document::create([
            'title' => 'Instrukcja bazowa',
            'document_number' => 'DOC-BASE',
            'description' => 'Opis bazowy',
            'category' => 'Procedury',
            'status' => 'Opublikowany',
            'type' => Document::TYPE_DOCUMENT,
            'source_document_id' => null,
            'active' => true,
            'file_path' => 'documents/source.pdf',
            'original_filename' => 'source.pdf',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('documents.store'), [
            'title' => 'Instrukcja po zmianie',
            'document_number' => 'DOC-002',
            'description' => 'Opis po zmianie',
            'category' => 'Procedury',
            'status' => 'Opublikowany',
            'type' => Document::TYPE_CHANGE,
            'source_document_id' => $sourceDocument->id,
            'active' => '1',
            'pdf' => UploadedFile::fake()->create('zmiana.pdf', 120, 'application/pdf'),
        ]);

        $response->assertRedirect(route('documents.index'));

        $this->assertDatabaseHas('documents', [
            'document_number' => 'DOC-002',
            'type' => Document::TYPE_CHANGE,
            'source_document_id' => $sourceDocument->id,
        ]);
    }

    public function test_change_can_use_source_document_pdf_without_uploading_new_file(): void
    {
        Storage::fake();
        Storage::put('documents/source.pdf', 'source-pdf-content');

        $user = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
        ]);
        DocumentCategory::create(['name' => 'Procedury']);
        DocumentStatus::create(['name' => 'Opublikowany']);

        $sourceDocument = Document::create([
            'title' => 'Instrukcja bazowa',
            'document_number' => 'DOC-BASE',
            'description' => 'Opis bazowy',
            'category' => 'Procedury',
            'status' => 'Opublikowany',
            'type' => Document::TYPE_DOCUMENT,
            'source_document_id' => null,
            'active' => true,
            'file_path' => 'documents/source.pdf',
            'original_filename' => 'source.pdf',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('documents.store'), [
            'title' => 'Instrukcja po zmianie',
            'document_number' => 'DOC-003',
            'description' => 'Opis po zmianie',
            'category' => 'Procedury',
            'status' => 'Opublikowany',
            'type' => Document::TYPE_CHANGE,
            'source_document_id' => $sourceDocument->id,
            'active' => '1',
        ]);

        $response->assertRedirect(route('documents.index'));

        $newDocument = Document::where('document_number', 'DOC-003')->firstOrFail();

        $this->assertSame(Document::TYPE_CHANGE, $newDocument->type);
        $this->assertSame($sourceDocument->id, $newDocument->source_document_id);
        $this->assertSame('source.pdf', $newDocument->original_filename);
        $this->assertNotSame($sourceDocument->file_path, $newDocument->file_path);
        Storage::assertExists($newDocument->file_path);
        $this->assertSame('source-pdf-content', Storage::get($newDocument->file_path));
    }
}
