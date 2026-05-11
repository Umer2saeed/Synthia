/*
|--------------------------------------------------------------------------
| Code Block Copy Button
|--------------------------------------------------------------------------
| This module scans the post content area for all <pre><code> blocks
| and injects a "Copy" button into each one.
|
| WHY we inject via JS instead of server-side:
| Post content is stored as HTML in the database. Adding buttons
| server-side would require modifying stored content or using a
| Blade directive — both are fragile. JS injection is cleaner,
| keeps the database content pure, and works with all future content.
|
| WHY we target #post-content specifically:
| We do NOT want copy buttons on code snippets in the admin panel,
| comments, or other areas. Only the public post reading experience.
*/
document.addEventListener('DOMContentLoaded', function () {

    /*
    | Only run on pages that have the post content container.
    | This prevents the script from doing unnecessary work on
    | pages like the blog listing or admin panel.
    */
    const postContent = document.getElementById('post-content');
    if (!postContent) return;

    /*
    | Find every <pre> element inside the post content.
    | TipTap generates: <pre class="code-block"><code>...</code></pre>
    | The button is added to <pre> not <code> so we can position it
    | relative to the visible block boundary (pre has the background).
    */
    const codeBlocks = postContent.querySelectorAll('pre');

    if (codeBlocks.length === 0) return;

    codeBlocks.forEach(function (pre) {

        /*
        |----------------------------------------------------------------------
        | Make the <pre> position:relative so we can position the
        | button absolute inside it.
        |----------------------------------------------------------------------
        | We use style directly instead of a class because this element's
        | styles are managed by Tailwind Typography (prose classes) and
        | we do not want to fight specificity with a class name.
        */
        pre.style.position = 'relative';

        /*
        |----------------------------------------------------------------------
        | Create the Copy button
        |----------------------------------------------------------------------
        */
        const button = document.createElement('button');
        button.type        = 'button';
        button.textContent = 'Copy';
        button.setAttribute('aria-label', 'Copy code to clipboard');

        /*
        | Apply styles directly for maximum specificity.
        | We cannot rely on Tailwind classes here because the prose
        | plugin applies aggressive resets to elements inside .prose.
        | Inline styles override everything reliably.
        */
        applyButtonStyles(button, false);

        /*
        |----------------------------------------------------------------------
        | Position the button in the top-right corner of the code block
        |----------------------------------------------------------------------
        */
        button.style.position = 'absolute';
        button.style.top      = '0.5rem';
        button.style.right    = '0.5rem';
        button.style.zIndex   = '10';

        /*
        |----------------------------------------------------------------------
        | Click handler — copy code text to clipboard
        |----------------------------------------------------------------------
        */
        button.addEventListener('click', async function () {

            const code = pre.querySelector('code');
            const text = code ? code.textContent : pre.textContent;

            /*
            |----------------------------------------------------------------------
            | Try modern clipboard API first (requires HTTPS or localhost).
            | Fall back to execCommand for HTTP dev environments like synthia.test
            |----------------------------------------------------------------------
            */
            const copied = await copyToClipboard(text);

            if (copied) {
                button.textContent = 'Copied!';
                applyButtonStyles(button, true);

                setTimeout(function () {
                    button.textContent = 'Copy';
                    applyButtonStyles(button, false);
                }, 2000);
            } else {
                button.textContent = 'Failed';
                setTimeout(function () {
                    button.textContent = 'Copy';
                }, 2000);
            }
        });

        /*
        | Show the button only when hovering over the code block.
        | This keeps the UI clean — button is not always visible.
        | On mobile (no hover), button is always visible.
        */
        button.style.opacity    = '0';
        button.style.transition = 'opacity 0.15s ease';

        pre.addEventListener('mouseenter', function () {
            button.style.opacity = '1';
        });

        pre.addEventListener('mouseleave', function () {
            /*
            | Do not hide the button while in "Copied!" state.
            | Let it finish its 2-second cycle first.
            */
            if (button.textContent !== 'Copied!') {
                button.style.opacity = '0';
            }
        });

        /*
        | On mobile there is no hover — always show the button
        | if the device is touch-based.
        */
        if (window.matchMedia('(pointer: coarse)').matches) {
            button.style.opacity = '1';
        }

        /*
        | Append the button to the <pre> element.
        | It will be positioned absolute inside it.
        */
        pre.appendChild(button);
    });

});


/*
|--------------------------------------------------------------------------
| copyToClipboard() — Copy text with HTTPS and HTTP fallback
|--------------------------------------------------------------------------
| navigator.clipboard.writeText() only works on:
|   - HTTPS pages
|   - localhost (exactly "localhost", not custom domains)
|
| synthia.test runs on HTTP so we fall back to the legacy
| execCommand('copy') approach which works on any HTTP page.
|
| Returns true on success, false on failure.
*/
async function copyToClipboard(text) {

    /*
    | Try modern API first
    */
    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            console.warn('Clipboard API failed, trying fallback:', err);
        }
    }

    /*
    | Fallback: create a temporary textarea, select its content,
    | and use the legacy execCommand to copy it.
    | This works on HTTP pages in all major browsers.
    */
    try {
        const textarea = document.createElement('textarea');

        textarea.value = text;

        /*
        | Position it off-screen so it is not visible to the user.
        */
        textarea.style.position = 'fixed';
        textarea.style.left     = '-9999px';
        textarea.style.top      = '-9999px';
        textarea.style.opacity  = '0';

        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();

        const success = document.execCommand('copy');
        document.body.removeChild(textarea);

        return success;

    } catch (err) {
        console.error('Both clipboard methods failed:', err);
        return false;
    }
}

/*
|--------------------------------------------------------------------------
| applyButtonStyles() — Apply styles based on button state
|--------------------------------------------------------------------------
| WHY a function instead of toggling classes?
| The prose Tailwind plugin resets many styles on elements inside .prose.
| Inline styles have higher specificity and work reliably regardless
| of what prose does to child elements.
|
| We read the dark mode state from the <html> element's class list
| so the button matches the current theme.
|
| @param button   The button DOM element
| @param success  true = "Copied!" green state, false = default state
*/
function applyButtonStyles(button, success) {
    const isDark = document.documentElement.classList.contains('dark');

    /*
    | Base styles shared between both states
    */
    Object.assign(button.style, {
        padding:       '0.2rem 0.6rem',
        fontSize:      '0.7rem',
        fontFamily:    'inherit',
        fontWeight:    '500',
        borderRadius:  '0.375rem',
        border:        'none',
        cursor:        'pointer',
        lineHeight:    '1.4',
        letterSpacing: '0.01em',
        transition:    'background-color 0.15s ease, color 0.15s ease, opacity 0.15s ease',
    });

    if (success) {
        /*
        | "Copied!" state — green background in both light and dark mode
        */
        button.style.backgroundColor = '#16a34a'; // green-600
        button.style.color           = '#ffffff';
    } else {
        /*
        | Default state — neutral, subtle, does not distract from code
        */
        if (isDark) {
            button.style.backgroundColor = '#374151'; // gray-700
            button.style.color           = '#d1d5db'; // gray-300
        } else {
            button.style.backgroundColor = '#e5e7eb'; // gray-200
            button.style.color           = '#374151'; // gray-700
        }
    }
}
