<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class RecipePhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "recipe_id",
        "photo",
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($recipePhoto) {
            if ($recipePhoto->photo) {
                Storage::delete($recipePhoto->photo);
            }
        });

        static::updating(function ($recipePhoto) {
            if ($recipePhoto->isDirty('photo') && $recipePhoto->getOriginal('photo')) {
                Storage::delete($recipePhoto->getOriginal('photo'));
            }
        });
    }
}
