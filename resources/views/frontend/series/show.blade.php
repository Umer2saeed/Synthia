@extends('frontend.layouts.app')

@section('content')
    <x-slot name="title">{{ $series->title }}</x-slot>

    <div class="max-w-3xl mx-auto px-4 py-10">

        {{-- Series header --}}
        <div class="mb-8">
            <div class="flex items-start gap-5 mb-5">
                <img src="{{ $series->cover_image_url }}"
                     alt="{{ $series->title }}"
                     class="w-24 h-24 rounded-2xl object-cover shrink-0
                            border border-gray-200 dark:border-gray-700">

                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $series->title }}
                        </h1>
                        @if($series->is_complete)
                            <span class="px-2 py-0.5 text-xs rounded-full
                                         bg-green-50 dark:bg-green-950
                                         text-green-600 dark:text-green-400
                                         border border-green-200 dark:border-green-800">
                                Complete
                            </span>
                        @else
                            <span class="px-2 py-0.5 text-xs rounded-full
                                         bg-indigo-50 dark:bg-indigo-950
                                         text-indigo-600 dark:text-indigo-400
                                         border border-indigo-200 dark:border-indigo-800">
                                Ongoing
                            </span>
                        @endif
                    </div>

                    @if($series->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            {{ $series->description }}
                        </p>
                    @endif

                    <div class="flex items-center gap-3 text-xs
                                text-gray-400 dark:text-gray-500">
                        <img src="{{ $series->user->avatar_url }}"
                             alt="{{ $series->user->name }}"
                             class="w-5 h-5 rounded-full object-cover">
                        <span>{{ $series->user->name }}</span>
                        <span>·</span>
                        <span>
                            {{ $posts->count() }}
                            {{ Str::plural('post', $posts->count()) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Posts list --}}
        @if($posts->isEmpty())
            <div class="text-center py-16">
                <p class="text-gray-400 dark:text-gray-500 text-sm">
                    No published posts in this series yet.
                </p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($posts as $post)
                    <a href="{{ route('blog.post', $post->slug) }}"
                       class="group flex items-center gap-4 p-4
                              bg-white dark:bg-gray-800
                              rounded-2xl border border-gray-100 dark:border-gray-700
                              hover:border-indigo-200 dark:hover:border-indigo-800
                              hover:shadow-sm transition-all duration-200">

                        {{-- Order number --}}
                        <span class="w-9 h-9 rounded-xl shrink-0
                                     bg-indigo-50 dark:bg-indigo-950
                                     text-indigo-600 dark:text-indigo-400
                                     text-sm font-bold
                                     flex items-center justify-center">
                            {{ $post->pivot->order }}
                        </span>

                        {{-- Post info --}}
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 dark:text-gray-200
                                       group-hover:text-indigo-600 dark:group-hover:text-indigo-400
                                       transition-colors truncate">
                                {{ $post->title }}
                            </p>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($post->category)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $post->category->name }}
                                    </span>
                                    <span class="text-gray-300 dark:text-gray-600 text-xs">·</span>
                                @endif
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ max(1, ceil(str_word_count(strip_tags($post->content)) / 200)) }}
                                    min read
                                </span>
                            </div>
                        </div>

                        <svg class="w-4 h-4 shrink-0
                                    text-gray-300 dark:text-gray-600
                                    group-hover:text-indigo-500 dark:group-hover:text-indigo-400
                                    transition-colors"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>

                    </a>
                @endforeach
            </div>
        @endif

    </div>
@endsection
