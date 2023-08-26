<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductController extends Controller

{
    use ApiResponseTrait;
   
    public function AllProducts(){
        $products = ProductResource::collection(Product::get());

        return $this->apiResponse($products,'success',200);  
    }
    public function index($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return $this->apiResponse(null ,'Category not found', 404);
        }

        $products = $category->products()->get();
        return $this->apiResponse($products,'success',200);
    }

    public function show($id)
    {
        $product = Product::with('ingredients')->find($id);
        
        if($product)
        {
            return $this->apiResponse(new ProductResource($product),'success',200);
        }else{
            return $this->apiResponse(null,'The product Not Found',404);
        }
        

    }

    
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|regex:/(^[A-Za-z ]+$)+/',
            'price' => 'required|numeric|min:0',
            'ingredients' => 'required|string|min:3|max:2500',
            'image' => 'nullable|file||image|mimes:jpeg,jpg,png',
            'estimated_time'=>'nullable|date_format:H:i:s',
            'status' => 'in:0,1',
            'category_id' => 'integer|exists:categories,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);


        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->ingredients = $request->ingredients;
        $product->estimated_time = $request->estimated_time;
        $product->status = $request->status;
        $product->category_id = $request->category_id;
        $product->branch_id = $request->branch_id;

        if($request->hasFile('image'))
        {
            $image = $request->file('image');
            $filename = $image->getClientOriginalName();
            $request->image->move(public_path('/images/product'),$filename);
            $product->image = $filename;
        }
        $product->save();

        $ingredientID = $request->ingredientID ?? [];
        $product->ingredients()->attach($ingredientID);
        
        if($product)
        {
            return $this->apiResponse(new ProductResource($product),'Data successfully Saved',201);
        }else{
            return $this->apiResponse(null,'The Data Not Save',400);
        }

        
    }
    
    public function update(Request $request ,$id)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'max:255|regex:/(^[A-Za-z ]+$)+/',
            'price' => 'numeric|min:0',
            'ingredients' => 'string|min:3|max:2500',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png',
            'estimated_time'=>'nullable|date_format:H:i:s',
            'status' => 'in:0,1',
            'category_id' => 'integer|exists:categories,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }


        $product=Product::find($id);

        if($product){
            $product->name = $request->name;
            $product->price = $request->price;
            $product->ingredients = $request->ingredients;
            $product->estimated_time = $request->estimated_time;
            $product->status = $request->status;
            $product->category_id = $request->category_id;
            $product->branch_id = $request->branch_id;

            if($request->hasFile('image'))
            {
                File::delete(public_path('/images/product/'.$product->image));
                $image = $request->file('image');
                $filename = $image->getClientOriginalName();
                $request->image->move(public_path('/images/product'),$filename);
                $product->image = $filename;
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


}
