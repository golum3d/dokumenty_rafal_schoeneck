@extends('layouts.app')

@section('title', __('documents.module_title'))

@section('content')
    @php
        $countFolderDocuments = function ($folders) use (&$countFolderDocuments) {
            return $folders->sum(fn ($folder) => $folder->documents->count() + $countFolderDocuments($folder->children));
        };

        $visibleDocumentsCount = $countFolderDocuments($folders) + $noFolderDocuments->count();
    @endphp
    <div class="space-y-8">
        <section class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Najnowsze ważne pliki</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Dostępne pliki</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">{{ $visibleDocumentsCount }} plików</span>
            </div>

            <form method="GET" action="{{ route('documents.user_index') }}" class="mt-6 border-t border-slate-200 pt-5">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(220px,0.8fr)]">
                    <div>
                        <label for="search" class="block text-sm font-medium text-slate-700">{{ __('documents.filters.search') }}</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="{{ __('documents.filters.search_placeholder') }}"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                        />
                    </div>
                    <div>
                        <label for="relation_state" class="block text-sm font-medium text-slate-700">{{ __('documents.filters.relation_state') }}</label>
                        <select
                            id="relation_state"
                            name="relation_state"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                        >
                            <option value="">{{ __('documents.filters.all_relation_states') }}</option>
                            @foreach($relationStates as $relationStateValue => $relationStateLabel)
                                <option value="{{ $relationStateValue }}" @selected(($filters['relation_state'] ?? '') === $relationStateValue)>{{ $relationStateLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-3">
                    <div>
                        <label for="category" class="block text-sm font-medium text-slate-700">{{ __('documents.fields.category') }}</label>
                        <select
                            id="category"
                            name="category"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                        >
                            <option value="">{{ __('documents.filters.all_categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}" @selected(($filters['category'] ?? '') === $category->name)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700">{{ __('documents.fields.status') }}</label>
                        <select
                            id="status"
                            name="status"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                        >
                            <option value="">{{ __('documents.filters.all_statuses') }}</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->name }}" @selected(($filters['status'] ?? '') === $status->name)>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="folder_id" class="block text-sm font-medium text-slate-700">{{ __('documents.fields.folder') }}</label>
                        <select
                            id="folder_id"
                            name="folder_id"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                        >
                            <option value="">{{ __('documents.filters.all_folders') }}</option>
                            <option value="__none__" @selected(($filters['folder_id'] ?? '') === '__none__')>{{ __('documents.no_folder') }}</option>
                            @foreach($allFolders as $folder)
                                <option value="{{ $folder->id }}" @selected((string) ($filters['folder_id'] ?? '') === (string) $folder->id)>{{ $folder->getFullPath() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                        {{ __('documents.filters.submit') }}
                    </button>
                    <a href="{{ route('documents.user_index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">
                        {{ __('documents.filters.reset') }}
                    </a>
                </div>
            </form>

            {{-- Debug info: helpful while troubleshooting empty lists --}}
            {{-- <div class="mt-3 text-sm text-slate-500">
                <span class="mr-4">Foldery: {{ $folders->count() }}</span>
                <span class="mr-4">Pliki w folderach: {{ $folders->sum(function($f){ return $f->documents->count(); }) }}</span>
                <span>Pliki bez folderu: {{ $noFolderDocuments->count() }}</span>
            </div> --}}

            @if($folders->isEmpty() && $noFolderDocuments->isEmpty())
                <div class="mt-6 rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-600">
                    Brak dostępnych plików.
                </div>
            @else
                <div class="mt-6 space-y-6">
                    @foreach($folders as $folder)
                        @include('documents.partials._public-folder-section', ['folder' => $folder, 'level' => 0])
                    @endforeach

                    @if($noFolderDocuments->isNotEmpty())
                        <div>
                            <div class="mb-3">
                                <h3 class="text-lg font-semibold text-slate-900">Bez folderu</h3>
                                <span class="text-sm text-slate-500">{{ $noFolderDocuments->count() }} plików</span>
                            </div>

                            <div class="space-y-3">
                                @foreach($noFolderDocuments as $document)
                                    @include('documents.partials._public-document-card', ['document' => $document])
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </section>
    </div>
@endsection
