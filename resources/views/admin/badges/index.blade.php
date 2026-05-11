@cannot('manage users')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Badges</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900
                        border border-green-200 dark:border-green-700
                        text-green-800 dark:text-green-300 text-sm rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-950
                        border border-red-200 dark:border-red-800
                        text-red-700 dark:text-red-400 text-sm rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        {{-- Award badge form --}}
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl border border-gray-200 dark:border-gray-700
                    p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Award Badge Manually
            </h3>
            <form action="{{ route('admin.badges.award') }}" method="POST"
                  class="flex flex-wrap gap-3 items-end">
                @csrf

                <div class="flex-1 min-w-40">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        User
                    </label>
                    <select name="user_id"
                            class="w-full border border-gray-300 dark:border-gray-600
                                   bg-white dark:bg-gray-900
                                   text-gray-800 dark:text-gray-200
                                   rounded-xl px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">Select user...</option>
                        @foreach(\App\Models\User::orderBy('name')->get(['id', 'name', 'email']) as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-40">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Badge
                    </label>
                    <select name="badge_id"
                            class="w-full border border-gray-300 dark:border-gray-600
                                   bg-white dark:bg-gray-900
                                   text-gray-800 dark:text-gray-200
                                   rounded-xl px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">Select badge...</option>
                        @foreach($badges as $badge)
                            <option value="{{ $badge->id }}">
                                {{ $badge->icon }} {{ $badge->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700
                               text-white text-sm font-medium rounded-xl transition">
                    Award Badge
                </button>
            </form>
        </div>

        {{-- Badges list --}}
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl border border-gray-200 dark:border-gray-700
                    overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-900
                              text-gray-500 dark:text-gray-400
                              uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-4">Badge</th>
                    <th class="px-5 py-4">Description</th>
                    <th class="px-5 py-4">Criteria</th>
                    <th class="px-5 py-4">Awarded To</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($badges as $badge)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $badge->icon }}</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">
                                        {{ $badge->name }}
                                    </span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-500 dark:text-gray-400 text-xs max-w-xs">
                            {{ $badge->description }}
                        </td>
                        <td class="px-5 py-4 text-xs">
                            @if($badge->criteria_type)
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700
                                                 text-gray-600 dark:text-gray-400 rounded-lg">
                                        {{ str_replace('_', ' ', $badge->criteria_type) }}
                                        ≥ {{ $badge->criteria_value }}
                                    </span>
                            @else
                                <span class="text-gray-400 dark:text-gray-500">
                                        Manual only
                                    </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-400">
                            {{ $badge->user_badges_count }}
                            {{ Str::plural('user', $badge->user_badges_count) }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
