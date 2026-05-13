<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                Moderation Queue
            </h2>
            @if($pendingCount + $flaggedCount > 0)
                <span class="px-2.5 py-1 text-xs font-bold rounded-full
                             bg-red-100 dark:bg-red-900
                             text-red-700 dark:text-red-400">
                    {{ $pendingCount + $flaggedCount }} need attention
                </span>
            @endif
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            {{ now()->format('l, d F Y') }}
        </p>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900
                        border border-green-300 dark:border-green-700
                        text-green-800 dark:text-green-300 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tabs --}}
        <div x-data="{ tab: 'pending' }" class="space-y-5">

            <div class="flex gap-1 p-1
                        bg-gray-100 dark:bg-gray-800
                        rounded-xl w-fit">
                <button type="button"
                        @click="tab = 'pending'"
                        :class="tab === 'pending'
                            ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="px-5 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2">
                    Pending
                    @if($pendingCount)
                        <span class="px-1.5 py-0.5 text-xs rounded-full
                                     bg-amber-100 dark:bg-amber-900
                                     text-amber-700 dark:text-amber-400">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </button>
                <button type="button"
                        @click="tab = 'flagged'"
                        :class="tab === 'flagged'
                            ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="px-5 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2">
                    Flagged
                    @if($flaggedCount)
                        <span class="px-1.5 py-0.5 text-xs rounded-full
                                     bg-red-100 dark:bg-red-900
                                     text-red-700 dark:text-red-400">
                            {{ $flaggedCount }}
                        </span>
                    @endif
                </button>
            </div>

            {{-- =============================================
                 PENDING TAB
            ============================================= --}}
            <div x-show="tab === 'pending'" x-cloak>

                @if($pendingComments->isEmpty())
                    <div class="text-center py-20 bg-white dark:bg-gray-800
                                rounded-2xl border border-gray-200 dark:border-gray-700">
                        <span class="text-4xl block mb-3">✅</span>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                            No pending comments
                        </p>
                        <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">
                            All comments have been moderated.
                        </p>
                    </div>
                @else

                    {{-- Bulk action form --}}
                    <form id="pending-bulk-form"
                          action="{{ route('admin.moderation.bulk') }}"
                          method="POST">
                        @csrf

                        <div class="flex items-center gap-3 mb-4
                                    px-4 py-3 bg-white dark:bg-gray-800
                                    border border-gray-200 dark:border-gray-700
                                    rounded-xl">

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="pending-select-all"
                                       class="w-4 h-4 rounded border-gray-300 dark:border-gray-600
                                              text-indigo-600 focus:ring-indigo-400">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    Select All
                                </span>
                            </label>

                            <div class="ml-auto flex items-center gap-2">
                                <input type="hidden" name="action" id="pending-bulk-action" value="">
                                <button type="button"
                                        onclick="submitBulk('pending-bulk-form', 'pending-bulk-action', 'approve')"
                                        class="px-3 py-1.5 text-xs font-medium
                                               bg-green-100 dark:bg-green-900
                                               text-green-700 dark:text-green-400
                                               rounded-lg hover:bg-green-200 dark:hover:bg-green-800
                                               transition">
                                    ✓ Approve Selected
                                </button>
                                <button type="button"
                                        onclick="submitBulk('pending-bulk-form', 'pending-bulk-action', 'reject')"
                                        class="px-3 py-1.5 text-xs font-medium
                                               bg-red-100 dark:bg-red-900
                                               text-red-700 dark:text-red-400
                                               rounded-lg hover:bg-red-200 dark:hover:bg-red-800
                                               transition">
                                    ✗ Reject Selected
                                </button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach($pendingComments as $comment)
                                <div class="bg-white dark:bg-gray-800
                                            rounded-2xl border border-gray-200 dark:border-gray-700
                                            overflow-hidden">

                                    {{-- Comment header --}}
                                    <div class="flex items-center gap-3 px-5 py-3
                                                border-b border-gray-100 dark:border-gray-700
                                                bg-amber-50 dark:bg-amber-950/30">

                                        <input type="checkbox"
                                               name="ids[]"
                                               value="{{ $comment->id }}"
                                               class="pending-checkbox w-4 h-4 rounded
                                                      border-gray-300 dark:border-gray-600
                                                      text-indigo-600 focus:ring-indigo-400">

                                        <img src="{{ $comment->user->avatar_url }}"
                                             alt="{{ $comment->user->name }}"
                                             class="w-7 h-7 rounded-full object-cover
                                                    border border-gray-200 dark:border-gray-700">

                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold
                                                       text-gray-800 dark:text-gray-200">
                                                {{ $comment->user->name }}
                                            </p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                                on
                                                <a href="{{ route('blog.post', $comment->post->slug) }}"
                                                   target="_blank"
                                                   class="text-indigo-500 dark:text-indigo-400
                                                          hover:underline">
                                                    {{ Str::limit($comment->post->title, 50) }}
                                                </a>
                                                · {{ $comment->created_at->diffForHumans() }}
                                            </p>
                                        </div>

                                        @if($comment->flags_count > 0)
                                            <span class="px-2 py-0.5 text-xs rounded-full
                                                         bg-red-100 dark:bg-red-900
                                                         text-red-600 dark:text-red-400">
                                                🚩 {{ $comment->flags_count }} flag{{ $comment->flags_count > 1 ? 's' : '' }}
                                            </span>
                                        @endif

                                    </div>

                                    {{-- Comment content --}}
                                    <div class="px-5 py-4">
                                        <p class="text-sm text-gray-700 dark:text-gray-300
                                                   leading-relaxed">
                                            {{ $comment->content }}
                                        </p>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700
                                                flex items-center gap-3
                                                bg-gray-50 dark:bg-gray-900/40">

                                        <form action="{{ route('admin.moderation.approve', $comment) }}"
                                              method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="px-4 py-1.5 text-xs font-medium
                                                           bg-green-600 hover:bg-green-700
                                                           text-white rounded-lg transition">
                                                ✓ Approve
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.moderation.reject', $comment) }}"
                                              method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="px-4 py-1.5 text-xs font-medium
                                                           bg-red-600 hover:bg-red-700
                                                           text-white rounded-lg transition">
                                                ✗ Reject
                                            </button>
                                        </form>

                                        <a href="{{ route('blog.post', $comment->post->slug) }}#comment-{{ $comment->id }}"
                                           target="_blank"
                                           class="ml-auto text-xs text-gray-400 dark:text-gray-500
                                                  hover:text-indigo-500 dark:hover:text-indigo-400
                                                  transition">
                                            View in context →
                                        </a>

                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </form>

                    <div class="mt-4">{{ $pendingComments->links() }}</div>

                @endif
            </div>

            {{-- =============================================
                 FLAGGED TAB
            ============================================= --}}
            <div x-show="tab === 'flagged'" x-cloak>

                @if($flaggedComments->isEmpty())
                    <div class="text-center py-20 bg-white dark:bg-gray-800
                                rounded-2xl border border-gray-200 dark:border-gray-700">
                        <span class="text-4xl block mb-3">🏳️</span>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                            No flagged comments
                        </p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($flaggedComments as $comment)
                            <div class="bg-white dark:bg-gray-800
                                        rounded-2xl border border-red-100 dark:border-red-900
                                        overflow-hidden">

                                <div class="flex items-center gap-3 px-5 py-3
                                            border-b border-red-100 dark:border-red-900
                                            bg-red-50 dark:bg-red-950/30">

                                    <img src="{{ $comment->user->avatar_url }}"
                                         alt="{{ $comment->user->name }}"
                                         class="w-7 h-7 rounded-full object-cover">

                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold
                                                   text-gray-800 dark:text-gray-200">
                                            {{ $comment->user->name }}
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            on
                                            <a href="{{ route('blog.post', $comment->post->slug) }}"
                                               target="_blank"
                                               class="text-indigo-500 dark:text-indigo-400 hover:underline">
                                                {{ Str::limit($comment->post->title, 50) }}
                                            </a>
                                        </p>
                                    </div>

                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                                 bg-red-100 dark:bg-red-900
                                                 text-red-700 dark:text-red-400">
                                        🚩 {{ $comment->flags_count }}
                                        {{ Str::plural('flag', $comment->flags_count) }}
                                    </span>

                                </div>

                                <div class="px-5 py-4">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                        {{ $comment->content }}
                                    </p>

                                    {{-- Show flag reasons --}}
                                    @if($comment->flags->isNotEmpty())
                                        <div class="mt-3 space-y-1">
                                            @foreach($comment->flags->take(3) as $flag)
                                                @if($flag->reason)
                                                    <p class="text-xs text-gray-400 dark:text-gray-500
                                                               italic">
                                                        "{{ $flag->reason }}"
                                                        — {{ $flag->user->name ?? 'Anonymous' }}
                                                    </p>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700
                                            flex items-center gap-3 bg-gray-50 dark:bg-gray-900/40">

                                    {{-- Dismiss flags — keep comment --}}
                                    <form action="{{ route('admin.moderation.dismiss', $comment) }}"
                                          method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="px-4 py-1.5 text-xs font-medium
                                                       bg-gray-200 dark:bg-gray-700
                                                       text-gray-700 dark:text-gray-300
                                                       rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600
                                                       transition">
                                            Dismiss Flags
                                        </button>
                                    </form>

                                    {{-- Reject comment entirely --}}
                                    <form action="{{ route('admin.moderation.reject', $comment) }}"
                                          method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="px-4 py-1.5 text-xs font-medium
                                                       bg-red-600 hover:bg-red-700
                                                       text-white rounded-lg transition">
                                            Remove Comment
                                        </button>
                                    </form>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">{{ $flaggedComments->links() }}</div>
                @endif

            </div>

        </div>
    </div>

    <script>
        // Select all for pending tab
        document.getElementById('pending-select-all')?.addEventListener('change', function () {
            document.querySelectorAll('.pending-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });

        function submitBulk(formId, actionInputId, action) {
            const checked = document.querySelectorAll('.pending-checkbox:checked').length;
            if (!checked) {
                alert('Please select at least one comment.');
                return;
            }
            if (!confirm(`${action === 'approve' ? 'Approve' : 'Reject'} ${checked} comment${checked > 1 ? 's' : ''}?`)) return;
            document.getElementById(actionInputId).value = action;
            document.getElementById(formId).submit();
        }
    </script>

</x-app-layout>
