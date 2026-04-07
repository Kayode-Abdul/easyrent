<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $table = 'blog';

    protected $fillable = [
        'topic',
        'topic_url',
        'content',
        'excerpt',
        'cover_photo',
        'author',
        'published',
        'date',
        'hide'
    ];

    protected $casts = [
        'published' => 'boolean',
        'date' => 'datetime',
    ];

    // Automatically generate URL slug from topic
    public static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            if (empty($blog->topic_url)) {
                $blog->topic_url = Str::slug($blog->topic);
                
                // Ensure uniqueness
                $count = static::where('topic_url', 'like', $blog->topic_url . '%')->count();
                if ($count > 0) {
                    $blog->topic_url = $blog->topic_url . '-' . ($count + 1);
                }
            }
            
            if (empty($blog->excerpt) && !empty($blog->content)) {
                $blog->excerpt = Str::limit(strip_tags($blog->content), 150);
            }
        });
    }

    // Scope for published posts
    public function scopePublished($query)
    {
        return $query->where('published', true)->whereNull('hide');
    }

    // Scope for recent posts
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('date', 'desc')->limit($limit);
    }
}
