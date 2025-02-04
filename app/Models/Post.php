<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Post
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string $image
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin IdeHelperPost
 */
class Post extends Model
{
  protected $fillable = ['title', 'slug', 'description', 'user_id', 'category_id', 'image'];

  // Relation entre Post et User
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, foreignKey: 'user_id');
  }

  // Relation entre Post et Comment
  public function comments(): HasMany
  {
    return $this->hasMany(Comment::class);
  }

  // Relation entre Post et Like
  public function likes(): HasMany
  {
    return $this->hasMany(Like::class);
  }

  // Relation entre Post et Category
  public function category(): BelongsTo
  {
    return $this->belongsTo(Category::class);
  }
}
