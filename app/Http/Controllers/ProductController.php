<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductController extends Controller

{
    use ApiResponseTrait;
   
    public function AllProducts()
    {
        $products = Product::orderByRaw('position IS NULL ASC, position ASC')->get();

        return $this->apiResponse(ProductResource::collection($products),'success',200);  
    }

    
    public function index($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return $this->apiResponse(null ,'Category not found', 404);
        }

        $products = $category->products()->get();

        if($products->isEmpty()){
            return $this->apiResponse(null ,'not found', 404);

        }
        return $this->apiResponse(ProductResource::collection($products),'success',200);
        
    }

    public function show($id)
    {
        $product = Product::find($id);

        if($product)
        {
            return $this->apiResponse(ProductResource::make($product),'success',200);
        }else{
            return $this->apiResponse(null,'The product Not Found',404);
        }
        

    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|regex:/(^[A-Za-z ]+$)+/',
            'price' => 'required|numeric|min:0',
            'ingredient' => 'required|string|min:3|max:2500',
            'image' => 'nullable|file||image|mimes:jpeg,jpg,png',
            'estimated_time'=>'nullable|date_format:i:s',
            'status' => 'in:0,1',
            'position' => 'nullable|integer|min:0',
            'category_id' => 'integer|exists:categories,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);


        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->ingredient = $request->ingredient;
        $product->estimated_time = Carbon::createFromTimestamp($request->estimated_time)->format("i:s");
        $product->category_id = $request->category_id;
        $product->branch_id = $request->branch_id;
        $product->status = $request->status;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $product->setImageAttribute($image);
        }

        if ($request->position) {
            

            $products = Product::orderBy('position')->get();
            if ($products->isNotEmpty()) {
                $highest_position = $products->last()->position;
                if ($request->position > $highest_position) {
                    $product->position = $highest_position+1;
                } else {
                    foreach ($products as $pro) {
                        if ($pro->position >= $request->position && $request->position !== null) {
                            $pro->position++;
                            $pro->save();
                        }
                    }
                }
            }
        }
        $product->position = $request->position;
        $product->save();

        $ingredientID = $request->ingredientID ?? [];
        $product->ingredients()->attach($ingredientID);
       if (! $product) {
        return $this->apiResponse(null,'The Data Not Save',400);
       }else{
        return $this->apiResponse(new ProductResource($product),'Data successfully Saved',201);
       }
      

        
    }
    
    public function update(Request $request ,$id)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'max:255|regex:/(^[A-Za-z ]+$)+/',
            'price' => 'numeric|min:0',
            'ingredient' => 'string|min:3|max:2500',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png',
            'estimated_time'=>'nullable|date_format:i:s',
            'position' => 'nullable|integer|min:0',
            'category_id' => 'integer|exists:categories,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }


        $product = Product::find($id);
        $position = $request->position;

        if($product){
            $product->name = $request->name;
            $product->price = $request->price;
            $product->ingredient = $request->ingredient;
            $product->estimated_time = Carbon::createFromTimestamp($request->estimated_time)->format("i:s");
            $product->category_id = $request->category_id;
            $product->branch_id = $request->branch_id;
            $product->save();
            if ($position != $product->position) {

                $products = Product::where('category_id',$request->category_id)->orderBy('position')->get();
                $highest_position = $products->last()->position;
                
                // check if requested position is greater than highest existing position
                if ($position > $highest_position) {
                    $product->position = $highest_position+1;
                    $product->save();
                } else {
                    // adjust positions of existing categories and update position of current category
                    foreach ($products as $pro) {
                        if ($pro->id != $id && $pro->position >= $position && $position !== null) {
                            $pro->position++;
                            $pro->save();
                        }
                    }
                    $product->position = $position;
                }
            }
            if ($request->hasFile('image')) {
                File::delete(public_path($product->image));
                $image = $request->file('image');
                $product->setImageAttribute($image);
            }
            $product->save();

            $ingredientID = $request->ingredientID ?? [];
            $product->ingredients()->sync($ingredientID);
       
            return $this->apiResponse(new ProductResource($product),'Data successfully Saved',201);
        }else{
            return $this->apiResponse(null,'The product Not Found',404);
        }

    }

    
    public function destroy($id){

        $product=Product::find($id);

        if($product)
        {
            $product->delete();

            File::delete(public_path('/images/product/'.$product->image));

            return $this->apiResponse(null,'Data deleted',200);
        }else{
            return $this->apiResponse(null,'The product Not Found',404);
        }

    }



    public function edit($id)
    {
        $product = Product::find($id);
        
        if ($product->status == '1') {
            $product->status = 0;
        } else {
            $product->status = 1;
        }
        $product->save();

        return $this->apiResponse($product->status,'Status change successfully.',200);
    }

    public function TotalSalesByMonth()
    {
        $totalPriceByMonth = DB::table('orders')
        ->join('orders_products', 'orders.id', '=', 'orders_products.order_id')
        ->join('products', 'orders_products.product_id', '=', 'products.id')
        ->selectRaw('MONTH(orders.created_at) as month, SUM(orders_products.quantity * products.price) as total')
        ->groupBy('month', 'products.name')
        ->get();
       
        return $this->apiResponse($totalPriceByMonth,'success',200);

    }

    public function maxSales()
    {
        $maxPriceByMonth = DB::table('orders')
        ->join('orders_products', 'orders.id', '=','orders_products.order_id')
        ->join('products', 'orders_products.product_id', '=', 'products.id')
        ->selectRaw('MONTH(orders.created_at) as month, MAX(orders_products.quantity * products.price) as total')
        ->groupBy('month', 'products.name')
        ->get();
        
        return $this->apiResponse($maxPriceByMonth,'success',200);

    }

    public function avgSalesByYear()
    {
        $avgSalesByYear = DB::table('orders') 
                        ->join('orders_products', 'orders.id', '=', 'orders_products.order_id')
                        ->join('products', 'orders_products.product_id', '=', 'products.id')
                        ->selectRaw('YEAR(orders.created_at) as year, AVG(orders_products.quantity * products.price) as average_sales')
                        ->groupBy('year')
                        ->get();
        return $this->apiResponse($avgSalesByYear,'success',200);

    }

    public function mostRequestedProduct()
    {
        $mostRequestedProduct = DB::table('products')
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
