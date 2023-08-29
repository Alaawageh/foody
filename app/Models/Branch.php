<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $table = 'branches';

        protected $fillable = [
            'name' , 'about' , 'image' , 'address' , 'taxRate'
        ];

        public function setImageAttribute ($image)
        {
            $newImageName = uniqid() . '_' . 'image' . '.' . $image->extension();
            $image->move(public_path('images/branch') , $newImageName);
            return $this->attributes['image'] ='/'.'images/branch'.'/' . $newImageName;
        }
        
        public function products()
        {
            return $this->hasMany(Product::class);
        }

        public function ingredients()
        {
            return $this->hasMany(Ingredient::class);
        } 

        public function users()
        {
            return $this->hasMany(User::class);
        }
        
        public function orders()
        {
            return $this->hasMany(Order::class);
        }

        

        
}
