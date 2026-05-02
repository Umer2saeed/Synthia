<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Categories</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

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
                    <a href="{{ route('admin.categories.create') }}"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                              text-white text-sm rounded-lg transition">
                        + New Category
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
                    <th class="px-6 py-4">Description</th>
                    @can('manage categories')
                        <th class="px-6 py-4 text-right">Actions</th>
                    @endcan
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 text-gray-400 dark:text-gray-500">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200">
                            {{ $category->name }}
                        </td>
                        <td class="px-6 py-4 text-indigo-500 dark:text-indigo-400 font-mono text-xs">
                            {{ $category->slug }}
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                            {{ $category->posts_count }}
                        </td>
                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400 truncate max-w-xs">
                            {{ $category->description ?? '—' }}
                        </td>
                        @can('manage categories')
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.categories.edit', $category) }}"
                                   class="text-indigo-600 dark:text-indigo-400
                                              hover:underline text-xs font-medium">
                                    Edit
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Delete this category?')">
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
                        <td colspan="{{ auth()->user()->can('manage categories') ? 6 : 5 }}"
                            class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                            No categories found.
                            @can('manage categories')
                                <a href="{{ route('admin.categories.create') }}"
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
            {{ $categories->links() }}
        </div>
    </div>
</x-app-layout>
