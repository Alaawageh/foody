<?php

namespace App\Models;

use App\Http\Resources\OrderDetailsResource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPUnit\Framework\isEmpty;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'status' , 'time' , 'time_end' ,
        'table_num' , 'total_price' ,
        'is_paid' , 'branch_id'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'orders_products')->withPivot('product_id', 'order_id', 'quantity','notes');
    }
    
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'orders_ingredients')->withPivot('order_id', 'ingredient_id');
    }
    
   public function services(){
    return $this->hasMany(Service::class);
   } 

    
    
}
