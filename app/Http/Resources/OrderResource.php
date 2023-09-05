<?php

namespace App\Http\Resources;

use App\Models\Ingredient;
use App\Models\OrderIngredient;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{


    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'table_num' => $this->table_num,
            'total_price' => $this->total_price,
            'time' => $this->time,
            'time_end' => $this->time_end,
            'status' => $this->status,
            'is_paid' => $this->is_paid,
            'products' => OrderProductResource::collection($this->products),
        ];
    }


   


}
