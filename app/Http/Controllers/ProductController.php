<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Month;

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
            'name' => 'required|max:255|string',
            'name_trans' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'ingredient' => 'required|string|min:3|max:2500',
            'ingredient_trans'=>'nullable|string',
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
        $product->name_trans = $request->name_trans;
        $product->price = $request->price;
        $product->ingredient = $request->ingredient;
        $product->ingredient_trans = $request->ingredient_trans;
        $product->estimated_time = Carbon::createFromTimestamp($request->estimated_time)->format("i:s");
        $product->status = $request->status;
        $product->category_id = $request->category_id;
        $product->branch_id = $request->branch_id;

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
            }else{
                $product->position = 1 ;
            }
        }
        
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
            'name' => 'max:255|string',
            'name_trans' => 'nullable|string',
            'price' => 'numeric|min:0',
            'ingredient' => 'string|min:3|max:2500',
            'ingredient_trans'=>'nullable|string',
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
            $product->name_trans = $request->name_trans;
            $product->price = $request->price;
            $product->ingredient = $request->ingredient;
            $product->ingredient_trans = $request->ingredient_trans;
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
        
        if ($product->status == 1) {
            $product->status = 0;
        } else {
            $product->status = 1;
        }
        $product->save();

        return $this->apiResponse($product->status,'Status change successfully.',200);
    }





}
