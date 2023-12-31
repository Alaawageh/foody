<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResturantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // 
        return [
            'id'=>$this->id,
            'resturant_name'=>$this->resturant_name,
            'email'=>$this->email,
            'password'=>$this->password,
            'phone' => $this->phone,
            'address'=>$this->address
        ];
    }
}
