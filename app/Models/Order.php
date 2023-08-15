<?php

namespace App\Models;

use App\Http\Resources\OrderDetailsResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPUnit\Framework\isEmpty;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'status' , 'time' , 'time_end' , 'estimated_time' ,
        'table_num' , 'total_price' , 'tax' ,
        'is_paid' , 'branch_id'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'orders_products')->withPivot('product_id', 'order_id', 'quantity');
    }
    
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'orders_ingredients')->withPivot('order_id', 'ingredient_id','quantity');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    
    
}
