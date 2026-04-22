{{--
| Global Toast Notification Component
| Placed in resources/views/components/toast.blade.php
| Used as <x-toast /> in layouts
--}}

<div
    x-data="toastManager()"
    x-init="initFromFlash()"
    class="fixed top-4 right-4 z-50 flex flex-col gap-2"
    style="max-width: 380px; width: calc(100% - 32px);"
    role="region"
    aria-label="Notifications"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="flex items-start gap-3 px-4 py-3 rounded-xl shadow-lg border"
            :class="toastClasses(toast.type)"
            role="alert"
            style="position: relative; overflow: hidden;"
        >
            {{-- Icon --}}
            <span class="text-base shrink-0 mt-0.5" x-text="toastIcon(toast.type)"></span>

            {{-- Message --}}
            <p class="flex-1 text-sm font-medium leading-relaxed" x-text="toast.message"></p>

            {{-- Close button --}}
            <button
                @click="dismiss(toast.id)"
                class="shrink-0 opacity-60 hover:opacity-100 transition-opacity"
                aria-label="Dismiss"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Progress bar --}}
            <div
                class="absolute bottom-0 left-0 h-0.5 rounded-b-xl"
                :class="progressClasses(toast.type)"
                :style="`width: ${toast.progress}%; transition: width ${toast.duration}ms linear`">
            </div>
        </div>
    </template>
</div>

{{--
| Hidden flash data elements.
| Alpine reads these on page load and converts them into toasts.
| This replaces ALL session flash @if blocks across every view.
--}}
@if(session('success'))
    <div id="flash-success" data-message="{{ session('success') }}" class="hidden"></div>
@endif
@if(session('error'))
    <div id="flash-error" data-message="{{ session('error') }}" class="hidden"></div>
@endif
@if(session('warning'))
    <div id="flash-warning" data-message="{{ session('warning') }}" class="hidden"></div>
@endif
@if(session('info'))
    <div id="flash-info" data-message="{{ session('info') }}" class="hidden"></div>
@endif

<script>
    function toastManager() {
        return {
            toasts: [],
            counter: 0,

            /*
            | initFromFlash() runs once when Alpine initialises this component.
            | It looks for hidden flash elements in the DOM and converts each
            | one into a toast notification automatically.
            | It also sets window.showToast so any JavaScript on the page
            | can trigger a toast without needing Alpine context.
            */
            initFromFlash() {
                ['success', 'error', 'warning', 'info'].forEach(type => {
                    const el = document.getElementById('flash-' + type);
                    if (el) {
                        setTimeout(() => this.show(el.dataset.message, type), 300);
                    }
                });

                /*
                | Expose showToast globally.
                | Any JavaScript anywhere on the page can call:
                |   window.showToast('Your message', 'success')
                |   window.showToast('Something went wrong', 'error')
                |   window.showToast('Heads up!', 'warning')
                |   window.showToast('Note', 'info')
                */
                window.showToast = (message, type = 'success', duration = 4000) => {
                    this.show(message, type, duration);
                };
            },

            /*
            | show() creates a toast object and pushes it into the reactive
            | toasts array. Alpine re-renders the x-for loop automatically.
            | The progress bar animates from 100 to 0 over the duration.
            | After duration ms the toast is dismissed automatically.
            */
            show(message, type = 'success', duration = 4000) {
                const id = ++this.counter;

                this.toasts.push({
                    id, message, type, duration,
                    visible: true,
                    progress: 100,
                });

                // Start progress animation after a small delay so
                // the element is rendered before we change width
                setTimeout(() => {
                    const t = this.toasts.find(t => t.id === id);
                    if (t) t.progress = 0;
                }, 50);

                // Auto-dismiss
                setTimeout(() => this.dismiss(id), duration);
            },

            /*
            | dismiss() hides a toast triggering the x-transition:leave animation.
            | After 250ms (the leave animation duration) the toast is removed
            | from the array so Alpine removes the DOM element.
            */
            dismiss(id) {
                const t = this.toasts.find(t => t.id === id);
                if (t) {
                    t.visible = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 250);
                }
            },

            // Returns Tailwind classes for the toast background and border
            toastClasses(type) {
                return {
                    success: 'bg-green-50 dark:bg-green-950 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200',
                    error:   'bg-red-50 dark:bg-red-950 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200',
                    warning: 'bg-amber-50 dark:bg-amber-950 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200',
                    info:    'bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200',
                }[type] ?? 'bg-blue-50 border-blue-200 text-blue-800';
            },

            // Returns classes for the progress bar colour
            progressClasses(type) {
                return {
                    success: 'bg-green-400',
                    error:   'bg-red-400',
                    warning: 'bg-amber-400',
                    info:    'bg-blue-400',
                }[type] ?? 'bg-blue-400';
            },

            // Returns the icon character for each toast type
            toastIcon(type) {
                return { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' }[type] ?? 'ℹ';
            },
        };
    }
</script>
