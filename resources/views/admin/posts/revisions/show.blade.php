<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            Revision Diff
        </h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            {{ $revision->created_at->format('d M Y, H:i') }}
            · by {{ $revision->user->name ?? 'System' }}
        </p>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

        {{-- Navigation --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('admin.posts.revisions.index', $post) }}"
               class="text-sm text-gray-500 dark:text-gray-400
                      hover:text-gray-700 dark:hover:text-gray-200 transition">
                ← All Revisions
            </a>

            <form action="{{ route('admin.posts.revisions.restore', [$post, $revision]) }}"
                  method="POST"
                  onsubmit="return confirm('Restore this revision?')">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700
                               text-white text-sm font-medium rounded-lg transition">
                    ↩ Restore This Revision
                </button>
            </form>
        </div>

        {{-- Legend --}}
        <div class="flex items-center gap-6 mb-5 text-xs">
            <span class="flex items-center gap-2">
                <span class="inline-block w-4 h-4 rounded
                             bg-red-100 dark:bg-red-950
                             border border-red-300 dark:border-red-800"></span>
                <span class="text-gray-600 dark:text-gray-400">Removed from current</span>
            </span>
            <span class="flex items-center gap-2">
                <span class="inline-block w-4 h-4 rounded
                             bg-green-100 dark:bg-green-950
                             border border-green-300 dark:border-green-800"></span>
                <span class="text-gray-600 dark:text-gray-400">Added in revision</span>
            </span>
            <span class="flex items-center gap-2">
                <span class="inline-block w-4 h-4 rounded
                             bg-gray-100 dark:bg-gray-700"></span>
                <span class="text-gray-600 dark:text-gray-400">Unchanged</span>
            </span>
        </div>

        {{-- Title diff --}}
        <div class="bg-white dark:bg-gray-800
                    rounded-2xl border border-gray-200 dark:border-gray-700
                    overflow-hidden mb-5">

            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700
                        bg-gray-50 dark:bg-gray-900">
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400
                            uppercase tracking-wider">
                    Title
                </h3>
            </div>

            <div class="px-5 py-4 font-semibold text-lg leading-relaxed
                        text-gray-800 dark:text-gray-200 break-words">
                @foreach($titleDiff as $segment)
                    @if($segment['type'] === 'delete')
                        <span class="bg-red-100 dark:bg-red-950
                                     text-red-700 dark:text-red-400
                                     line-through rounded px-0.5">{{ $segment['text'] }}</span>
                    @elseif($segment['type'] === 'insert')
                        <span class="bg-green-100 dark:bg-green-950
                                     text-green-700 dark:text-green-400
                                     rounded px-0.5">{{ $segment['text'] }}</span>
                    @else
                        <span>{{ $segment['text'] }}</span>
                    @endif
                @endforeach
            </div>

        </div>

        {{--
        | ONE x-data wrapper for the entire content section.
        | Both the toggle buttons and the panels share the same `view` variable.
        | This is the fix for the split view not responding to button clicks.
        --}}
        <div class="bg-white dark:bg-gray-800
                    rounded-2xl border border-gray-200 dark:border-gray-700
                    overflow-hidden"
             x-data="{ view: 'diff' }">

            {{-- Tab header --}}
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700
                        bg-gray-50 dark:bg-gray-900 flex items-center justify-between">
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400
                            uppercase tracking-wider">
                    Content
                </h3>

                {{-- View toggle — same x-data scope as panels below --}}
                <div class="flex items-center gap-3">
                    <button type="button"
                            @click="view = 'diff'"
                            :class="view === 'diff'
                                ? 'text-indigo-600 dark:text-indigo-400 font-semibold underline underline-offset-2'
                                : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                            class="text-xs transition">
                        Diff View
                    </button>
                    <span class="text-gray-300 dark:text-gray-600 text-xs">|</span>
                    <button type="button"
                            @click="view = 'split'"
                            :class="view === 'split'
                                ? 'text-indigo-600 dark:text-indigo-400 font-semibold underline underline-offset-2'
                                : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                            class="text-xs transition">
                        Split View
                    </button>
                </div>
            </div>

            {{-- Diff View Panel --}}
            <div x-show="view === 'diff'"
                 class="px-5 py-5 text-sm leading-relaxed
                        text-gray-700 dark:text-gray-300
                        break-words whitespace-pre-wrap font-mono
                        max-h-[600px] overflow-y-auto">
                @foreach($contentDiff as $segment)
                    @if($segment['type'] === 'delete')
                        <span class="bg-red-100 dark:bg-red-950
                                     text-red-700 dark:text-red-400
                                     line-through">{{ $segment['text'] }}</span>
                    @elseif($segment['type'] === 'insert')
                        <span class="bg-green-100 dark:bg-green-950
                                     text-green-700 dark:text-green-400">{{ $segment['text'] }}</span>
                    @else
                        <span>{{ $segment['text'] }}</span>
                    @endif
                @endforeach
            </div>

            {{-- Split View Panel --}}
            <div x-show="view === 'split'"
                 x-cloak
                 class="grid grid-cols-2 divide-x divide-gray-100 dark:divide-gray-700
                        max-h-[600px]">

                <div class="p-5 overflow-y-auto">
                    <p class="text-xs font-semibold text-amber-600 dark:text-amber-400
                               uppercase tracking-wider mb-3 sticky top-0
                               bg-white dark:bg-gray-800 pb-2">
                        Revision (saved snapshot)
                    </p>
                    <div class="text-xs text-gray-600 dark:text-gray-400
                                leading-relaxed font-mono whitespace-pre-wrap break-words">
                        {{ strip_tags($revision->content) }}
                    </div>
                </div>

                <div class="p-5 overflow-y-auto">
                    <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400
                               uppercase tracking-wider mb-3 sticky top-0
                               bg-white dark:bg-gray-800 pb-2">
                        Current (live version)
                    </p>
                    <div class="text-xs text-gray-600 dark:text-gray-400
                                leading-relaxed font-mono whitespace-pre-wrap break-words">
                        {{ strip_tags($post->content) }}
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
