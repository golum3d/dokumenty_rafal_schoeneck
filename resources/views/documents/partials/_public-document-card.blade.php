@php
    [$cardBgClass, $cardBorderClass] = match ($document->relationColorType()) {
        \App\Models\Document::TYPE_CHANGE => ['bg-amber-50/70', 'border-amber-200'],
        \App\Models\Document::TYPE_REPEAL => ['bg-rose-50/70', 'border-rose-200'],
        default => ['bg-slate-50', 'border-slate-200'],
    };
@endphp

<div class="rounded-3xl border {{ $cardBorderClass }} {{ $cardBgClass }} p-4 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-slate-900">{{ $document->title }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $document->document_number }} • {{ __('documents.types.' . $document->type) }} • {{ $document->category }} • {{ $document->status }}</p>
            @if($document->type !== \App\Models\Document::TYPE_DOCUMENT && $document->sourceDocument)
                <p class="mt-1 text-sm text-slate-500">
                    Dotyczy: {{ $document->sourceDocument->title }}
                    @if(!empty($document->sourceDocument->document_number))
                        ({{ $document->sourceDocument->document_number }})
                    @endif
                </p>
            @endif
            <p class="mt-1 text-sm text-slate-600">Opis: {{ $document->description }}</p>
            <p class="mt-1 text-xs text-slate-500">Ważne: @if($document->valid_from) {{ $document->valid_from->format('d.m.Y') }} @else - @endif — @if($document->valid_to) {{ $document->valid_to->format('d.m.Y') }} @else - @endif</p>
        </div>

        <div class="flex gap-2 items-center">
            <a href="{{ route('documents.preview_public', $document) }}" class="text-slate-500 hover:text-slate-700 p-2" aria-label="{{ __('documents.buttons.preview') }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 3C5 3 1.73 7.11.46 9.04a1.67 1.67 0 000 1.92C1.73 12.89 5 17 10 17s8.27-4.11 9.54-6.04a1.67 1.67 0 000-1.92C18.27 7.11 15 3 10 3zm0 11a4 4 0 110-8 4 4 0 010 8zm0-2.5A1.5 1.5 0 1010 8a1.5 1.5 0 000 3.5z"></path>
                </svg>
            </a>
            <a href="{{ route('documents.download_public', $document) }}" class="text-slate-500 hover:text-slate-700 p-2" aria-label="{{ __('documents.buttons.download') }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </a>
        </div>
    </div>
</div>
