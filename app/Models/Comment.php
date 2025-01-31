<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperComment
 */
class Comment extends Model
{
  protected $fillable = ['user_id', 'post_id', 'comment'];
}
