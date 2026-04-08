<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Categories</h2>

            {{--
            | Only admin and editor have 'manage categories'.
            | Author sees the list but has no create button.
            --}}
            @can('manage categories')
                <a href="{{ route('admin.categories.create') }}"
                   class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                    + New Category
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-4">#</th>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Slug</th>
                    <th class="px-6 py-4">Posts</th>
                    <th class="px-6 py-4">Description</th>
                    {{--
                    | Only show the Actions column header if user
                    | has something to do — no point showing an empty column.
                    --}}
                    @can('manage categories')
                        <th class="px-6 py-4 text-right">Actions</th>
                    @endcan
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-400">{{ $loop->iteration }}</td>

                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $category->name }}
                        </td>

                        <td class="px-6 py-4 text-indigo-500 font-mono text-xs">
                            {{ $category->slug }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $category->posts_count }}
                        </td>

                        <td class="px-6 py-4 text-gray-500 truncate max-w-xs">
                            {{ $category->description ?? '—' }}
                        </td>

                        {{--
                        | Actions column — only rendered for admin and editor.
                        | Authors see the row data but no action buttons.
                        --}}
                        @can('manage categories')
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.categories.edit', $category) }}"
                                   class="text-indigo-600 hover:underline text-xs font-medium">
                                    Edit
                                </a>

                                <form action="{{ route('admin.categories.destroy', $category) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 hover:underline text-xs font-medium">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('manage categories') ? 6 : 5 }}"
                            class="px-6 py-10 text-center text-gray-400">
                            No categories found.
                            @can('manage categories')
                                <a href="{{ route('admin.categories.create') }}"
                                   class="text-indigo-600 hover:underline">
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
