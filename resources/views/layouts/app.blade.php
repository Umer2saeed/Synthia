<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Synthia') }} — {{ $title ?? 'Dashboard' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts (Vite + Tailwind + Alpine via Breeze) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">

<div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside
        :class="sidebarOpen ? 'w-64' : 'w-16'"
        class="relative flex flex-col flex-shrink-0 bg-white border-r border-gray-200 transition-all duration-300 ease-in-out overflow-hidden"
    >
        {{-- Logo / Brand --}}
        <div class="flex items-center h-16 px-4 border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-gray-900 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H9v-2a2 2 0 01.586-1.414z"/>
                    </svg>
                </div>
                <span x-show="sidebarOpen"
                      x-transition:enter="transition-opacity duration-200 delay-100"
                      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                      x-transition:leave="transition-opacity duration-100"
                      x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                      class="text-gray-900 font-semibold text-lg tracking-tight whitespace-nowrap">
                    Synthia
                </span>
            </div>
        </div>


        {{-- Navigation --}}
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto overflow-x-hidden">

            @php
                /*
                |----------------------------------------------------------------------
                | Define all nav items with required permission or role guard.
                |----------------------------------------------------------------------
                | Each item has:
                |   'permission' => checked via $user->can('...')
                |   'role'       => checked via $user->hasRole('...')
                |   null         => always visible to any authenticated user
                |
                | We use 'permission' where possible because it is more granular.
                | We only use 'role' for strictly role-based items like Roles and
                | Permissions which are admin-only by design.
                */
                $user = auth()->user();

                /*
                |----------------------------------------------------------------------
                | Compute trash count for the badge
                |----------------------------------------------------------------------
                | Admin and editor see all trashed posts count.
                | Author sees only their own trashed posts count.
                | We only compute this once here and pass it to the nav item below.
                */
                $trashedCount = 0;
                if (auth()->user()->can('delete own posts')) {
                    $trashedCount = auth()->user()->can('delete all posts')
                        ? \App\Models\Post::onlyTrashed()->count()
                        : \App\Models\Post::onlyTrashed()->where('user_id', auth()->id())->count();
                }


                $navItems = [
                    [
                        'href'       => route('admin.dashboard'),
                        'label'      => 'Dashboard',
                        'permission' => null, // visible to all authenticated users
                        'role'       => null,
                        'badge'      => null,
                        'icon'       => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                    ],
                    [
                        'href'       => route('admin.posts.index'),
                        'label'      => 'Posts',
                        'permission' => 'view posts',
                        'role'       => null,
                        'badge'      => null,
                        'icon'       => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    ],
                    [
                        'href'       => route('admin.posts.trash'),
                        'label'      => 'Trash',
                        'permission' => 'delete own posts', // authors, editors, admins all have this
                        'role'       => null,
                        'badge'      => $trashedCount > 0 ? $trashedCount : null,
                        'icon'       => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
                    ],
                    [
                        'href'       => route('admin.categories.index'),
                        'label'      => 'Categories',
                        'permission' => 'view categories',
                        'role'       => null,
                        'badge'      => null,
                        'icon'       => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
                    ],
                    [
                        'href'       => route('admin.tags.index'),
                        'label'      => 'Tags',
                        'permission' => 'view categories', // tags follow same access as categories
                        'role'       => null,
                        'badge'      => null,
                        'icon'       => 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14',
                    ],
                    [
                        'href'       => route('admin.comments.index'),
                        'label'      => 'Comments',
                        'permission' => 'delete comments', // only admin and editor see comment management
                        'role'       => null,
                        'badge'      => null,
                        'icon'       => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                    ],
                    [
                        'href'       => route('admin.users.index'),
                        'label'      => 'Users',
                        'permission' => 'manage users',
                        'role'       => null,
                        'badge'      => null,
                        'icon'       => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    ],
                    [
                        'href'       => route('admin.roles.index'),
                        'label'      => 'Roles',
                        'permission' => 'manage roles',
                        'role'       => null,
                        'badge'      => null,
                        'icon'       => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    ],
                    [
                        'href'       => route('admin.permissions.index'),
                        'label'      => 'Permissions',
                        'permission' => 'manage roles', // same guard as roles
                        'role'       => null,
                        'badge'      => null,
                        'icon'       => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
                    ],
                    [
                        'href'       => route('admin.profile.edit'),
                        'label'      => 'Profile',
                        'permission' => null, // always visible
                        'role'       => null,
                        'badge'      => null,
                        'icon'       => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                    ],
                ];
            @endphp

            @foreach($navItems as $item)
                @php
                    /*
                    |------------------------------------------------------------------
                    | Visibility check
                    |------------------------------------------------------------------
                    | If neither 'permission' nor 'role' is set → always show.
                    | If 'permission' is set → only show if user has that permission.
                    | If 'role' is set → only show if user has that role.
                    | Both can be set → user must satisfy both.
                    */
                    $show = true;

                    if (!empty($item['permission']) && !$user->can($item['permission'])) {
                        $show = false;
                    }

                    if (!empty($item['role']) && !$user->hasRole($item['role'])) {
                        $show = false;
                    }
                @endphp

                @if($show)
                    @php
                        $active = str_starts_with(request()->url(), $item['href']) ||
                                  request()->url() === $item['href'];
                    @endphp

                    <a href="{{ $item['href'] }}"
                       title="{{ $item['label'] }}"
                       class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm
                          font-medium transition-colors duration-150
                          {{ $active
                              ? 'bg-gray-900 text-white'
                              : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">

                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="1.75" d="{{ $item['icon'] }}"/>
                        </svg>

                        <span x-show="sidebarOpen"
                              x-transition:enter="transition-opacity duration-200 delay-100"
                              x-transition:enter-start="opacity-0"
                              x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-100"
                              x-transition:leave-start="opacity-100"
                              x-transition:leave-end="opacity-0"
                              class="whitespace-nowrap flex-1">
                            {{ $item['label'] }}
                        </span>

                        {{--
                        | Badge — shows the count next to the nav item label.
                        | Only rendered when sidebarOpen is true (collapsed sidebar
                        | has no room for a badge alongside the icon).
                        | Only shown when the item has a non-null badge value.
                        --}}
                        @if(!empty($item['badge']))
                            <span x-show="sidebarOpen"
                                  x-transition:enter="transition-opacity duration-200 delay-100"
                                  x-transition:enter-start="opacity-0"
                                  x-transition:enter-end="opacity-100"
                                  x-transition:leave="transition-opacity duration-100"
                                  x-transition:leave-start="opacity-100"
                                  x-transition:leave-end="opacity-0"
                                  class="ml-auto px-1.5 py-0.5 text-xs font-semibold rounded-full
                                         {{ $active
                                             ? 'bg-white text-gray-900'
                                             : 'bg-red-100 text-red-700' }}">
                                {{ $item['badge'] }}
                            </span>
                        @endif

                    </a>
                @endif
            @endforeach

        </nav>

        {{-- User Profile (bottom) --}}
        <div class="flex-shrink-0 border-t border-gray-200 p-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0 text-sm font-semibold text-gray-700 uppercase">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div x-show="sidebarOpen"
                     x-transition:enter="transition-opacity duration-200 delay-100"
                     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity duration-100"
                     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>

    </aside>

    {{-- ===================== MAIN AREA ===================== --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

        {{-- Top Navbar --}}
        <header class="flex items-center h-16 px-4 bg-white border-b border-gray-200 gap-4 flex-shrink-0">

            {{-- Sidebar Toggle --}}
            <button @click="sidebarOpen = !sidebarOpen"
                    class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Page Title --}}
{{--            <h1 class="text-base font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>--}}

            {{-- Page Title / Header Slot --}}
            <div class="flex-1">
                @if (isset($header))
                    {{ $header }}
                @else
                    <h1 class="text-base font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>
                @endif
            </div>

            <div class="flex-1"></div>

            {{-- Notifications --}}
            <button class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </button>

            {{-- User Dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                    <div class="w-7 h-7 rounded-full bg-gray-300 flex items-center justify-center text-xs font-semibold text-gray-700 uppercase">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span class="hidden sm:block">{{ Auth::user()->name }}</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg border border-gray-200 shadow-lg py-1 z-50">
                    <a href="/profile"
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>

        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>

    </div>
</div>

</body>
</html>
