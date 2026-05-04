@cannot('manage users')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                    Activity Log
                </h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                    {{ now()->format('l, d F Y') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.activity.index') }}"
              class="mb-5 flex flex-wrap gap-3 items-center">

            <select name="action"
                    class="border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-800
                           text-gray-800 dark:text-gray-200
                           rounded-lg px-4 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Actions</option>
                @foreach($actionGroups as $prefix => $label)
                    <option value="{{ $prefix }}"
                        {{ request('action') === $prefix ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <select name="user_id"
                    class="border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-800
                           text-gray-800 dark:text-gray-200
                           rounded-lg px-4 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>

            <input type="date" name="from"
                   value="{{ request('from') }}"
                   class="border border-gray-300 dark:border-gray-600
                          bg-white dark:bg-gray-800
                          text-gray-800 dark:text-gray-200
                          rounded-lg px-4 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-400">

            <input type="date" name="to"
                   value="{{ request('to') }}"
                   class="border border-gray-300 dark:border-gray-600
                          bg-white dark:bg-gray-800
                          text-gray-800 dark:text-gray-200
                          rounded-lg px-4 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-400">

            <button type="submit"
                    class="px-4 py-2 bg-gray-700 dark:bg-gray-600
                           hover:bg-gray-800 dark:hover:bg-gray-500
                           text-white text-sm rounded-lg transition">
                Filter
            </button>

            @if(request()->hasAny(['action', 'user_id', 'from', 'to']))
                <a href="{{ route('admin.activity.index') }}"
                   class="text-sm text-red-500 dark:text-red-400 hover:underline">
                    Clear
                </a>
            @endif
        </form>

        {{-- Activity Log Table --}}
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl overflow-hidden
                    border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-900
                              text-gray-500 dark:text-gray-400
                              uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-4">Action</th>
                    <th class="px-5 py-4">Description</th>
                    <th class="px-5 py-4">User</th>
                    <th class="px-5 py-4">IP</th>
                    <th class="px-5 py-4">When</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition align-top">

                        <td class="px-5 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                             {{ $log->action_color }}">
                                    {{ $log->action_label }}
                                </span>
                        </td>

                        <td class="px-5 py-3 text-gray-700 dark:text-gray-300 text-xs max-w-sm">
                            {{ $log->description }}
                        </td>

                        <td class="px-5 py-3">
                            @if($log->user)
                                <div>
                                    <p class="text-xs font-medium text-gray-800 dark:text-gray-200">
                                        {{ $log->user->name }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $log->user->email }}
                                    </p>
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                        System
                                    </span>
                            @endif
                        </td>

                        <td class="px-5 py-3 text-gray-400 dark:text-gray-500
                                       text-xs font-mono">
                            {{ $log->ip ?? '—' }}
                        </td>

                        <td class="px-5 py-3 text-gray-400 dark:text-gray-500 text-xs whitespace-nowrap">
                                <span title="{{ $log->created_at->format('d M Y H:i:s') }}">
                                    {{ $log->created_at->diffForHumans() }}
                                </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"
                            class="px-6 py-16 text-center text-gray-400 dark:text-gray-500">
                            No activity recorded yet.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>

    </div>
</x-app-layout>
