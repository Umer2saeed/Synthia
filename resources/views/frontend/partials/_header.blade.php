<header class="sticky top-0 z-50 bg-white/90 dark:bg-gray-950/90 backdrop-blur-md border-b border-gray-100 dark:border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}"
               class="font-display text-2xl font-bold text-gray-900 dark:text-white tracking-tight hover:opacity-80 transition-opacity">
                Synthia
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ route('home') }}"
                   class="text-sm font-medium transition-colors
                       {{ request()->routeIs('home')
                           ? 'text-indigo-600 dark:text-indigo-400'
                           : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                    Home
                </a>
                <a href="{{ route('blog') }}"
                   class="text-sm font-medium transition-colors
                       {{ request()->routeIs('blog*')
                           ? 'text-indigo-600 dark:text-indigo-400'
                           : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                    Blog
                </a>

                {{-- Categories Dropdown --}}
                <div class="relative group">
                    <button class="flex items-center gap-1 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                        Categories
                        <svg class="w-3.5 h-3.5 mt-0.5 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown --}}
                    <div class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-48
                                bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800
                                rounded-xl shadow-xl opacity-0 invisible
                                group-hover:opacity-100 group-hover:visible
                                transition-all duration-200 translate-y-1 group-hover:translate-y-0">
                        @php
                            $navCategories = \App\Models\Category::withCount(['posts' => fn($q) => $q->published()])
                                ->orderByDesc('posts_count')->limit(6)->get();
                        @endphp
                        @foreach($navCategories as $navCat)
                            <a href="{{ route('blog.category', $navCat->slug) }}"
                               class="flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300
                                      hover:bg-indigo-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400
                                      first:rounded-t-xl last:rounded-b-xl transition-colors">
                                {{ $navCat->name }}
                                <span class="text-xs text-gray-400">{{ $navCat->posts_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </nav>

            {{-- Right side: Search + Dark Mode + Auth --}}
            <div class="flex items-center gap-3">

                {{-- Search --}}
                <form action="{{ route('blog') }}" method="GET" class="hidden sm:block">
                    <div class="relative">
                        <input type="text" name="search"
                               value="{{ request('search') }}"
                               placeholder="Search..."
                               class="w-40 lg:w-56 pl-9 pr-4 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 border border-transparent
                                      focus:border-indigo-300 dark:focus:border-indigo-700 rounded-lg
                                      text-gray-700 dark:text-gray-300 placeholder-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800
                                      transition-all focus:w-48 lg:focus:w-64">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>

                {{-- Dark Mode Toggle --}}
                <button @click="toggle()"
                        class="w-9 h-9 flex items-center justify-center rounded-lg
                               bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700
                               text-gray-600 dark:text-gray-300 transition-colors"
                        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'">
                    {{-- Sun icon (shown in dark mode) --}}
                    <svg x-show="isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    {{-- Moon icon (shown in light mode) --}}
                    <svg x-show="!isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                {{-- Auth Links --}}
                @auth
                    {{-- Profile dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                            <img src="{{ auth()->user()->avatar_url }}"
                                 alt="{{ auth()->user()->name }}"
                                 class="w-8 h-8 rounded-full object-cover border-2
                        border-indigo-100 dark:border-indigo-900">
                            <svg class="w-3.5 h-3.5 text-gray-400 hidden sm:block"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-900
                    border border-gray-100 dark:border-gray-800
                    rounded-xl shadow-xl py-1 z-50">

                            {{-- User name header --}}
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800">
                                <p class="text-xs font-semibold text-gray-800 dark:text-white truncate">
                                    {{ auth()->user()->name }}
                                </p>
                                <p class="text-xs text-gray-400 truncate">
                                    {{ auth()->user()->email }}
                                </p>
                            </div>

                            {{-- My Profile --}}
                            <a href="{{ route('frontend.profile.show') }}"
                               class="flex items-center gap-2 px-4 py-2 text-sm
          text-gray-700 dark:text-gray-300
          hover:bg-indigo-50 dark:hover:bg-gray-800
          hover:text-indigo-600 dark:hover:text-indigo-400
          transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                My Profile
                            </a>

                            {{-- My Bookmarks --}}
                            <a href="{{ route('bookmarks.index') }}"
                               class="flex items-center gap-2 px-4 py-2 text-sm
                                  text-gray-700 dark:text-gray-300
                                  hover:bg-indigo-50 dark:hover:bg-gray-800
                                  hover:text-indigo-600 dark:hover:text-indigo-400
                                  transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                                My Bookmarks
                            </a>

                            {{-- Following --}}
                            <a href="{{ route('following.index') }}"
                               class="flex items-center gap-2 px-4 py-2 text-sm
          text-gray-700 dark:text-gray-300
          hover:bg-indigo-50 dark:hover:bg-gray-800
          hover:text-indigo-600 dark:hover:text-indigo-400
          transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Following
                            </a>


                            {{-- Admin Panel (non-readers only) --}}
                            @if(auth()->user()->can('access admin panel'))
                                <a href="{{ route('admin.dashboard') }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm
                          text-gray-700 dark:text-gray-300
                          hover:bg-indigo-50 dark:hover:bg-gray-800
                          hover:text-indigo-600 dark:hover:text-indigo-400
                          transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    Admin Panel
                                </a>
                            @endif

                            <div class="border-t border-gray-100 dark:border-gray-800 my-1"></div>

                            {{-- Logout --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex items-center gap-2 w-full px-4 py-2 text-sm
                               text-red-600 dark:text-red-400
                               hover:bg-red-50 dark:hover:bg-red-950
                               transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                       class="text-sm font-medium text-gray-600 dark:text-gray-300
              hover:text-gray-900 dark:hover:text-white transition-colors">
                        Login
                    </a>
                    <a href="{{ route('register') }}"
                       class="hidden sm:block px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700
              text-white text-sm font-medium rounded-lg transition-colors">
                        Sign Up
                    </a>
                @endauth


                {{-- Mobile menu button --}}
                <button id="mobile-menu-btn"
                        class="md:hidden w-9 h-9 flex items-center justify-center rounded-lg
                               bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu"
             class="hidden md:hidden border-t border-gray-100 dark:border-gray-800 py-3 space-y-1">
            <a href="{{ route('home') }}"
               class="block px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg">
                Home
            </a>
            <a href="{{ route('blog') }}"
               class="block px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg">
                Blog
            </a>
            @auth
                <a href="{{ route('admin.dashboard') }}"
                   class="block px-3 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-800 rounded-lg">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="block px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg">
                    Login
                </a>
                <a href="{{ route('register') }}"
                   class="block px-3 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-800 rounded-lg">
                    Sign Up
                </a>
            @endauth
        </div>
    </div>
</header>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-btn')
        .addEventListener('click', function () {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
</script>
