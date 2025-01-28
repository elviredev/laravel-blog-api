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
        'author' => [
          'id' => $this->user_id,
          'name' => $this->user->name ?? 'Anonymous'
        ]
      ];
    }
}
