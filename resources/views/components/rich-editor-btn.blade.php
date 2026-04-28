{{--
| Reusable toolbar button for the rich text editor.
| Usage: <x-rich-editor-btn action="bold" title="Bold">SVG icon</x-rich-editor-btn>
--}}

@props([
    'action' => '',
    'title'  => '',
])

<button
    type="button"
    data-action="{{ $action }}"
    title="{{ $title }}"
    class="editor-toolbar-btn
           flex items-center justify-center
           w-8 h-8 rounded-lg
           text-gray-600 dark:text-gray-400
           hover:bg-gray-200 dark:hover:bg-gray-700
           hover:text-gray-900 dark:hover:text-white
           transition-colors duration-150
           [&.is-active]:bg-indigo-100 [&.is-active]:text-indigo-700
           dark:[&.is-active]:bg-indigo-900 dark:[&.is-active]:text-indigo-300"
>
    {{ $slot }}
</button>
