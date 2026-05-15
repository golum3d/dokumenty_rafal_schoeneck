<?php

namespace App\Http\Controllers;

use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentCategoryController extends Controller
{
    public function index()
    {
        return view('documents.categories.index', [
            'categories' => DocumentCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('document_categories', 'name')],
        ]);

        DocumentCategory::create([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('documents.categories.index')->with('success', 'Kategoria została dodana.');
    }

    public function edit(DocumentCategory $category)
    {
        return view('documents.categories.index', [
            'categories' => DocumentCategory::orderBy('name')->get(),
            'editCategory' => $category,
        ]);
    }

    public function update(Request $request, DocumentCategory $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('document_categories', 'name')->ignore($category->id)],
        ]);

        $category->update(['name' => $request->input('name')]);

        return redirect()->route('documents.categories.index')->with('success', 'Kategoria została zaktualizowana.');
    }

    public function destroy(DocumentCategory $category)
    {
        $category->delete();

        return redirect()->route('documents.categories.index')->with('success', 'Kategoria została usunięta.');
    }
}
