<x-guest-layout>

    <div class="mb-6 text-center">
        {{-- Icon --}}
        <div style="font-size: 48px; margin-bottom: 12px;">✉️</div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">
            Verify your email
        </h1>
    </div>

    {{-- Status message when resend is clicked --}}
    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl">
            A new verification link has been sent to your email address.
            Please check your inbox and spam folder.
        </div>
    @endif

    <p class="mb-6 text-sm text-gray-600 leading-relaxed text-center">
        Thanks for signing up! Before getting started, please verify
        your email address by clicking the link we just emailed to you.
        If you did not receive the email, click below to request another.
    </p>

    <div class="flex flex-col gap-3">

        {{-- Resend button --}}
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700
                           text-white text-sm font-medium rounded-xl transition-colors">
                Resend Verification Email
            </button>
        </form>

        {{-- Log out --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full px-4 py-2 text-sm text-gray-500
                           hover:text-gray-700 transition-colors">
                Log Out
            </button>
        </form>

    </div>

</x-guest-layout>
