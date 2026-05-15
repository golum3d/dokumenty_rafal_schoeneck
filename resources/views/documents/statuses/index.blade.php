@extends('layouts.app')

@section('title', __('document_statuses.page_title'))

@section('content')
    <div class="space-y-8">
        @if (session('success'))
            <div class="rounded-[1.75rem] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-900 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.32em] text-slate-500">{{ __('document_statuses.page_title') }}</p>
                    <h1 class="mt-3 text-3xl font-semibold text-slate-950">{{ __('document_statuses.manage_title') }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ __('document_statuses.description') }}</p>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                <section class="space-y-6">
                    @if($statuses->isEmpty())
                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-8 text-center text-slate-600">
                            {{ __('document_statuses.empty') }}
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($statuses as $status)
                                <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                    <span class="text-sm text-slate-800">{{ $status->name }}</span>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('documents.statuses.edit', $status) }}" class="rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition hover:border-slate-400 hover:bg-slate-100">{{ __('document_statuses.buttons.edit') }}</a>
                                        <form method="POST" action="{{ route('documents.statuses.destroy', $status) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Usuń</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <aside class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6">
                    @if(isset($editStatus))
                        <h2 class="text-xl font-semibold text-slate-950">{{ __('document_statuses.edit_title') }}</h2>
                        <form method="POST" action="{{ route('documents.statuses.update', $editStatus) }}" class="mt-6 space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-sm font-medium text-slate-700">{{ __('document_statuses.label_name') }}</label>
                                <input name="name" value="{{ old('name', $editStatus->name) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                                @error('name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <button type="submit" class="inline-flex justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">{{ __('document_statuses.buttons.save') }}</button>
                                <a href="{{ route('documents.statuses.index') }}" class="inline-flex justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">{{ __('document_statuses.buttons.cancel') }}</a>
                            </div>
                        </form>
                    @else
                        <h2 class="text-xl font-semibold text-slate-950">{{ __('document_statuses.add_title') }}</h2>
                        <form method="POST" action="{{ route('documents.statuses.store') }}" class="mt-6 space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-slate-700">{{ __('document_statuses.label_name') }}</label>
                                <input name="name" value="{{ old('name') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                                @error('name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="inline-flex justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">{{ __('document_statuses.buttons.add') }}</button>
                        </form>
                    @endif
                </aside>
            </div>
        </div>
    </div>
@endsection
