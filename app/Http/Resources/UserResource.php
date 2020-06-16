<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // $this->designs
        return [
            'data' => [
                'id' => $this->id,
                'username' => $this->username,
                $this->mergeWhen(
                    auth()->check() && auth()->id() == $this->id, [
                        'email' => $this->email
                    ]
                ),
                'photo_url'  => $this->photo_url,
                'name' => $this->name,
                'designs' => DesignResource::collection(
                    $this->whenLoaded('designs')
                ),
                'created_dates' => [
                    'created_at_human' => $this->created_at->diffForHumans(),
                    'created_at' => $this->created_at
                ],
                'formatted_address' => $this->formatted_address,
                'tagline' => $this->tagline,
                'about' => $this->about,
                'location' => $this->location,
                'available_to_hire' => $this->available_to_hire
            ]
        ];
        // return parent::toArray($request);
    }
}
