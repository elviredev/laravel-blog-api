<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperCategory
 */
class Category extends Model
{
    protected $fillable = ['name'];

    // Relation entre Category et Post
    public function posts(): HasMany
    {
      return $this->hasMany(Post::class);
    }
}
