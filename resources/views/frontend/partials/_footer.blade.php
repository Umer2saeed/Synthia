<footer class="bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

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
