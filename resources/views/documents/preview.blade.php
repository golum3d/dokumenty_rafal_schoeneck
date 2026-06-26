@extends('layouts.app')

@section('title', __('documents.preview_title'))

@section('content')
    @php
        [$metaCardClass, $metaBorderClass] = match ($document->relationColorType()) {
            \App\Models\Document::TYPE_CHANGE => ['bg-amber-50', 'border-amber-200'],
            \App\Models\Document::TYPE_REPEAL => ['bg-rose-50', 'border-rose-200'],
            default => ['bg-slate-50', 'border-slate-100'],
        };
    @endphp
    <div class="space-y-8">
        <div class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="mb-6">
                <div class="w-full rounded-2xl border {{ $metaBorderClass }} {{ $metaCardClass }} p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-500">{{ __('documents.preview_title') }}</p>
                    <h1 class="mt-3 text-3xl font-semibold text-slate-950">{{ $document->title }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ __('documents.fields.document_number') }}: <span class="font-medium text-slate-900">{{ $document->document_number }}</span></p>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-xs text-slate-500">Kategoria</p>
                            <p class="font-medium text-slate-900">{{ $document->category }}</p>

                            <p class="mt-3 text-xs text-slate-500">Status</p>
                            <p class="font-medium text-slate-900">{{ $document->status }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs text-slate-500">Opis</p>
                            <p class="text-sm text-slate-700">{{ $document->description }}</p>

                            <p class="mt-3 text-xs text-slate-500">Ważne od — do</p>
                            <p class="font-medium text-slate-900 text-sm">@if($document->valid_from) {{ $document->valid_from->format('d.m.Y') }} @else - @endif — @if($document->valid_to) {{ $document->valid_to->format('d.m.Y') }} @else - @endif</p>

                            <p class="mt-3 text-xs text-slate-500">Plik</p>
                            <p class="font-medium text-slate-900">{{ $document->original_filename }}</p>

                            <div class="mt-3 grid gap-4 md:grid-cols-2">
                                <div>
                                    <p class="text-xs text-slate-500">{{ __('documents.fields.type') }}</p>
                                    <p class="font-medium text-slate-900">{{ __('documents.types.' . $document->type) }}</p>
                                </div>

                                @if($document->type !== \App\Models\Document::TYPE_DOCUMENT)
                                    <div>
                                        <p class="text-xs text-slate-500">{{ __('documents.fields.source_document') }}</p>
                                        <p class="font-medium text-slate-900">
                                            @if($document->sourceDocument)
                                                {{ $document->sourceDocument->title }}
                                                @if(!empty($document->sourceDocument->document_number))
                                                    <span class="text-slate-500">({{ $document->sourceDocument->document_number }})</span>
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if($document->type === \App\Models\Document::TYPE_DOCUMENT && $document->derivedDocuments->isNotEmpty())
                                <div class="mt-4">
                                    <p class="text-xs text-slate-500">{{ __('documents.related_documents') }}</p>
                                    <div class="mt-2 space-y-2">
                                        @foreach($document->derivedDocuments as $relatedDocument)
                                            @php
                                                [$relatedBgClass, $relatedBorderClass, $relatedHoverClass] = match ($relatedDocument->relationColorType()) {
                                                    \App\Models\Document::TYPE_CHANGE => ['bg-amber-50/80', 'border-amber-200', 'hover:bg-amber-100/80'],
                                                    \App\Models\Document::TYPE_REPEAL => ['bg-rose-50/80', 'border-rose-200', 'hover:bg-rose-100/80'],
                                                    default => ['bg-white/70', 'border-slate-200', 'hover:bg-white'],
                                                };
                                            @endphp
                                            <a href="{{ !empty($publicView) ? route('documents.preview_public', $relatedDocument) : route('documents.preview', $relatedDocument) }}" class="block rounded-xl border {{ $relatedBorderClass }} {{ $relatedBgClass }} px-3 py-2 transition {{ $relatedHoverClass }}">
                                                <p class="text-sm font-medium text-slate-900">
                                                    {{ $relatedDocument->title }}
                                                    @if(!empty($relatedDocument->document_number))
                                                        <span class="text-slate-500">({{ $relatedDocument->document_number }})</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-slate-500">{{ __('documents.types.' . $relatedDocument->type) }}</p>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 flex gap-2">
                        <a href="{{ url()->previous() }}" onclick="event.preventDefault(); window.history.back();" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">Powrót</a>
                        @if(!empty($publicView))
                            <a href="{{ route('documents.download_public', $document) }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Pobierz</a>
                        @else
                            <a href="{{ route('documents.download', $document) }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Pobierz</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="h-[90vh] overflow-hidden rounded-[2rem] border border-slate-200">
                <iframe
                    src="{{ !empty($publicView) ? route('documents.file_public', $document) : route('documents.file', $document) }}"
                    class="h-full w-full"
                    frameborder="0"
                ></iframe>
            </div>
        </div>
    </div>
@endsection
