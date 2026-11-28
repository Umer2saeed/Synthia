<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Post Details</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

        <div class="flex mb-4 justify-between">
            <div class="flex items-center gap-2">
                @if(auth()->user()->can('edit all posts') ||
                   (auth()->user()->can('edit own posts') && $post->user_id === auth()->id()))
                    <a href="{{ route('admin.posts.edit', $post) }}"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                              text-white text-sm rounded-lg transition">
                        Edit
                    </a>
                @endif
            </div>
            <div class="flex items-center">
                <a href="{{ route('admin.posts.index') }}"
                   class="px-4 py-2 bg-gray-100 dark:bg-gray-800
                          text-gray-700 dark:text-gray-300
                          border border-gray-200 dark:border-gray-700
                          text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                    ← Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- MAIN COLUMN --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Cover Image --}}
                <div class="bg-white dark:bg-gray-800
                            shadow rounded-xl overflow-hidden
                            border border-gray-200 dark:border-gray-700">
                    <img src="{{ $post->cover_image_url }}"
                         alt="{{ $post->title }}"
                         class="w-full h-64 object-cover">
                </div>

                {{-- Title + Content --}}
                <div class="bg-white dark:bg-gray-800
                            shadow rounded-xl
                            border border-gray-200 dark:border-gray-700
                            p-6 space-y-4">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                        {{ $post->title }}
                    </h1>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                        slug: {{ $post->slug }}
                    </p>
                    <hr class="border-gray-100 dark:border-gray-700">
                    <div class="text-sm text-gray-700 dark:text-gray-300
                                leading-relaxed prose dark:prose-invert max-w-none">
                        {!! $post->content !!}
                    </div>
                </div>

                {{-- AI Summary --}}
                @if($post->ai_summary)
                    <div class="bg-indigo-50 dark:bg-indigo-950
                                border border-indigo-100 dark:border-indigo-900
                                rounded-xl p-5">
                        <p class="text-xs font-semibold text-indigo-500 dark:text-indigo-400
                                  uppercase tracking-wide mb-2">
                            AI Summary
                        </p>
                        <p class="text-sm text-indigo-800 dark:text-indigo-300">
                            {{ $post->ai_summary }}
                        </p>
                    </div>
                @endif

                {{-- Comments Section --}}
                <div class="bg-white dark:bg-gray-800
                            shadow rounded-xl
                            border border-gray-200 dark:border-gray-700
                            p-6" id="comments-section">

                    <h3 class="text-base font-semibold text-gray-800 dark:text-white mb-5">
                        Comments
                        <span id="comment-count"
                              class="ml-1 px-2 py-0.5
                                     bg-indigo-50 dark:bg-indigo-950
                                     text-indigo-600 dark:text-indigo-400
                                     text-xs font-medium rounded-full">
                            {{ $post->comments()->approved()->count() }}
                        </span>
                    </h3>

                    <div id="comments-list" class="space-y-4">
                        @forelse($post->comments()->approved()->with('user')->latest()->get() as $comment)
                            @include('admin.comments._comment', compact('comment'))
                        @empty
                            <p id="no-comments-msg"
                               class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">
                                No comments yet!
                            </p>
                        @endforelse
                    </div>

                </div>

            </div>

            {{-- SIDEBAR --}}
            <div class="space-y-5">

                {{-- Post Meta --}}
                <div class="bg-white dark:bg-gray-800
                            shadow rounded-xl
                            border border-gray-200 dark:border-gray-700
                            p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                               border-b border-gray-100 dark:border-gray-700 pb-2">
                        Post Info
                    </h3>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Status</span>
                        @php
                            $badgeColor = match($post->status) {
                                'published' => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400',
                                'scheduled' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-400',
                                default     => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badgeColor }}">
                            {{ $post->status_label }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Category</span>
                        <span class="text-xs font-medium text-gray-800 dark:text-gray-200">
                            {{ $post->category->name ?? '—' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Author</span>
                        <span class="text-xs font-medium text-gray-800 dark:text-gray-200">
                            {{ $post->user->name ?? '—' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Featured</span>
                        <span class="text-xs font-medium
                                     {{ $post->is_featured ? 'text-yellow-500' : 'text-gray-400 dark:text-gray-600' }}">
                            {{ $post->is_featured ? '★ Yes' : 'No' }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Tags</span>
                        <div class="flex flex-wrap gap-1 justify-end max-w-[65%]">
                            @forelse($post->tags as $tag)
                                <span class="px-2 py-0.5
                                             bg-indigo-50 dark:bg-indigo-950
                                             text-indigo-600 dark:text-indigo-400
                                             text-xs rounded-full">
                                    {{ $tag->name }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400 dark:text-gray-500">No tags</span>
                            @endforelse
                        </div>
                    </div>

                    @if($post->published_at)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Published At</span>
                            <span class="text-xs text-gray-800 dark:text-gray-200">
                                {{ $post->published_at->format('d M Y, H:i') }}
                            </span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Created</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $post->created_at->format('d M Y') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Last Updated</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $post->updated_at->format('d M Y') }}
                        </span>
                    </div>

                    {{-- Reactions breakdown --}}
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                        <span class="text-xs text-gray-500 dark:text-gray-400 block mb-2">
                            Reactions
                        </span>
                        @php
                            $reactionData = $post->getReactionCounts();
                            $totalReactions = array_sum($reactionData);
                        @endphp
                        @if($totalReactions > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach(\App\Models\Reaction::DISPLAY as $type => $display)
                                    @if($reactionData[$type] > 0)
                                        <span class="flex items-center gap-1 px-2 py-1
                                 bg-gray-50 dark:bg-gray-700
                                 rounded-lg text-xs text-gray-600 dark:text-gray-400">
                        {{ $display['emoji'] }} {{ $reactionData[$type] }}
                                </span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-gray-400 dark:text-gray-500">No reactions yet</span>
                        @endif
                    </div>
                </div>

                {{-- AI Metadata --}}
                @if($post->ai_metadata)
                    <div class="bg-white dark:bg-gray-800
                                shadow rounded-xl
                                border border-gray-200 dark:border-gray-700
                                p-5">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                                   border-b border-gray-100 dark:border-gray-700 pb-2 mb-3">
                            AI Metadata
                        </h3>
                        @foreach($post->ai_metadata as $key => $value)
                            <div class="flex items-start justify-between py-1
                                        border-b border-gray-50 dark:border-gray-700 last:border-0">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                    {{ $key }}
                                </span>
                                <span class="text-xs text-gray-700 dark:text-gray-300
                                             text-right max-w-[60%] break-all">
                                    {{ is_array($value) ? json_encode($value) : $value }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Danger Zone --}}
                @if(auth()->user()->can('delete all posts') ||
                   (auth()->user()->can('delete own posts') && $post->user_id === auth()->id()))
                    <div class="bg-white dark:bg-gray-800
                                shadow rounded-xl
                                border border-gray-200 dark:border-gray-700
                                p-5">
                        <h3 class="text-sm font-semibold text-red-500 dark:text-red-400
                                   border-b border-red-100 dark:border-red-900 pb-2 mb-3">
                            Danger Zone
                        </h3>
                        <form action="{{ route('admin.posts.destroy', $post) }}"
                              method="POST"
                              onsubmit="return confirm('Move this post to trash?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full px-4 py-2 text-sm
                                           text-red-600 dark:text-red-400
                                           border border-red-200 dark:border-red-800
                                           rounded-lg hover:bg-red-50 dark:hover:bg-red-950
                                           transition">
                                Move to Trash
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- JavaScript unchanged — no dark mode changes needed in JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content;
            const form        = document.getElementById('comment-form');
            const textarea    = document.getElementById('comment-content');
            const submitBtn   = document.getElementById('comment-submit-btn');
            const errorMsg    = document.getElementById('comment-error');
            const commentList = document.getElementById('comments-list');
            const countBadge  = document.getElementById('comment-count');
            const charCount   = document.getElementById('char-count');
            const noMsg       = document.getElementById('no-comments-msg');

            if (textarea) {
                textarea.addEventListener('input', function () {
                    charCount.textContent = this.value.length;
                });
            }

            if (form) {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Posting...';
                    errorMsg.classList.add('hidden');
                    errorMsg.textContent = '';

                    try {
                        const response = await fetch('{{ route('comments.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN':     csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData,
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            if (noMsg) noMsg.remove();
                            commentList.insertAdjacentHTML('afterbegin', data.html);
                            countBadge.textContent = data.count;
                            textarea.value = '';
                            charCount.textContent = '0';
                        } else if (response.status === 422) {
                            const firstError = Object.values(data.errors)[0][0];
                            errorMsg.textContent = firstError;
                            errorMsg.classList.remove('hidden');
                        } else {
                            errorMsg.textContent = data.message || 'Something went wrong.';
                            errorMsg.classList.remove('hidden');
                        }
                    } catch (err) {
                        errorMsg.textContent = 'Network error. Please check your connection.';
                        errorMsg.classList.remove('hidden');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Post Comment';
                    }
                });
            }

            if (commentList) {
                commentList.addEventListener('click', async function (e) {
                    if (!e.target.classList.contains('delete-comment-btn')) return;
                    if (!confirm('Delete this comment?')) return;

                    const btn       = e.target;
                    const commentId = btn.dataset.commentId;
                    const commentEl = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);

                    try {
                        const response = await fetch(`/comments/${commentId}`, {
                            method:  'DELETE',
                            headers: {
                                'X-CSRF-TOKEN':     csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type':     'application/json',
                            },
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            commentEl.style.transition = 'opacity 0.3s ease';
                            commentEl.style.opacity    = '0';
                            setTimeout(() => {
                                commentEl.remove();
                                const newCount = Math.max(0, parseInt(countBadge.textContent) - 1);
                                countBadge.textContent = newCount;
                                if (commentList.querySelectorAll('.comment-item').length === 0) {
                                    commentList.innerHTML =
                                        '<p id="no-comments-msg" class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">No comments yet!</p>';
                                }
                            }, 300);
                        } else {
                            alert(data.message || 'Could not delete comment.');
                        }
                    } catch (err) {
                        alert('Network error. Please try again.');
                    }
                });
            }
        });
    </script>
</x-app-layout>
