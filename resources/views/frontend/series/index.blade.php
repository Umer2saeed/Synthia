@extends('frontend.layouts.app')

@section('content')
    <x-slot name="title">Series</x-slot>

    <div class="max-w-5xl mx-auto px-4 py-10">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Series
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2">
                Curated collections of posts on a single topic.
            </p>
        </div>

        @if($series->isEmpty())
            <div class="text-center py-20">
                <p class="text-gray-400 dark:text-gray-500">
                    No series available yet.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($series as $s)
                    <a href="{{ route('series.show', $s->slug) }}"
                       class="group flex gap-4 p-5
                              bg-white dark:bg-gray-800
                              rounded-2xl border border-gray-100 dark:border-gray-700
                              hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-800
                              transition-all duration-200">

                        <img src="{{ $s->cover_image_url }}"
                             alt="{{ $s->title }}"
                             class="w-20 h-20 rounded-xl object-cover shrink-0
                                    border border-gray-100 dark:border-gray-700">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h2 class="font-semibold text-gray-900 dark:text-white
                                           group-hover:text-indigo-600 dark:group-hover:text-indigo-400
                                           transition-colors leading-snug">
                                    {{ $s->title }}
                                </h2>
                                @if($s->is_complete)
                                    <span class="shrink-0 px-2 py-0.5 text-xs rounded-full
                                                 bg-green-50 dark:bg-green-950
                                                 text-green-600 dark:text-green-400
                                                 border border-green-200 dark:border-green-800">
                                        Complete
                                    </span>
                                @else
                                    <span class="shrink-0 px-2 py-0.5 text-xs rounded-full
                                                 bg-indigo-50 dark:bg-indigo-950
                                                 text-indigo-600 dark:text-indigo-400
                                                 border border-indigo-200 dark:border-indigo-800">
                                        Ongoing
                                    </span>
                                @endif
                            </div>

                            @if($s->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400
                                           line-clamp-2 mb-2">
                                    {{ $s->description }}
                                </p>
                            @endif

                            <div class="flex items-center gap-3 text-xs
                                        text-gray-400 dark:text-gray-500">
                                <span>
                                    {{ $s->published_posts_count }}
                                    {{ Str::plural('post', $s->published_posts_count) }}
                                </span>
                                <span>·</span>
                                <span>by {{ $s->user->name }}</span>
                            </div>
                        </div>

                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $series->links() }}
            </div>
        @endif

    </div>
@endsection
