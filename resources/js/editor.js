/*
|--------------------------------------------------------------------------
| TipTap Rich Text Editor for Synthia
|--------------------------------------------------------------------------
| This file initializes TipTap on any element with data-tiptap-editor.
| It is imported in app.js and runs automatically on page load.
|
| HOW IT CONNECTS TO THE FORM:
|   1. TipTap renders in a div (not a textarea)
|   2. A hidden <input> field holds the actual HTML content
|   3. When TipTap content changes, we update the hidden input
|   4. When the form submits, the hidden input value is sent to the server
|   5. The server receives HTML in $request->content
*/

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import CharacterCount from '@tiptap/extension-character-count';

/*
|--------------------------------------------------------------------------
| initEditor() — Initialize TipTap on a given container element
|--------------------------------------------------------------------------
| @param container  The wrapper div element (has data-tiptap-editor)
| @param hiddenInput The hidden <input> that stores the HTML for form submit
| @param initialContent  Existing content (for edit post form)
*/
function initEditor(container, hiddenInput, initialContent) {
    /*
    | The editor renders inside this div.
    | TipTap creates a contenteditable div here automatically.
    */
    const editorElement = container.querySelector('[data-editor-content]');

    if (!editorElement) {
        console.error('TipTap: missing [data-editor-content] element');
        return;
    }

    /*
    |----------------------------------------------------------------------
    | Create the TipTap Editor instance
    |----------------------------------------------------------------------
    */
    const editor = new Editor({
        /*
        | element: where TipTap renders the contenteditable div
        */
        element: editorElement,

        /*
        |------------------------------------------------------------------
        | Extensions — what features the editor supports
        |------------------------------------------------------------------
        | StarterKit includes: paragraphs, headings (h1-h6), bold, italic,
        | strike, code, blockquote, lists (ul/ol), horizontal rule,
        | undo/redo history
        |
        | We configure StarterKit to disable h1 (reserved for post title)
        | and include only h2, h3, h4 for content structure.
        */
        extensions: [
            StarterKit.configure({
                heading: {
                    levels: [2, 3, 4], // h1 is the post title, not for content
                },
                /*
                | CodeBlock is included in StarterKit but we disable it here
                | because we want the plain code block, not the highlighted one.
                | If you want syntax highlighting, enable the CodeBlockLowlight
                | extension instead (requires additional setup).
                */
                codeBlock: {
                    HTMLAttributes: {
                        class: 'code-block',
                    },
                },
            }),

            /*
            | Link extension allows inserting and editing hyperlinks.
            | openOnClick: false means clicking a link selects it for editing
            |   rather than following it (you are in an editor, not a browser).
            | autolink: true automatically detects URLs as you type and converts.
            */
            Link.configure({
                openOnClick:   false,
                autolink:      true,
                defaultProtocol: 'https',
                HTMLAttributes: {
                    rel:    'noopener noreferrer',
                    target: '_blank',
                },
            }),

            /*
            | Placeholder shows a hint text when the editor is empty.
            | Disappears as soon as the user starts typing.
            */
            Placeholder.configure({
                placeholder: 'Start writing your post content here...',
            }),

            /*
            | CharacterCount tracks how many characters/words are in the editor.
            | We use this to show a live word count below the editor.
            */
            CharacterCount,
        ],

        /*
        | Initial content for the editor.
        | On create post: empty string
        | On edit post: the existing HTML content of the post
        */
        content: initialContent || '',

        /*
        |------------------------------------------------------------------
        | onUpdate — fires every time the editor content changes
        |------------------------------------------------------------------
        | We sync the hidden input with the editor HTML on every change.
        | This ensures the form always submits the latest content.
        */
        onUpdate({ editor }) {
            /*
            | getHTML() returns the current editor content as an HTML string.
            | Example: "<h2>My Heading</h2><p>Some <strong>bold</strong> text</p>"
            |
            | We set this as the hidden input value so it gets submitted.
            */
            hiddenInput.value = editor.getHTML();

            /*
            | Update the word count display if it exists on the page.
            */
            updateWordCount(container, editor);
        },

        /*
        | onCreate fires when the editor first loads.
        | We sync the hidden input immediately with the initial content.
        */
        onCreate({ editor }) {
            hiddenInput.value = editor.getHTML();
            updateWordCount(container, editor);
        },
    });

    /*
    |----------------------------------------------------------------------
    | Attach toolbar button event listeners
    |----------------------------------------------------------------------
    | Each toolbar button has a data-action attribute.
    | We read that attribute and call the corresponding TipTap command.
    */
    const toolbar = container.querySelector('[data-editor-toolbar]');

    if (toolbar) {
        toolbar.addEventListener('click', function (e) {
            const button = e.target.closest('[data-action]');
            if (!button) return;

            e.preventDefault();

            const action = button.dataset.action;
            handleToolbarAction(editor, action, button);

            /*
            | After executing a command, focus back to the editor
            | so the user can keep typing without clicking the editor.
            */
            editor.commands.focus();
        });

        /*
        | Update toolbar button active states when cursor moves.
        | Example: when cursor is inside bold text, the Bold button
        | should appear highlighted/active.
        */
        editor.on('selectionUpdate', () => updateToolbarState(editor, toolbar));
        editor.on('transaction',     () => updateToolbarState(editor, toolbar));
    }

    return editor;
}

/*
|--------------------------------------------------------------------------
| handleToolbarAction() — Execute TipTap commands from toolbar buttons
|--------------------------------------------------------------------------
| Each data-action value maps to a TipTap chain command.
*/
function handleToolbarAction(editor, action, button) {
    const chain = editor.chain().focus();

    switch (action) {
        case 'bold':        chain.toggleBold().run();              break;
        case 'italic':      chain.toggleItalic().run();            break;
        case 'strike':      chain.toggleStrike().run();            break;
        case 'code':        chain.toggleCode().run();              break;
        case 'h2':          chain.toggleHeading({ level: 2 }).run(); break;
        case 'h3':          chain.toggleHeading({ level: 3 }).run(); break;
        case 'h4':          chain.toggleHeading({ level: 4 }).run(); break;
        case 'blockquote':  chain.toggleBlockquote().run();        break;
        case 'code-block':  chain.toggleCodeBlock().run();         break;
        case 'bullet-list': chain.toggleBulletList().run();        break;
        case 'ordered-list':chain.toggleOrderedList().run();       break;
        case 'hr':          chain.setHorizontalRule().run();       break;
        case 'undo':        chain.undo().run();                    break;
        case 'redo':        chain.redo().run();                    break;

        case 'link':
            /*
            | Link requires a URL prompt.
            | If text is selected and a URL is provided, wrap it in <a>.
            | If no URL provided (user pressed cancel), remove the link.
            */
            const previousUrl = editor.getAttributes('link').href;
            const url         = window.prompt('Enter URL:', previousUrl || 'https://');

            if (url === null) break; // user cancelled

            if (url === '') {
                chain.extendMarkRange('link').unsetLink().run();
            } else {
                chain.extendMarkRange('link').setLink({ href: url }).run();
            }
            break;

        case 'unlink':
            chain.unsetLink().run();
            break;
    }
}

/*
|--------------------------------------------------------------------------
| updateToolbarState() — Highlight active toolbar buttons
|--------------------------------------------------------------------------
| When the cursor is inside bold text, the Bold button should look active.
| We add/remove the 'is-active' CSS class based on TipTap's isActive().
*/
function updateToolbarState(editor, toolbar) {
    const buttons = toolbar.querySelectorAll('[data-action]');

    buttons.forEach(button => {
        const action    = button.dataset.action;
        let   isActive  = false;

        switch (action) {
            case 'bold':         isActive = editor.isActive('bold');                    break;
            case 'italic':       isActive = editor.isActive('italic');                  break;
            case 'strike':       isActive = editor.isActive('strike');                  break;
            case 'code':         isActive = editor.isActive('code');                    break;
            case 'h2':           isActive = editor.isActive('heading', { level: 2 });   break;
            case 'h3':           isActive = editor.isActive('heading', { level: 3 });   break;
            case 'h4':           isActive = editor.isActive('heading', { level: 4 });   break;
            case 'blockquote':   isActive = editor.isActive('blockquote');              break;
            case 'code-block':   isActive = editor.isActive('codeBlock');               break;
            case 'bullet-list':  isActive = editor.isActive('bulletList');              break;
            case 'ordered-list': isActive = editor.isActive('orderedList');             break;
            case 'link':         isActive = editor.isActive('link');                    break;
        }

        /*
        | Toggle the is-active class.
        | Our CSS styles .is-active buttons differently (highlighted).
        */
        button.classList.toggle('is-active', isActive);
    });
}

/*
|--------------------------------------------------------------------------
| updateWordCount() — Display live word and character counts
|--------------------------------------------------------------------------
*/
function updateWordCount(container, editor) {
    const wordCountEl = container.querySelector('[data-word-count]');
    const charCountEl = container.querySelector('[data-char-count]');

    if (wordCountEl) {
        wordCountEl.textContent = editor.storage.characterCount.words();
    }
    if (charCountEl) {
        charCountEl.textContent = editor.storage.characterCount.characters();
    }
}

/*
|--------------------------------------------------------------------------
| Auto-initialize all editors on the page
|--------------------------------------------------------------------------
| We look for every element with [data-tiptap-editor] on the page
| and initialize a TipTap instance for each one.
| This allows multiple editors on the same page if needed.
*/
document.addEventListener('DOMContentLoaded', function () {
    const editorContainers = document.querySelectorAll('[data-tiptap-editor]');

    editorContainers.forEach(function (container) {
        /*
        | The hidden input that the form will submit.
        | Located by [data-editor-input] attribute.
        */
        const hiddenInput = container.querySelector('[data-editor-input]');

        if (!hiddenInput) {
            console.error('TipTap: missing [data-editor-input] inside', container);
            return;
        }

        /*
        | Read the initial content from the hidden input's value.
        | On create post: empty string.
        | On edit post: the existing HTML content (set by Blade).
        */
        const initialContent = hiddenInput.value || '';

        initEditor(container, hiddenInput, initialContent);
    });
});
