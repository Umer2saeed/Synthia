<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            Revisions
        </h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            {{ Str::limit($post->title, 60) }}
        </p>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">

        {{-- Flash message --}}
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900
                        border border-green-300 dark:border-green-700
                        text-green-800 dark:text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Back + Edit buttons --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('admin.posts.edit', $post) }}"
               class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400
                      hover:text-gray-700 dark:hover:text-gray-200 transition">
                ← Back to Editor
            </a>
            <span class="text-xs text-gray-400 dark:text-gray-500">
                Keeping last {{ \App\Services\RevisionService::MAX_REVISIONS }} revisions
            </span>
        </div>

        {{-- Info banner --}}
        <div class="mb-6 px-4 py-3 rounded-xl
                    bg-blue-50 dark:bg-blue-950
                    border border-blue-100 dark:border-blue-900
                    text-blue-700 dark:text-blue-400 text-sm flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Each revision is a snapshot of the post content at the time of an update.
            Restoring a revision will save the current version as a new revision first.
        </div>

        @if($revisions->isEmpty())
            <div class="text-center py-20 bg-white dark:bg-gray-800
                        rounded-2xl border border-gray-200 dark:border-gray-700">
                <p class="text-gray-400 dark:text-gray-500 text-sm">
                    No revisions yet. Revisions are created each time a post is updated.
                </p>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800
                        rounded-2xl border border-gray-200 dark:border-gray-700
                        overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900
                                  text-gray-500 dark:text-gray-400
                                  uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-5 py-4">#</th>
                        <th class="px-5 py-4">Title Snapshot</th>
                        <th class="px-5 py-4">Saved By</th>
                        <th class="px-5 py-4">When</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($revisions as $index => $revision)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                            <td class="px-5 py-4">
                                    <span class="w-7 h-7 rounded-full
                                                 bg-indigo-50 dark:bg-indigo-950
                                                 text-indigo-600 dark:text-indigo-400
                                                 text-xs font-bold
                                                 flex items-center justify-center">
                                        {{ $revisions->count() - $index }}
                                    </span>
                            </td>

                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-800 dark:text-gray-200
                                               text-xs">
                                    {{ Str::limit($revision->title, 60) }}
                                </p>
                                {{-- Highlight if title changed vs current --}}
                                @if($revision->title !== $post->title)
                                    <span class="text-xs text-amber-500 dark:text-amber-400">
                                            Title differs from current
                                        </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    @if($revision->user)
                                        <img src="{{ $revision->user->avatar_url }}"
                                             alt="{{ $revision->user->name }}"
                                             class="w-6 h-6 rounded-full object-cover
                                                        border border-gray-200 dark:border-gray-700">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">
                                                {{ $revision->user->name }}
                                            </span>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">
                                                System
                                            </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4 text-xs text-gray-400 dark:text-gray-500">
                                    <span title="{{ $revision->created_at->format('d M Y H:i:s') }}">
                                        {{ $revision->created_at->diffForHumans() }}
                                    </span>
                            </td>

                            <td class="px-5 py-4 text-right space-x-3">
                                <a href="{{ route('admin.posts.revisions.show', [$post, $revision]) }}"
                                   class="text-indigo-600 dark:text-indigo-400
                                              hover:underline text-xs font-medium">
                                    View Diff
                                </a>

                                <form action="{{ route('admin.posts.revisions.restore', [$post, $revision]) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Restore this revision? Current content will be saved as a new revision first.')">
                                    @csrf
                                    <button type="submit"
                                            class="text-green-600 dark:text-green-400
                                                       hover:underline text-xs font-medium">
                                        Restore
                                    </button>
                                </form>
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</x-app-layout>
