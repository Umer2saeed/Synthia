@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">

        {{-- Header --}}
        <div class="mb-10 pb-8 border-b border-gray-100 dark:border-gray-800">
            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Category</span>
            <h1 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mt-2 mb-2">
                {{ $category->name }}
            </h1>
            @if($category->description)
                <p class="text-gray-500 dark:text-gray-400 max-w-2xl">{{ $category->description }}</p>
            @endif
            <p class="text-sm text-gray-400 mt-2">
                {{ $posts->total() }} {{ Str::plural('article', $posts->total()) }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2">
                @if($posts->isEmpty())
                    <div class="text-center py-20">
                        <p class="text-4xl mb-4">📂</p>
                        <p class="text-lg font-medium text-gray-700 dark:text-gray-300">No posts in this category yet.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                        @foreach($posts as $post)
                            @include('frontend.partials._post-card', compact('post'))
                        @endforeach
                    </div>
                    {{ $posts->links() }}
                @endif
            </div>

            @include('frontend.partials._sidebar')
        </div>
    </div>
@endsection
