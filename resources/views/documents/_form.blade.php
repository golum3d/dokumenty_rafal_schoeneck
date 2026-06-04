@csrf

<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('documents.fields.title') }}</label>
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
        <label class="block text-sm font-medium text-slate-700">{{ __('documents.fields.document_number') }}</label>
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
        <label class="block text-sm font-medium text-slate-700">{{ __('documents.fields.description') }}</label>
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
            <label class="block text-sm font-medium text-slate-700">{{ __('documents.fields.category') }}</label>
            <select
                name="category"
                required
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
            >
                <option value="" disabled {{ old('category', $document->category) ? '' : 'selected' }}>{{ __('documents.choose_category') }}</option>
                @foreach($categories as $categoryOption)
                    <option value="{{ $categoryOption->name }}" {{ old('category', $document->category) === $categoryOption->name ? 'selected' : '' }}>{{ $categoryOption->name }}</option>
                @endforeach
            </select>
            @if($categories->isEmpty())
                <p class="mt-2 text-sm text-slate-500">{{ __('documents.no_categories') }}</p>
            @endif
            @error('category')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('documents.fields.status') }}</label>
            <select
                name="status"
                required
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
            >
                <option value="" disabled {{ old('status', $document->status) ? '' : 'selected' }}>{{ __('documents.choose_status') }}</option>
                @foreach($statuses as $statusOption)
                    <option value="{{ $statusOption->name }}" {{ old('status', $document->status) === $statusOption->name ? 'selected' : '' }}>{{ $statusOption->name }}</option>
                @endforeach
            </select>
            @if($statuses->isEmpty())
                <p class="mt-2 text-sm text-slate-500">{{ __('documents.no_statuses') }}</p>
            @endif
            @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('documents.fields.valid_from') }}</label>
            <input
                type="date"
                name="valid_from"
                value="{{ old('valid_from', optional($document->valid_from)->format('Y-m-d')) }}"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
            />
            @error('valid_from')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('documents.fields.valid_to') }}</label>
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
            <label class="block text-sm font-medium text-slate-700">{{ __('documents.fields.pdf') }}</label>
            <input
                type="file"
                name="pdf"
                accept="application/pdf"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                {{ $document->exists ? '' : 'required' }}
            />
            @error('pdf')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            @if($document->exists)
                <p class="mt-2 text-sm text-slate-500">{{ __('documents.current_file') }}: {{ $document->original_filename }}</p>
            @endif
        </div>

        <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <input type="checkbox" name="active" value="1" {{ old('active', $document->active) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
            {{ __('documents.fields.active') }}
        </label>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <button type="submit" class="inline-flex justify-center rounded-2xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
            {{ $document->exists ? __('documents.buttons.save') : __('documents.buttons.create') }}
        </button>
        <a href="{{ route('documents.index') }}" class="inline-flex justify-center rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">{{ __('documents.buttons.cancel') }}</a>
    </div>
</div>
