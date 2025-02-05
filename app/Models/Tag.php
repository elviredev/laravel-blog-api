<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperTag
 */
class Tag extends Model
{
  protected $fillable = ['name'];

  // Relation Tag/Post: un tag peut être associé à plusieurs posts
  public function posts(): BelongsToMany
  {
    return $this->belongsToMany(Post::class)->withTimestamps();
  }
}
