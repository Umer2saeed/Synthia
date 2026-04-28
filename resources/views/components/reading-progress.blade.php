{{--
| Reading Progress Bar Component
|
| Usage: <x-reading-progress />
| Place this at the very top of the post layout, before the navbar.
|
| HOW IT WORKS:
|   - A fixed div spans the full width of the viewport at the top
|   - Its width starts at 0% and grows as the reader scrolls
|   - JavaScript calculates scroll position and updates the width
|   - CSS transition makes the width change smooth
|
| The bar tracks scrolling within the #post-content element only.
| This means the progress represents reading the article content,
| not the entire page (header, footer, comments).
--}}

<div
    id="reading-progress-container"
    class="fixed top-0 left-0 right-0 z-50 h-1
           bg-transparent pointer-events-none">

    <div
        id="reading-progress-bar"
        class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600
               transition-all duration-100 ease-out"
        style="width: 0%"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="0"
        aria-label="Reading progress">
    </div>

</div>

<script>
    /*
    |--------------------------------------------------------------------------
    | Reading Progress Bar JavaScript
    |--------------------------------------------------------------------------
    | Runs immediately when the script tag is parsed (not DOMContentLoaded)
    | because we want to attach the scroll listener as early as possible.
    */
    (function () {
        /*
        | Wait for DOM to be ready before accessing elements.
        | We use DOMContentLoaded because the elements must exist.
        */
        document.addEventListener('DOMContentLoaded', function () {
            const bar     = document.getElementById('reading-progress-bar');
            const content = document.getElementById('post-content');

            /*
            | If there is no #post-content on this page (e.g. blog listing page),
            | the component was included somewhere it should not be.
            | We silently exit — no errors thrown.
            */
            if (!bar || !content) return;

            /*
            |----------------------------------------------------------------------
            | calculateProgress() — compute scroll percentage
            |----------------------------------------------------------------------
            |
            | We track scrolling relative to the post content area only.
            | This is more accurate than tracking the full page scroll.
            |
            | contentRect.top  → where the content starts (from viewport top)
            | contentRect.bottom → where the content ends (from viewport top)
            |
            | When top is positive: user has not reached the content yet → 0%
            | When bottom is negative: user has passed the content → 100%
            | In between: calculate the exact percentage
            */
            function calculateProgress() {
                const contentRect   = content.getBoundingClientRect();
                const contentHeight = contentRect.height;
                const windowHeight  = window.innerHeight;

                /*
                | How far from the top of the viewport to the top of the content.
                | Negative when user has scrolled past the start of content.
                */
                const scrolledIntoContent = -contentRect.top;

                /*
                | Total scrollable range is content height minus window height.
                | We subtract windowHeight because the last window-height of
                | content is visible before you reach the "end".
                */
                const totalScrollable = contentHeight - windowHeight;

                if (totalScrollable <= 0) {
                    // Content fits in one screen — already at 100%
                    return 100;
                }

                if (scrolledIntoContent <= 0) {
                    // User has not reached the content yet
                    return 0;
                }

                const percentage = (scrolledIntoContent / totalScrollable) * 100;

                // Clamp between 0 and 100
                return Math.min(100, Math.max(0, percentage));
            }

            /*
            |----------------------------------------------------------------------
            | updateProgress() — update bar width and aria attribute
            |----------------------------------------------------------------------
            */
            function updateProgress() {
                const progress = calculateProgress();
                const rounded  = Math.round(progress);

                bar.style.width        = progress + '%';
                bar.setAttribute('aria-valuenow', rounded);
            }

            /*
            |----------------------------------------------------------------------
            | Throttle the scroll event for performance
            |----------------------------------------------------------------------
            | The scroll event fires many times per second (60+ fps on smooth scroll).
            | Updating the DOM on every single fire is wasteful.
            | requestAnimationFrame throttles updates to the browser's render cycle
            | (typically 60fps) which is the maximum useful update rate.
            |
            | ticking prevents multiple rAF calls queuing up.
            */
            let ticking = false;

            window.addEventListener('scroll', function () {
                if (!ticking) {
                    window.requestAnimationFrame(function () {
                        updateProgress();
                        ticking = false;
                    });
                    ticking = true;
                }
            }, { passive: true }); // passive: true improves scroll performance

            // Calculate initial progress (page might load scrolled down)
            updateProgress();
        });
    })();
</script>
