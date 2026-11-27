@extends('frontend.layouts.app')

@section('content')
    <x-slot name="title">{{ $readingList->name }}</x-slot>

    <div class="max-w-3xl mx-auto px-4 py-10">

        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $readingList->name }}
                </h1>
                @if($readingList->is_public)
                    <span class="px-2 py-0.5 bg-green-50 dark:bg-green-950
                                 text-green-600 dark:text-green-400
                                 text-xs rounded-full">
                        Public
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Curated by
                <a href="{{ route('author.profile', $readingList->user->username ?? $readingList->user->id) }}"
                   class="font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    {{ $readingList->user->name }}
                </a>
                · {{ $items->count() }} {{ Str::plural('post', $items->count()) }}
            </p>
        </div>

        @if($items->isEmpty())
            <div class="text-center py-16">
                <p class="text-gray-400 dark:text-gray-500 text-sm">
                    This list has no posts yet.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($items as $item)
                    <article class="bg-white dark:bg-gray-800
                                    rounded-2xl border border-gray-100 dark:border-gray-700
                                    p-5 flex gap-4
                                    hover:shadow-md transition-shadow">

                        @if($item->post->cover_image)
                            <a href="{{ route('blog.post', $item->post->slug) }}"
                               class="shrink-0">
                                <img src="{{ $item->post->cover_image_url }}"
                                     alt="{{ $item->post->title }}"
                                     class="w-20 h-20 object-cover rounded-xl
                                            border border-gray-100 dark:border-gray-700">
                            </a>
                        @endif

                        <div class="flex-1 min-w-0">
                            <h2 class="font-semibold text-gray-900 dark:text-white
                                       text-sm mb-1 line-clamp-2">
                                <a href="{{ route('blog.post', $item->post->slug) }}"
                                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                    {{ $item->post->title }}
                                </a>
                            </h2>
                            <div class="flex items-center gap-2 text-xs
                                        text-gray-400 dark:text-gray-500">
                                <span>{{ $item->post->user->name ?? '—' }}</span>
                                @if($item->post->category)
                                    <span>·</span>
                                    <span>{{ $item->post->category->name }}</span>
                                @endif
                                <span>·</span>
                                <span>Added {{ $item->added_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        {{-- Remove from list (owner only) --}}
                        @auth
                            @if($readingList->user_id === auth()->id())
                                <button type="button"
                                        data-list-id="{{ $readingList->id }}"
                                        data-post-id="{{ $item->post->id }}"
                                        class="remove-from-list-btn shrink-0 text-xs
                                               text-red-400 dark:text-red-500
                                               hover:text-red-600 dark:hover:text-red-400
                                               transition self-start mt-1">
                                    Remove
                                </button>
                            @endif
                        @endauth

                    </article>
                @endforeach
            </div>
        @endif

    </div>

    @auth
        @if($readingList->user_id === auth()->id())
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    document.querySelectorAll('.remove-from-list-btn').forEach(btn => {
                        btn.addEventListener('click', async function () {
                            if (!confirm('Remove this post from the list?')) return;

                            const listId = this.dataset.listId;
                            const postId = this.dataset.postId;
                            const article = this.closest('article');

                            try {
                                const response = await fetch(`/reading-lists/${listId}/items`, {
                                    method:  'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Content-Type': 'application/json',
                                        'Accept':       'application/json',
                                    },
                                    body: JSON.stringify({ post_id: parseInt(postId) }),
                                });

                                const data = await response.json();

                                if (response.ok && data.success && !data.in_list) {
                                    article.style.transition = 'opacity 0.3s ease';
                                    article.style.opacity    = '0';
                                    setTimeout(() => article.remove(), 300);
                                }
                            } catch (err) {
                                alert('Could not remove post. Please try again.');
                            }
                        });
                    });
                });
            </script>
        @endif
    @endauth

@endsection
