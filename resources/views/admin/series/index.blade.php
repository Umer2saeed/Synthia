<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Series</h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            {{ now()->format('l, d F Y') }}
        </p>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900
                        border border-green-300 dark:border-green-700
                        text-green-800 dark:text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-end mb-4">
            <a href="{{ route('admin.series.create') }}"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                      text-white text-sm rounded-lg transition">
                + New Series
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl overflow-hidden
                    border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-900
                              text-gray-500 dark:text-gray-400
                              uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-4">Title</th>
                    <th class="px-5 py-4">Author</th>
                    <th class="px-5 py-4">Posts</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Created</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($series as $s)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $s->cover_image_url }}"
                                     alt="{{ $s->title }}"
                                     class="w-10 h-10 rounded-lg object-cover
                                                border border-gray-200 dark:border-gray-700">
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-200">
                                        {{ Str::limit($s->title, 50) }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                        {{ $s->slug }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-3 text-gray-600 dark:text-gray-400 text-xs">
                            {{ $s->user->name ?? '—' }}
                        </td>

                        <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                            {{ $s->posts_count }}
                        </td>

                        <td class="px-5 py-3">
                            @if($s->is_complete)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                                 bg-green-100 dark:bg-green-900
                                                 text-green-700 dark:text-green-400">
                                        Complete
                                    </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                                 bg-yellow-100 dark:bg-yellow-900
                                                 text-yellow-700 dark:text-yellow-400">
                                        Ongoing
                                    </span>
                            @endif
                        </td>

                        <td class="px-5 py-3 text-gray-400 dark:text-gray-500 text-xs">
                            {{ $s->created_at->format('d M Y') }}
                        </td>

                        <td class="px-5 py-3 text-right space-x-3">
                            <a href="{{ route('series.show', $s->slug) }}"
                               target="_blank"
                               class="text-gray-500 dark:text-gray-400
                                          hover:text-gray-700 dark:hover:text-gray-200
                                          text-xs font-medium">
                                View
                            </a>
                            <a href="{{ route('admin.series.edit', $s) }}"
                               class="text-indigo-600 dark:text-indigo-400
                                          hover:underline text-xs font-medium">
                                Edit
                            </a>
                            <form action="{{ route('admin.series.destroy', $s) }}"
                                  method="POST" class="inline"
                                  onsubmit="return confirm('Delete series \'{{ addslashes($s->title) }}\'?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-500 dark:text-red-400
                                                   hover:underline text-xs font-medium">
                                    Delete
                                </button>
                            </form>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6"
                            class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                            No series yet.
                            <a href="{{ route('admin.series.create') }}"
                               class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                Create one
                            </a>.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $series->links() }}</div>

    </div>
</x-app-layout>
