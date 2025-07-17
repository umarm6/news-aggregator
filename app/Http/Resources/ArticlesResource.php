<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticlesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => $this->when($request->routeIs('articles.show'), $this->content),
            'url' => $this->url,
            'published_at' => $this->published_at->format('Y-m-d H:i:s'),
            'author' => $this->author,
            'category' => $this->category,
            'image_url' => $this->image_url,
            'source' => [
                'name' => $this->source->name,
                'id' => $this->source->id,
            ],
        ];
    }
}
