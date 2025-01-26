<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Post
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Post extends Model
{
  protected $fillable = ['title', 'slug', 'description', 'user_id'];

  // Relation entre Post et User
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
