<div class="border-b border-slate-200 last:border-b-0">
    <div class="flex items-center gap-3 px-6 py-4 hover:bg-slate-50 cursor-pointer"
         draggable="true"
         data-drag-type="folder"
         data-drag-id="{{ $folder->id }}"
         ondragstart="onDragStart(event)"
         ondragend="onDragEnd(event)"
         ondragover="onDragOver(event)"
         ondragleave="onDragLeave(event)"
         ondrop="onDrop(event)"
         data-folder-id="{{ $folder->id }}"
         onclick="toggleFolder({{ $folder->id }})">
        <span id="folder-toggle-{{ $folder->id }}" class="text-slate-500 font-bold w-4">{{ $folder->children->count() > 0 || $folder->documents->count() > 0 ? '▼' : '•' }}</span>
        <svg class="w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
        </svg>
        <div class="flex-1">
            <p class="text-sm font-medium text-slate-900">{{ $folder->name }}</p>
            <p class="text-xs text-slate-500">{{ $folder->documents->count() }} {{ __('documents.documents') }}</p>
        </div>
        <div class="flex gap-2" onclick="event.stopPropagation();">
            <button onclick='openFolderModal({ id: {{ $folder->id }}, name: @json($folder->name), parent_id: {{ $folder->parent_id ?? 'null' }}, isEdit: true })' class="text-slate-500 hover:text-slate-700 p-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.414 2.586a2 2 0 010 2.828L8.414 14.414a1 1 0 01-.442.263l-4 1a1 1 0 01-1.213-1.213l1-4a1 1 0 01.263-.442L14.586 2.586a2 2 0 012.828 0zM15.5 4.5L6 14l-.5 2 2-.5L17.5 6.5 15.5 4.5z"></path>
                </svg>
            </button>
            <button onclick='openFolderModal({ parent_id: {{ $folder->id }} })' class="text-slate-500 hover:text-slate-700 p-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
            </button>
            <button onclick="deleteFolder({{ $folder->id }})" class="text-slate-500 hover:text-rose-600 p-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </div>

    <div id="folder-content-{{ $folder->id }}" class="bg-slate-50 drop-target"
         ondragover="onDragOver(event)"
         ondragleave="onDragLeave(event)"
         ondrop="onDrop(event)"
         data-folder-id="{{ $folder->id }}">
        {{-- Nested folders --}}
        @forelse($folder->children as $childFolder)
            @include('documents.partials._folder-item', ['folder' => $childFolder, 'level' => ($level ?? 0) + 1])
        @empty
        @endforelse

        {{-- Documents in this folder --}}
        @forelse($folder->documents as $document)
            @include('documents.partials._document-item', ['document' => $document, 'level' => ($level ?? 0) + 1])
        @empty
        @endforelse
    </div>
</div>
