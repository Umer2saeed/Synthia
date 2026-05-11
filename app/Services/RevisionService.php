<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostRevision;

class RevisionService
{
    // Maximum revisions kept per post
    const MAX_REVISIONS = 10;

    /*
    | Snapshot the CURRENT state of a post before it gets updated.
    | Call this BEFORE saving new content — not after.
    */
    public function snapshot(Post $post): PostRevision
    {
        $revision = PostRevision::create([
            'post_id'    => $post->id,
            'user_id'    => auth()->id(),
            'title'      => $post->title,
            'content'    => $post->content,
            'created_at' => now(),
        ]);

        $this->pruneOldRevisions($post->id);

        return $revision;
    }

    /*
    | Delete revisions beyond the MAX_REVISIONS limit.
    | Keeps the newest N, deletes the rest.
    */
    private function pruneOldRevisions(int $postId): void
    {
        $keepIds = PostRevision::where('post_id', $postId)
            ->orderByDesc('created_at')
            ->limit(self::MAX_REVISIONS)
            ->pluck('id');

        PostRevision::where('post_id', $postId)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }

    /*
    | Build a simple word-level diff between two text strings.
    | Returns an array of segments: each segment has 'text' and 'type'
    | type = 'equal' | 'insert' | 'delete'
    */
    public function diff(string $old, string $new): array
    {
        // Strip HTML for readable diff
        $oldWords = preg_split('/(\s+)/', strip_tags($old), -1, PREG_SPLIT_DELIM_CAPTURE);
        $newWords = preg_split('/(\s+)/', strip_tags($new), -1, PREG_SPLIT_DELIM_CAPTURE);

        return $this->computeDiff($oldWords, $newWords);
    }

    /*
    | Myers diff algorithm — simplified for word-level comparison.
    | Returns array of ['type' => 'equal|insert|delete', 'text' => '...']
    */
    private function computeDiff(array $old, array $new): array
    {
        $oldCount = count($old);
        $newCount = count($new);
        $matrix   = [];

        // Build LCS (Longest Common Subsequence) matrix
        for ($i = 0; $i <= $oldCount; $i++) {
            for ($j = 0; $j <= $newCount; $j++) {
                if ($i === 0 || $j === 0) {
                    $matrix[$i][$j] = 0;
                } elseif ($old[$i - 1] === $new[$j - 1]) {
                    $matrix[$i][$j] = $matrix[$i - 1][$j - 1] + 1;
                } else {
                    $matrix[$i][$j] = max($matrix[$i - 1][$j], $matrix[$i][$j - 1]);
                }
            }
        }

        // Backtrack to build diff segments
        $diff = [];
        $i    = $oldCount;
        $j    = $newCount;

        while ($i > 0 || $j > 0) {
            if ($i > 0 && $j > 0 && $old[$i - 1] === $new[$j - 1]) {
                array_unshift($diff, ['type' => 'equal', 'text' => $old[$i - 1]]);
                $i--;
                $j--;
            } elseif ($j > 0 && ($i === 0 || $matrix[$i][$j - 1] >= $matrix[$i - 1][$j])) {
                array_unshift($diff, ['type' => 'insert', 'text' => $new[$j - 1]]);
                $j--;
            } else {
                array_unshift($diff, ['type' => 'delete', 'text' => $old[$i - 1]]);
                $i--;
            }
        }

        // Merge consecutive segments of same type for cleaner output
        return $this->mergeSegments($diff);
    }

    private function mergeSegments(array $diff): array
    {
        if (empty($diff)) {
            return [];
        }

        $merged  = [];
        $current = $diff[0];

        for ($i = 1; $i < count($diff); $i++) {
            if ($diff[$i]['type'] === $current['type']) {
                $current['text'] .= $diff[$i]['text'];
            } else {
                $merged[]  = $current;
                $current   = $diff[$i];
            }
        }

        $merged[] = $current;

        return $merged;
    }
}
