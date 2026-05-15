@extends('layouts.app')

@section('title', 'Dodaj dokument')

@section('content')
    <div class="space-y-8">
        <div class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="mb-6">
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Dodaj dokument</p>
                <h1 class="mt-3 text-3xl font-semibold text-slate-950">Utwórz nowy rekord dokumentu</h1>
                <p class="mt-2 text-sm text-slate-600">Wypełnij metadane i załaduj plik PDF. System wygeneruje identyfikator i zapisze autora rekordu.</p>
            </div>

            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                @include('documents._form')
            </form>
        </div>
    </div>
@endsection
