<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Collection;

class DocumentFamily
{
    private ?Collection $documents = null;

    private array $neighbours = [];

    private array $families = [];

    public function forget(): void
    {
        $this->documents = null;
        $this->neighbours = [];
        $this->families = [];
    }

    public function forDocument(Document $document): Collection
    {
        if (! $document->exists) {
            return collect();
        }

        if ($this->documents === null) {
            // One metadata query per request, also when rendering a whole document list.
            $this->documents = Document::query()->get([
                'id', 'source_document_id', 'title', 'document_number', 'type',
                'created_at', 'active', 'valid_from', 'valid_to',
            ])->keyBy('id');

            foreach ($this->documents as $member) {
                if ($member->source_document_id && $this->documents->has($member->source_document_id)) {
                    $this->neighbours[$member->id][] = $member->source_document_id;
                    $this->neighbours[$member->source_document_id][] = $member->id;
                }
            }
        }

        if (! isset($this->families[$document->id])) {
            $pending = [$document->id];
            $visited = [];

            while ($pending !== []) {
                $id = array_pop($pending);
                if (isset($visited[$id])) {
                    continue;
                }
                $visited[$id] = true;
                array_push($pending, ...($this->neighbours[$id] ?? []));
            }

            $family = $this->documents->only(array_keys($visited))
                ->sortBy([['created_at', 'asc'], ['id', 'asc']])->values();

            foreach ($family as $member) {
                $this->families[$member->id] = $family;
            }
        }

        return $this->families[$document->id] ?? collect();
    }
}
