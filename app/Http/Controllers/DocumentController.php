<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentHistory;
use App\Models\DocumentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with('creator')->orderBy('created_at', 'desc')->get();

        return view('documents.index', [
            'documents' => $documents,
        ]);
    }

    public function create()
    {
        return view('documents.create', [
            'document' => new Document(),
            'categories' => DocumentCategory::orderBy('name')->get(),
            'statuses' => DocumentStatus::orderBy('name')->get(),
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

        return view('documents.edit', [
            'document' => $document,
            'categories' => DocumentCategory::orderBy('name')->get(),
            'statuses' => DocumentStatus::orderBy('name')->get(),
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
            'pdf' => ['nullable', 'file', 'mimetypes:application/pdf', 'max:10240'],
        ]);

        $data['active'] = $request->boolean('active');

        $original = $document->only([
            'title', 'document_number', 'description', 'category', 'status', 'valid_from', 'valid_to', 'active', 'file_path', 'original_filename',
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
}
