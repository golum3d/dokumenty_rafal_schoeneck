@extends('layouts.app')

@section('title', __('documents.create_title'))

@section('content')
    <div class="space-y-8">
        <div class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="mb-6 flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-500">{{ __('documents.create_title') }}</p>
                    <h1 class="mt-3 text-3xl font-semibold text-slate-950">{{ __('documents.create_title') }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ __('documents.module_description') }}</p>
                    @if(($document->type ?? \App\Models\Document::TYPE_DOCUMENT) !== \App\Models\Document::TYPE_DOCUMENT)
                        <p class="mt-2 text-sm text-slate-600">
                            {{ __('documents.fields.type') }}: <span class="font-medium text-slate-900">{{ __('documents.types.' . $document->type) }}</span>
                            @if(!empty($sourceDocument))
                                | {{ __('documents.fields.source_document') }}: <span class="font-medium text-slate-900">{{ $sourceDocument->title }}</span>
                            @endif
                        </p>
                    @endif
                </div>

                <div class="min-w-[260px] rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">{{ __('documents.meta_title') }}</p>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('documents.fields.created_by') }}</p>
                            <p class="mt-1 font-medium text-slate-900">{{ auth()->user()?->name ?? __('documents.unknown_user') }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('documents.fields.created_at') }}</p>
                            <p class="mt-1 font-medium text-slate-900">{{ __('documents.not_saved_yet') }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('documents.fields.updated_at') }}</p>
                            <p class="mt-1 font-medium text-slate-900">{{ __('documents.not_saved_yet') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                @include('documents._form')
            </form>
        </div>
    </div>
@endsection
