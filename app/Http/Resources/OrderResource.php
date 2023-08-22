<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return [
        //     'id' => $this->id,
        //     'table_num' => $this->table_num,
        //     'total_price' => $this->total_price,
        //     'time' => $this->time,
        //     'time_end' => $this->time_end,
        //     'status' => $this->status,
        //     'products' => ProductResource::collection($this->whenLoaded('products')),
         
        // ];
        return parent::toArray($request);
    }
}
