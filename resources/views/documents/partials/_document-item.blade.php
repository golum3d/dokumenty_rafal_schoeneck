<div class="border-b border-slate-200 last:border-b-0" style="margin-left: {{ ($level ?? 0) * 24 }}px;">
    <div class="flex items-center gap-3 px-6 py-4 hover:bg-slate-50">
        <svg class="w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
            <path d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7H4.458c-.133 0-.263.044-.371.125L2.567 8.99a.5.5 0 00.048.65l1.414 1.414a.5.5 0 00.707 0l1.414-1.414a.5.5 0 00-.707-.707L5.378 9h9.122a2 2 0 012 2v2a2 2 0 01-2 2H5v2a2 2 0 102 2h10a2 2 0 002-2V9a2 2 0 00-2-2z"></path>
        </svg>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-900 truncate">{{ $document->title }}</p>
            <p class="text-xs text-slate-500">{{ $document->category }} • {{ $document->status }}</p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <a href="{{ route('documents.download', $document) }}" class="text-slate-500 hover:text-slate-700 p-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </a>
            <a href="{{ route('documents.edit', $document) }}" class="text-slate-500 hover:text-slate-700 p-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                </svg>
            </a>
        </div>
    </div>
</div>
