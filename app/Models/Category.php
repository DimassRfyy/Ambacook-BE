<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "name",
        "slug",
        "icon",
    ];


    public function setNameAttribute($value)
    {
        $this->attributes["name"] = $value;
        $this->attributes["slug"] = Str::slug($value);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {
            if ($category->icon) {
                Storage::delete($category->icon);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('icon') && $category->getOriginal('icon')) {
                Storage::delete($category->getOriginal('icon'));
            }
        });
    }
}
