<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentHistory;
use App\Models\DocumentStatus;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        // Get root level folders and documents
        $folders = Folder::where('user_id', $userId)
            ->whereNull('parent_id')
            ->with(['children', 'documents'])
            ->orderBy('name')
            ->get();

        // Get documents not in any folder
        $documents = Document::where('folder_id', null)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        $allFolders = Folder::where('user_id', $userId)->orderBy('name')->get();

        return view('documents.index', [
            'folders' => $folders,
            'documents' => $documents,
            'allFolders' => $allFolders,
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

        return view('documents.create', [
            'document' => new Document(),
            'categories' => DocumentCategory::orderBy('name')->get(),
            'statuses' => DocumentStatus::orderBy('name')->get(),
            'folders' => $folders,
        ]);
    }

    public function store(Request $request)
    {
        $categoryNames = DocumentCategory::pluck('name')->all();
        $statusNames = DocumentStatus::pluck('name')->all();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_number' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', Rule::in($categoryNames)],
            'status' => ['required', 'string', Rule::in($statusNames)],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date'],
            'active' => ['boolean'],
            'folder_id' => ['nullable', 'exists:folders,id'],
            'pdf' => ['required', 'file', 'mimetypes:application/pdf', 'max:10240'],
        ]);

        $data['active'] = $request->boolean('active');
        $data['created_by'] = Auth::id();

        $document = Document::create(array_merge($data, [
            'file_path' => '',
            'original_filename' => '',
        ]));

        $pdf = $request->file('pdf');
        $path = $pdf->storeAs('documents', $document->system_identifier . '.pdf');

        $document->update([
            'file_path' => $path,
            'original_filename' => $pdf->getClientOriginalName(),
        ]);

        return redirect()->route('documents.index')->with('success', 'Dokument został zapisany.');
    }

    public function edit(Document $document)
    {
        $document->load('histories.user', 'creator');
        $userId = Auth::id();
        $folders = Folder::where('user_id', $userId)->orderBy('name')->get();

        return view('documents.edit', [
            'document' => $document,
            'categories' => DocumentCategory::orderBy('name')->get(),
            'statuses' => DocumentStatus::orderBy('name')->get(),
            'folders' => $folders,
        ]);
    }

    public function update(Request $request, Document $document)
    {
        $categoryNames = DocumentCategory::pluck('name')->all();
        $statusNames = DocumentStatus::pluck('name')->all();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_number' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', Rule::in($categoryNames)],
            'status' => ['required', 'string', Rule::in($statusNames)],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date'],
            'active' => ['boolean'],
            'folder_id' => ['nullable', 'exists:folders,id'],
            'pdf' => ['nullable', 'file', 'mimetypes:application/pdf', 'max:10240'],
        ]);

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
                $changes[$key] = ['old' => $value, 'new' => $document->getAttribute($key)];
            }
        }

        if (! empty($changes)) {
            DocumentHistory::create([
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'changes' => $changes,
            ]);
        }

        return redirect()->route('documents.index')->with('success', 'Dokument został zaktualizowany.');
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

        $document->update(['folder_id' => $validated['folder_id'] ?? null]);

        return response()->json([
            'success' => true,
            'message' => __('documents.document_moved'),
            'document' => $document,
        ]);
    }
}
