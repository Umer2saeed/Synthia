@extends('frontend.layouts.app')

@section('content')

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-12">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="font-display text-3xl font-bold text-gray-900 dark:text-white">
                    My Followers
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">
                    People who follow you.
                </p>
            </div>
            <a href="{{ route('following.index') }}"
               class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                ← Who I Follow
            </a>
        </div>

        @if($follows->isEmpty())
            <div class="text-center py-20">
                <div class="w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-950
                        flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-indigo-400" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    No followers yet
                </h3>
                <p class="text-sm text-gray-400 mb-6 max-w-sm mx-auto">
                    Keep publishing great articles and readers will start following you.
                </p>
                <a href="{{ route('blog') }}"
                   class="inline-block px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700
                      text-white text-sm font-medium rounded-xl transition-colors">
                    Go to Blog
                </a>
            </div>

        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
                @foreach($follows as $follow)
                    @if($follow->follower)
                        @php $follower = $follow->follower; @endphp

                        <div class="bg-white dark:bg-gray-900 rounded-2xl border
                                border-gray-100 dark:border-gray-800 p-5
                                hover:border-indigo-200 dark:hover:border-indigo-800
                                transition-colors">

                            {{-- Follower Avatar + Name --}}
                            <div class="flex items-center gap-3 mb-3">
                                <a href="{{ route('author.profile', $follower->username ?? $follower->id) }}">
                                    <img src="{{ $follower->avatar_url }}"
                                         alt="{{ $follower->name }}"
                                         class="w-12 h-12 rounded-full object-cover
                                            border-2 border-gray-100 dark:border-gray-700
                                            hover:opacity-80 transition-opacity">
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('author.profile', $follower->username ?? $follower->id) }}"
                                       class="font-semibold text-gray-900 dark:text-white
                                          hover:text-indigo-600 dark:hover:text-indigo-400
                                          transition-colors block truncate">
                                        {{ $follower->display_name }}
                                    </a>
                                    @if($follower->username)
                                        <p class="text-xs text-gray-400 font-mono">
                                            @{{ $follower->username }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Bio --}}
                            @if($follower->bio)
                                <p class="text-sm text-gray-500 dark:text-gray-400
                                      leading-relaxed line-clamp-2 mb-3">
                                    {{ $follower->bio }}
                                </p>
                            @endif

                            {{-- Followed since --}}
                            <p class="text-xs text-gray-400 pt-3
                                  border-t border-gray-50 dark:border-gray-800">
                                Following since {{ $follow->created_at->format('d M Y') }}
                            </p>

                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Pagination --}}
            {{ $follows->links() }}
        @endif

    </div>

@endsection
