/*
|--------------------------------------------------------------------------
| Draft Autosave — Post Create and Edit Pages
|--------------------------------------------------------------------------
| This module runs on the post create and edit pages.
| It monitors the title and TipTap content fields and saves
| to the server every 60 seconds when content has changed.
|
| It also handles:
|   - The "restore draft" banner when an existing draft is found
|   - Clearing the draft when the form is successfully submitted
*/
document.addEventListener('DOMContentLoaded', function () {

    /*
    | Only run on post create/edit pages.
    | We detect these pages by looking for the autosave status element.
    */
    const statusEl = document.getElementById('autosave-status');
    if (!statusEl) return;

    const statusText = document.getElementById('autosave-status-text');
    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content;

    /*
    | Read the post ID if we are on the edit page.
    | On create page this is null (new post, no ID yet).
    */
    const postId = document.querySelector('[data-post-id]')?.dataset.postId || null;

    /*
    | Track state to know when content has changed since last save.
    */
    let lastSavedTitle   = '';
    let lastSavedContent = '';
    let isDirty          = false;
    let autosaveTimer    = null;
    let savedAt          = null;
    let draftId          = null;

    /*
    |--------------------------------------------------------------------------
    | Watch for content changes
    |--------------------------------------------------------------------------
    | Title: standard input event
    | Content: TipTap stores in a hidden input — we watch that input
    */
    const titleInput   = document.querySelector('input[name="title"]');
    const contentInput = document.querySelector('[data-editor-input]');

    if (titleInput) {
        titleInput.addEventListener('input', markDirty);
    }

    if (contentInput) {
        /*
        | MutationObserver watches for value changes on the hidden input.
        | TipTap updates the hidden input value via JavaScript — the
        | 'input' event does not fire for programmatic value changes.
        | MutationObserver watches the 'value' attribute directly.
        */
        const observer = new MutationObserver(markDirty);
        observer.observe(contentInput, { attributes: true, attributeFilter: ['value'] });

        /*
        | Also listen for input event as a fallback.
        */
        contentInput.addEventListener('input', markDirty);
    }

    function markDirty() {
        isDirty = true;
        statusEl.classList.remove('hidden');
        statusText.textContent = 'Unsaved changes...';
    }

    /*
    |--------------------------------------------------------------------------
    | Autosave every 60 seconds when content has changed
    |--------------------------------------------------------------------------
    */
    autosaveTimer = setInterval(async function () {

        if (!isDirty) return; // nothing changed since last save

        const currentTitle   = titleInput?.value || '';
        const currentContent = contentInput?.value || '';

        /*
        | Do not save if nothing meaningful was entered yet
        */
        if (!currentTitle.trim() && !currentContent.trim()) return;

        /*
        | Do not save if content is identical to last save
        */
        if (currentTitle === lastSavedTitle && currentContent === lastSavedContent) {
            isDirty = false;
            return;
        }

        statusText.textContent = 'Saving...';

        try {
            const response = await fetch('/admin/posts/autosave', {
                method:  'POST',
                headers: {
                    'X-CSRF-TOKEN':  csrfToken,
                    'Content-Type':  'application/json',
                    'Accept':        'application/json',
                },
                body: JSON.stringify({
                    post_id: postId ? parseInt(postId) : null,
                    title:   currentTitle,
                    content: currentContent,
                }),
            });

            const data = await response.json();

            if (data.success) {
                lastSavedTitle   = currentTitle;
                lastSavedContent = currentContent;
                isDirty          = false;
                savedAt          = new Date(data.saved_at);
                draftId          = data.draft_id;

                statusText.textContent = 'Draft saved';

                /*
                | Start updating "Saved X seconds ago" display
                */
                startRelativeTimeUpdater();
            }

        } catch (err) {
            statusText.textContent = 'Save failed — check connection';
            console.warn('Autosave failed:', err);
        }

    }, 60000); // every 60 seconds

    /*
    |--------------------------------------------------------------------------
    | Update "Saved X seconds ago" display
    |--------------------------------------------------------------------------
    */
    function startRelativeTimeUpdater() {
        /*
        | Update the "Saved X ago" display every 10 seconds.
        */
        const interval = setInterval(function () {
            if (!savedAt) {
                clearInterval(interval);
                return;
            }
            const seconds = Math.round((Date.now() - savedAt.getTime()) / 1000);
            if (seconds < 60) {
                statusText.textContent = `Draft saved ${seconds}s ago`;
            } else if (seconds < 3600) {
                statusText.textContent = `Draft saved ${Math.round(seconds / 60)}m ago`;
            } else {
                statusText.textContent = `Draft saved ${Math.round(seconds / 3600)}h ago`;
            }
        }, 10000);
    }

    /*
    |--------------------------------------------------------------------------
    | Restore draft banner — "Restore" button
    |--------------------------------------------------------------------------
    */
    const restoreBtn = document.getElementById('restore-draft-btn');
    const dismissBtn = document.getElementById('dismiss-draft-btn');
    const banner     = document.getElementById('autosave-banner');

    if (restoreBtn) {
        restoreBtn.addEventListener('click', function () {
            const savedTitle   = this.dataset.title || '';
            const savedContent = this.dataset.content || '';

            /*
            | Restore the title
            */
            if (titleInput && savedTitle) {
                titleInput.value = savedTitle;
                titleInput.dispatchEvent(new Event('input')); // trigger slug generation
            }

            /*
            | Restore the content into TipTap.
            | We set the hidden input value and dispatch a change event.
            | TipTap listens for this and updates its internal state.
            */
            if (contentInput && savedContent) {
                contentInput.value = savedContent;
                contentInput.dispatchEvent(new Event('input'));

                /*
                | Also try to set TipTap editor content directly
                | by finding the editor instance on the window object.
                | TipTap does not expose a global API so we use the
                | hidden input approach which is reliable.
                */
            }

            banner?.remove();
        });
    }

    if (dismissBtn) {
        dismissBtn.addEventListener('click', async function () {
            /*
            | Discard the draft from the server.
            */
            try {
                await fetch('/admin/posts/autosave', {
                    method:  'DELETE',
                    headers: {
                        'X-CSRF-TOKEN':  csrfToken,
                        'Content-Type':  'application/json',
                    },
                    body: JSON.stringify({
                        post_id: postId ? parseInt(postId) : null,
                    }),
                });
            } catch (err) {
                console.warn('Could not discard draft:', err);
            }

            banner?.remove();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Clear draft on successful form submit
    |--------------------------------------------------------------------------
    | When the author submits the post form (create or update), we clear
    | the autosave draft before the form navigates away.
    | We use 'beforeunload' would not be reliable — instead we listen
    | on the submit button click and send a quick synchronous fetch.
    */
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.addEventListener('click', async function () {
            if (!draftId) return; // no draft to clear

            try {
                await fetch('/admin/posts/autosave', {
                    method:  'DELETE',
                    headers: {
                        'X-CSRF-TOKEN':  csrfToken,
                        'Content-Type':  'application/json',
                    },
                    body: JSON.stringify({
                        post_id: postId ? parseInt(postId) : null,
                    }),
                });
            } catch (err) {
                // Silent — if this fails the draft will just be overwritten
                // on the next autosave cycle
            }
        });
    }

});
