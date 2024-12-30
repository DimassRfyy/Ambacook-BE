<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class RecipeAuthor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "name",
        "photo",
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($author) {
            if ($author->photo) {
                Storage::delete($author->photo);
            }
        });

        static::updating(function ($author) {
            if ($author->isDirty('photo') && $author->getOriginal('photo')) {
                Storage::delete($author->getOriginal('photo'));
            }
        });
    }

    public function recipe(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }
}
