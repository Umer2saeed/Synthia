{{--
|--------------------------------------------------------------------------
| Welcome Email View
|--------------------------------------------------------------------------
| This view receives these variables:
|   $user            → the User model (public property from WelcomeMail)
|   $roleName        → 'admin', 'editor', 'author', or 'reader'
|   $roleDescription → role-specific explanation string
|   $ctaLabel        → button text ('Start Reading' or 'Go to Dashboard')
|   $ctaUrl          → button URL (blog or admin dashboard)
|
| The <x-emails.layouts.master> component wraps everything with
| the standard Synthia email header and footer.
--}}

<x-emails.layouts.master>

    {{-- ================================================
         SECTION 1: PERSONALIZED GREETING
         ================================================
         Using the user's actual name makes the email feel
         personal rather than automated. Even though it IS
         automated, personalization increases open rates and
         engagement significantly.
    ================================================ --}}
    <p style="margin: 0 0 8px;
              font-size: 13px;
              font-weight: 600;
              color: #6366f1;
              text-transform: uppercase;
              letter-spacing: 0.05em;">
        Welcome aboard
    </p>

    <h1 style="margin: 0 0 16px;
               font-size: 26px;
               font-weight: 800;
               color: #1e293b;
               line-height: 1.3;">
        Hello, {{ $user->name }}! 👋
    </h1>

    <p style="margin: 0 0 24px;
              font-size: 15px;
              color: #475569;
              line-height: 1.7;">
        Your Synthia account has been created successfully.
        We are thrilled to have you join our community of readers
        and writers.
    </p>

    {{-- ================================================
         SECTION 2: ROLE BADGE
         ================================================
         Shows the user exactly what role they have.
         Different roles have different colors so it is
         visually clear and memorable.
    ================================================ --}}
    @php
        /*
        | Role badge colors — must use inline styles for email compatibility.
        | We define both background and text color per role.
        */
        $badgeStyles = match($roleName) {
            'admin'  => 'background-color:#fef2f2; color:#dc2626; border: 1px solid #fecaca;',
            'editor' => 'background-color:#eff6ff; color:#2563eb; border: 1px solid #bfdbfe;',
            'author' => 'background-color:#f0fdf4; color:#16a34a; border: 1px solid #bbf7d0;',
            default  => 'background-color:#f5f3ff; color:#7c3aed; border: 1px solid #ddd6fe;',
        };
    @endphp

    <table role="presentation" cellspacing="0" cellpadding="0" border="0"
           style="margin: 0 0 20px;">
        <tr>
            <td style="{{ $badgeStyles }}
                        padding: 6px 14px;
                        border-radius: 20px;
                        font-size: 13px;
                        font-weight: 700;">
                {{ ucfirst($roleName) }} Account
            </td>
        </tr>
    </table>

    {{-- ================================================
         SECTION 3: ROLE DESCRIPTION
         ================================================
         Tells the user exactly what they can do.
         This reduces confusion and support requests.
         Users who understand their access are more engaged.
    ================================================ --}}
    @include('components.emails.partials._panel', [
        'content' => $roleDescription,
        'color'   => '#f8fafc',
        'border'  => '#6366f1',
    ])

    {{-- ================================================
         SECTION 4: PRIMARY CALL TO ACTION
         ================================================
         The single most important button in the email.
         There is only ONE primary action — not three.
         Too many options causes decision paralysis.
         The user should know exactly what to do next.
    ================================================ --}}
    @include('components.emails.partials._button', [
        'url'   => $ctaUrl,
        'label' => $ctaLabel,
    ])

    @include('components.emails.partials._divider')

    {{-- ================================================
         SECTION 5: QUICK START TIPS
         ================================================
         Three specific things the user can do right now.
         Numbered list because order matters — start simple,
         build to more complex actions.
         Each tip links directly to the relevant page.
    ================================================ --}}
    <p style="margin: 0 0 16px;
              font-size: 15px;
              font-weight: 700;
              color: #1e293b;">
        Get started in 3 steps:
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="margin-bottom: 24px;">

        {{-- Tip 1 --}}
        <tr>
            <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="width: 36px; vertical-align: top; padding-top: 2px;">
                            <div style="width: 26px;
                                        height: 26px;
                                        background-color: #6366f1;
                                        border-radius: 50%;
                                        text-align: center;
                                        font-size: 13px;
                                        font-weight: 700;
                                        color: white;
                                        line-height: 26px;">
                                1
                            </div>
                        </td>
                        <td style="padding-left: 12px;">
                            <p style="margin: 0 0 4px;
                                      font-size: 14px;
                                      font-weight: 600;
                                      color: #1e293b;">
                                Complete your profile
                            </p>
                            <p style="margin: 0;
                                      font-size: 13px;
                                      color: #64748b;
                                      line-height: 1.5;">
                                Add a username, bio, and avatar so other readers know who you are.
                                <a href="{{ route('frontend.profile.edit') }}"
                                   style="color: #6366f1; text-decoration: none; font-weight: 600;">
                                    Edit your profile →
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Tip 2 --}}
        <tr>
            <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="width: 36px; vertical-align: top; padding-top: 2px;">
                            <div style="width: 26px;
                                        height: 26px;
                                        background-color: #6366f1;
                                        border-radius: 50%;
                                        text-align: center;
                                        font-size: 13px;
                                        font-weight: 700;
                                        color: white;
                                        line-height: 26px;">
                                2
                            </div>
                        </td>
                        <td style="padding-left: 12px;">
                            <p style="margin: 0 0 4px;
                                      font-size: 14px;
                                      font-weight: 600;
                                      color: #1e293b;">
                                Explore the blog
                            </p>
                            <p style="margin: 0;
                                      font-size: 13px;
                                      color: #64748b;
                                      line-height: 1.5;">
                                Browse articles by category or search for topics that interest you.
                                <a href="{{ route('blog') }}"
                                   style="color: #6366f1; text-decoration: none; font-weight: 600;">
                                    Browse articles →
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Tip 3 --}}
        <tr>
            <td style="padding: 12px 0; vertical-align: top;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="width: 36px; vertical-align: top; padding-top: 2px;">
                            <div style="width: 26px;
                                        height: 26px;
                                        background-color: #6366f1;
                                        border-radius: 50%;
                                        text-align: center;
                                        font-size: 13px;
                                        font-weight: 700;
                                        color: white;
                                        line-height: 26px;">
                                3
                            </div>
                        </td>
                        <td style="padding-left: 12px;">
                            <p style="margin: 0 0 4px;
                                      font-size: 14px;
                                      font-weight: 600;
                                      color: #1e293b;">
                                @if($roleName === 'reader')
                                    Follow your favorite authors
                                @else
                                    Publish your first post
                                @endif
                            </p>
                            <p style="margin: 0;
                                      font-size: 13px;
                                      color: #64748b;
                                      line-height: 1.5;">
                                @if($roleName === 'reader')
                                    Visit any author's profile and click Follow to get notified of their new articles.
                                    <a href="{{ route('blog') }}"
                                       style="color: #6366f1; text-decoration: none; font-weight: 600;">
                                        Find authors →
                                    </a>
                                @else
                                    Share your knowledge with the Synthia community.
                                    <a href="{{ route('admin.posts.create') }}"
                                       style="color: #6366f1; text-decoration: none; font-weight: 600;">
                                        Write a post →
                                    </a>
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>

    @include('components.emails.partials._divider')

    {{-- ================================================
         SECTION 6: ACCOUNT SUMMARY
         ================================================
         Shows the user their account details.
         Useful if they are checking "did my registration work?"
         Also reminds them what email they used.
    ================================================ --}}
    <p style="margin: 0 0 12px;
              font-size: 14px;
              font-weight: 700;
              color: #1e293b;">
        Your account details:
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="margin-bottom: 24px; background-color: #f8fafc; border-radius: 8px;">
        <tr>
            <td style="padding: 16px 20px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">

                    {{-- Name row --}}
                    <tr>
                        <td style="padding: 5px 0;
                                   font-size: 13px;
                                   color: #94a3b8;
                                   width: 80px;">
                            Name
                        </td>
                        <td style="padding: 5px 0;
                                   font-size: 13px;
                                   color: #1e293b;
                                   font-weight: 600;">
                            {{ $user->name }}
                        </td>
                    </tr>

                    {{-- Email row --}}
                    <tr>
                        <td style="padding: 5px 0;
                                   font-size: 13px;
                                   color: #94a3b8;">
                            Email
                        </td>
                        <td style="padding: 5px 0;
                                   font-size: 13px;
                                   color: #1e293b;
                                   font-weight: 600;">
                            {{ $user->email }}
                        </td>
                    </tr>

                    {{-- Role row --}}
                    <tr>
                        <td style="padding: 5px 0;
                                   font-size: 13px;
                                   color: #94a3b8;">
                            Role
                        </td>
                        <td style="padding: 5px 0;
                                   font-size: 13px;
                                   color: #1e293b;
                                   font-weight: 600;">
                            {{ ucfirst($roleName) }}
                        </td>
                    </tr>

                    {{-- Joined row --}}
                    <tr>
                        <td style="padding: 5px 0;
                                   font-size: 13px;
                                   color: #94a3b8;">
                            Joined
                        </td>
                        <td style="padding: 5px 0;
                                   font-size: 13px;
                                   color: #1e293b;
                                   font-weight: 600;">
                            {{ $user->created_at->format('d F Y') }}
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

    {{-- ================================================
         SECTION 7: CLOSING MESSAGE
         ================================================
         Warm, human closing paragraph.
         Reminds the user they can reply if they need help.
         Builds trust and reduces support anxiety.
    ================================================ --}}
    <p style="margin: 0 0 8px;
              font-size: 15px;
              color: #475569;
              line-height: 1.7;">
        We are excited to have you on Synthia. If you ever have
        questions, suggestions, or just want to say hello — this
        email goes straight to our team.
    </p>

    <p style="margin: 0;
              font-size: 15px;
              color: #475569;
              line-height: 1.7;">
        Happy reading! 📚<br>
        <strong style="color: #1e293b;">The Synthia Team</strong>
    </p>

</x-emails.layouts.master>
