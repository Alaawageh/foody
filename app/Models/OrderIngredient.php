<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderIngredient extends Model
{
    use HasFactory;
    
    protected $table = 'orders_ingredients';

    protected $fillable = ['order_id', 'ingredient_id'];
    
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

        

    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }
}
