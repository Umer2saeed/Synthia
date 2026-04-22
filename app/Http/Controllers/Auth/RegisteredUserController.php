<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        /*
        |----------------------------------------------------------------------
        | Assign default role
        |----------------------------------------------------------------------
        | Every new user who registers through the public form gets
        | the 'reader' role automatically.
        |
        | WHY here and not in the User model's boot()?
        | Because user creation also happens in seeders (admin user).
        | We only want public registrations to get 'reader' role.
        | Admin users are assigned roles manually in the seeder.
        |
        | WHY before dispatch?
        | The WelcomeMail reads $user->roles to determine the email content.
        | If we dispatch before assigning the role, the job might run
        | before the role is saved, and the user would get a roleless email.
        | Assigning role THEN dispatching guarantees the role is in the DB.
        */
        $user->assignRole('reader');

        event(new Registered($user));

        /*
        |----------------------------------------------------------------------
        | Dispatch the welcome email job
        |----------------------------------------------------------------------
        | dispatch() stores the job in the jobs table immediately.
        | The HTTP response is returned RIGHT AFTER this line.
        | The actual email sends asynchronously in the background.
        |
        | The user does NOT wait for the email to send.
        | They see their homepage in milliseconds.
        */
        SendWelcomeEmailJob::dispatch($user);

        Auth::login($user);

        /*
        |----------------------------------------------------------------------
        | Redirect based on role
        |----------------------------------------------------------------------
        | Readers go to the public homepage — they have no admin access.
        | Other roles go to the admin dashboard.
        |
        | Since we just assigned 'reader' above, new public registrations
        | always go to 'home'.
        */
        if ($user->hasRole('reader')) {
            return redirect()->route('home')
                ->with('success', 'Welcome to Synthia, ' . $user->name . '!');
        }

        return redirect()->intended(route('admin.dashboard'));
    }
}
