<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class avgRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'average_rating'=>round($this->average_rating),
            'prodcut' => $this->product->name
        ];
    }
}
