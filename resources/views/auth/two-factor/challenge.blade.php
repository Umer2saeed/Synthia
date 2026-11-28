<x-guest-layout>

    <h1 class="form-title">Two-factor verification</h1>
    <p class="form-subtitle" id="challenge-hint">
        Open your authenticator app and enter the 6-digit code for
        <strong>{{ config('app.name') }}</strong>.
    </p>

    @if($errors->any())
        <div class="alert-error-list">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf

        <div class="field">
            <label for="code" id="code-label">Authentication code</label>
            <input id="code" type="text" name="code" required autofocus maxlength="32"
                   inputmode="numeric" autocomplete="one-time-code" placeholder="123456"
                   style="text-align:center; font-size:20px; font-weight:600; letter-spacing:.3em;">
        </div>

        <button type="submit" class="btn-primary">Verify and sign in</button>
    </form>

    <p style="margin-top:18px; text-align:center;">
        <button type="button" id="toggle-recovery" class="link-subtle"
                style="background:none; border:0; cursor:pointer;">
            Use a recovery code instead
        </button>
    </p>

    <p style="margin-top:10px; text-align:center;">
        <a href="{{ route('login') }}" class="link-subtle">← Back to sign in</a>
    </p>

    <script>
        (function () {
            var useRecovery = false;
            var input  = document.getElementById('code');
            var label  = document.getElementById('code-label');
            var hint   = document.getElementById('challenge-hint');
            var toggle = document.getElementById('toggle-recovery');

            toggle.addEventListener('click', function () {
                useRecovery = !useRecovery;

                if (useRecovery) {
                    label.textContent = 'Recovery code';
                    hint.textContent  = 'Enter one of the recovery codes you saved when you set up 2FA. Each code works once.';
                    input.placeholder = 'XXXX-XXXX-XXXX';
                    input.setAttribute('inputmode', 'text');
                    input.removeAttribute('autocomplete');
                    input.style.letterSpacing = '.1em';
                    input.style.fontSize = '17px';
                    toggle.textContent = 'Use my authenticator app instead';
                } else {
                    label.textContent = 'Authentication code';
                    hint.textContent  = 'Open your authenticator app and enter the 6-digit code.';
                    input.placeholder = '123456';
                    input.setAttribute('inputmode', 'numeric');
                    input.setAttribute('autocomplete', 'one-time-code');
                    input.style.letterSpacing = '.3em';
                    input.style.fontSize = '20px';
                    toggle.textContent = 'Use a recovery code instead';
                }

                input.value = '';
                input.focus();
            });
        })();
    </script>

</x-guest-layout>
