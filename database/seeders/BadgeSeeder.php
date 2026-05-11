<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            /*
            | Writing badges — triggered by posts_published count
            */
            [
                'name'           => 'First Post',
                'description'    => 'Published your first post on Synthia.',
                'icon'           => '✍️',
                'criteria_type'  => 'posts_published',
                'criteria_value' => 1,
                'color'          => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400',
            ],
            [
                'name'           => 'Regular Writer',
                'description'    => 'Published 5 posts.',
                'icon'           => '📝',
                'criteria_type'  => 'posts_published',
                'criteria_value' => 5,
                'color'          => 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400',
            ],
            [
                'name'           => 'Prolific Author',
                'description'    => 'Published 10 posts.',
                'icon'           => '📚',
                'criteria_type'  => 'posts_published',
                'criteria_value' => 10,
                'color'          => 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-400',
            ],
            /*
            | Engagement badges — triggered by claps_received count
            */
            [
                'name'           => 'Crowd Pleaser',
                'description'    => 'Received 50 claps across all posts.',
                'icon'           => '👏',
                'criteria_type'  => 'claps_received',
                'criteria_value' => 50,
                'color'          => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-400',
            ],
            [
                'name'           => 'Community Favourite',
                'description'    => 'Received 200 claps across all posts.',
                'icon'           => '🌟',
                'criteria_type'  => 'claps_received',
                'criteria_value' => 200,
                'color'          => 'bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-400',
            ],
            /*
            | Special badges — manually awarded only
            */
            [
                'name'           => 'Verified Author',
                'description'    => 'Officially verified author on Synthia.',
                'icon'           => '✅',
                'criteria_type'  => null,
                'criteria_value' => null,
                'color'          => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400',
            ],
            [
                'name'           => 'Staff Pick',
                'description'    => 'Recognized by the Synthia editorial team.',
                'icon'           => '⭐',
                'criteria_type'  => null,
                'criteria_value' => null,
                'color'          => 'bg-rose-100 dark:bg-rose-900 text-rose-700 dark:text-rose-400',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['slug' => Str::slug($badge['name'])],
                array_merge($badge, ['slug' => Str::slug($badge['name'])])
            );
        }

        $this->command->info('Badges seeded: ' . count($badges) . ' badges.');
    }
}
