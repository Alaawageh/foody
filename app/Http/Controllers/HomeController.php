<?php

namespace App\Http\Controllers;

use App\Http\Resources\HomeResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ReadyOrderResource;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    use ApiResponseTrait;

    public function TotalSalesByMonth()
    {
        $totalSales= Order::selectRaw('SUM(total_price) as total , MONTH(created_at) as month ')
        ->groupBy('month')
        ->get();
        return $this->apiResponse($totalSales,'success',200);

    }

    public function maxSales()
    {
        $maxSales= Order::selectRaw('MAX(total_price) as Max_Sales , MONTH(created_at) as month')
        ->groupBy('month')
        ->get();
        return $this->apiResponse($maxSales,'success',200);

    }

    public function avgSalesByYear()
    {
        $avgSalesByYear = Order::selectRaw('round(AVG(total_price)) as Average_Sales , YEAR(created_at) as year')
        ->groupBy('year')
        ->get();
        
        return $this->apiResponse(($avgSalesByYear),'success',200);

    }
    public function mostRequestedProduct()
    {
        $mostRequestedProduct = OrderProduct::selectRaw('SUM(quantity) as most_order , product_id')
        ->groupBy('product_id')
        ->orderByRaw('SUM(quantity) DESC')
        ->limit(5)
        ->get();

        if ($mostRequestedProduct) {
            return $this->apiResponse(HomeResource::collection($mostRequestedProduct),'success',200);       
        } else {
             return $this->apiResponse(null,'No product has been requested yet',404);
        }
    }
    public function leastRequestedProduct()
    {
        $leastRequestedProduct = OrderProduct::selectRaw('SUM(quantity) as most_order , product_id')
        ->groupBy('product_id')
        ->orderByRaw('SUM(quantity) ASC')
        ->limit(5)
        ->get();
        if ($leastRequestedProduct) {
            return $this->apiResponse(HomeResource::collection($leastRequestedProduct),'success',200);
        } else {
            return $this->apiResponse(null,'No product has been requested yet',404);
        }
    }
    public function readyOrder($id)
    {

        $order = Order::where('id', $id)->first();
        if(! $order->time_end)
        {
            return $this->apiResponse(null, 'This order Preparing', 201);
        }
        $start_at = Carbon::parse($order->time);
        $end_at = Carbon::parse($order->time_end);
        
        $preparationTime = $end_at->diff($start_at)->format('%H:%i:%s');

        if($preparationTime){
            return $this->apiResponse($preparationTime,'Order preparation time',200);
        } else {
            return $this->apiResponse(null,'Not Found',404);
        }
            
    }
    public function avgRating() {
        $avgRating = Service::selectRaw('AVG(service_rate) as Service_Rate')->orderByRae('AVG(service_rate) DESC')->get();
        if(! $avgRating ) {
            return $this->apiResponse(null,'Not Found',404);
        }else{
            return $this->apiResponse($avgRating,'average Service Rate',200);
        }

    }
    public function peakTimes()
    {
    
    $peakHours = Order::select('time')->groupBy('time')->orderByRaw('COUNT(time) DESC')->get();
    if ($peakHours) {
        return $this->apiResponse($peakHours,'This time is peak time',200);
    } else {
        return $this->apiResponse(null,'No product has been requested yet',404);
    }
     
    }
    public function ordersByDay()
    {
        $ordersByDay = Order::selectRaw('DATE(created_at) as day, COUNT(*) as count')
                ->groupBy('day')
                ->get();
        return $this->apiResponse($ordersByDay,'The number of orders by day',200);

    }
    public function mostRatedProduct()
    {
        $mostRatedProduct = DB::table('products')
        ->join('ratings', 'products.id', '=', 'ratings.product_id')
        ->select('products.name', DB::raw('AVG(ratings.value) as average_rating'))
        ->groupBy('products.name')
        ->orderBy('average_rating','DESC')
        ->limit('5')
        ->get();

        if($mostRatedProduct)
        {
            return $this->apiResponse($mostRatedProduct,'The most rated product',200);

        }else{
            return $this->apiResponse(null,'No product has been Rated yet',404);
        }
    }

    public function leastRatedProduct(){
        $mostRatedProduct = DB::table('products')
        ->join('ratings', 'products.id', '=', 'ratings.product_id')
        ->select('products.name', DB::raw('AVG(ratings.value) as average_rating'))
        ->groupBy('products.name')
        ->orderBy('average_rating','ASC')
        ->limit('5')
        ->get();

        if($mostRatedProduct)
        {
            return $this->apiResponse($mostRatedProduct,'The most rated product',200);

        }else{
            return $this->apiResponse(null,'No product has been Rated yet',404);
        }
    }
}
