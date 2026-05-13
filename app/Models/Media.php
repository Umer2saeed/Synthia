<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'uploaded_by',
        'filename',
        'original_name',
        'disk',
        'path',
        'mime_type',
        'size',
        'width',
        'height',
    ];

    protected $casts = [
        'size'   => 'integer',
        'width'  => 'integer',
        'height' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    /*
| Returns posts that reference this media file.
| Checks both post content (as img src) and cover_image column.
*/
    public function getUsedInPostsAttribute(): \Illuminate\Support\Collection
    {
        return Post::where(function ($q) {
            $q->where('content', 'like', '%' . $this->filename . '%')
                ->orWhere('cover_image', 'like', '%' . $this->filename . '%');
        })
            ->select(['id', 'title', 'slug'])
            ->get();
    }
}
