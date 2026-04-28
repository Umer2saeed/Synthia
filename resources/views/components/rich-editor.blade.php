{{--
|--------------------------------------------------------------------------
| Rich Text Editor Component
|--------------------------------------------------------------------------
| Usage:
|   <x-rich-editor name="content" :value="old('content', $post->content ?? '')" />
|
| Props:
|   name  → the HTML input name (submitted to the server)
|   value → initial HTML content (empty for create, existing for edit)
|   id    → optional custom ID for the hidden input
--}}

@props([
    'name'  => 'content',
    'value' => '',
    'id'    => 'tiptap-content',
])

{{--
| data-tiptap-editor marks this div as an editor container.
| The JavaScript looks for this attribute on DOMContentLoaded.
--}}
<div data-tiptap-editor class="rich-editor-wrapper">

    {{--
    |----------------------------------------------------------------------
    | HIDDEN INPUT — what actually gets submitted with the form
    |----------------------------------------------------------------------
    | data-editor-input marks this as the target for editor HTML output.
    | The JavaScript updates this value on every editor change.
    | The server receives this value as $request->{{ $name }}
    --}}
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $id }}"
        data-editor-input
        value="{{ $value }}"
    >

    {{--
    |----------------------------------------------------------------------
    | TOOLBAR
    |----------------------------------------------------------------------
    | Each button has data-action which the JavaScript reads.
    | Buttons get 'is-active' class when their format is active in editor.
    --}}
    <div data-editor-toolbar
         class="flex flex-wrap items-center gap-0.5 p-2
                bg-gray-50 dark:bg-gray-800
                border border-gray-200 dark:border-gray-700
                rounded-t-xl">

        {{-- Text Formatting Group --}}
        <div class="flex items-center gap-0.5 pr-2 mr-1
                    border-r border-gray-200 dark:border-gray-700">

            <x-rich-editor-btn action="bold" title="Bold (Ctrl+B)">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/>
                </svg>
            </x-rich-editor-btn>

            <x-rich-editor-btn action="italic" title="Italic (Ctrl+I)">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/>
                </svg>
            </x-rich-editor-btn>

            <x-rich-editor-btn action="strike" title="Strikethrough">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6.85 7.08C6.85 4.37 9.45 3 12.24 3c1.64 0 3 .49 3.9 1.28.77.65 1.46 1.73 1.46 3.24h-3.01c0-.31-.05-.59-.15-.85-.29-.86-1.2-1.28-2.25-1.28-1.86 0-2.34.92-2.34 1.67 0 .11.02.21.04.31H6.85v-.29zm8.35 5.92H5v2h14v-2h-3.8zm-1.04 2H13.6l.3.15c.65.38 1.06.86 1.06 1.59 0 1.39-1.56 1.86-2.96 1.86-1.64 0-2.52-.49-3.17-1.22-.36-.41-.63-.96-.76-1.61H5.01c.13.96.54 1.99 1.13 2.73C7.2 20.6 9.17 21 11 21c3.33 0 5.96-1.57 5.96-4.22 0-.79-.2-1.47-.56-2.07l-.24-.71z"/>
                </svg>
            </x-rich-editor-btn>

            <x-rich-editor-btn action="code" title="Inline Code">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z"/>
                </svg>
            </x-rich-editor-btn>

        </div>

        {{-- Heading Group --}}
        <div class="flex items-center gap-0.5 pr-2 mr-1
                    border-r border-gray-200 dark:border-gray-700">

            <x-rich-editor-btn action="h2" title="Heading 2">
                <span class="text-xs font-bold leading-none">H2</span>
            </x-rich-editor-btn>

            <x-rich-editor-btn action="h3" title="Heading 3">
                <span class="text-xs font-bold leading-none">H3</span>
            </x-rich-editor-btn>

            <x-rich-editor-btn action="h4" title="Heading 4">
                <span class="text-xs font-bold leading-none">H4</span>
            </x-rich-editor-btn>

        </div>

        {{-- Block Format Group --}}
        <div class="flex items-center gap-0.5 pr-2 mr-1
                    border-r border-gray-200 dark:border-gray-700">

            <x-rich-editor-btn action="blockquote" title="Blockquote">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/>
                </svg>
            </x-rich-editor-btn>

            <x-rich-editor-btn action="code-block" title="Code Block">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 3a2 2 0 00-2 2v4a2 2 0 01-2 2H3v2h1a2 2 0 012 2v4a2 2 0 002 2h2v-2H8v-5a2 2 0 00-2-2 2 2 0 002-2V5h2V3H8zm8 0a2 2 0 012 2v4a2 2 0 002 2h1v2h-1a2 2 0 00-2 2v4a2 2 0 01-2 2h-2v-2h2v-5a2 2 0 012-2 2 2 0 01-2-2V5h-2V3h2z"/>
                </svg>
            </x-rich-editor-btn>

            <x-rich-editor-btn action="bullet-list" title="Bullet List">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z"/>
                </svg>
            </x-rich-editor-btn>

            <x-rich-editor-btn action="ordered-list" title="Numbered List">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/>
                </svg>
            </x-rich-editor-btn>

            <x-rich-editor-btn action="hr" title="Horizontal Rule">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 13H5v-2h14v2z"/>
                </svg>
            </x-rich-editor-btn>

        </div>

        {{-- Link Group --}}
        <div class="flex items-center gap-0.5 pr-2 mr-1
                    border-r border-gray-200 dark:border-gray-700">

            <x-rich-editor-btn action="link" title="Insert Link">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/>
                </svg>
            </x-rich-editor-btn>

            <x-rich-editor-btn action="unlink" title="Remove Link">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17 7h-4v2h4c1.65 0 3 1.35 3 3s-1.35 3-3 3h-4v2h4c2.76 0 5-2.24 5-5s-2.24-5-5-5zm-6 8H7c-1.65 0-3-1.35-3-3s1.35-3 3-3h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-2zm-3-4h8v2H8v-2zm7.08-6.49l-1.41 1.41 1.41 1.41 1.41-1.41-1.41-1.41zM4.92 17.08l1.41-1.41-1.41-1.41-1.41 1.41 1.41 1.41z"/>
                </svg>
            </x-rich-editor-btn>

        </div>

        {{-- History Group --}}
        <div class="flex items-center gap-0.5">

            <x-rich-editor-btn action="undo" title="Undo (Ctrl+Z)">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"/>
                </svg>
            </x-rich-editor-btn>

            <x-rich-editor-btn action="redo" title="Redo (Ctrl+Shift+Z)">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18.4 10.6C16.55 8.99 14.15 8 11.5 8c-4.65 0-8.58 3.03-9.96 7.22L3.9 16c1.05-3.19 4.05-5.5 7.6-5.5 1.95 0 3.73.72 5.12 1.88L13 16h9V7l-3.6 3.6z"/>
                </svg>
            </x-rich-editor-btn>

        </div>

    </div>
    {{-- End Toolbar --}}

    {{--
    |----------------------------------------------------------------------
    | EDITOR CONTENT AREA
    |----------------------------------------------------------------------
    | data-editor-content marks where TipTap renders the contenteditable div.
    | TipTap creates a div inside this element automatically.
    | The prose classes apply Tailwind Typography styling to the content.
    --}}
    <div
        data-editor-content
        class="min-h-[400px] p-4
               bg-white dark:bg-gray-900
               border-x border-b border-gray-200 dark:border-gray-700
               rounded-b-xl
               prose prose-indigo dark:prose-invert
               max-w-none
               focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-opacity-50">
    </div>

    {{--
    |----------------------------------------------------------------------
    | FOOTER — word count and character count
    |----------------------------------------------------------------------
    --}}
    <div class="flex items-center justify-between mt-1.5 text-xs text-gray-400">
        <span>
            <span data-word-count>0</span> words ·
            <span data-char-count>0</span> characters
        </span>
        <span class="text-gray-300 dark:text-gray-600 text-xs">
            Ctrl+B Bold · Ctrl+I Italic · Ctrl+Z Undo
        </span>
    </div>

</div>
{{-- End data-tiptap-editor --}}
