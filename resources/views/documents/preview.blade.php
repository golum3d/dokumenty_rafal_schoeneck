@extends('layouts.app')

@section('title', 'Podgląd dokumentu')

@section('content')
    <div class="space-y-8">
        <div class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Podgląd dokumentu</p>
                        <h1 class="mt-3 text-3xl font-semibold text-slate-950">{{ $document->title }}</h1>
                        <p class="mt-2 text-sm text-slate-600">Numer dokumentu: <span class="font-medium text-slate-900">{{ $document->document_number }}</span></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ url()->previous() }}" onclick="event.preventDefault(); window.history.back();" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">Powrót</a>
                        <a href="{{ route('documents.download', $document) }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Pobierz PDF</a>
                    </div>
                </div>
            <div class="h-[80vh] overflow-hidden rounded-[2rem] border border-slate-200">
                <iframe
                    src="{{ route('documents.file', $document) }}"
                    class="h-full w-full"
                    frameborder="0"
                ></iframe>
            </div>
        </div>
    </div>
@endsection
