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
            'name_trans' => $this->name_trans,
            'position'=>$this->position,
            'image' => url($this->image),
            'price'=>$this->price,
            'ingredient'=>$this->ingredient,
            'ingredient_trans' => $this->ingredient_trans,
            'estimated_time'=>date($this->estimated_time),
            'status'=> $this->status,
            'category'=>CategoryResource::make($this->category),
            'extraIngredients'=>IngredientResource::collection($this->ingredients),
            'rating' => $this->ratings->avg('value'),
            'branch_id'=>$this->branch_id,
            
        ];
    }
}
