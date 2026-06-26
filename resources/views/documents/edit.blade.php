@extends('layouts.app')

@section('title', __('documents.edit_title'))

@section('content')
    @php
        [$metaCardClass, $metaBorderClass] = match ($document->relationColorType()) {
            \App\Models\Document::TYPE_CHANGE => ['bg-amber-50', 'border-amber-200'],
            \App\Models\Document::TYPE_REPEAL => ['bg-rose-50', 'border-rose-200'],
            default => ['bg-slate-50', 'border-slate-200'],
        };
    @endphp
    <div class="space-y-8">
        <div class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="mb-6 flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-500">{{ __('documents.edit_title') }}</p>
                    <h1 class="mt-3 text-3xl font-semibold text-slate-950">{{ $document->title }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ __('documents.fields.system_identifier') }}: <span class="font-medium text-slate-900">{{ $document->system_identifier }}</span></p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('documents.preview', $document) }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">{{ __('documents.buttons.preview') }}</a>
                        <a href="{{ route('documents.download', $document) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">{{ __('documents.buttons.download') }}</a>
                        <a href="{{ old('return_url', $returnUrl ?? route('documents.index')) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">{{ __('documents.buttons.back') }}</a>
                    </div>
                </div>

                <div class="min-w-[260px] rounded-[1.5rem] border {{ $metaBorderClass }} {{ $metaCardClass }} px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">{{ __('documents.meta_title') }}</p>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('documents.fields.created_by') }}</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $document->creator?->name ?? __('documents.unknown_user') }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('documents.fields.created_at') }}</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $document->created_at?->format('Y-m-d H:i') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('documents.fields.updated_at') }}</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $document->updated_at?->format('Y-m-d H:i') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('documents.fields.type') }}</p>
                            <p class="mt-1 font-medium text-slate-900">{{ __('documents.types.' . $document->type) }}</p>
                        </div>
                        @if($document->type !== \App\Models\Document::TYPE_DOCUMENT)
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('documents.fields.source_document') }}</p>
                                <p class="mt-1 font-medium text-slate-900">
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
                        @if($document->type === \App\Models\Document::TYPE_DOCUMENT && $document->derivedDocuments->isNotEmpty())
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('documents.related_documents') }}</p>
                                <div class="mt-2 space-y-2">
                                    @foreach($document->derivedDocuments as $relatedDocument)
                                        @php
                                            [$relatedBgClass, $relatedBorderClass, $relatedHoverClass] = match ($relatedDocument->relationColorType()) {
                                                \App\Models\Document::TYPE_CHANGE => ['bg-amber-50/80', 'border-amber-200', 'hover:bg-amber-100/80'],
                                                \App\Models\Document::TYPE_REPEAL => ['bg-rose-50/80', 'border-rose-200', 'hover:bg-rose-100/80'],
                                                default => ['bg-white/70', 'border-slate-200', 'hover:bg-white'],
                                            };
                                        @endphp
                                        <a href="{{ route('documents.edit', ['document' => $relatedDocument, 'return_url' => url()->full()]) }}" class="block rounded-xl border {{ $relatedBorderClass }} {{ $relatedBgClass }} px-3 py-2 transition {{ $relatedHoverClass }}">
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
            </div>

            <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data">
                @method('PUT')
                @include('documents._form')
            </form>
        </div>

        @if($document->histories->isNotEmpty())
            <section class="rounded-[2rem] bg-slate-50 p-8 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-2xl font-semibold text-slate-950">{{ __('documents.history') }}</h2>
                <div class="mt-6 space-y-4">
                    @foreach($document->histories as $history)
                        <div class="rounded-2xl bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $history->user?->name ?? __('documents.unknown_user') }}</p>
                                    <p class="text-sm text-slate-500">{{ $history->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>

                            <div class="mt-4 space-y-3 text-sm text-slate-600">
                                @foreach($history->changes as $field => $change)
                                    @php
                                        $fieldKey = 'documents.fields.' . $field;
                                    @endphp
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="font-semibold text-slate-900">{{ Lang::has($fieldKey) ? __($fieldKey) : ucfirst(str_replace('_', ' ', $field)) }}</p>
                                    <p class="mt-2 text-slate-600">{{ __('documents.change_from') }} <span class="font-medium text-slate-900">{{ $change['old'] ?? '–' }}</span></p>
                                    <p class="text-slate-600">{{ __('documents.change_to') }} <span class="font-medium text-slate-900">{{ $change['new'] ?? '–' }}</span></p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
