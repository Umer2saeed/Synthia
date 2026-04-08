<x-app-layout>
    {{-- Header buttons --}}
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h2 class="text-xl font-semibold text-gray-800">Post Details</h2>
            <div class="flex items-center gap-2">

                {{-- Edit: admin/editor OR own post author --}}
                @if(auth()->user()->can('edit all posts') ||
                   (auth()->user()->can('edit own posts') && $post->user_id === auth()->id()))
                    <a href="{{ route('admin.posts.edit', $post) }}"
                       class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                        Edit
                    </a>
                @endif

                <a href="{{ route('admin.posts.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition">
                    ← Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- =============================================
                 MAIN COLUMN: Cover + Content + Comments
                 ============================================= --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Cover Image --}}
                <div class="bg-white shadow rounded-xl overflow-hidden">
                    <img src="{{ $post->cover_image_url }}"
                         alt="{{ $post->title }}"
                         class="w-full h-64 object-cover">
                </div>

                {{-- Title + Content --}}
                <div class="bg-white shadow rounded-xl p-6 space-y-4">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $post->title }}</h1>
                    <p class="text-xs text-gray-400 font-mono">slug: {{ $post->slug }}</p>
                    <hr class="border-gray-100">
                    <div class="text-sm text-gray-700 leading-relaxed prose max-w-none">
                        {!! nl2br(e($post->content)) !!}
                    </div>
                </div>

                {{-- AI Summary --}}
                @if($post->ai_summary)
                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5">
                        <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wide mb-2">
                            AI Summary
                        </p>
                        <p class="text-sm text-indigo-800">{{ $post->ai_summary }}</p>
                    </div>
                @endif

                {{-- =============================================
                     COMMENTS SECTION
                     ============================================= --}}
                <div class="bg-white shadow rounded-xl p-6" id="comments-section">

                    {{-- Header with live count --}}
                    <h3 class="text-base font-semibold text-gray-800 mb-5">
                        Comments
                        <span id="comment-count"
                              class="ml-1 px-2 py-0.5 bg-indigo-50 text-indigo-600 text-xs font-medium rounded-full">
                            {{ $post->comments()->approved()->count() }}
                        </span>
                    </h3>

                    {{-- =========================================
                         COMMENTS LIST
                         ========================================= --}}
                    <div id="comments-list" class="space-y-4">
                        @forelse($post->comments()->approved()->with('user')->latest()->get() as $comment)
                            @include('admin.comments._comment', compact('comment'))
                        @empty
                            <p id="no-comments-msg" class="text-sm text-gray-400 text-center py-4">
                                No comments yet!
                            </p>
                        @endforelse
                    </div>

                </div>
                {{-- END COMMENTS SECTION --}}

            </div>

            {{-- =============================================
                 SIDEBAR: Meta Details
                 ============================================= --}}
            <div class="space-y-5">

                {{-- Post Meta --}}
                <div class="bg-white shadow rounded-xl p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 border-b pb-2">Post Info</h3>

                    {{-- Status Badge --}}
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Status</span>
                        @php
                            $badgeColor = match($post->status) {
                                'published' => 'bg-green-100 text-green-700',
                                'scheduled' => 'bg-yellow-100 text-yellow-700',
                                default     => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badgeColor }}">
                            {{ $post->status_label }}
                        </span>
                    </div>

                    {{-- Category --}}
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Category</span>
                        <span class="text-xs font-medium text-gray-800">
                            {{ $post->category->name ?? '—' }}
                        </span>
                    </div>

                    {{-- Author --}}
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Author</span>
                        <span class="text-xs font-medium text-gray-800">{{ $post->user->name ?? '—' }}</span>
                    </div>

                    {{-- Featured --}}
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Featured</span>
                        <span class="text-xs font-medium {{ $post->is_featured ? 'text-yellow-500' : 'text-gray-400' }}">
                            {{ $post->is_featured ? '★ Yes' : 'No' }}
                        </span>
                    </div>

                    {{-- Tags --}}
                    <div class="flex items-start justify-between">
                        <span class="text-xs text-gray-500">Tags</span>
                        <div class="flex flex-wrap gap-1 justify-end max-w-[65%]">
                            @forelse($post->tags as $tag)
                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-xs rounded-full">
                                    {{ $tag->name }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400">No tags</span>
                            @endforelse
                        </div>
                    </div>

                    {{-- Published At --}}
                    @if($post->published_at)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Published At</span>
                            <span class="text-xs text-gray-800">
                                {{ $post->published_at->format('d M Y, H:i') }}
                            </span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Created</span>
                        <span class="text-xs text-gray-500">{{ $post->created_at->format('d M Y') }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Last Updated</span>
                        <span class="text-xs text-gray-500">{{ $post->updated_at->format('d M Y') }}</span>
                    </div>
                </div>

                {{-- AI Metadata --}}
                @if($post->ai_metadata)
                    <div class="bg-white shadow rounded-xl p-5">
                        <h3 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-3">AI Metadata</h3>
                        @foreach($post->ai_metadata as $key => $value)
                            <div class="flex items-start justify-between py-1 border-b border-gray-50 last:border-0">
                                <span class="text-xs text-gray-400 font-mono">{{ $key }}</span>
                                <span class="text-xs text-gray-700 text-right max-w-[60%] break-all">
                                    {{ is_array($value) ? json_encode($value) : $value }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Danger Zone --}}
                @if(auth()->user()->can('delete all posts') ||
                   (auth()->user()->can('delete own posts') && $post->user_id === auth()->id()))
                    <div class="bg-white shadow rounded-xl p-5">
                        <h3 class="text-sm font-semibold text-red-500 border-b border-red-100 pb-2 mb-3">
                            Danger Zone
                        </h3>
                        <form action="{{ route('admin.posts.destroy', $post) }}"
                              method="POST"
                              onsubmit="return confirm('Move this post to trash?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full px-4 py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">
                                Move to Trash
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- =============================================
         AJAX COMMENT JAVASCRIPT
         =============================================

         This script handles three things:
           1. Submitting a new comment via fetch() (no page reload)
           2. Deleting a comment via fetch() (no page reload)
           3. Live character counter on the textarea

         We use the native fetch() API — no jQuery needed.
         CSRF token is read from the meta tag that Laravel Breeze
         already includes in your layout.
    --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /*
            |----------------------------------------------------------------------
            | Read the CSRF token from the page
            |----------------------------------------------------------------------
            | Laravel requires this token on every POST/DELETE/PATCH request
            | to protect against Cross-Site Request Forgery attacks.
            | Breeze's layout already includes <meta name="csrf-token"> for us.
            */
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            /*
            |----------------------------------------------------------------------
            | Elements
            |----------------------------------------------------------------------
            */
            const form        = document.getElementById('comment-form');
            const textarea    = document.getElementById('comment-content');
            const submitBtn   = document.getElementById('comment-submit-btn');
            const errorMsg    = document.getElementById('comment-error');
            const commentList = document.getElementById('comments-list');
            const countBadge  = document.getElementById('comment-count');
            const charCount   = document.getElementById('char-count');
            const noMsg       = document.getElementById('no-comments-msg');

            /*
            |----------------------------------------------------------------------
            | 1. Live character counter
            |----------------------------------------------------------------------
            */
            if (textarea) {
                textarea.addEventListener('input', function () {
                    charCount.textContent = this.value.length;
                });
            }

            /*
            |----------------------------------------------------------------------
            | 2. AJAX Comment Submission
            |----------------------------------------------------------------------
            | We listen for the form's submit event, prevent the default
            | (which would cause a full page reload), then use fetch() to
            | POST the data to our CommentController@store route.
            */
            if (form) {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault(); // Stop the browser from submitting normally

                    // Collect form data (includes post_id, content, _token)
                    const formData = new FormData(form);

                    // Disable the button to prevent double submission
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Posting...';

                    // Hide any previous error
                    errorMsg.classList.add('hidden');
                    errorMsg.textContent = '';

                    try {
                        /*
                        | Send the POST request to /comments
                        | Headers must include:
                        |   X-CSRF-TOKEN → Laravel's CSRF protection
                        |   X-Requested-With → tells Laravel this is an AJAX request
                        |                       (makes $request->ajax() return true)
                        */
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

                            // Remove "No comments yet" message if it exists
                            if (noMsg) noMsg.remove();

                            /*
                            | Inject the server-rendered HTML for the new comment
                            | at the TOP of the comments list (afterbegin = first child).
                            | This is the HTML string returned by view()->render() in the controller.
                            */
                            commentList.insertAdjacentHTML('afterbegin', data.html);

                            // Update the comment count badge
                            countBadge.textContent = data.count;

                            // Clear the textarea and reset char counter
                            textarea.value = '';
                            charCount.textContent = '0';

                        } else if (response.status === 422) {
                            /*
                            | 422 Unprocessable Entity = Laravel validation failed.
                            | Laravel returns errors as: { errors: { content: ['...'] } }
                            */
                            const errors = data.errors;
                            const firstError = Object.values(errors)[0][0];
                            errorMsg.textContent = firstError;
                            errorMsg.classList.remove('hidden');

                        } else {
                            // Generic error fallback
                            errorMsg.textContent = data.message || 'Something went wrong. Please try again.';
                            errorMsg.classList.remove('hidden');
                        }

                    } catch (err) {
                        // Network error or server completely down
                        errorMsg.textContent = 'Network error. Please check your connection.';
                        errorMsg.classList.remove('hidden');

                    } finally {
                        // Always re-enable the button when done
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Post Comment';
                    }
                });
            }

            /*
            |----------------------------------------------------------------------
            | 3. AJAX Comment Delete
            |----------------------------------------------------------------------
            | We use event delegation — listen on the parent container rather than
            | individual buttons. This way, dynamically added comments (via AJAX
            | above) also get delete functionality without re-binding events.
            */
            if (commentList) {
                commentList.addEventListener('click', async function (e) {

                    // Only trigger if a delete button was clicked
                    if (!e.target.classList.contains('delete-comment-btn')) return;

                    if (!confirm('Delete this comment?')) return;

                    const btn       = e.target;
                    const commentId = btn.dataset.commentId;
                    const commentEl = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);

                    try {
                        /*
                        | Send DELETE request to /comments/{id}
                        | Laravel requires _method=DELETE spoofing for HTML forms,
                        | but with fetch() we can use method: 'DELETE' directly.
                        */
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
                            // Animate out and remove the comment element from DOM
                            commentEl.style.transition = 'opacity 0.3s ease';
                            commentEl.style.opacity    = '0';

                            setTimeout(() => {
                                commentEl.remove();

                                // Update the count badge
                                const currentCount = parseInt(countBadge.textContent);
                                const newCount     = Math.max(0, currentCount - 1);
                                countBadge.textContent = newCount;

                                // Show "no comments" message if list is now empty
                                if (commentList.querySelectorAll('.comment-item').length === 0) {
                                    commentList.innerHTML =
                                        '<p id="no-comments-msg" class="text-sm text-gray-400 text-center py-4">No comments yet. Be the first to comment!</p>';
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
