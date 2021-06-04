<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "artwork_url",
        "rss_url",
        "description",
        "language",
        "website_url",
    ];

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }
}
