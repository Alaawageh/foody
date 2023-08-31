<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\IngredientResource;

class ProductResource extends JsonResource
{
    
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'position'=>$this->position,
            'image' => url($this->image),
            'price'=>$this->price,
            'ingredient'=>$this->ingredient,
            'estimated_time'=>date($this->estimated_time),
            'status'=> $this->status,
            'category'=>CategoryResource::make($this->category),
            'extraIngredients'=>IngredientResource::collection($this->ingredients),
            'branch_id'=>$this->branch_id,
        ];
    }
}
