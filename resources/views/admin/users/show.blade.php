@cannot('manage users')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">User Profile</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }} -
                </p>
            </div>
        </div>
    </x-slot>


    <div class="py-8 max-w-4xl mx-auto px-4">

        <div class="flex justify-between">

            <div class="flex gap-2 mb-4">

                <a href="{{ route('admin.users.edit', $user) }}"
                   class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                    Edit User
                </a>

                <a href="{{ route('admin.users.roles.edit', $user) }}"
                   class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition">
                    Manage Roles
                </a>

                {{-- Cannot delete yourself or another admin --}}
                @if($user->id !== auth()->id() && !$user->hasRole('admin'))
                    <form action="{{ route('admin.users.destroy', $user) }}"
                          method="POST"
                          onsubmit="return confirm('Permanently delete {{ addslashes($user->name) }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition">
                            Delete
                        </button>
                    </form>
                @endif

            </div>

            <div class="flex gap-2 mb-4">
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700
                              text-sm rounded-lg hover:bg-gray-200 transition">
                    ← Back
                </a>
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- LEFT: Avatar + Identity --}}
            <div class="space-y-5">

                {{-- Avatar Card --}}
                <div class="bg-white shadow rounded-xl p-6 text-center">
                    <img src="{{ $user->avatar_url }}"
                         alt="{{ $user->name }}"
                         class="w-24 h-24 rounded-full object-cover border-4 border-indigo-100 mx-auto mb-4">

                    <h3 class="text-lg font-semibold text-gray-800">{{ $user->name }}</h3>

                    @if($user->username)
                        <p class="text-sm text-gray-400 font-mono">'{{ $user->username }}</p>
                    @endif

                    {{-- Status badge --}}
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium
                        {{ $user->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                        {{ ucfirst($user->status) }}
                    </span>

                    {{-- Roles --}}
                    <div class="flex flex-wrap justify-center gap-1 mt-3">
                        @foreach($user->roles as $role)
                            @php
                                $color = match($role->name) {
                                    'admin'  => 'bg-red-100 text-red-700',
                                    'editor' => 'bg-blue-100 text-blue-700',
                                    'author' => 'bg-green-100 text-green-700',
                                    default  => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                {{ ucfirst($role->name) }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Stats Card --}}
                <div class="bg-white shadow rounded-xl p-5">
                    <h4 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-3">Activity</h4>
                    <div class="space-y-3">
                        @foreach([
                            ['Posts',     $user->posts_count],
                            ['Comments',  $user->comments_count],
                            ['Followers', $user->followers_count],
                            ['Following', $user->following_count],
                        ] as [$label, $count])
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">{{ $label }}</span>
                                <span class="text-sm font-semibold text-gray-800">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- RIGHT: Details --}}
            <div class="md:col-span-2 space-y-5">

                {{-- Info Card --}}
                <div class="bg-white shadow rounded-xl p-6 space-y-4">
                    <h4 class="text-sm font-semibold text-gray-700 border-b pb-2">Account Details</h4>

                    @foreach([
                        ['Email',       $user->email],
                        ['Joined',      $user->created_at->format('d F Y')],
                        ['Last Login',  $user->last_login_at?->diffForHumans() ?? 'Never'],
                    ] as [$label, $value])
                        <div class="flex items-start justify-between gap-4">
                            <span class="text-xs text-gray-400 w-24 shrink-0">{{ $label }}</span>
                            <span class="text-sm text-gray-700 text-right">{{ $value }}</span>
                        </div>
                    @endforeach

                    {{-- Bio --}}
                    @if($user->bio)
                        <div>
                            <span class="text-xs text-gray-400 block mb-1">Bio</span>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $user->bio }}</p>
                        </div>
                    @endif
                </div>

                {{-- Recent Posts --}}
                <div class="bg-white shadow rounded-xl p-6">
                    <h4 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-4">Recent Posts</h4>

                    @forelse($user->posts()->latest()->limit(5)->get() as $post)
                        <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                            <div>
                                <p class="text-sm text-gray-800 font-medium">
                                    {{ Str::limit($post->title, 50) }}
                                </p>
                                <p class="text-xs text-gray-400">{{ $post->created_at->format('d M Y') }}</p>
                            </div>
                            @php
                                $statusColor = match($post->status) {
                                    'published' => 'bg-green-100 text-green-700',
                                    'scheduled' => 'bg-yellow-100 text-yellow-700',
                                    default     => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $statusColor }}">
                                {{ ucfirst($post->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No posts yet.</p>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
