<?php
/*
| Removes the old inline flash blocks, for example
|
|     @if(session('success'))
|         <div class="mb-4 px-4 py-3 bg-green-50 ...">{{ session('success') }}</div>
|     @endif
|
| <x-toast /> already shows every flash message, so these blocks only cause a
| second, non-dismissable copy.
|
|   php tools/remove-flash-blocks.php            DRY RUN: lists what it would remove, changes nothing
|   php tools/remove-flash-blocks.php --write    removes them, saving a .bak copy of every changed file
|
| Only simple blocks are removed (no nested @if/@else/@foreach/@php/<script> inside).
| Anything else is listed under "REVIEW BY HAND" with its line number.
*/

$write = in_array('--write', $argv ?? [], true);
$root  = getcwd() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';

if (! is_dir($root)) {
    fwrite(STDERR, "Run this from the Laravel project root (resources/views not found).\n");
    exit(1);
}

$keys     = 'success|error|warning|info';
$patterns = [
    '/[ \t]*@if\s*\(\s*session\(\s*[\'"](?:' . $keys . ')[\'"]\s*\)\s*\)(.*?)@endif[ \t]*\R?/s',
    '/[ \t]*@session\(\s*[\'"](?:' . $keys . ')[\'"]\s*\)(.*?)@endsession[ \t]*\R?/s',
];

$removed = 0;
$files   = 0;
$review  = [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

foreach ($iterator as $file) {
    $path = $file->getPathname();
    $norm = str_replace('\\', '/', $path);

    if (! str_ends_with($path, '.blade.php')) continue;
    if (str_ends_with($norm, 'components/toast.blade.php')) continue;   // never touch the toast itself

    $rel      = str_replace(str_replace('\\', '/', getcwd()) . '/', '', $norm);
    $original = file_get_contents($path);
    $updated  = $original;

    foreach ($patterns as $pattern) {
        $updated = preg_replace_callback($pattern, function ($m) use ($rel, $original, &$removed) {
            if (preg_match('/@if|@else|@foreach|@php|<script/i', $m[1])) {
                return $m[0];                                           // too complex: leave it
            }
            $line = substr_count(substr($original, 0, (int) strpos($original, $m[0])), "\n") + 1;
            echo "  remove  {$rel}:{$line}\n";
            $removed++;
            return '';
        }, $updated);
    }

    if ($updated !== $original) {
        $files++;
        if ($write) {
            copy($path, $path . '.bak');
            file_put_contents($path, $updated);
        }
    }

    // Anything that still reads a flash key needs a human look.
    foreach (preg_split('/\R/', $updated) as $i => $text) {
        if (preg_match('/session\(\s*[\'"](?:' . $keys . ')[\'"]\s*\)/', $text)) {
            $review[] = sprintf('%s:%d   %s', $rel, $i + 1, trim($text));
        }
    }
}

echo "\n" . ($write ? 'REMOVED' : 'WOULD REMOVE') . ": {$removed} block(s) in {$files} file(s)\n";

if ($review) {
    echo "\nREVIEW BY HAND (still reads a flash message):\n";
    foreach ($review as $line) echo "  - {$line}\n";
}

echo $write
    ? "\nBackups saved as *.blade.php.bak. After checking the pages: find resources/views -name \"*.bak\" -delete\n"
    : "\nDry run only. Nothing was changed. Run again with --write to apply.\n";
