@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-8">
        <section class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Panel użytkownika</p>
                    <h1 class="mt-3 text-3xl font-semibold text-slate-950">Witaj ponownie!</h1>
                    <p class="mt-2 text-sm text-slate-600">Jesteś zalogowany jako <span class="font-medium text-slate-900">{{ auth()->user()->email }}</span>.</p>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Najnowsze pliki</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Dodane w ciągu ostatnich 7 dni</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">{{ $recentDocuments->count() }} plików</span>
            </div>

            @if($recentDocuments->isEmpty())
                <div class="mt-6 rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-600">
                    Brak plików dodanych w ciągu ostatnich 7 dni.
                </div>
            @else
                <div class="mt-6 space-y-3">
                    @foreach($recentDocuments as $document)
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $document->title }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $document->document_number }} • {{ $document->category }} • {{ $document->status }}</p>
                                </div>
                                <div class="text-sm text-slate-500">{{ $document->created_at->format('d.m.Y H:i') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        
    </div>
@endsection
