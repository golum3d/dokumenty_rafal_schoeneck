<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentHistory;
use App\Models\DocumentStatus;
use App\Models\Folder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::check() || ! Auth::user()->can('manage-documents')) {
            return $this->publicIndex($request);
        }

        $userId = Auth::id();
        $filters = [
            'search' => trim((string) $request->string('search')),
            'category' => $request->string('category')->toString(),
            'status' => $request->string('status')->toString(),
            'folder_id' => $request->string('folder_id')->toString(),
        ];
        $hasActiveFilters = collect($filters)->contains(fn (string $value) => $value !== '');

        $documentQuery = Document::query()->with('creator');
        $this->applyDocumentFilters($documentQuery, $filters);

        $documents = $documentQuery
            ->orderBy('created_at', 'desc')
            ->get();

        $allFolders = Folder::where('user_id', $userId)->orderBy('name')->get();
        $selectedFolderId = $filters['folder_id'] !== '' && $filters['folder_id'] !== '__none__'
            ? (int) $filters['folder_id']
            : null;
        [$folders, $rootDocuments] = $this->buildFilteredFolderTree($allFolders, $documents, $hasActiveFilters, $selectedFolderId);

        $categories = DocumentCategory::orderBy('name')->get();
        $statuses = DocumentStatus::orderBy('name')->get();

        return view('documents.index', [
            'folders' => $folders,
            'documents' => $rootDocuments,
            'allFolders' => $allFolders,
            'categories' => $categories,
            'statuses' => $statuses,
            'filters' => $filters,
            'canManageDocuments' => true,
        ]);
    }

    protected function publicIndex(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->string('search')),
            'category' => $request->string('category')->toString(),
            'status' => $request->string('status')->toString(),
            'folder_id' => $request->string('folder_id')->toString(),
        ];
        $hasActiveFilters = collect($filters)->contains(fn (string $value) => $value !== '');

        $documentQuery = Document::query()
            ->where('active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', now());
            });

        $this->applyDocumentFilters($documentQuery, $filters);

        $documents = $documentQuery
            ->orderBy('created_at', 'desc')
            ->get();

        $allFolders = Folder::orderBy('name')->get();
        $selectedFolderId = $filters['folder_id'] !== '' && $filters['folder_id'] !== '__none__'
            ? (int) $filters['folder_id']
            : null;
        [$folders, $rootDocuments] = $this->buildFilteredFolderTree($allFolders, $documents, $hasActiveFilters, $selectedFolderId);

        return view('documents.index', [
            'folders' => $folders,
            'documents' => $rootDocuments,
            'allFolders' => $allFolders,
            'categories' => DocumentCategory::orderBy('name')->get(),
            'statuses' => DocumentStatus::orderBy('name')->get(),
            'filters' => $filters,
            'canManageDocuments' => false,
        ]);
    }

    /**
     * Read-only list for regular users: active documents within valid range.
     */
    public function userIndex()
    {
        // Base visible documents query
        $visibleQuery = Document::where('active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', now());
            });

        // Folders that contain visible documents (including nested)
        $folders = Folder::whereHas('documents', function ($q) use ($visibleQuery) {
                // apply same visibility constraints
                $q->where('active', true)
                    ->where(function ($q2) {
                        $q2->whereNull('valid_from')->orWhere('valid_from', '<=', now());
                    })
                    ->where(function ($q2) {
                        $q2->whereNull('valid_to')->orWhere('valid_to', '>=', now());
                    });
            })
            ->with(['documents' => function ($q) {
                $q->where('active', true)
                    ->where(function ($q2) {
                        $q2->whereNull('valid_from')->orWhere('valid_from', '<=', now());
                    })
                    ->where(function ($q2) {
                        $q2->whereNull('valid_to')->orWhere('valid_to', '>=', now());
                    })
                    ->orderBy('created_at', 'desc');
            }, 'children'])
            ->orderBy('name')
            ->get();

        // Documents without folder
        $noFolderDocuments = (clone $visibleQuery)->whereNull('folder_id')->orderBy('created_at', 'desc')->get();

        return view('documents.user_index', [
            'folders' => $folders,
            'noFolderDocuments' => $noFolderDocuments,
        ]);
    }

    public function create()
    {
        $userId = Auth::id();
        $folders = Folder::where('user_id', $userId)->orderBy('name')->get();
        $sourceDocument = null;
        $type = request()->string('type')->toString();

        if (! in_array($type, [Document::TYPE_CHANGE, Document::TYPE_REPEAL], true)) {
            $type = Document::TYPE_DOCUMENT;
        }

        if ($type !== Document::TYPE_DOCUMENT && request()->filled('source_document_id')) {
            $sourceDocument = Document::findOrFail((int) request('source_document_id'));
        }

        $document = new Document();

        if ($sourceDocument) {
            $document->fill([
                'title' => $sourceDocument->title,
                'document_number' => $sourceDocument->document_number,
                'description' => $sourceDocument->description,
                'category' => $sourceDocument->category,
                'status' => $sourceDocument->status,
                'valid_from' => $sourceDocument->valid_from,
                'valid_to' => $sourceDocument->valid_to,
                'active' => $sourceDocument->active,
                'folder_id' => $sourceDocument->folder_id,
                'type' => $type,
                'source_document_id' => $sourceDocument->id,
            ]);
        } else {
            $document->type = $type;
        }

        return view('documents.create', [
            'document' => $document,
            'categories' => DocumentCategory::orderBy('name')->get(),
            'statuses' => DocumentStatus::orderBy('name')->get(),
            'folders' => $folders,
            'returnUrl' => $this->resolveReturnUrl(request()),
            'sourceDocument' => $sourceDocument,
        ]);
    }

    public function store(Request $request)
    {
        $type = $request->input('type', Document::TYPE_DOCUMENT);
        $sourceDocumentId = $request->filled('source_document_id')
            ? (int) $request->input('source_document_id')
            : null;
        $data = $this->validateDocumentData($request, $this->isPdfRequiredForRequest($type, $sourceDocumentId));

        $this->createDocument($data, $request, $type, $sourceDocumentId);

        $message = match ($type) {
            Document::TYPE_CHANGE => __('documents.change_created'),
            Document::TYPE_REPEAL => __('documents.repeal_created'),
            default => __('documents.document_created'),
        };

        return redirect($this->resolveReturnUrl($request))
            ->with('success', $message);
    }

    public function edit(Document $document)
    {
        $document->load('histories.user', 'creator', 'sourceDocument');
        $userId = Auth::id();
        $folders = Folder::where('user_id', $userId)->orderBy('name')->get();

        return view('documents.edit', [
            'document' => $document,
            'categories' => DocumentCategory::orderBy('name')->get(),
            'statuses' => DocumentStatus::orderBy('name')->get(),
            'folders' => $folders,
            'returnUrl' => $this->resolveReturnUrl(request()),
        ]);
    }

    public function update(Request $request, Document $document)
    {
        $data = $this->validateDocumentData($request, false);

        $data['active'] = $request->boolean('active');

        $original = $document->only([
            'title', 'document_number', 'description', 'category', 'status', 'valid_from', 'valid_to', 'active', 'file_path', 'original_filename', 'folder_id',
        ]);

        if ($request->hasFile('pdf')) {
            Storage::delete($document->file_path);
            $pdf = $request->file('pdf');
            $path = $pdf->storeAs('documents', $document->system_identifier . '.pdf');

            $data['file_path'] = $path;
            $data['original_filename'] = $pdf->getClientOriginalName();
        }

        $document->update($data);

        $changes = [];
        foreach ($original as $key => $value) {
            if ($document->getAttribute($key) != $value) {
                $changes[$key] = [
                    'old' => $this->formatHistoryValue($key, $value),
                    'new' => $this->formatHistoryValue($key, $document->getAttribute($key)),
                ];
            }
        }

        if (! empty($changes)) {
            DocumentHistory::create([
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'changes' => $changes,
            ]);
        }

        return redirect($this->resolveReturnUrl($request))
            ->with('success', 'Dokument został zaktualizowany.');
    }

    public function destroy(Document $document)
    {
        if (! empty($document->file_path) && Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        $document->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('documents.document_deleted'),
            ]);
        }

        return redirect()->route('documents.index')->with('success', __('documents.document_deleted'));
    }

    public function preview(Document $document)
    {
        return view('documents.preview', [
            'document' => $document,
            'publicView' => false,
        ]);
    }

    /**
     * Public preview for regular users — only when document is active and inside valid range.
     */
    public function previewPublic(Document $document)
    {
        if (! $this->isVisibleToPublic($document)) {
            abort(403);
        }
        return view('documents.preview', [
            'document' => $document,
            'publicView' => true,
        ]);
    }

    public function file(Document $document)
    {
        $path = Storage::path($document->file_path);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"',
        ]);
    }

    public function download(Document $document)
    {
        return Storage::download($document->file_path, $document->original_filename);
    }

    /**
     * Public download for regular users — only when document is active and inside valid range.
     */
    public function downloadPublic(Document $document)
    {
        if (! $this->isVisibleToPublic($document)) {
            abort(403);
        }

        return $this->download($document);
    }

    public function filePublic(Document $document)
    {
        if (! $this->isVisibleToPublic($document)) {
            abort(403);
        }

        return $this->file($document);
    }

    protected function isVisibleToPublic(Document $document): bool
    {
        if (! $document->active) {
            return false;
        }

        $now = now();

        if ($document->valid_from && $document->valid_from->gt($now)) {
            return false;
        }

        if ($document->valid_to && $document->valid_to->lt($now)) {
            return false;
        }

        return true;
    }

    public function move(Request $request, Document $document)
    {
        $validated = $request->validate([
            'folder_id' => ['nullable', 'exists:folders,id'],
        ]);

        if (! empty($validated['folder_id'])) {
            $folder = Folder::where('id', $validated['folder_id'])
                ->where('user_id', Auth::id())
                ->first();

            if (! $folder) {
                return response()->json(['error' => __('documents.folder_unauthorized')], 403);
            }
        }

        $originalFolderId = $document->folder_id;
        $newFolderId = $validated['folder_id'] ?? null;

        $document->update(['folder_id' => $newFolderId]);

        if ($originalFolderId != $newFolderId) {
            DocumentHistory::create([
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'changes' => [
                    'folder_id' => [
                        'old' => $this->formatHistoryValue('folder_id', $originalFolderId),
                        'new' => $this->formatHistoryValue('folder_id', $newFolderId),
                    ],
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('documents.document_moved'),
            'document' => $document,
        ]);
    }

    protected function formatHistoryValue(string $field, mixed $value): mixed
    {
        if ($field === 'folder_id') {
            if (empty($value)) {
                return null;
            }

            return Folder::find($value)?->getFullPath();
        }

        if ($field === 'type' && $value !== null) {
            return __('documents.types.' . $value);
        }

        if ($field === 'source_document_id') {
            if (empty($value)) {
                return null;
            }

            return Document::find($value)?->title;
        }

        return $value;
    }

    protected function validateDocumentData(Request $request, bool $pdfRequired): array
    {
        $categoryNames = DocumentCategory::pluck('name')->all();
        $statusNames = DocumentStatus::pluck('name')->all();

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_number' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', Rule::in($categoryNames)],
            'status' => ['required', 'string', Rule::in($statusNames)],
            'type' => ['nullable', 'string', Rule::in(Document::types())],
            'source_document_id' => ['nullable', 'integer', 'exists:documents,id'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date'],
            'active' => ['boolean'],
            'folder_id' => ['nullable', 'exists:folders,id'],
            'pdf' => [$pdfRequired ? 'required' : 'nullable', 'file', 'mimetypes:application/pdf', 'max:10240'],
        ]);
    }

    protected function isPdfRequiredForRequest(string $type, ?int $sourceDocumentId): bool
    {
        if ($type === Document::TYPE_DOCUMENT || empty($sourceDocumentId)) {
            return true;
        }

        $sourceDocument = Document::find($sourceDocumentId);

        return ! $sourceDocument || empty($sourceDocument->file_path);
    }

    protected function createDocument(array $data, Request $request, string $type, ?int $sourceDocumentId = null): Document
    {
        if (! in_array($type, Document::types(), true)) {
            $type = Document::TYPE_DOCUMENT;
        }

        if ($type === Document::TYPE_DOCUMENT) {
            $sourceDocumentId = null;
        }

        $document = Document::create(array_merge($data, [
            'type' => $type,
            'source_document_id' => $sourceDocumentId,
            'active' => $request->boolean('active'),
            'created_by' => Auth::id(),
            'file_path' => '',
            'original_filename' => '',
        ]));

        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $path = $pdf->storeAs('documents', $document->system_identifier . '.pdf');

            $document->update([
                'file_path' => $path,
                'original_filename' => $pdf->getClientOriginalName(),
            ]);

            return $document;
        }

        if ($sourceDocumentId !== null) {
            $sourceDocument = Document::findOrFail($sourceDocumentId);
            $path = 'documents/' . $document->system_identifier . '.pdf';
            Storage::copy($sourceDocument->file_path, $path);

            $document->update([
                'file_path' => $path,
                'original_filename' => $sourceDocument->original_filename,
            ]);
        }

        return $document;
    }

    protected function applyDocumentFilters(Builder $query, array $filters): void
    {
        $search = $filters['search'] ?? '';
        $category = $filters['category'] ?? '';
        $status = $filters['status'] ?? '';
        $folderId = $filters['folder_id'] ?? '';

        if ($search !== '') {
            $query->where(function (Builder $searchQuery) use ($search) {
                $searchQuery
                    ->where('title', 'like', '%' . $search . '%')
                    ->orWhere('document_number', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('original_filename', 'like', '%' . $search . '%');
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($folderId === '__none__') {
            $query->whereNull('folder_id');
        } elseif ($folderId !== '') {
            $query->where('folder_id', $folderId);
        }
    }

    protected function buildFilteredFolderTree(
        Collection $allFolders,
        Collection $documents,
        bool $pruneEmptyFolders = true,
        ?int $selectedFolderId = null
    ): array
    {
        $folderMap = $allFolders
            ->mapWithKeys(function (Folder $folder) {
                $folder->setRelation('children', collect());
                $folder->setRelation('documents', collect());

                return [$folder->id => $folder];
            });

        $rootFolders = collect();
        foreach ($folderMap as $folder) {
            if ($folder->parent_id && $folderMap->has($folder->parent_id)) {
                $folderMap->get($folder->parent_id)->children->push($folder);
                continue;
            }

            $rootFolders->push($folder);
        }

        $rootDocuments = collect();
        foreach ($documents as $document) {
            if ($document->folder_id && $folderMap->has($document->folder_id)) {
                $folderMap->get($document->folder_id)->documents->push($document);
                continue;
            }

            $rootDocuments->push($document);
        }

        if (! $pruneEmptyFolders) {
            return [$rootFolders->values(), $rootDocuments->values()];
        }

        $prunedFolders = $rootFolders
            ->map(fn (Folder $folder) => $this->pruneEmptyFolders($folder, $selectedFolderId))
            ->filter()
            ->values();

        return [$prunedFolders, $rootDocuments->values()];
    }

    protected function pruneEmptyFolders(Folder $folder, ?int $selectedFolderId = null): ?Folder
    {
        $children = $folder->children
            ->map(fn (Folder $child) => $this->pruneEmptyFolders($child, $selectedFolderId))
            ->filter()
            ->values();

        $folder->setRelation('children', $children);

        if ($selectedFolderId !== null && $folder->id === $selectedFolderId) {
            return $folder;
        }

        if ($folder->documents->isEmpty() && $children->isEmpty()) {
            return null;
        }

        return $folder;
    }

    protected function resolveReturnUrl(Request $request): string
    {
        $returnUrl = (string) $request->input('return_url', $request->query('return_url', ''));
        $documentsIndexUrl = route('documents.index');

        if ($returnUrl !== '' && Str::startsWith($returnUrl, $documentsIndexUrl)) {
            return $returnUrl;
        }

        return $documentsIndexUrl;
    }
}
