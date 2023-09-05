<?php

namespace App\Http\Controllers;

use App\Events\NewOrder;
use App\Events\OrderNotification;
use App\Events\ToCasher;
use App\Exports\OrdersExport;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Http\Resources\RatingResource;
use App\Models\Ingredient;
use App\Models\OrderIngredient;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class OrderController extends Controller
{
    use ApiResponseTrait;
 
    
    public function index()
    {
        $orders = Order::get();
        return $this->apiResponse(OrderResource::collection($orders),'success',200);
    }

    public function show($id)
    {

        $order = Order::find($id);

        if($order){
            return $this->apiResponse(OrderResource::make($order),'success',200);
        }else{
            return $this->apiResponse(null,'The order Not Found',404);
        }
    }


    public function store(Request $request)
    {
        $v = $request->validate([
            'table_num' => 'required',
            'time' => 'date_format:H:i:s',
            'branch_id'=> 'exists:branches,id',
            'product_id' => 'exists:products',
            'ingredient_id' => 'exists:ingredients',
        ]);
        $order = new Order();
        $order->table_num = $v['table_num'];
        $order->branch_id = $v['branch_id'];
        $order->time = Carbon::now()->format('H:i:s');

        $totalPrice = 0;
        
        $products = $request->products;
        if($products){
            foreach($products as $productData){

                $product = Product::find($productData['product_id']);

                if($product){
                    $productPrice = $product->price;
                    $order->save();
                }else{
                    return $this->apiResponse(null,'The product Not Found',404);
                }
                
                $orderProduct = new OrderProduct();
                $orderProduct->order_id = $order->id;
                $orderProduct->product_id = $productData['product_id'];
                $orderProduct->quantity = $productData['quantity'];
                $orderProduct->notes = $productData['notes'];
                $orderProduct->save();

                
                
                 // Calculate the product subtotal
                 $productSubtotal = $productPrice * $productData['quantity'];
                
                 // Add the product subtotal to the total price
                 $totalPrice += $productSubtotal; 

                if(isset($productData['ingredients'])){
                    foreach($productData['ingredients'] as $ingredientData){

                        $ingredient = Ingredient::find($ingredientData['ingredient_id']);
                        if($ingredient){
                            $ingredientPrice = $ingredient->price_by_piece;
                        }else{
                            return $this->apiResponse(null,'The ingredient Not Found',404);
                        }

                        $orderIngredient = new OrderIngredient();
                        $orderIngredient->order_id = $order->id;
                        $orderIngredient->ingredient_id = $ingredientData['ingredient_id'];
                        $orderIngredient->save();
                        
                        // Add the ingredient subtotal to the total price
                        $totalPrice += $ingredientPrice;
                    }
                }
            }
          
        }
        // $order->save();
        $orderTax = intval($order->branch->taxRate);//0.15
        
        $orderTaxRate = $orderTax / 100;
        
        $order->total_price = $totalPrice + ($totalPrice * $orderTaxRate);
        
        $order->save();
        if ($order)
        {
            event(new NewOrder($order));
            return $this->apiResponse(new OrderResource($order), 'The order Save', 201);
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
            'branch_id'=> 'exists:branches,id',
            'product_id' => 'exists:products',
            'ingredient_id' => 'exists:ingredients',
        ]);

        $order = Order::find($id);
        
        if(! $order)
        {
            return $this->apiResponse(null, 'Order not found', 404);  
        }
        
        if ($order && $order->status == 'Befor_Preparing')
        {
            $order->table_num = $v['table_num'];
            $order->branch_id = $v['branch_id'];
            $order->time = Carbon::now()->format('H:i:s');
            // $order->save();

            // Remove all existing order products and ingredients
            $order->products()->detach();
            $order->ingredients()->detach();

            $totalPrice = 0;
            $products = $request->products;
            if ($products) {
                foreach ($products as $productData) {

                    $product = Product::find($productData['product_id']);

                    if ($product) {
                        $productPrice = $product->price;
                        $order->save();
                    }else{
                        return $this->apiResponse(null,'The product Not Found',404);
                    }
                    $orderProduct = new OrderProduct();
                    $orderProduct->order_id = $order->id;
                    $orderProduct->product_id = $productData['product_id'];
                    $orderProduct->quantity = $productData['quantity'];
                    $orderProduct->notes = $productData['notes'];
                    $orderProduct->save();

                    
                    // Calculate the product subtotal
                    $productSubtotal = $productPrice * $productData['quantity'];

                    // Add the product subtotal to the total price
                    $totalPrice += $productSubtotal;

                    if (isset($productData['ingredients'])) {
                        foreach ($productData['ingredients'] as $ingredientData) {

                            $ingredient = Ingredient::find($ingredientData['ingredient_id']);
                            if ($ingredient) {
                                $ingredientPrice = $ingredient->price_by_piece;
                            }else{
                                return $this->apiResponse(null,'The ingredient Not Found',404);
                            }

                            $orderIngredient = new OrderIngredient();
                            $orderIngredient->order_id = $order->id;
                            $orderIngredient->ingredient_id = $ingredientData['ingredient_id'];
                            // $orderIngredient->quantity = $ingredientData['quantity'];
                            $orderIngredient->save();

                            

                            // Calculate the ingredient subtotal
                            $ingredientSubtotal = $ingredientPrice ;

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

            return $this->apiResponse(new OrderResource($order), 'The order updated successfully', 200);
        }else{
            return $this->apiResponse(null, 'It is not possible to modify your order. The order is in preparation ', 400); 
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



    public function exportOrderReport(Request $request)
    {

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
    


   
    public function GetStatusOrder(Request $request)
    {
        $order = Order::where('table_num',$request->table_num)->where('status','Befor_Preparing')->latest()->first();
        if(! $order){
            return $this->apiResponse(null,'This order is under preparation',404);
        }
        return $this->apiResponse(OrderResource::make($order),'success',200);
        
    } 

    public function getStatus()
    {
        $orders = Order::where('status','=','Befor_Preparing')->get();
        
        if($orders->isNotEmpty()){
            return $this->apiResponse(OrderResource::collection($orders), 'This order Befor_Preparing', 201);

        }else{
            return $this->apiResponse(null, ' Not Found', 404);

        }
    }

    public function ChangeToPreparing(Request $request)
    {

        $order = Order::where('id',$request->status_id)->first();
        
        if($order && $order->status = 'Befor_Preparing')
        {
            $order->update([
                'status' => 'Preparing',
            ]);
            $order->save();
           
            return $this->apiResponse(OrderResource::make($order), 'Changes saved successfully', 201);

        }else{
             return $this->apiResponse(null, ' Not Found', 404);
            
        }
    } 

    public function ChangeToDone(Request $request)
    {
        $order = Order::where('id',$request->status_id)->first();
        
        if ($order && $order->status = 'Preparing'){
            $order->update([
                'status' => 'Done',
                'time_end' => Carbon::now()->format('H:i:s'),
            ]);
            $order->save();
            event(new ToCasher($order));
            return $this->apiResponse(OrderResource::make($order), 'Changes saved successfully', 201);
        }else{
             return $this->apiResponse(null, ' Not Found', 404);
            
        }
    }
    public function CheckPaid()
    {
        $orders = Order::where('status','Done')->where('is_paid',0)->get();
        
        if($orders->isNotEmpty()){
            return $this->apiResponse(OrderResource::collection($orders), 'This orders Not Paid Yet', 201);
        }else{
            return $this->apiResponse(null, 'There are no ready orders', 404);
        }
    }
    public function ChangePaid(Request $request)
    {

        $order = Order::where('id',$request->check_id)->where('status','Done')->where('is_paid',0)->first();
        
        if(!$order){
            return $this->apiResponse(null, 'Order Not Found Or Status Order Not Done', 404);
        }else{
            $order->update([
                'is_paid' => '1',
            ]);
            $order->save();
        }
        return $this->apiResponse(OrderResource::make($order), ' Payment status changed successfully', 201);
         
        
    }


    public function TotalOrderByMonth()
    {
        $ordersByMonth = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
        ->groupBy('month')
        ->get();
    
        return $this->apiResponse($ordersByMonth,'success',200);

    }

    
    public function getOrderforRate($table_num)
    {
        $order = Order::where('table_num',$table_num)->where('status','Done')->latest()->first();
        if(!$order){
            return $this->apiResponse(null,'not found',404);
        }
        $orderForRate = Service::where('order_id',$order->id)->first();
       
        if(! $orderForRate){
            return $this->apiResponse(OrderResource::make($order),'success',200);
            

        }else{
            return $this->apiResponse(null,'The order has evaluate',404);

        }
        
        
    }
}

