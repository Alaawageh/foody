<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name' , 'name_trans', 'image' , 'price_by_piece' , 'branch_id'
    ];

    public function setImageAttribute ($image)
    {
        $newImageName = uniqid() . '_' . 'image' . '.' . $image->extension();
        $image->move(public_path('images/ingredient') , $newImageName);
        return $this->attributes['image'] ='/'.'images/ingredient'.'/' . $newImageName;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    
    public function products()
    {
        return $this->belongsToMany(Product::class,'ingredient_product');
    }

    public function orders()
    {
    return $this->belongsToMany(Order::class,'orders_ingredients');
    }

        
}
