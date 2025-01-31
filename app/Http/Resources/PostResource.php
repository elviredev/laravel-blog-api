<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Centraliser la logique de formatage des données
     * Réutilisabilité: PostResource peut être utilisée dans d'autres méthodes (index, show)
     * Flexibilité: permet d'ajuster les données retournées sans modifier la logique dans les contrôleurs
     * @return array<string, mixed>
     */


    public function toArray(Request $request): array
    {
      return [
        'id' => $this->id,
        'title' => $this->title,
        'slug' => $this->slug,
        'description' => $this->description,
        'published_at' => $this->created_at->toDateTimeString(),
        'last_update' => $this->updated_at->toDateTimeString(),
        'author' => [
          'id' => $this->user_id,
          'name' => $this->user->name ?? 'Anonymous'
        ],
        // total commentaires sur un article
        'comments_count' => $this->comments_count,
        // Inclure les commentaires associés à un post. Boucler dans le tableau 'comments'
        'comments' => $this->comments->map(function ($comment) {
          return [
            'id' => $comment->id,
            'post_id' => $comment->post_id,
            'comment' => $comment->comment ?? '',
            'created_at' => $comment->created_at->toDateTimeString(),
          ];
        }),
        // total likes sur un article
        'likes_count' => $this->likes_count
      ];
    }
}
