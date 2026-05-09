@extends('frontend.layouts.app')

@section('content')
    <x-slot name="title">My Reading Lists</x-slot>

    <div class="max-w-4xl mx-auto px-4 py-10">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                My Reading Lists
            </h1>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900
                        border border-green-200 dark:border-green-700
                        text-green-800 dark:text-green-300 text-sm rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-950
                        border border-red-200 dark:border-red-800
                        text-red-700 dark:text-red-400 text-sm rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        {{-- Create new list form --}}
        <div class="bg-white dark:bg-gray-800
                    rounded-2xl border border-gray-100 dark:border-gray-700
                    p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                Create New List
            </h2>
            <form action="{{ route('reading-lists.store') }}" method="POST"
                  class="flex flex-wrap gap-3 items-end">
                @csrf
                <div class="flex-1 min-w-48">
                    <input type="text" name="name"
                           placeholder="e.g. Laravel Tutorials"
                           maxlength="100"
                           value="{{ old('name') }}"
                           class="w-full border border-gray-200 dark:border-gray-700
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  placeholder-gray-400 dark:placeholder-gray-500
                                  rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_public" value="1"
                           id="is_public_create"
                           class="rounded border-gray-300 dark:border-gray-600
                                  text-indigo-600 focus:ring-indigo-400">
                    <label for="is_public_create"
                           class="text-sm text-gray-600 dark:text-gray-400">
                        Make public
                    </label>
                </div>
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                               text-white text-sm font-medium rounded-xl transition">
                    Create List
                </button>
            </form>
        </div>

        {{-- Lists --}}
        @if($lists->isEmpty())
            <div class="text-center py-16">
                <p class="text-gray-400 dark:text-gray-500 text-sm">
                    No reading lists yet. Create your first one above.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($lists as $list)

                    {{--
                    | ONE x-data on the card wrapper.
                    | Both the Edit button and the inline form
                    | share the same Alpine scope — so clicking
                    | Edit actually shows the form.
                    --}}
                    <div class="bg-white dark:bg-gray-800
                                rounded-2xl border border-gray-100 dark:border-gray-700
                                p-5"
                         x-data="{ editing: false }">

                        <div class="flex items-start justify-between gap-4">

                            {{-- List info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <a href="{{ $list->share_url }}"
                                       class="font-semibold text-gray-900 dark:text-white
                                              hover:text-indigo-600 dark:hover:text-indigo-400
                                              transition">
                                        {{ $list->name }}
                                    </a>

                                    @if($list->is_public)
                                        <span class="px-2 py-0.5 bg-green-50 dark:bg-green-950
                                                     text-green-600 dark:text-green-400
                                                     text-xs rounded-full border
                                                     border-green-200 dark:border-green-800">
                                            Public
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-gray-50 dark:bg-gray-700
                                                     text-gray-500 dark:text-gray-400
                                                     text-xs rounded-full border
                                                     border-gray-200 dark:border-gray-600">
                                            Private
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $list->items_count }}
                                    {{ Str::plural('post', $list->items_count) }}
                                    · Created {{ $list->created_at->diffForHumans() }}
                                </p>

                                {{-- Share URL row — only for public lists --}}
                                @if($list->is_public)
                                    <div class="mt-2 flex items-center gap-2">
                                        <input type="text"
                                               value="{{ $list->share_url }}"
                                               readonly
                                               id="share-url-{{ $list->id }}"
                                               class="text-xs text-gray-500 dark:text-gray-400
                                                      bg-gray-50 dark:bg-gray-700
                                                      border border-gray-200 dark:border-gray-600
                                                      rounded-lg px-3 py-1.5 w-72 font-mono
                                                      focus:outline-none cursor-text">

                                        {{--
                                        | Copy button uses a plain JS function defined below.
                                        | navigator.clipboard fails on HTTP (synthia.test).
                                        | We fall back to the legacy execCommand('copy')
                                        | which works on any HTTP page.
                                        --}}
                                        <button type="button"
                                                onclick="copyToClipboard('share-url-{{ $list->id }}', this)"
                                                class="text-xs text-indigo-500 dark:text-indigo-400
                                                       hover:underline shrink-0 transition">
                                            Copy
                                        </button>
                                    </div>
                                @endif
                            </div>

                            {{-- Actions: Edit + Delete --}}
                            <div class="flex items-center gap-3 shrink-0">

                                {{--
                                | @click toggles `editing` in the parent x-data.
                                | The inline form below reads the same `editing` variable.
                                --}}
                                <button type="button"
                                        @click="editing = !editing"
                                        class="text-xs font-medium
                                               text-gray-400 dark:text-gray-500
                                               hover:text-indigo-500 dark:hover:text-indigo-400
                                               transition"
                                        x-text="editing ? 'Cancel' : 'Edit'">
                                </button>

                                <form action="{{ route('reading-lists.destroy', $list) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Delete \'{{ addslashes($list->name) }}\'? All {{ $list->items_count }} posts will be removed.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-medium
                                                   text-red-400 dark:text-red-500
                                                   hover:text-red-600 dark:hover:text-red-400
                                                   transition">
                                        Delete
                                    </button>
                                </form>

                            </div>
                        </div>

                        {{--
                        | Inline edit form.
                        | x-show reads `editing` from the SAME parent x-data.
                        | x-cloak hides it before Alpine initialises (prevents flash).
                        | No separate x-data here — that was the bug.
                        --}}
                        <div x-show="editing"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">

                            <form action="{{ route('reading-lists.update', $list) }}"
                                  method="POST"
                                  class="flex flex-wrap gap-3 items-center">
                                @csrf
                                @method('PUT')

                                <input type="text"
                                       name="name"
                                       value="{{ $list->name }}"
                                       maxlength="100"
                                       class="flex-1 min-w-48
                                              border border-gray-200 dark:border-gray-700
                                              bg-white dark:bg-gray-900
                                              text-gray-800 dark:text-gray-200
                                              rounded-xl px-3 py-1.5 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-400">

                                <div class="flex items-center gap-2">
                                    <input type="checkbox"
                                           name="is_public"
                                           value="1"
                                           id="is_public_{{ $list->id }}"
                                           {{ $list->is_public ? 'checked' : '' }}
                                           class="rounded border-gray-300 dark:border-gray-600
                                                  text-indigo-600 focus:ring-indigo-400">
                                    <label for="is_public_{{ $list->id }}"
                                           class="text-sm text-gray-600 dark:text-gray-400">
                                        Public
                                    </label>
                                </div>

                                <button type="submit"
                                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700
                                               text-white text-xs font-medium
                                               rounded-lg transition">
                                    Save
                                </button>

                                {{-- Cancel also closes the form via Alpine --}}
                                <button type="button"
                                        @click="editing = false"
                                        class="text-xs text-gray-400 dark:text-gray-500
                                               hover:underline transition">
                                    Cancel
                                </button>

                            </form>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </div>

    {{--
    | copyToClipboard() — works on both HTTPS and HTTP.
    |
    | Modern API (navigator.clipboard) requires HTTPS or localhost.
    | synthia.test runs on HTTP so we always fall back to execCommand.
    | We try the modern API first and fall back silently if it fails.
    --}}
    <script>
        function copyToClipboard(inputId, btn) {
            var input = document.getElementById(inputId);
            var text  = input ? input.value : '';

            if (!text) return;

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function () {
                    btn.textContent = 'Copied!';
                    setTimeout(function () { btn.textContent = 'Copy'; }, 2000);
                }).catch(function () {
                    fallbackCopy(text, btn);
                });
            } else {
                fallbackCopy(text, btn);
            }
        }

        function fallbackCopy(text, btn) {
            var ta       = document.createElement('textarea');
            ta.value     = text;
            ta.style.position = 'fixed';
            ta.style.left     = '-9999px';
            ta.style.top      = '-9999px';
            ta.style.opacity  = '0';

            document.body.appendChild(ta);
            ta.focus();
            ta.select();

            try {
                document.execCommand('copy');
                btn.textContent = 'Copied!';
            } catch (e) {
                btn.textContent = 'Failed';
            }

            document.body.removeChild(ta);
            setTimeout(function () { btn.textContent = 'Copy'; }, 2000);
        }
    </script>

@endsection
