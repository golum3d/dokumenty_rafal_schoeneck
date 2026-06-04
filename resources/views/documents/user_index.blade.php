@extends('layouts.app')

@section('title', __('documents.module_title'))

@section('content')
    <div class="space-y-8">
        <section class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Najnowsze ważne pliki</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Dostępne pliki</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">{{ $documents->count() }} plików</span>
            </div>

            @if($documents->isEmpty())
                <div class="mt-6 rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-600">
                    Brak dostępnych plików.
                </div>
            @else
                <div class="mt-6 space-y-3">
                    @foreach($documents as $document)
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $document->title }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $document->document_number }} • {{ $document->category }} • {{ $document->status }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Ważne: @if($document->valid_from) {{ $document->valid_from->format('d.m.Y') }} @else - @endif — @if($document->valid_to) {{ $document->valid_to->format('d.m.Y') }} @else - @endif</p>
                                </div>

                                <div class="flex gap-2 items-center">
                                    <a href="{{ route('documents.preview_public', $document) }}" class="text-slate-500 hover:text-slate-700 p-2">Podgląd</a>
                                    <a href="{{ route('documents.download_public', $document) }}" class="text-slate-500 hover:text-slate-700 p-2">Pobierz</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
