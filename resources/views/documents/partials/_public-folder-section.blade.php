<div class="{{ ($level ?? 0) > 0 ? 'ml-6 border-l border-slate-200 pl-4' : '' }}">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-slate-900">{{ $folder->name }}</h3>
        <span class="text-sm text-slate-500">{{ $folder->documents->count() }} plików</span>
    </div>

    <div class="space-y-3">
        @foreach($folder->documents as $document)
            @include('documents.partials._public-document-card', ['document' => $document])
        @endforeach
    </div>

    @if($folder->children->isNotEmpty())
        <div class="mt-5 space-y-5">
            @foreach($folder->children as $childFolder)
                @include('documents.partials._public-folder-section', ['folder' => $childFolder, 'level' => ($level ?? 0) + 1])
            @endforeach
        </div>
    @endif
</div>
