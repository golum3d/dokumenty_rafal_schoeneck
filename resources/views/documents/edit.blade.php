@extends('layouts.app')

@section('title', 'Edytuj dokument')

@section('content')
    <div class="space-y-8">
        <div class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="mb-6">
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Edytuj dokument</p>
                <h1 class="mt-3 text-3xl font-semibold text-slate-950">{{ $document->title }}</h1>
                <p class="mt-2 text-sm text-slate-600">Identyfikator systemowy: <span class="font-medium text-slate-900">{{ $document->system_identifier }}</span></p>
            </div>

            <div class="mb-6 flex flex-wrap gap-3">
                <a href="{{ route('documents.preview', $document) }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Podgląd PDF</a>
                <a href="{{ route('documents.download', $document) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">Pobierz PDF</a>
            </div>

            <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data">
                @method('PUT')
                @include('documents._form')
            </form>
        </div>

        @if($document->histories->isNotEmpty())
            <section class="rounded-[2rem] bg-slate-50 p-8 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-2xl font-semibold text-slate-950">Historia zmian</h2>
                <div class="mt-6 space-y-4">
                    @foreach($document->histories as $history)
                        <div class="rounded-2xl bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $history->user?->name ?? 'Nieznany użytkownik' }}</p>
                                    <p class="text-sm text-slate-500">{{ $history->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>

                            <div class="mt-4 space-y-3 text-sm text-slate-600">
                                @foreach($history->changes as $field => $change)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="font-semibold text-slate-900">{{ ucfirst(str_replace('_', ' ', $field)) }}</p>
                                        <p class="mt-2 text-slate-600">Z: <span class="font-medium text-slate-900">{{ $change['old'] ?? '–' }}</span></p>
                                        <p class="text-slate-600">Na: <span class="font-medium text-slate-900">{{ $change['new'] ?? '–' }}</span></p>
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
