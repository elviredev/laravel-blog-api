<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperLike
 */
class Like extends Model
{
  protected $fillable = ['user_id', 'post_id'];
}
