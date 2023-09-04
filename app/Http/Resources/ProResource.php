<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProResource extends JsonResource
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
            'id'=>$this->id,
            'name'=>$this->name,
            'name trans' => $this->name_trans,
            'position'=>$this->position,
            'image' => url($this->image),
            'price'=>$this->price,
            'ingredient'=>$this->ingredient,
            'ingredient trans' => $this->ingredient_trans,
            'estimated_time'=>date($this->estimated_time),
            'status'=> $this->status,
            'rating' => $this->ratings->avg('value'),
            'branch_id'=>$this->branch_id,
        ];
    }
}
