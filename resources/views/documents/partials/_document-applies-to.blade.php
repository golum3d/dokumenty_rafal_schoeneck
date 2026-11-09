@if($document->type !== \App\Models\Document::TYPE_DOCUMENT)
    @php
        $affectedDocuments = $document->connectedDocuments()->reject(fn ($member) => $member->id === $document->id);
    @endphp
    @if($affectedDocuments->isNotEmpty())
        <div class="mt-2 text-xs text-slate-600">
            <span class="font-semibold">Dotyczy całego zestawu:</span>
            <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                @foreach($affectedDocuments as $member)
                    <a href="{{ !empty($publicView) ? route('documents.preview_public', $member) : route('documents.preview', $member) }}" class="underline decoration-slate-300 underline-offset-2 hover:text-slate-900">
                        {{ $member->title }}@if($member->document_number) ({{ $member->document_number }})@endif
                    </a>
                    @unless($loop->last)<span aria-hidden="true">→</span>@endunless
                @endforeach
            </span>
        </div>
    @endif
@endif
