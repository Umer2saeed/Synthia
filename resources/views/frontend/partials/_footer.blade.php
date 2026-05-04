<footer class="bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

            {{-- Brand --}}
            <div>
                <a href="{{ route('home') }}"
                   class="font-display text-xl font-bold text-gray-900 dark:text-white">
                    Synthia
                </a>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    A modern blogging platform powered by Laravel.
                    Read, write, and explore ideas.
                </p>
            </div>

            {{-- Quick links --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Navigate</h4>
                <ul class="space-y-2">
                    @foreach([['Home', 'home'], ['Blog', 'blog']] as [$label, $routeName])
                        <li>
                            <a href="{{ route($routeName) }}"
                               class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>


            {{-- Recent Categories --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Categories</h4>
                <ul class="space-y-2">
                    @foreach(\App\Models\Category::withCount(['posts' => fn($q) => $q->published()])->orderByDesc('posts_count')->limit(5)->get() as $cat)
                        <li>
                            <a href="{{ route('blog.category', $cat->slug) }}"
                               class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Recent Categories --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">RSS Feed</h4>
                <ul class="space-y-2">
                    {{-- RSS Feed link --}}
                    <a href="{{ route('feed.index') }}"
                       title="Subscribe to RSS Feed"
                       class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400
              hover:text-orange-500 dark:hover:text-orange-400 transition-colors">
                            {{-- RSS icon (standard orange signal icon) --}}
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19.01 7.38 20 6.18 20
                     C4.98 20 4 19.01 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56
                     15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9
                     9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93V10.1z"/>
                        </svg>
                        RSS
                    </a>
                </ul>
            </div>

        </div>

        <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-gray-400">
                © {{ date('Y') }} Synthia. Built with Laravel.
            </p>
            @auth
                <a href="{{ route('admin.dashboard') }}"
                   class="text-xs text-gray-400 hover:text-indigo-500 transition-colors">
                    Admin Panel →
                </a>
            @endauth
        </div>
    </div>
</footer>
