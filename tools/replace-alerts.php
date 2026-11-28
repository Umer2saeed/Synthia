<?php
/*
| Replaces browser alert(...) pop-ups with toast notifications.
|
|   php tools/replace-alerts.php            DRY RUN: shows every change, edits nothing
|   php tools/replace-alerts.php --write    applies it, saving a .bak copy of each changed file
|
| Only the function name changes: alert(data.message || 'x')  ->  toast.error(data.message || 'x')
| so variables and multi-line calls keep working. confirm() is never touched.
*/

$write   = in_array('--write', $argv ?? [], true);
$roots   = ['resources/views', 'resources/js'];
$changes = 0;
$files   = 0;

foreach ($roots as $root) {
    if (! is_dir($root)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        $path = $file->getPathname();
        $norm = str_replace('\\', '/', $path);

        if (! preg_match('/\.(blade\.php|js)$/', $path)) continue;
        if (str_ends_with($norm, 'components/toast.blade.php')) continue;

        $original = file_get_contents($path);
        $parts    = preg_split('/(\r\n|\n|\r)/', $original, -1, PREG_SPLIT_DELIM_CAPTURE);
        $touched  = false;

        for ($i = 0; $i < count($parts); $i += 2) {
            $line = $parts[$i];

            if (stripos($line, 'role="alert"') !== false) continue;                  // HTML attribute, not a pop-up
            if (strpos($line, 'synthiaToast') !== false || strpos($line, 'showToast') !== false) continue; // intentional fallback
            if (preg_match('#^\s*(//|/\*|\*|\{\{--)#', $line)) continue;             // comments

            $type = preg_match('/select|not ready/i', $line) ? 'warning' : 'error';

            $new = preg_replace('/(?<![\w.$])alert\s*\(/', "toast.{$type}(", $line, -1, $count);

            if ($count > 0) {
                $changes += $count;
                $touched  = true;
                $parts[$i] = $new;
                printf("  %s:%d\n      - %s\n      + %s\n", $norm, intdiv($i, 2) + 1, trim($line), trim($new));
            }
        }

        if ($touched) {
            $files++;
            if ($write) {
                copy($path, $path . '.bak');
                file_put_contents($path, implode('', $parts));
            }
        }
    }
}

echo "\n" . ($write ? 'CHANGED' : 'WOULD CHANGE') . ": {$changes} alert() call(s) in {$files} file(s)\n";
echo $write
    ? "Backups saved as *.bak. After testing: find resources -name \"*.bak\" -delete\n"
    : "Dry run only. Nothing was changed. Run again with --write to apply.\n";
