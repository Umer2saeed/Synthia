<?php

namespace App\Console\Commands;

use App\Services\SanitizationService;
use Illuminate\Console\Command;

class DiagnoseSanitization extends Command
{
    protected $signature   = 'diagnose:sanitization';
    protected $description = 'Test all sanitization methods step by step';

    public function handle(): int
    {
        $this->info('=== SANITIZATION DIAGNOSIS ===');
        $this->newLine();

        // Step 1: Check installation
        $this->checkInstallation();

        // Step 2: Test each method
        $service = app(SanitizationService::class);
        $this->testCleanText($service);
        $this->testCleanRichText($service);
        $this->testCleanComment($service);
        $this->testCleanSearch($service);
        $this->testCleanUsername($service);
        $this->testCleanUrl($service);

        return Command::SUCCESS;
    }

    private function checkInstallation(): void
    {
        $this->line('--- Installation Check ---');

        $checks = [
            'mews/purifier package installed' => class_exists('Mews\Purifier\Purifier'),
            'clean() helper exists'            => function_exists('clean'),
            'purifier config exists'           => file_exists(config_path('purifier.php')),
            'purifier cache dir exists'        => is_dir(storage_path('app/purifier')),
            'purifier cache dir writable'      => is_writable(storage_path('app/purifier')),
        ];

        foreach ($checks as $label => $result) {
            $this->line('  ' . ($result ? '✓' : '✗') . ' ' . $label);
        }

        // Test purifier instantiation
        try {
            app('purifier');
            $this->line('  ✓ Purifier instantiates correctly');
        } catch (\Throwable $e) {
            $this->line('  ✗ Purifier failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    private function testCleanText(SanitizationService $s): void
    {
        $this->line('--- cleanText() Tests ---');

        $tests = [
            [
                'input'    => '<b>Hello</b> <script>alert(1)</script> World',
                'expected' => 'Hello World',
                'label'    => 'Strips HTML tags',
            ],
            [
                'input'    => '  Hello    World  ',
                'expected' => 'Hello World',
                'label'    => 'Normalizes whitespace',
            ],
            [
                'input'    => '<h1>Category Name</h1>',
                'expected' => 'Category Name',
                'label'    => 'Strips heading tags',
            ],
            [
                'input'    => null,
                'expected' => '',
                'label'    => 'Handles null input',
            ],
        ];

        foreach ($tests as $test) {
            $result = $s->cleanText($test['input']);
            $pass   = $result === $test['expected'];
            $icon   = $pass ? '✓' : '✗';
            $this->line("  {$icon} {$test['label']}");
            if (!$pass) {
                $this->line("    Expected: " . json_encode($test['expected']));
                $this->line("    Got:      " . json_encode($result));
            }
        }

        $this->newLine();
    }

    private function testCleanRichText(SanitizationService $s): void
    {
        $this->line('--- cleanRichText() Tests ---');

        $tests = [
            [
                'input'    => '<p>Hello <script>steal()</script> World</p>',
                'label'    => 'Removes script tags',
                'check'    => fn($r) => !str_contains($r, 'script') && str_contains($r, 'Hello'),
            ],
            [
                'input'    => '<p onclick="steal()">Click</p>',
                'label'    => 'Removes event attributes',
                'check'    => fn($r) => !str_contains($r, 'onclick'),
            ],
            [
                'input'    => '<a href="javascript:steal()">Bad</a>',
                'label'    => 'Removes javascript: href',
                'check'    => fn($r) => !str_contains($r, 'javascript'),
            ],
            [
                'input'    => '<p>Normal text</p><b>Bold</b>',
                'label'    => 'Preserves safe content',
                'check'    => fn($r) => str_contains($r, 'Normal text') && trim($r) !== '',
            ],
            [
                'input'    => '<h2>Heading</h2><p>Para</p><ul><li>Item</li></ul>',
                'label'    => 'Preserves headings, paragraphs, lists',
                'check'    => fn($r) => str_contains($r, 'Heading') && str_contains($r, 'Item'),
            ],
        ];

        foreach ($tests as $test) {
            $result = $s->cleanRichText($test['input']);
            $pass   = ($test['check'])($result);
            $icon   = $pass ? '✓' : '✗';
            $this->line("  {$icon} {$test['label']}");
            if (!$pass) {
                $this->line("    Input:  " . \Str::limit($test['input'], 60));
                $this->line("    Output: " . \Str::limit($result, 60));
            }
        }

        $this->newLine();
    }

    private function testCleanComment(SanitizationService $s): void
    {
        $this->line('--- cleanComment() Tests ---');

        $tests = [
            [
                'input' => '<b>Great</b> post! <script>steal()</script>',
                'label' => 'Keeps bold, removes script',
                'check' => fn($r) => str_contains($r, '<b>Great</b>') && !str_contains($r, 'script'),
            ],
            [
                'input' => '<h1>Big</h1> comment',
                'label' => 'Removes h1 (not allowed in comments)',
                'check' => fn($r) => !str_contains($r, '<h1>') && str_contains($r, 'comment'),
            ],
            [
                'input' => '<a href="https://example.com">Link</a>',
                'label' => 'Preserves safe links',
                'check' => fn($r) => str_contains($r, 'Link'),
            ],
        ];

        foreach ($tests as $test) {
            $result = $s->cleanComment($test['input']);
            $pass   = ($test['check'])($result);
            $icon   = $pass ? '✓' : '✗';
            $this->line("  {$icon} {$test['label']}");
            if (!$pass) {
                $this->line("    Input:  " . $test['input']);
                $this->line("    Output: " . $result);
            }
        }

        $this->newLine();
    }

    private function testCleanSearch(SanitizationService $s): void
    {
        $this->line('--- cleanSearch() Tests ---');

        $tests = [
            [
                'input'    => '<script>alert(1)</script>laravel',
                'expected' => 'laravel',
                'label'    => 'Strips HTML from search',
            ],
            [
                'input'    => '  laravel   tips  ',
                'expected' => 'laravel tips',
                'label'    => 'Normalizes whitespace',
            ],
        ];

        foreach ($tests as $test) {
            $result = $s->cleanSearch($test['input']);
            $pass   = $result === $test['expected'];
            $icon   = $pass ? '✓' : '✗';
            $this->line("  {$icon} {$test['label']}");
            if (!$pass) {
                $this->line("    Expected: " . json_encode($test['expected']));
                $this->line("    Got:      " . json_encode($result));
            }
        }

        $this->newLine();
    }

    private function testCleanUsername(SanitizationService $s): void
    {
        $this->line('--- cleanUsername() Tests ---');

        $tests = [
            [
                'input'    => 'Umer_123',
                'expected' => 'umer_123',
                'label'    => 'Lowercases username',
            ],
            [
                'input'    => 'user<script>alert</script>',
                'expected' => 'useralert',
                'label'    => 'Removes HTML from username',
            ],
            [
                'input'    => 'user name',
                'expected' => 'username',
                'label'    => 'Removes spaces',
            ],
        ];

        foreach ($tests as $test) {
            $result = $s->cleanUsername($test['input']);
            $pass   = $result === $test['expected'];
            $icon   = $pass ? '✓' : '✗';
            $this->line("  {$icon} {$test['label']}");
            if (!$pass) {
                $this->line("    Expected: " . json_encode($test['expected']));
                $this->line("    Got:      " . json_encode($result));
            }
        }

        $this->newLine();
    }

    private function testCleanUrl(SanitizationService $s): void
    {
        $this->line('--- cleanUrl() Tests ---');

        $tests = [
            [
                'input'    => 'javascript:steal()',
                'expected' => '',
                'label'    => 'Blocks javascript: URI',
            ],
            [
                'input'    => 'data:text/html,<script>alert(1)</script>',
                'expected' => '',
                'label'    => 'Blocks data: URI',
            ],
            [
                'input'    => 'https://synthia.test',
                'expected' => 'https://synthia.test',
                'label'    => 'Allows https:// URL',
            ],
            [
                'input'    => '',
                'expected' => '',
                'label'    => 'Handles empty string',
            ],
        ];

        foreach ($tests as $test) {
            $result = $s->cleanUrl($test['input']);
            $pass   = $result === $test['expected'];
            $icon   = $pass ? '✓' : '✗';
            $this->line("  {$icon} {$test['label']}");
            if (!$pass) {
                $this->line("    Expected: " . json_encode($test['expected']));
                $this->line("    Got:      " . json_encode($result));
            }
        }

        $this->newLine();
    }
}
