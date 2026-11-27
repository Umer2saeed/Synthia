{{--
| Global toast notifications
|
| Already included in the admin and frontend layouts as <x-toast />.
|
| FROM A CONTROLLER (works exactly as before):
|     return back()->with('success', 'Post created.');      // success | error | warning | info
|
|     // richer toast with a title and an action button:
|     return back()->with('toast', [
|         'type'    => 'success',
|         'title'   => 'Post deleted',
|         'message' => 'The post was moved to trash.',
|         'action'  => ['label' => 'View trash', 'href' => route('admin.posts.trash')],
|     ]);
|
| FROM JAVASCRIPT (any page, any module):
|     toast.success('Saved.');            toast.error('Could not save.');
|     toast.warning('Careful.');          toast.info('FYI.');
|     toast.show({ type: 'success', title: 'Done', message: '...', duration: 8000,
|                  action: { label: 'Undo', onClick: () => undo() } });
|     window.showToast('Saved.', 'success');   // old API, still works
|
| Duration: success 4s, info 5s, warning 6.5s, error stays until closed.
| Close with X, swipe sideways, or press Esc. Hover pauses the timer.
|
| <x-toast :validation="false" /> turns off the automatic "Please check the form" toast.
--}}
@props(['validation' => true])

@php
    $queue = [];

    foreach (['success', 'error', 'warning', 'info'] as $type) {
        $flash = session($type);
        if (is_string($flash) && $flash !== '') {
            $queue[] = ['type' => $type, 'message' => $flash];
        }
    }

    $rich = session('toast');
    if (is_array($rich) && ! empty($rich['message'])) {
        $queue[] = $rich;
    }

    if ($validation && isset($errors) && $errors->any()) {
        $problems = $errors->count();
        $queue[] = [
            'type'     => 'error',
            'title'    => 'Please check the form',
            'message'  => $problems === 1 ? 'There is 1 problem to fix.' : "There are {$problems} problems to fix.",
            'duration' => 7000,
        ];
    }
@endphp

<div id="synt-region" class="synt-region" role="region" aria-label="Notifications" aria-live="polite"></div>

<style>
    .synt-region { position: fixed; z-index: 9999; top: 16px; left: 16px; right: 16px; display: flex; flex-direction: column; pointer-events: none; }
    @media (min-width: 640px) { .synt-region { left: auto; right: 20px; top: 20px; width: 360px; } }

    .synt-item { pointer-events: auto; margin-bottom: 10px;
        transition: height .3s cubic-bezier(.4,0,.2,1) .06s, margin-bottom .3s cubic-bezier(.4,0,.2,1) .06s; }

    .synt-card { --rgb: 99,102,241; position: relative; display: flex; align-items: flex-start; gap: 11px;
        padding: 12px 38px 14px 14px; border-radius: 14px; overflow: hidden;
        background: #ffffff; border: 1px solid #e5e7eb; color: #111827;
        box-shadow: 0 10px 30px -10px rgba(17,24,39,.22), 0 2px 6px rgba(17,24,39,.06);
        touch-action: pan-y; user-select: none; -webkit-user-select: none; cursor: grab;
        animation: synt-in .42s cubic-bezier(.21,1.02,.73,1) backwards;
        transition: transform .25s ease, opacity .25s ease; }
    .synt-card::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: rgb(var(--rgb)); }
    .synt-card.is-dragging { cursor: grabbing; transition: none; }
    .synt-card.synt-bump { animation: synt-bump .3s ease; }
    .synt-item.is-leaving .synt-card { transition: transform .28s cubic-bezier(.4,0,1,1), opacity .24s ease-in; }

    .synt-success { --rgb: 16,185,129; }
    .synt-error   { --rgb: 239,68,68; }
    .synt-warning { --rgb: 245,158,11; }
    .synt-info    { --rgb: 99,102,241; }

    .synt-icon { flex: none; width: 28px; height: 28px; border-radius: 9999px; display: grid; place-items: center;
        background: rgba(var(--rgb), .14); color: rgb(var(--rgb)); }
    .synt-icon svg { width: 16px; height: 16px; }

    .synt-body { flex: 1; min-width: 0; }
    .synt-title { margin: 0; font-size: 13.5px; font-weight: 600; line-height: 1.3; color: #111827; }
    .synt-msg   { margin: 2px 0 0; font-size: 13px; line-height: 1.4; color: #4b5563; word-wrap: break-word; }
    .synt-title + .synt-msg { margin-top: 1px; }
    .synt-body > .synt-msg:first-child { margin-top: 4px; color: #111827; font-weight: 500; }

    .synt-action { display: inline-block; margin-top: 8px; padding: 0; background: none; border: 0; cursor: pointer;
        font-size: 12.5px; font-weight: 600; color: rgb(var(--rgb)); text-decoration: none; }
    .synt-action:hover { text-decoration: underline; }

    .synt-count { position: absolute; right: 40px; top: 11px; padding: 1px 7px; border-radius: 9999px;
        font-size: 11px; font-weight: 700; background: rgba(var(--rgb), .15); color: rgb(var(--rgb)); }

    .synt-close { position: absolute; top: 8px; right: 8px; width: 24px; height: 24px; display: grid; place-items: center;
        border: 0; border-radius: 8px; background: transparent; color: #9ca3af; cursor: pointer; transition: background .15s, color .15s; }
    .synt-close:hover { background: rgba(107,114,128,.15); color: #374151; }
    .synt-close svg { width: 14px; height: 14px; }

    .synt-bar { position: absolute; left: 0; bottom: 0; height: 3px; width: 100%; background: rgb(var(--rgb)); opacity: .55;
        transform-origin: left center; animation: synt-bar var(--dur, 4000ms) linear forwards; }
    .synt-item:hover .synt-bar, .synt-item:focus-within .synt-bar, .synt-card.is-dragging .synt-bar { animation-play-state: paused; }

    .dark .synt-card, [data-theme="dark"] .synt-card { background: #1f2937; border-color: #374151; color: #f9fafb;
        box-shadow: 0 14px 34px -12px rgba(0,0,0,.65), 0 2px 6px rgba(0,0,0,.35); }
    .dark .synt-title, [data-theme="dark"] .synt-title { color: #f9fafb; }
    .dark .synt-msg, [data-theme="dark"] .synt-msg { color: #9ca3af; }
    .dark .synt-body > .synt-msg:first-child, [data-theme="dark"] .synt-body > .synt-msg:first-child { color: #f3f4f6; }
    .dark .synt-icon, [data-theme="dark"] .synt-icon { background: rgba(var(--rgb), .22); }
    .dark .synt-close:hover, [data-theme="dark"] .synt-close:hover { background: rgba(255,255,255,.1); color: #e5e7eb; }

    @keyframes synt-in   { from { opacity: 0; transform: translateX(28px) scale(.95); } to { opacity: 1; transform: none; } }
    @keyframes synt-bar  { from { transform: scaleX(1); } to { transform: scaleX(0); } }
    @keyframes synt-bump { 0% { transform: scale(1); } 40% { transform: scale(1.035); } 100% { transform: scale(1); } }
    @media (max-width: 639px) { @keyframes synt-in { from { opacity: 0; transform: translateY(-16px) scale(.96); } to { opacity: 1; transform: none; } } }
    @media (prefers-reduced-motion: reduce) {
        .synt-card, .synt-item { animation-duration: .01ms !important; transition-duration: .01ms !important; }
    }
</style>

<script>
    (function () {
        var QUEUE = @json($queue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        if (!window.toast) {
            var MAX_VISIBLE = 4;
            var DEFAULTS = {
                success: { duration: 4000, title: 'Success' },
                info:    { duration: 5000, title: 'Heads up' },
                warning: { duration: 6500, title: 'Warning' },
                error:   { duration: 0,    title: 'Something went wrong' }   // 0 = stays until closed
            };
            var ICONS = {
                success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>',
                error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6L6 18"/></svg>',
                warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9L2.4 17.5A2 2 0 004.1 20.5h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>',
                info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16v-4"/><path d="M12 8h.01"/><circle cx="12" cy="12" r="9"/></svg>'
            };
            var CLOSE = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>';
            var counter = 0, pending = [];

            function region() { return document.getElementById('synt-region'); }

            function el(tag, cls, text) {
                var node = document.createElement(tag);
                if (cls) node.className = cls;
                if (text) node.textContent = text;          // textContent: messages can never inject HTML
                return node;
            }

            function dismiss(target, dir) {
                var item = target;
                if (typeof target === 'number') {
                    var host = region();
                    item = host && Array.prototype.filter.call(host.children, function (c) { return c._id === target; })[0];
                }
                if (!item || item._leaving) return;
                item._leaving = true;

                var card = item.firstChild;
                item.style.height = item.offsetHeight + 'px';
                void item.offsetHeight;                      // lock the height, then animate it to 0
                item.classList.add('is-leaving');
                card.style.transform = 'translateX(' + ((dir || 1) * 115) + '%) scale(.96)';
                card.style.opacity = '0';
                item.style.height = '0px';
                item.style.marginBottom = '0px';

                setTimeout(function () { if (item.parentNode) item.parentNode.removeChild(item); }, 380);
            }

            function enableSwipe(card, item) {
                var startX = 0, dx = 0, startT = 0, dragging = false;

                card.addEventListener('pointerdown', function (e) {
                    if (e.button !== 0 || e.target.closest('button, a')) return;
                    dragging = true; startX = e.clientX; startT = Date.now(); dx = 0;
                    card.classList.add('is-dragging');
                    try { card.setPointerCapture(e.pointerId); } catch (err) {}
                });

                card.addEventListener('pointermove', function (e) {
                    if (!dragging) return;
                    dx = e.clientX - startX;
                    card.style.transform = 'translateX(' + dx + 'px)';
                    card.style.opacity = String(Math.max(0.25, 1 - Math.abs(dx) / 260));
                });

                function end() {
                    if (!dragging) return;
                    dragging = false;
                    card.classList.remove('is-dragging');
                    var speed = Math.abs(dx) / Math.max(Date.now() - startT, 1);
                    if (Math.abs(dx) > 90 || (speed > 0.6 && Math.abs(dx) > 30)) {
                        dismiss(item, dx >= 0 ? 1 : -1);
                    } else {
                        card.style.transform = ''; card.style.opacity = '';
                    }
                }
                card.addEventListener('pointerup', end);
                card.addEventListener('pointercancel', end);
            }

            function show(o) {
                o = o || {};
                var type = DEFAULTS[o.type] ? o.type : 'info';
                var message = (o.message === undefined || o.message === null) ? '' : String(o.message);
                var title = (o.title === false || o.title === '') ? '' : (o.title || DEFAULTS[type].title);
                if (!message && !title) return null;

                var host = region();
                if (!host) { pending.push(o); return null; }

                var duration = (typeof o.duration === 'number') ? o.duration : DEFAULTS[type].duration;
                var key = type + '|' + title + '|' + message;

                // Same toast already on screen: count it and restart its timer instead of stacking copies.
                var dup = Array.prototype.filter.call(host.children, function (c) { return c._key === key && !c._leaving; })[0];
                if (dup) {
                    dup._count++;
                    dup._badge.textContent = '×' + dup._count;
                    dup._badge.hidden = false;
                    if (dup._bar) { dup._bar.style.animation = 'none'; void dup._bar.offsetWidth; dup._bar.style.animation = ''; }
                    dup.firstChild.classList.remove('synt-bump'); void dup.firstChild.offsetWidth; dup.firstChild.classList.add('synt-bump');
                    return dup._id;
                }

                var item = el('div', 'synt-item');
                item._key = key; item._count = 1; item._id = ++counter;

                var card = el('div', 'synt-card synt-' + type);
                card.setAttribute('role', (type === 'error' || type === 'warning') ? 'alert' : 'status');

                var icon = el('span', 'synt-icon'); icon.innerHTML = ICONS[type];
                var body = el('div', 'synt-body');
                if (title)   body.appendChild(el('p', 'synt-title', title));
                if (message) body.appendChild(el('p', 'synt-msg', message));

                if (o.action && o.action.label) {
                    var isLink = !!o.action.href && !/^\s*javascript:/i.test(o.action.href);
                    var act = el(isLink ? 'a' : 'button', 'synt-action', o.action.label);
                    if (isLink) act.href = o.action.href; else act.type = 'button';
                    act.addEventListener('click', function () {
                        if (typeof o.action.onClick === 'function') o.action.onClick();
                        dismiss(item, 1);
                    });
                    body.appendChild(act);
                }

                var badge = el('span', 'synt-count'); badge.hidden = true; item._badge = badge;

                var close = el('button', 'synt-close');
                close.type = 'button';
                close.setAttribute('aria-label', 'Dismiss notification');
                close.innerHTML = CLOSE;
                close.addEventListener('click', function (e) { e.stopPropagation(); dismiss(item, 1); });

                card.appendChild(icon); card.appendChild(body); card.appendChild(badge); card.appendChild(close);

                if (duration > 0) {
                    var bar = el('span', 'synt-bar');
                    card.style.setProperty('--dur', duration + 'ms');
                    bar.addEventListener('animationend', function (e) { if (e.animationName === 'synt-bar') dismiss(item, 1); });
                    card.appendChild(bar);
                    item._bar = bar;
                }

                item.appendChild(card);
                host.appendChild(item);
                enableSwipe(card, item);

                var live = Array.prototype.filter.call(host.children, function (c) { return !c._leaving; });
                while (live.length > MAX_VISIBLE) dismiss(live.shift(), 1);

                return item._id;
            }

            window.toast = {
                show:    show,
                success: function (m, o) { return show(Object.assign({ type: 'success', message: m }, o)); },
                error:   function (m, o) { return show(Object.assign({ type: 'error',   message: m }, o)); },
                warning: function (m, o) { return show(Object.assign({ type: 'warning', message: m }, o)); },
                info:    function (m, o) { return show(Object.assign({ type: 'info',    message: m }, o)); },
                dismiss: dismiss,
                clear:   function () { var h = region(); if (h) Array.prototype.slice.call(h.children).forEach(function (c) { dismiss(c, 1); }); }
            };

            // Old API used by your existing scripts: showToast(message, type, duration)
            window.showToast   = function (message, type, duration) { return show({ type: type || 'success', message: message, duration: duration }); };
            window.synthiaToast = window.showToast;

            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') window.toast.clear(); });
            document.addEventListener('DOMContentLoaded', function () { pending.splice(0).forEach(show); });
        }

        // Flash messages from the session (fired once per page load)
        if (!window.__toastFlashFired) {
            window.__toastFlashFired = true;
            var fire = function () {
                QUEUE.forEach(function (q, i) { setTimeout(function () { window.toast.show(q); }, 250 + i * 120); });
            };
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fire); else fire();
        }
    })();
</script>
