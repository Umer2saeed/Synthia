@extends('frontend.layouts.app')

@section('content')

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-12">

{{--         Flash Messages--}}
        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800
                    text-green-700 dark:text-green-400 text-sm rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            {{-- =============================================
                 LEFT COLUMN: Avatar + Identity + Stats
                 ============================================= --}}
            <div class="space-y-5">

                {{-- Profile Card --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100
                        dark:border-gray-800 p-6 text-center">

                    {{-- Avatar --}}
                    <div class="relative inline-block mb-4">
                        <img src="{{ $user->avatar_url }}"
                             alt="{{ $user->name }}"
                             class="w-24 h-24 rounded-full object-cover border-4
                                border-indigo-100 dark:border-indigo-900 mx-auto">
                    </div>

                    {{-- Name --}}
                    <h1 class="font-display text-xl font-bold text-gray-900 dark:text-white">
                        {{ $user->name }}
                    </h1>

                    {{-- Username --}}
                    @if($user->username)
                        <p class="text-sm text-gray-400 font-mono mt-0.5">
                            @ {{ $user->username }}
                        </p>
                    @endif

                    {{-- Role badge --}}
                    @php
                        $role      = $user->roles->first();
                        $roleColor = match($role?->name) {
                            'admin'  => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400',
                            'editor' => 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400',
                            'author' => 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-400',
                            default  => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                        };
                    @endphp
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium {{ $roleColor }}">
                    {{ ucfirst($role?->name ?? 'member') }}
                </span>

                    {{-- Bio --}}
                    @if($user->bio)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                            {{ $user->bio }}
                        </p>
                    @else
                        <p class="text-sm text-gray-300 dark:text-gray-600 mt-3 italic">
                            No bio yet.
                        </p>
                    @endif

                    {{-- Edit Profile Button --}}
                    <a href="{{ route('frontend.profile.edit') }}"
                       class="mt-4 inline-block w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                          text-white text-sm font-medium rounded-xl transition-colors">
                        Edit Profile
                    </a>
                </div>

                <div class="space-y-3">
                    {{-- existing stats --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Posts Published</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
            {{ $user->posts_count }}
        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Comments Made</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
            {{ $user->comments_count }}
        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Saved Articles</span>
                        <a href="{{ route('bookmarks.index') }}"
                           class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ auth()->user()->bookmarks()->count() }}
                        </a>
                    </div>

                    {{-- Following count with link --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Following</span>
                        <a href="{{ route('following.index') }}"
                           class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ $user->following()->count() }}
                        </a>
                    </div>

                    {{-- Followers count with link --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Followers</span>
                        <a href="{{ route('followers.index') }}"
                           class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ $user->followers()->count() }}
                        </a>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Member Since</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ $user->created_at->format('M Y') }}
        </span>
                    </div>
                </div>

                {{-- Admin Panel Link (non-readers only) --}}
                @if($user->can('access admin panel'))
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center justify-center gap-2 w-full px-4 py-2.5
                          bg-gray-900 dark:bg-gray-800 hover:bg-gray-700
                          text-white text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Go to Admin Panel
                    </a>
                @endif

            </div>

            {{-- =============================================
                 RIGHT COLUMN: Recent Posts + Recent Comments
                 ============================================= --}}
            <div class="md:col-span-2 space-y-6">

                {{-- Recent Posts (only if user has published posts) --}}
                @if($recentPosts->isNotEmpty())
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100
                            dark:border-gray-800 p-6">
                        <h2 class="font-display text-lg font-bold text-gray-900 dark:text-white mb-4">
                            Recent Posts
                        </h2>
                        <div class="space-y-3">
                            @foreach($recentPosts as $post)
                                <div class="flex items-start gap-3 py-3 border-b
                                        border-gray-50 dark:border-gray-800 last:border-0">
                                    <img src="{{ $post->cover_image_url }}"
                                         alt="{{ $post->title }}"
                                         class="w-12 h-12 rounded-xl object-cover
                                            border border-gray-100 dark:border-gray-700 shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('blog.post', $post->slug) }}"
                                           class="text-sm font-semibold text-gray-800 dark:text-white
                                              hover:text-indigo-600 dark:hover:text-indigo-400
                                              transition-colors line-clamp-1">
                                            {{ $post->title }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            @if($post->category)
                                                <span class="text-xs text-indigo-600 dark:text-indigo-400">
                                                {{ $post->category->name }}
                                            </span>
                                                <span class="text-gray-300 dark:text-gray-700">·</span>
                                            @endif
                                            <span class="text-xs text-gray-400">
                                            {{ $post->published_at?->format('d M Y') }}
                                        </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Recent Comments --}}
                @if($recentComments->isNotEmpty())
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100
                            dark:border-gray-800 p-6">
                        <h2 class="font-display text-lg font-bold text-gray-900 dark:text-white mb-4">
                            Recent Comments
                        </h2>
                        <div class="space-y-3">
                            @foreach($recentComments as $comment)
                                <div class="py-3 border-b border-gray-50 dark:border-gray-800 last:border-0">
                                    <a href="{{ route('blog.post', $comment->post->slug) }}#comments"
                                       class="text-xs text-indigo-600 dark:text-indigo-400
                                          hover:underline font-medium">
                                        {{ Str::limit($comment->post->title, 50) }}
                                    </a>
                                    <p class="text-sm text-gray-600 dark:text-gray-400
                                          mt-1 leading-relaxed line-clamp-2">
                                        {{ $comment->content }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $comment->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Empty state for new users with no activity --}}
                @if($recentPosts->isEmpty() && $recentComments->isEmpty())
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100
                            dark:border-gray-800 p-12 text-center">
                        <p class="text-4xl mb-4">👋</p>
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Welcome to Synthia!
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            You have not made any activity yet. Start by reading some articles
                            and leaving your first comment.
                        </p>
                        <a href="{{ route('blog') }}"
                           class="inline-block px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                              text-white text-sm font-medium rounded-xl transition-colors">
                            Browse Articles
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>

@endsection
