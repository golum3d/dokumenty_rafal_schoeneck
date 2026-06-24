@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-8">
        <section class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Panel użytkownika</p>
                    <h1 class="mt-3 text-3xl font-semibold text-slate-950">Witaj ponownie!</h1>
                    @auth
                        <p class="mt-2 text-sm text-slate-600">Jesteś zalogowany jako <span class="font-medium text-slate-900">{{ auth()->user()->email }}</span>.</p>
                    @else
                        <p class="mt-2 text-sm text-slate-600">Przeglądasz publiczny widok dashboardu bez logowania.</p>
                    @endauth
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
                        @php
                            [$cardBgClass, $cardBorderClass] = match ($document->type) {
                                \App\Models\Document::TYPE_CHANGE => ['bg-amber-50/70', 'border-amber-200'],
                                \App\Models\Document::TYPE_REPEAL => ['bg-rose-50/70', 'border-rose-200'],
                                default => ['bg-slate-50', 'border-slate-200'],
                            };
                        @endphp
                        <div class="rounded-3xl border {{ $cardBorderClass }} {{ $cardBgClass }} p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-900">{{ $document->title }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $document->document_number }} • {{ __('documents.types.' . $document->type) }} • {{ $document->category }} • {{ $document->status }}</p>
                                    @if($document->type !== \App\Models\Document::TYPE_DOCUMENT && $document->sourceDocument)
                                        <p class="mt-1 text-sm text-slate-500">
                                            Dotyczy: {{ $document->sourceDocument->title }}
                                            @if(!empty($document->sourceDocument->document_number))
                                                ({{ $document->sourceDocument->document_number }})
                                            @endif
                                        </p>
                                    @endif
                                    <p class="mt-1 text-sm text-slate-600">Opis: {{ $document->description }}</p>
                                </div>
                                <div class="flex items-center gap-4 flex-shrink-0">
                                    <div class="text-sm text-slate-500">{{ $document->created_at->format('d.m.Y H:i') }}</div>
                                    <div class="flex gap-2 items-center">
                                        <a href="{{ $canManageDocuments ? route('documents.preview', $document) : route('documents.preview_public', $document) }}" class="text-slate-500 hover:text-slate-700 p-1" aria-label="{{ __('documents.buttons.preview') }}">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 3C5 3 1.73 7.11.46 9.04a1.67 1.67 0 000 1.92C1.73 12.89 5 17 10 17s8.27-4.11 9.54-6.04a1.67 1.67 0 000-1.92C18.27 7.11 15 3 10 3zm0 11a4 4 0 110-8 4 4 0 010 8zm0-2.5A1.5 1.5 0 1010 8a1.5 1.5 0 000 3.5z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ $canManageDocuments ? route('documents.download', $document) : route('documents.download_public', $document) }}" class="text-slate-500 hover:text-slate-700 p-1" aria-label="{{ __('documents.buttons.download') }}">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        
    </div>
@endsection
