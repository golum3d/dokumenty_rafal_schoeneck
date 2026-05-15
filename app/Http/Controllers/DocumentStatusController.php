<?php

namespace App\Http\Controllers;

use App\Models\DocumentStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentStatusController extends Controller
{
    public function index()
    {
        return view('documents.statuses.index', [
            'statuses' => DocumentStatus::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('document_statuses', 'name')],
        ]);

        DocumentStatus::create([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('documents.statuses.index')->with('success', 'Status został dodany.');
    }

    public function edit(DocumentStatus $status)
    {
        return view('documents.statuses.index', [
            'statuses' => DocumentStatus::orderBy('name')->get(),
            'editStatus' => $status,
        ]);
    }

    public function update(Request $request, DocumentStatus $status)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('document_statuses', 'name')->ignore($status->id)],
        ]);

        $status->update(['name' => $request->input('name')]);

        return redirect()->route('documents.statuses.index')->with('success', 'Status został zaktualizowany.');
    }

    public function destroy(DocumentStatus $status)
    {
        $status->delete();

        return redirect()->route('documents.statuses.index')->with('success', 'Status został usunięty.');
    }
}
