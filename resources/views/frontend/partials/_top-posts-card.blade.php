<div class="bg-white dark:bg-gray-900
            rounded-2xl border border-gray-200 dark:border-gray-800/80
            overflow-hidden">

    <div class="px-5 py-5 border-b border-gray-100 dark:border-gray-800/80">
        <h2 class="text-sm font-semibold text-gray-800 dark:text-white">
            {{ $title }}
        </h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            {{ $subtitle }}
        </p>
    </div>

    @if(empty($posts))
        <div class="px-5 py-10 text-center">
            <p class="text-xs text-gray-400 dark:text-gray-500">
                No data yet.
            </p>
        </div>
    @else
        @php $maxVal = collect($posts)->max('value') ?: 1; @endphp

        <div class="p-5 space-y-4">
            @foreach($posts as $index => $post)
                <div>
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <a href="{{ route('blog.post', $post['slug']) }}"
                           class="text-xs font-medium text-gray-700 dark:text-gray-300
                                  hover:text-indigo-600 dark:hover:text-indigo-400
                                  transition line-clamp-1 flex-1">
                            {{ $post['title'] }}
                        </a>
                        <span class="text-xs font-bold {{ $accent }} shrink-0">
                            {{ number_format($post['value']) }}
                        </span>
                    </div>
                    {{-- Progress bar --}}
                    <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5">
                        <div class="{{ $bar }} h-1.5 rounded-full transition-all"
                             style="width: {{ round(($post['value'] / $maxVal) * 100) }}%">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-600 mt-1">
                        {{ number_format($post['value']) }} {{ $unit }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif

</div>
