<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Tags</h2>

            {{--
            | Only admin and editor have 'manage categories'.
            | Author sees the list but has no create button.
            --}}
            @can('manage categories')
                <a href="{{ route('admin.tags.create') }}"
                   class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                    + New Tag
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">

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
                    {{--
                    | Only show Actions column header if user
                    | has manage permissions — keeps the table clean.
                    --}}
                    @can('manage categories')
                        <th class="px-6 py-4 text-right">Actions</th>
                    @endcan
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($tags as $tag)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-400">{{ $loop->iteration }}</td>

                        {{-- Tag name shown as a pill badge --}}
                        <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">
                                    {{ $tag->name }}
                                </span>
                        </td>

                        <td class="px-6 py-4 text-gray-400 font-mono text-xs">
                            {{ $tag->slug }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $tag->posts_count }} {{ Str::plural('post', $tag->posts_count) }}
                        </td>

                        {{--
                        | Actions — only rendered for admin and editor.
                        | Authors see the row data but no action buttons.
                        --}}
                        @can('manage categories')
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.tags.edit', $tag) }}"
                                   class="text-indigo-600 hover:underline text-xs font-medium">
                                    Edit
                                </a>

                                <form action="{{ route('admin.tags.destroy', $tag) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Delete tag \'{{ $tag->name }}\'? It will be removed from all posts.')">
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
                        <td colspan="{{ auth()->user()->can('manage categories') ? 5 : 4 }}"
                            class="px-6 py-10 text-center text-gray-400">
                            No tags yet.
                            @can('manage categories')
                                <a href="{{ route('admin.tags.create') }}"
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
            {{ $tags->links() }}
        </div>

    </div>
</x-app-layout>
