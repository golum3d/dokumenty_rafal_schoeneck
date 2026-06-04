<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FolderController extends Controller
{
    /**
     * Store a newly created folder in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $validated['user_id'] = Auth::id();

        $folder = Folder::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('documents.folder_created'),
            'folder' => $folder,
        ]);
    }

    /**
     * Update the specified folder in storage.
     */
    public function update(Request $request, Folder $folder)
    {
        // Check authorization
        if ($folder->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => ['nullable', 'exists:folders,id', Rule::notIn([$folder->id])],
        ]);

        $folder->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('documents.folder_updated'),
            'folder' => $folder,
        ]);
    }

    /**
     * Remove the specified folder from storage.
     */
    public function destroy(Folder $folder)
    {
        // Check authorization
        if ($folder->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if folder is empty
        if ($folder->documents()->exists() || $folder->children()->exists()) {
            return response()->json([
                'error' => __('documents.folder_not_empty'),
            ], 422);
        }

        $folder->delete();

        return response()->json([
            'success' => true,
            'message' => __('documents.folder_deleted'),
        ]);
    }
}
