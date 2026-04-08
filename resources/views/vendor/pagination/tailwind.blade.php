@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between flex-wrap gap-4">

        {{-- Mobile: simple prev/next --}}
        <div class="flex justify-between flex-1 sm:hidden">

            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 text-sm font-medium text-gray-400
                             bg-white dark:bg-gray-900
                             border border-gray-200 dark:border-gray-700
                             rounded-lg cursor-not-allowed">
                    ← Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300
                          bg-white dark:bg-gray-900
                          border border-gray-200 dark:border-gray-700
                          rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    ← Previous
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300
                          bg-white dark:bg-gray-900
                          border border-gray-200 dark:border-gray-700
                          rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Next →
                </a>
            @else
                <span class="px-4 py-2 text-sm font-medium text-gray-400
                             bg-white dark:bg-gray-900
                             border border-gray-200 dark:border-gray-700
                             rounded-lg cursor-not-allowed">
                    Next →
                </span>
            @endif

        </div>

        {{-- Desktop: full pagination --}}
        <div class="hidden sm:flex sm:items-center sm:justify-between w-full">

            {{-- Results info --}}
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Showing
                <span class="font-semibold text-gray-700 dark:text-gray-300">
                    {{ $paginator->firstItem() }}
                </span>
                to
                <span class="font-semibold text-gray-700 dark:text-gray-300">
                    {{ $paginator->lastItem() }}
                </span>
                of
                <span class="font-semibold text-gray-700 dark:text-gray-300">
                    {{ $paginator->total() }}
                </span>
                results
            </p>

            {{-- Page links --}}
            <div class="flex items-center gap-1">

                {{-- Previous button --}}
                @if ($paginator->onFirstPage())
                    <span class="px-3 py-1.5 text-sm text-gray-400
                                 bg-white dark:bg-gray-900
                                 border border-gray-200 dark:border-gray-700
                                 rounded-lg cursor-not-allowed select-none">
                        ←
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                       class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400
                              bg-white dark:bg-gray-900
                              border border-gray-200 dark:border-gray-700
                              rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800
                              hover:text-gray-900 dark:hover:text-white
                              transition-colors">
                        ←
                    </a>
                @endif

                {{-- Page numbers --}}
                @foreach ($elements as $element)

                    {{-- Three dots separator --}}
                    @if (is_string($element))
                        <span class="px-3 py-1.5 text-sm text-gray-400
                                     dark:text-gray-600 select-none">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Page number links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                {{-- Current page — highlighted --}}
                                <span class="px-3 py-1.5 text-sm font-semibold
                                             text-white bg-indigo-600
                                             border border-indigo-600
                                             rounded-lg select-none">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400
                                          bg-white dark:bg-gray-900
                                          border border-gray-200 dark:border-gray-700
                                          rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950
                                          hover:text-indigo-600 dark:hover:text-indigo-400
                                          hover:border-indigo-200 dark:hover:border-indigo-800
                                          transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif

                @endforeach

                {{-- Next button --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400
                              bg-white dark:bg-gray-900
                              border border-gray-200 dark:border-gray-700
                              rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800
                              hover:text-gray-900 dark:hover:text-white
                              transition-colors">
                        →
                    </a>
                @else
                    <span class="px-3 py-1.5 text-sm text-gray-400
                                 bg-white dark:bg-gray-900
                                 border border-gray-200 dark:border-gray-700
                                 rounded-lg cursor-not-allowed select-none">
                        →
                    </span>
                @endif

            </div>
        </div>
    </nav>
@endif
