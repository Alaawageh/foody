<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name' , 'image' , 'price' , 'ingredients' , 'estimated_time' , 'status' , 'position', 'category_id' , 'branch_id'
    ];
    
    public function category()
    {
    	return $this->belongsTo(Category::class);
    }

    public function branch()
    {
    	return $this->belongsTo(Branch::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class,'ingredient_product');
    }
    
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function orders(){
        return $this->belongsToMany(Order::class,'orders_products')->withPivot('product_id', 'order_id', 'quantity');
    }
    
   
    



}
