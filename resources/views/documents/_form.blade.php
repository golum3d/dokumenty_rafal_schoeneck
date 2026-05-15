@csrf

<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium text-slate-700">Tytuł dokumentu</label>
        <input
            type="text"
            name="title"
            value="{{ old('title', $document->title) }}"
            required
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
        />
        @error('title')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Numer dokumentu</label>
        <input
            type="text"
            name="document_number"
            value="{{ old('document_number', $document->document_number) }}"
            required
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
        />
        @error('document_number')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Opis</label>
        <textarea
            name="description"
            rows="4"
            required
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
        >{{ old('description', $document->description) }}</textarea>
        @error('description')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700">Kategoria</label>
            <input
                type="text"
                name="category"
                value="{{ old('category', $document->category) }}"
                required
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
            />
            @error('category')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <input
                type="text"
                name="status"
                value="{{ old('status', $document->status) }}"
                required
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
            />
            @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700">Data obowiązywania od</label>
            <input
                type="date"
                name="valid_from"
                value="{{ old('valid_from', optional($document->valid_from)->format('Y-m-d')) }}"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
            />
            @error('valid_from')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Data obowiązywania do</label>
            <input
                type="date"
                name="valid_to"
                value="{{ old('valid_to', optional($document->valid_to)->format('Y-m-d')) }}"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
            />
            @error('valid_to')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
        <div>
            <label class="block text-sm font-medium text-slate-700">Plik PDF</label>
            <input
                type="file"
                name="pdf"
                accept="application/pdf"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                {{ $document->exists ? '' : 'required' }}
            />
            @error('pdf')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            @if($document->exists)
                <p class="mt-2 text-sm text-slate-500">Aktualny plik: {{ $document->original_filename }}</p>
            @endif
        </div>

        <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <input type="checkbox" name="active" value="1" {{ old('active', $document->active) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
            Aktywny
        </label>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <button type="submit" class="inline-flex justify-center rounded-2xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
            {{ $document->exists ? 'Zapisz zmiany' : 'Utwórz dokument' }}
        </button>
        <a href="{{ route('documents.index') }}" class="inline-flex justify-center rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">Anuluj</a>
    </div>
</div>
