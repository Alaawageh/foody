<?php

namespace App\Http\Controllers;

use App\Events\NewOrder;
use App\Events\OrderNotification;
use App\Exports\OrdersExport;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Models\Branch;
use App\Models\Ingredient;
use App\Models\OrderIngredient;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Resturant;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class OrderController extends Controller
{
    use ApiResponseTrait;
 
    
    public function index()
    {
        $orders = OrderResource::collection(Order::get());
        return $this->apiResponse($orders,'success',200);
    }

    public function show($id)
    {

        $order = Order::with('products.ingredients')->find($id);

        if($order){
            return $this->apiResponse(new OrderResource($order),'ok',200);
        }else{
            return $this->apiResponse(null,'The order Not Found',404);
        }
    }


    public function store(Request $request)
    {
        $v = $request->validate([
            'table_num' => 'required',
            'time' => 'date_format:H:i:s',
            'time_end' => 'date_format:H:i:s',
            'branch_id'=> 'exists:branches,id'
        ]);
        $order = new Order();
        $order->table_num = $v['table_num'];
        $order->branch_id = $v['branch_id'];
        $order->time = Carbon::now()->format('H:i:s');
        $order->save();

        $totalPrice = 0;
        
        $products = $request->products;
        if($products){
            foreach($products as $productData){
                $orderProduct = new OrderProduct();
                $orderProduct->order_id = $order->id;
                $orderProduct->product_id = $productData['product_id'];
                $orderProduct->quantity = $productData['quantity'];
                $orderProduct->save();

                $product = Product::find($productData['product_id']);
                
                if($product){
                    $productPrice = $product->price;
                }
                 // Calculate the product subtotal
                 $productSubtotal = $productPrice * $productData['quantity'];
                
                 // Add the product subtotal to the total price
                 $totalPrice += $productSubtotal; 

                if(isset($productData['ingredients'])){
                    foreach($productData['ingredients'] as $ingredientData){
                        $orderIngredient = new OrderIngredient();
                        $orderIngredient->order_id = $order->id;
                        $orderIngredient->ingredient_id = $ingredientData['ingredient_id'];
                        $orderIngredient->quantity = $ingredientData['quantity'];
                        $orderIngredient->save();

                        $ingredient = Ingredient::find($ingredientData['ingredient_id']);
                        if($ingredient){
                            $ingredientPrice = $ingredient->price_by_piece;
                        }

                        // Calculate the ingredient subtotal
                        $ingredientSubtotal = $ingredientPrice * $ingredientData['quantity'];
                        
                        // Add the ingredient subtotal to the total price
                        $totalPrice += $ingredientSubtotal;
                    }
                }
            }
          
        }
        
        $orderTax = intval($order->branch->taxRate);//0.15
        
        $orderTaxRate = $orderTax / 100;
        
        $order->total_price = $totalPrice + ($totalPrice * $orderTaxRate);
        
        $order->save();
        if ($order)
        {
            event(new NewOrder($order));
            return $this->apiResponse(new OrderResource($order->load(['products'])), 'The order Save', 201);
        }else{
            return $this->apiResponse(null, 'The order Not Save', 400);
        }
    }
    public function update(Request $request, $id)
    {
        $v = $request->validate([
            'time' => 'date_format:H:i:s',
            'time_end' => 'date_format:H:i:s',
            'table_num' => 'required',
            'branch_id'=> 'exists:branches,id'
        ]);

        $order = Order::find($id);
        
        if(! $order)
        {
            return $this->apiResponse(null, 'Order not found', 404);  
        }
        if ($order)
        {
            $order->table_num = $v['table_num'];
            $order->branch_id = $v['branch_id'];
            $order->time = Carbon::now()->format('H:i:s');
            $order->save();

            // Remove all existing order products and ingredients
            $order->products()->detach();
            $order->ingredients()->detach();

            $totalPrice = 0;
            $products = $request->products;
            if ($products) {
                foreach ($products as $productData) {
                    $orderProduct = new OrderProduct();
                    $orderProduct->order_id = $order->id;
                    $orderProduct->product_id = $productData['product_id'];
                    $orderProduct->quantity = $productData['quantity'];
                    $orderProduct->save();

                    $product = Product::find($productData['product_id']);

                    if ($product) {
                        $productPrice = $product->price;
                    }
                    // Calculate the product subtotal
                    $productSubtotal = $productPrice * $productData['quantity'];

                    // Add the product subtotal to the total price
                    $totalPrice += $productSubtotal;

                    if (isset($productData['ingredients'])) {
                        foreach ($productData['ingredients'] as $ingredientData) {
                            $orderIngredient = new OrderIngredient();
                            $orderIngredient->order_id = $order->id;
                            $orderIngredient->ingredient_id = $ingredientData['ingredient_id'];
                            $orderIngredient->quantity = $ingredientData['quantity'];
                            $orderIngredient->save();

                            $ingredient = Ingredient::find($ingredientData['ingredient_id']);
                            if ($ingredient) {
                                $ingredientPrice = $ingredient->price_by_piece;
                            }

                            // Calculate the ingredient subtotal
                            $ingredientSubtotal = $ingredientPrice * $ingredientData['quantity'];

                            // Add the ingredient subtotal to the total price
                            $totalPrice += $ingredientSubtotal;
                        }
                    }
                }
            }
            $orderTax = intval($order->branch->taxRate);//0.15
        
            $orderTaxRate = $orderTax / 100;
        
            $order->total_price = $totalPrice + ($totalPrice * $orderTaxRate);
            $order->save();

            event(new NewOrder($order));

            return $this->apiResponse(new OrderResource($order->load(['products'])), 'The order updated successfully', 200);
        }
    }

    public function destroy($id)
    {

        $order=Order::find($id);

        if($order)
        {
            $order->delete();

            return $this->apiResponse(null,'The order deleted',200);
        }else{
            return $this->apiResponse(null,'The order Not Found',404);
        }

    }

    public function peakTimes()
   {
   
    $peakHours = Order::select('time')->groupBy('time')->orderByRaw('COUNT(time) DESC')->first();
    if ($peakHours) {
        return $this->apiResponse($peakHours,'This time is peak time',200);
    } else {
        return $this->apiResponse(null,'No product has been requested yet',404);
    }
    
   }

    public function exportOrderReport(Request $request)
    {
        // $start_at = date($request->start_at);
        // $end_at = date($request->end_at);
        $start_at = $request->input('start_at');
        $end_at = $request->input('end_at');
        $orders = Order::whereBetween('created_at', [$start_at,$end_at])->get();

        return Excel::download(new OrdersExport($orders), 'orders.xlsx');

        if ($orders) {
            
            return $this->apiResponse($orders,'success',200);
        } else {
            return $this->apiResponse(null,'Not Found',404);
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

    public function getStatus($id)
    {
        $order=Order::find($id);

        if($order){
          
            return $this->apiResponse($order->status, 'This order '.$order->status, 201);
        }else{
             return $this->apiResponse(null, 'The Order Not Found', 404);
            
            }
    } 

    public function changeStatus($id)
    {

        $order=Order::find($id);

        if($order){
            
            $order->update([
                'status' => 'Done',
                'time_end' => Carbon::now()->format('H:i:s'),
            ]);
            $order->save();
            return $this->apiResponse($order, 'Changes saved successfully', 201);
        }else{
             return $this->apiResponse(null, 'Changes are not saved', 400);
            
            }
    } 
    public function CheckPaid($id)
    {
        $order = Order::find($id);
        if($order){
            if($order->is_paid == 0){
                return $this->apiResponse($order->is_paid, 'This order Not Paid Yet', 201);
            }else{
                return $this->apiResponse($order->is_paid, 'This order is Paid ', 201);
            }
        }else{
            return $this->apiResponse(null, 'Not Found', 404);
        }
    }
    public function ChangePaid($id)
    {

        $order = Order::find($id);

        if(!$order){
            return $this->apiResponse(null, 'Not Found', 404);
        }
        
        if($order->is_paid == '0'){
            $order->update([
                'is_paid' => '1',
            ]);
           
            $order->save();
            return $this->apiResponse($order->is_paid, ' Payment status changed successfully', 201);
            
        }else{
            return $this->apiResponse(null, 'Changes are not saved', 400);
        }
        
    }

    public function mostRequestedProduct()
    {
        $mostRequestedProduct =DB::table('products')
            ->leftJoin('orders_products', 'products.id', '=', 'orders_products.product_id')->select('products.name')
            ->groupBy('products.name')
            ->orderByRaw('COUNT(product_id) DESC')
            ->limit(5)
            ->get();
             
        if ($mostRequestedProduct) {
           
            return $this->apiResponse($mostRequestedProduct,'success',200);
           
                
        } else {
             return $this->apiResponse(null,'No product has been requested yet',404);
        }
    }

    public function leastRequestedProduct()
    {
        $leastRequestedProduct = DB::table('products')
        ->leftJoin('orders_products', 'products.id', '=', 'orders_products.product_id')->select('products.name')
        ->groupBy('products.name')
        ->orderByRaw('COUNT(product_id)')
        ->limit(5)
        ->get();

        if ($leastRequestedProduct) {
            return $this->apiResponse($leastRequestedProduct,'success',200);
        } else {
            return $this->apiResponse(null,'No product has been requested yet',404);
        }
    }

    
   
}

