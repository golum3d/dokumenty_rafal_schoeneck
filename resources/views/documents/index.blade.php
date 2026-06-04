@extends('layouts.app')

@section('title', __('documents.module_title'))

@section('content')
    <div class="space-y-8">
        @if (session('success'))
            <div class="rounded-[1.75rem] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-900 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col gap-4 rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">{{ __('documents.module_title') }}</p>
                <h1 class="mt-3 text-3xl font-semibold text-slate-950">{{ __('documents.module_title') }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ __('documents.module_description') }}</p>
            </div>
            <a href="{{ route('documents.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">{{ __('documents.buttons.create') }}</a>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            @forelse($documents as $document)
                <article class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.28em] text-slate-500">{{ $document->category }}</p>
                            <h2 class="mt-2 text-xl font-semibold text-slate-950">{{ $document->title }}</h2>
                            <p class="mt-2 text-sm text-slate-600">{{ __('documents.fields.document_number') }}: <span class="font-medium text-slate-900">{{ $document->document_number }}</span></p>
                        </div>
                        <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-white">{{ $document->status }}</span>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-white p-4 text-sm text-slate-600 shadow-sm">
                            <p class="text-slate-500">{{ __('documents.fields.system_identifier') }}</p>
                            <p class="mt-2 font-medium text-slate-900">{{ $document->system_identifier }}</p>
                        </div>
                        <div class="rounded-2xl bg-white p-4 text-sm text-slate-600 shadow-sm">
                            <p class="text-slate-500">{{ __('documents.fields.active') }}</p>
                            <p class="mt-2 font-medium text-slate-900">{{ $document->active ? 'Tak' : 'Nie' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                        <p>{{ __('documents.fields.created_at') }}: {{ $document->created_at->format('Y-m-d') }}</p>
                        <p>{{ __('documents.fields.created_by') }}: {{ $document->creator?->name ?? '—' }}</p>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('documents.preview', $document) }}" class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">{{ __('documents.buttons.preview') }}</a>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('documents.download', $document) }}" class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">{{ __('documents.buttons.download') }}</a>
                            <a href="{{ route('documents.edit', $document) }}" class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">{{ __('documents.buttons.edit_document') }}</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[2rem] border border-slate-200 bg-white p-10 text-center text-slate-600 shadow-sm">
                    {{ __('documents.empty') }}
                </div>
            @endforelse
        </div>
    </div>
@endsection
