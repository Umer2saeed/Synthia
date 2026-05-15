<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Export Posts</h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            Download post data as CSV or PDF
        </p>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 space-y-6">

        @if(session('success'))
            <div class="px-4 py-3 bg-green-100 dark:bg-green-900
                        border border-green-300 dark:border-green-700
                        text-green-800 dark:text-green-300 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- CSV Export --}}
        <div class="bg-white dark:bg-gray-800
                    rounded-2xl border border-gray-200 dark:border-gray-700 p-6">

            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900
                            flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1
                                 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
                        Export to CSV
                    </h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Exports: ID, title, slug, status, author, category, tags, views, claps, comments, dates, URL
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.export.csv') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            Status
                        </label>
                        <select name="status"
                                class="w-full border border-gray-300 dark:border-gray-600
                                       bg-white dark:bg-gray-900
                                       text-gray-800 dark:text-gray-200
                                       rounded-xl px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">All statuses</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            Author
                        </label>
                        <select name="author_id"
                                class="w-full border border-gray-300 dark:border-gray-600
                                       bg-white dark:bg-gray-900
                                       text-gray-800 dark:text-gray-200
                                       rounded-xl px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">All authors</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}">{{ $author->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            Category
                        </label>
                        <select name="category_id"
                                class="w-full border border-gray-300 dark:border-gray-600
                                       bg-white dark:bg-gray-900
                                       text-gray-800 dark:text-gray-200
                                       rounded-xl px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">All categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            Date From
                        </label>
                        <input type="date" name="date_from"
                               class="w-full border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-900
                                      text-gray-800 dark:text-gray-200
                                      rounded-xl px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            Date To
                        </label>
                        <input type="date" name="date_to"
                               class="w-full border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-900
                                      text-gray-800 dark:text-gray-200
                                      rounded-xl px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>

                </div>

                <div class="flex items-center justify-between pt-2">
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Exports over 500 posts will be emailed to you.
                    </p>
                    <button type="submit"
                            class="flex items-center gap-2 px-5 py-2.5
                                   bg-emerald-600 hover:bg-emerald-700
                                   text-white text-sm font-medium rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1
                                     1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export CSV
                    </button>
                </div>

            </form>
        </div>

        {{-- PDF Export (per post) --}}
        <div class="bg-white dark:bg-gray-800
                    rounded-2xl border border-gray-200 dark:border-gray-700 p-6">

            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900
                            flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1
                                 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
                        Export Single Post to PDF
                    </h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Full formatted post with title, content, category, tags, and metadata
                    </p>
                </div>
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                PDF export is available from the post edit page. Click
                <strong>"Export PDF"</strong> in the top action bar of any post.
            </p>

            <a href="{{ route('admin.posts.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2
                      bg-gray-100 dark:bg-gray-700
                      text-gray-700 dark:text-gray-300
                      text-sm rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600
                      transition">
                Go to Posts
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

        </div>

    </div>
</x-app-layout>
