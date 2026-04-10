{{-- Safety net on top of route middleware --}}
@cannot('delete comments')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Comments</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        {{-- Quick stats --}}
        <div class="mb-4 flex gap-3 text-xs items-center flex-wrap">
            <span class="px-3 py-1 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full">
                Pending: {{ \App\Models\Comment::pending()->count() }}
            </span>
            <span class="px-3 py-1 bg-green-50 text-green-700 border border-green-200 rounded-full">
                Approved: {{ \App\Models\Comment::approved()->count() }}
            </span>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.comments.index') }}"
              class="mb-5 flex flex-wrap gap-3 items-center">

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search comment content..."
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-72">

            <select name="status"
                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Comments</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
            </select>

            <button type="submit"
                    class="px-4 py-2 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-800 transition">
                Filter
            </button>

            @if(request('search') || request('status'))
                <a href="{{ route('admin.comments.index') }}"
                   class="text-sm text-red-500 hover:underline">Clear</a>
            @endif
        </form>

        {{-- Comments Table --}}
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-4">Author</th>
                    <th class="px-5 py-4">Comment</th>
                    <th class="px-5 py-4">Post</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Date</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="admin-comments-list">
                @forelse($comments as $comment)
                    <tr class="hover:bg-gray-50 transition align-top"
                        id="comment-row-{{ $comment->id }}">

                        {{-- Author --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <img src="{{ $comment->user->avatar_url }}"
                                     alt="{{ $comment->user->name }}"
                                     class="w-7 h-7 rounded-full object-cover border border-gray-200 shrink-0">
                                <div>
                                    <p class="font-medium text-gray-800 text-xs">
                                        {{ $comment->user->name }}
                                    </p>
                                    <p class="text-gray-400 text-xs">
                                        {{ $comment->user->email }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Content --}}
                        <td class="px-5 py-4 max-w-xs">
                            <p class="text-gray-700 text-xs leading-relaxed line-clamp-3">
                                {{ $comment->content }}
                            </p>
                        </td>

                        {{-- Post Link --}}
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.posts.show', $comment->post) }}"
                               class="text-indigo-600 hover:underline text-xs">
                                {{ Str::limit($comment->post->title, 35) }}
                            </a>
                        </td>

                        {{-- Approval Toggle --}}
                        <td class="px-5 py-4">
                            <button
                                type="button" class="approve-btn px-2 py-1 rounded-full text-xs font-medium transition
                                        {{ $comment->is_approved
                                            ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                            : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' }}"
                                data-comment-id="{{ $comment->id }}"
                                data-approved="{{ $comment->is_approved ? '1' : '0' }}">
                                {{ $comment->is_approved ? 'Approved' : 'Pending' }}
                            </button>
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-4 text-gray-400 text-xs">
                            {{ $comment->created_at->diffForHumans() }}
                        </td>

                        {{-- Delete --}}
                        <td class="px-5 py-4 text-right">
                            <button
                                type="button"
                                class="admin-delete-btn text-red-500 hover:underline text-xs font-medium"
                                data-comment-id="{{ $comment->id }}">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"
                            class="px-6 py-12 text-center text-gray-400">
                            No comments found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $comments->links() }}
        </div>
    </div>

    {{-- AJAX: approve toggle + delete --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            /*
            |----------------------------------------------------------------------
            | Approve / Unapprove Toggle
            |----------------------------------------------------------------------
            */
            document.querySelectorAll('.approve-btn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const commentId = this.dataset.commentId;

                    try {
                        const response = await fetch(`/comments/${commentId}/approve`, {
                            method:  'PATCH',
                            headers: {
                                'X-CSRF-TOKEN':     csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type':     'application/json',
                            },
                        });

                        const data = await response.json();

                        console.log(data);

                        if (data.success) {
                            const isApproved = data.is_approved;

                            this.textContent      = isApproved ? 'Approved' : 'Pending';
                            this.dataset.approved = isApproved ? '1' : '0';
                            this.className = `approve-btn px-2 py-1 rounded-full text-xs font-medium transition ${
                                isApproved
                                    ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                    : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                            }`;
                        }
                    } catch (err) {
                        alert('Could not update status. Try again.');
                    }
                });
            });

            /*
            |----------------------------------------------------------------------
            | Admin Delete
            |----------------------------------------------------------------------
            */
            document.querySelectorAll('.admin-delete-btn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    if (!confirm('Permanently delete this comment?')) return;

                    const commentId = this.dataset.commentId;
                    const row       = document.getElementById(`comment-row-${commentId}`);

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

                        if (data.success) {
                            row.style.transition = 'opacity 0.3s ease';
                            row.style.opacity    = '0';
                            setTimeout(() => row.remove(), 300);
                        } else {
                            alert(data.message || 'Could not delete.');
                        }
                    } catch (err) {
                        alert('Network error. Try again.');
                    }
                });
            });

        });
    </script>

</x-app-layout>
