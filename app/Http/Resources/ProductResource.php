<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'image' => url('/images/product/' .$this->image),
            'price'=>$this->price,
            'ingredients'=>$this->ingredients,
            'estimated_time'=>$this->estimated_time,
            'status'=> $this->status,
            'position'=>$this->position,
            'category_id'=>$this->category_id,
            'branch_id'=>$this->branch_id,
        ];
    }
}
