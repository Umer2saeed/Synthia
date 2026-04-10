<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 space-y-6">

        {{-- Pending alerts --}}
        <div class="flex items-center gap-2 flex-wrap">
            <div class="pending-comments">
                <a href="{{ route('admin.comments.index', ['status' => 'pending']) }}"
                    @class([
                        'flex items-center gap-1.5 px-3 py-1.5 border rounded-lg transition text-xs font-medium',
                        'bg-indigo-50 border-indigo-200 text-indigo-600 hover:bg-indigo-100' => $pendingComments > 0,
                        'bg-gray-50 border-gray-200 text-gray-500 hover:bg-gray-100' => $pendingComments <= 0,
                    ])>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ $pendingComments }} {{ Str::plural('pending', $pendingComments) }}
                </a>
            </div>

            <div class="draft-posts">
                <a href="{{ route('admin.posts.index', ['status' => 'draft']) }}"
                    @class([
                        'flex items-center gap-1.5 px-3 py-1.5 border rounded-lg transition text-xs font-medium',
                        'bg-indigo-50 border-indigo-200 text-indigo-600 hover:bg-indigo-100' => $draftPosts > 0,
                        'bg-gray-50 border-gray-200 text-gray-500 hover:bg-gray-100' => $draftPosts <= 0,
                    ])>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ $draftPosts }} {{ Str::plural('draft', $draftPosts) }}
                </a>
            </div>
        </div>


        {{-- =============================================
             ROW 1: Welcome Card + Personal Stats
             ============================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

            {{-- Welcome Card --}}
            <div class="lg:col-span-2 bg-gray-900 rounded-xl p-6 text-white relative overflow-hidden">
                {{-- Decorative background circle --}}
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-white opacity-5 rounded-full"></div>
                <div class="absolute -right-2 bottom-4 w-20 h-20 bg-white opacity-5 rounded-full"></div>

                <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-1">
                    Welcome back
                </p>
                <p class="text-xl font-bold text-white mb-1">
                    {{ auth()->user()->name }} 👋
                </p>
                <p class="text-sm text-gray-400 leading-relaxed mb-4">
                    You have
                    <a href="{{ route('admin.posts.index', ['status' => 'draft']) }}"
                       class="text-white font-semibold hover:underline">
                        {{ $myDraftsCount }} {{ Str::plural('draft', $myDraftsCount) }}
                    </a>
                    waiting to be published
                    @if($pendingComments > 0)
                        and
                        <a href="{{ route('admin.comments.index', ['status' => 'pending']) }}"
                           class="text-yellow-400 font-semibold hover:underline">
                            {{ $pendingComments }} {{ Str::plural('comment', $pendingComments) }}
                        </a>
                        awaiting review.
                    @else
                        . All comments are reviewed. ✓
                    @endif
                </p>

                {{-- Personal mini stats --}}
                <div class="flex items-center gap-4 pt-4 border-t border-white border-opacity-10">
                    <div>
                        <p class="text-2xl font-bold text-white">{{ $myPostsCount }}</p>
                        <p class="text-xs text-gray-400">Your Posts</p>
                    </div>
                    <div class="w-px h-8 bg-white opacity-10"></div>
                    <div>
                        <p class="text-2xl font-bold text-white">{{ $myDraftsCount }}</p>
                        <p class="text-xs text-gray-400">Drafts</p>
                    </div>
                    <div class="w-px h-8 bg-white opacity-10"></div>
                    <div>
                        <p class="text-2xl font-bold text-white">{{ $myCommentsCount }}</p>
                        <p class="text-xs text-gray-400">Comments Made</p>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Actions</h3>
                <div class="space-y-2">

                    @can('create posts')
                        <a href="{{ route('admin.posts.create') }}"
                           class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            New Post
                        </a>
                    @endcan

                    @can('manage categories')
                        <a href="{{ route('admin.categories.create') }}"
                           class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            New Category
                        </a>

                        <a href="{{ route('admin.tags.create') }}"
                           class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                            New Tag
                        </a>
                    @endcan

                    @role('admin')
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Manage Users
                    </a>
                    @endrole

                    <a href="{{ route('admin.profile.edit') }}"
                       class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Edit Profile
                    </a>
                </div>
            </div>

            {{-- Post Status Breakdown --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Posts by Status</h3>

                @php
                    $statusConfig = [
                        'published' => ['label' => 'Published', 'color' => 'bg-green-500',  'text' => 'text-green-700',  'bg' => 'bg-green-50'],
                        'draft'     => ['label' => 'Draft',     'color' => 'bg-gray-400',   'text' => 'text-gray-600',   'bg' => 'bg-gray-50'],
                        'scheduled' => ['label' => 'Scheduled', 'color' => 'bg-yellow-400', 'text' => 'text-yellow-700', 'bg' => 'bg-yellow-50'],
                    ];
                    // Total for percentage calculation (avoid division by zero)
                    $totalForCalc = max($totalPosts, 1);
                @endphp

                <div class="space-y-3">
                    @foreach($statusConfig as $status => $config)
                        @php
                            $count = $postsByStatus[$status] ?? 0;
                            $pct   = round(($count / $totalForCalc) * 100);
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600">{{ $config['label'] }}</span>
                                <span class="text-xs font-semibold text-gray-800">{{ $count }}</span>
                            </div>
                            {{-- Progress bar --}}
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $config['color'] }} transition-all duration-500"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ $totalPosts }}</p>
                    <p class="text-xs text-gray-400">Total Posts Ever</p>
                </div>
            </div>
        </div>

        {{-- =============================================
             ROW 2: Four Stat Cards
             ============================================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

            @php
                $statCards = [
                    [
                        'label'    => 'Published Posts',
                        'value'    => $publishedPosts,
                        'sub'      => '+' . $postsThisMonth . ' this month',
                        'color'    => 'bg-blue-50 text-blue-600',
                        'href'     => route('admin.posts.index', ['status' => 'published']),
                        'icon'     => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    ],
                    [
                        'label'    => 'Total Comments',
                        'value'    => $totalComments,
                        'sub'      => '+' . $commentsThisWeek . ' this week',
                        'color'    => 'bg-green-50 text-green-600',
                        'href'     => route('admin.comments.index'),
                        'icon'     => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                    ],
                    [
                        'label'    => 'Total Users',
                        'value'    => $totalUsers,
                        'sub'      => '+' . $usersThisMonth . ' this month',
                        'color'    => 'bg-purple-50 text-purple-600',
                        'href'     => route('admin.users.index'),
                        'icon'     => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    ],
                    [
                        'label'    => 'Categories & Tags',
                        'value'    => $totalCategories,
                        'sub'      => $totalTags . ' tags total',
                        'color'    => 'bg-orange-50 text-orange-600',
                        'href'     => route('admin.categories.index'),
                        'icon'     => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
                    ],
                ];
            @endphp

            @foreach($statCards as $card)
                <a href="{{ $card['href'] }}"
                   class="bg-white rounded-xl border border-gray-200 p-5 flex items-start gap-4 hover:border-indigo-200 hover:shadow-sm transition group">
                    <div class="w-10 h-10 rounded-lg {{ $card['color'] }} flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-900 leading-tight">
                            {{ number_format($card['value']) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $card['sub'] }}</p>
                    </div>
                </a>
            @endforeach

        </div>

        {{-- =============================================
             ROW 3: Recent Posts + Recent Comments
             ============================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Recent Posts --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800">Recent Posts</h3>
                    <a href="{{ route('admin.posts.index') }}"
                       class="text-xs text-indigo-600 hover:underline font-medium">
                        View all →
                    </a>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($recentPosts as $post)
                        <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition">
                            {{-- Cover thumbnail --}}
                            <img src="{{ $post->cover_image_url }}"
                                 alt="{{ $post->title }}"
                                 class="w-10 h-10 rounded-lg object-cover border border-gray-100 shrink-0">

                            <div class="flex-1 min-w-0">
                                <a href="{{ route('admin.posts.show', $post) }}"
                                   class="text-sm font-medium text-gray-800 hover:text-indigo-600 truncate block transition">
                                    {{ Str::limit($post->title, 45) }}
                                </a>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $post->category->name ?? 'Uncategorized' }}
                                    &middot;
                                    {{ $post->created_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Status badge --}}
                            @php
                                $statusColor = match($post->status) {
                                    'published' => 'bg-green-100 text-green-700',
                                    'scheduled' => 'bg-yellow-100 text-yellow-700',
                                    default     => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium shrink-0 {{ $statusColor }}">
                                {{ ucfirst($post->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-gray-400 text-sm">
                            No posts yet.
                            <a href="{{ route('admin.posts.create') }}" class="text-indigo-600 hover:underline">Create one</a>.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Comments --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800">Recent Comments</h3>
                    <a href="{{ route('admin.comments.index') }}"
                       class="text-xs text-indigo-600 hover:underline font-medium">
                        View all →
                    </a>
                </div>

                <div class="divide-y divide-gray-100">

                    @forelse($recentComments as $comment)
                        {{--
                        | Guard: skip this comment entirely if its post or user
                        | has been deleted. Prevents route() from crashing when
                        | $comment->post is null.
                        --}}
                        @if(!$comment->post || !$comment->user)
                            @continue
                        @endif

                        <div class="flex items-start gap-3 px-5 py-3 hover:bg-gray-50 transition">

                            {{-- Avatar --}}
                            <img src="{{ $comment->user->avatar_url }}"
                                 alt="{{ $comment->user->name }}"
                                 class="w-7 h-7 rounded-full object-cover border border-gray-200 shrink-0 mt-0.5">

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="text-xs font-semibold text-gray-800">
                                    {{ $comment->user->name }}
                                </span>
                                                    <span class="text-xs text-gray-400 shrink-0">
                                    {{ $comment->created_at->diffForHumans() }}
                                </span>
                                    </div>

                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2 leading-relaxed">
                                        {{ $comment->content }}
                                    </p>

                                    {{--
                                    | Use optional() as an extra safety net.
                                    | If post is somehow still null, optional() returns null
                                    | instead of throwing an error — the link just shows nothing.
                                    --}}
                                    @if($comment->post)
                                        <a href="{{ route('admin.posts.show', $comment->post) }}"
                                           class="text-xs text-indigo-500 hover:underline mt-0.5 block truncate">
                                            on: {{ Str::limit($comment->post->title, 40) }}
                                        </a>
                                    @endif
                                </div>

                                    @if(!$comment->is_approved)
                                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full shrink-0">
                                Pending
                            </span>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-gray-400 text-sm">
                            No comments yet.
                        </div>
                    @endforelse

                </div>
            </div>

        </div>

        {{-- =============================================
             ROW 4: Top Categories + Top Tags + Recent Users
             ============================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Top Categories --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Top Categories</h3>
                    <a href="{{ route('admin.categories.index') }}"
                       class="text-xs text-indigo-600 hover:underline">View all</a>
                </div>

                @php
                    // Max posts across all top categories — used for progress bar width
                    $maxCatPosts = $topCategories->max('posts_count') ?: 1;
                @endphp

                <div class="space-y-3">
                    @forelse($topCategories as $category)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-700 truncate max-w-[70%]">
                                    {{ $category->name }}
                                </span>
                                <span class="text-xs text-gray-500 font-semibold">
                                    {{ $category->posts_count }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full bg-indigo-400 transition-all duration-500"
                                     style="width: {{ round(($category->posts_count / $maxCatPosts) * 100) }}%">
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">No categories yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Top Tags --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Top Tags</h3>
                    <a href="{{ route('admin.tags.index') }}"
                       class="text-xs text-indigo-600 hover:underline">View all</a>
                </div>

                @if($topTags->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-4">No tags yet.</p>
                @else
                    {{-- Tag cloud — size based on post count --}}
                    <div class="flex flex-wrap gap-2">
                        @foreach($topTags as $tag)
                            @php
                                /*
                                | Scale font size between 11px and 16px based on
                                | how many posts this tag has relative to the top tag.
                                | This creates a simple visual tag cloud effect.
                                */
                                $maxTagPosts = $topTags->max('posts_count') ?: 1;
                                $ratio       = $tag->posts_count / $maxTagPosts;
                                $fontSize    = round(11 + ($ratio * 5)); // 11px to 16px
                            @endphp
                            <a href="{{ route('admin.tags.index') }}"
                               style="font-size: {{ $fontSize }}px"
                               class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full font-medium hover:bg-indigo-100 transition"
                               title="{{ $tag->posts_count }} {{ Str::plural('post', $tag->posts_count) }}">
                                {{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent Users --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Recent Users</h3>
                    @role('admin')
                    <a href="{{ route('admin.users.index') }}"
                       class="text-xs text-indigo-600 hover:underline">View all</a>
                    @endrole
                </div>

                <div class="space-y-3">
                    @forelse($recentUsers as $user)
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar_url }}"
                                 alt="{{ $user->name }}"
                                 class="w-8 h-8 rounded-full object-cover border border-gray-200 shrink-0">

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                            </div>

                            {{-- Role badge --}}
                            @php
                                $role      = $user->roles->first();
                                $roleColor = match($role?->name) {
                                    'admin'  => 'bg-red-100 text-red-700',
                                    'editor' => 'bg-blue-100 text-blue-700',
                                    'author' => 'bg-green-100 text-green-700',
                                    default  => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium shrink-0 {{ $roleColor }}">
                                {{ ucfirst($role?->name ?? 'none') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">No users yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- =============================================
             ROW 5: Pending Attention Banner
             (only shown if there are items needing action)
             ============================================= --}}
        @if($pendingComments > 0 || $scheduledPosts > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-amber-800 mb-3">
                    ⚡ Needs Your Attention
                </h3>
                <div class="flex flex-wrap gap-4">

                    @if($pendingComments > 0)
                        <a href="{{ route('admin.comments.index', ['status' => 'pending']) }}"
                           class="flex items-center gap-2 px-4 py-2 bg-white border border-amber-200 rounded-lg text-sm text-amber-700 font-medium hover:bg-amber-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Review {{ $pendingComments }} pending {{ Str::plural('comment', $pendingComments) }}
                        </a>
                    @endif

                    @if($scheduledPosts > 0)
                        <a href="{{ route('admin.posts.index', ['status' => 'scheduled']) }}"
                           class="flex items-center gap-2 px-4 py-2 bg-white border border-amber-200 rounded-lg text-sm text-amber-700 font-medium hover:bg-amber-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $scheduledPosts }} {{ Str::plural('post', $scheduledPosts) }} scheduled
                        </a>
                    @endif

                </div>
            </div>
        @endif

    </div>
</x-app-layout>
