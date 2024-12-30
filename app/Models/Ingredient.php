<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "name",
        "photo",
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($ingredient) {
            if ($ingredient->photo) {
                Storage::delete($ingredient->photo);
            }
        });

        static::updating(function ($ingredient) {
            if ($ingredient->isDirty('photo') && $ingredient->getOriginal('photo')) {
                Storage::delete($ingredient->getOriginal('photo'));
            }
        });
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }
}
