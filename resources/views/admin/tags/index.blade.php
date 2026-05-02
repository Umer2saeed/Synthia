<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Tags</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900
                        border border-green-300 dark:border-green-700
                        text-green-800 dark:text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-end mb-2">
            <div class="flex items-center gap-3">
                @can('manage categories')
                    <a href="{{ route('admin.tags.create') }}"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                              text-white text-sm rounded-lg transition">
                        + New Tag
                    </a>
                @endcan
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl overflow-hidden
                    border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-900
                              text-gray-500 dark:text-gray-400
                              uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-4">#</th>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Slug</th>
                    <th class="px-6 py-4">Posts</th>
                    @can('manage categories')
                        <th class="px-6 py-4 text-right">Actions</th>
                    @endcan
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($tags as $tag)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 text-gray-400 dark:text-gray-500">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-6 py-4">
                                <span class="px-3 py-1
                                             bg-indigo-50 dark:bg-indigo-950
                                             text-indigo-700 dark:text-indigo-400
                                             text-xs font-medium rounded-full">
                                    {{ $tag->name }}
                                </span>
                        </td>
                        <td class="px-6 py-4 text-gray-400 dark:text-gray-500 font-mono text-xs">
                            {{ $tag->slug }}
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                            {{ $tag->posts_count }} {{ Str::plural('post', $tag->posts_count) }}
                        </td>
                        @can('manage categories')
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.tags.edit', $tag) }}"
                                   class="text-indigo-600 dark:text-indigo-400
                                              hover:underline text-xs font-medium">
                                    Edit
                                </a>
                                <form action="{{ route('admin.tags.destroy', $tag) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Delete tag \'{{ $tag->name }}\'?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 dark:text-red-400
                                                       hover:underline text-xs font-medium">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('manage categories') ? 5 : 4 }}"
                            class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                            No tags yet.
                            @can('manage categories')
                                <a href="{{ route('admin.tags.create') }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                    Create one
                                </a>.
                            @endcan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $tags->links() }}
        </div>
    </div>
</x-app-layout>
