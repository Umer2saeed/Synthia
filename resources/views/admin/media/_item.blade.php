@php
    $dims = ($item->width && $item->height)
        ? $item->width . '×' . $item->height
        : '';
@endphp

{{--
| Wrapper is NOT a link — clicking the image opens the lightbox.
| Clicking the checkbox selects for bulk delete.
| Hovering reveals the checkbox + delete button.
--}}
<div class="group relative rounded-xl overflow-hidden select-none
            cursor-pointer">

    {{-- Square thumbnail cell --}}
    <div class="relative aspect-square overflow-hidden rounded-xl
                bg-gray-100 dark:bg-gray-700
                border border-gray-200 dark:border-gray-700
                hover:border-indigo-400 dark:hover:border-indigo-600
                transition-colors duration-200">

        {{-- Checkbox — top-left, visible on hover --}}
        <div class="absolute top-2 left-2 z-20">
            <input type="checkbox"
                   name="ids[]"
                   value="{{ $item->id }}"
                   class="media-checkbox w-4 h-4 rounded
                          border-2 border-white dark:border-gray-200
                          bg-white/90 dark:bg-gray-800/90
                          text-indigo-600 focus:ring-indigo-400
                          opacity-0 group-hover:opacity-100
                          transition-opacity duration-150"
                   onclick="event.stopPropagation()">
        </div>

        {{-- Delete button — top-right, visible on hover --}}
        <div class="absolute top-2 right-2 z-20
            opacity-0 group-hover:opacity-100 transition-opacity duration-150">
            <button type="button"
                    class="single-delete-btn w-7 h-7 rounded-full
                   bg-red-500 hover:bg-red-600
                   flex items-center justify-center
                   shadow-lg transition"
                    data-id="{{ $item->id }}"
                    data-name="{{ $item->original_name }}"
                    data-url="{{ route('admin.media.destroy', $item) }}">
                <svg class="w-3.5 h-3.5 text-white pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Thumbnail image --}}
        <img src="{{ $item->url }}"
             alt="{{ $item->original_name }}"
{{--             data-lb-index="{{ $loop->index }}"--}}
             data-lb-url="{{ $item->url }}"
             data-lb-name="{{ $item->original_name }}"
             data-lb-size="{{ $item->formatted_size }}"
             data-lb-dims="{{ $dims }}"
             class="w-full h-full object-cover
                    hover:scale-105 transition-transform duration-300"
             loading="lazy">

        {{-- Hover overlay --}}
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15
                    transition-colors duration-200 rounded-xl pointer-events-none">
        </div>

    </div>

    {{-- Caption below thumbnail --}}
    <div class="pt-1.5 pb-1 px-0.5">
        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate"
           title="{{ $item->original_name }}">
            {{ $item->original_name }}
        </p>
        <p class="text-xs text-gray-400 dark:text-gray-500">
            {{ $item->formatted_size }}
            @if($dims)
                · {{ $dims }}
            @endif
        </p>
{{--        @php $usedIn = $item->used_in_posts_count; @endphp--}}
{{--        @if($usedIn > 0)--}}
{{--            <p class="text-xs text-indigo-500 dark:text-indigo-400 mt-0.5">--}}
{{--                Used in {{ $usedIn }} {{ Str::plural('post', $usedIn) }}--}}
{{--            </p>--}}
{{--        @endif--}}

        @php $usedInPosts = $item->used_in_posts; @endphp
        @if($usedInPosts->isNotEmpty())
            <div class="mt-0.5">
                @foreach($usedInPosts as $post)
                    <a href="{{ route('admin.posts.show', $post) }}"
                       target="_blank"
                       class="block text-xs text-indigo-500 dark:text-indigo-400
                      hover:underline truncate"
                       title="{{ $post->title }}">
                        ↗ {{ Str::limit($post->title, 30) }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

</div>
