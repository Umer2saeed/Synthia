@cannot('manage roles')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Create Permission</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }}
    </x-slot>

    <div class="py-8 max-w-xl mx-auto px-4">
        <div class="bg-white shadow rounded-xl p-6">
            <form action="{{ route('admin.permissions.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Permission Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('name') border-red-400 @enderror"
                           placeholder="e.g. manage tags">
                    <p class="text-xs text-gray-400 mt-1">Use lowercase words separated by spaces, e.g. "edit all posts"</p>
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                        Create Permission
                    </button>
                    <a href="{{ route('admin.permissions.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
