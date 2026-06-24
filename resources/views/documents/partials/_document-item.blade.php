@php
    [$rowClass, $hoverClass] = match ($document->type) {
        \App\Models\Document::TYPE_CHANGE => ['bg-amber-50/70', 'hover:bg-amber-100/70'],
        \App\Models\Document::TYPE_REPEAL => ['bg-rose-50/70', 'hover:bg-rose-100/70'],
        default => ['bg-white', 'hover:bg-slate-50'],
    };
@endphp

<div class="border-b border-slate-200 last:border-b-0 {{ $rowClass }}" style="margin-left: {{ ($level ?? 0) * 24 }}px;">
    <div class="flex items-center gap-3 px-6 py-4 transition-colors {{ $hoverClass }}"
         @if($canManageDocuments)
             draggable="true"
             data-drag-type="document"
             data-drag-id="{{ $document->id }}"
             ondragstart="onDragStart(event)"
             ondragend="onDragEnd(event)"
         @endif>
        <i class="fa-regular fa-file-pdf text-slate-600 text-lg"></i>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-900 truncate">{{ $document->title }}</p>
            <p class="text-xs text-slate-500">{{ $document->document_number }} • {{ __('documents.types.' . $document->type) }} • {{ $document->category }} • {{ $document->status }}</p>
            <p class="mt-1 text-xs text-slate-500 line-clamp-2">Opis: {{ $document->description }}</p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <a href="{{ $canManageDocuments ? route('documents.preview', $document) : route('documents.preview_public', $document) }}" class="text-slate-500 hover:text-slate-700 p-1" aria-label="{{ __('documents.buttons.preview') }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 3C5 3 1.73 7.11.46 9.04a1.67 1.67 0 000 1.92C1.73 12.89 5 17 10 17s8.27-4.11 9.54-6.04a1.67 1.67 0 000-1.92C18.27 7.11 15 3 10 3zm0 11a4 4 0 110-8 4 4 0 010 8zm0-2.5A1.5 1.5 0 1010 8a1.5 1.5 0 000 3.5z"></path>
                </svg>
            </a>
            <a href="{{ $canManageDocuments ? route('documents.download', $document) : route('documents.download_public', $document) }}" class="text-slate-500 hover:text-slate-700 p-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </a>
            @if($canManageDocuments)
                <a href="{{ route('documents.edit', ['document' => $document, 'return_url' => url()->full()]) }}" class="text-slate-500 hover:text-slate-700 p-1" aria-label="{{ __('documents.buttons.edit_document') }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                </a>
                <button type="button" onclick="deleteDocument(event, {{ $document->id }})" class="text-slate-500 hover:text-rose-600 p-1" aria-label="{{ __('documents.buttons.delete') }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            @endif
        </div>
    </div>
</div>
