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
   
    public function AllProducts()
    {
        $pro = Product::orderByRaw('position IS NULL ASC, position ASC')->get();
        $products = ProductResource::collection($pro);

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
            'position' => 'nullable|integer|min:0',
            'category_id' => 'integer|exists:categories,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);


        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }
        $products = Product::where('category_id',$request->category_id)->orderBy('position')->get();
        if ($products->isEmpty())
        {
            // save new product with position 1
            $product = new Product();
            $product->name = $request->name;
            $product->price = $request->price;
            $product->ingredients = $request->ingredients;
            $product->estimated_time = $request->estimated_time;
            // $product->status = $request->status;
            $product->position = null;
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

            
            
        }else{
            // get highest existing position
            $highest_position = $products->last()->position;
            $position = $request->position;
            // check if requested position is greater than highest existing position
            if ($position > $highest_position)
            {
                // save new category with requested position
                $product = new Product();
                $product->name = $request->name;
                $product->price = $request->price;
                $product->ingredients = $request->ingredients;
                $product->estimated_time = $request->estimated_time;
                // $product->status = $request->status;
                $product->category_id = $request->category_id;
                $product->branch_id = $request->branch_id;
                $product->position = $highest_position+1;
                if($request->hasFile('image'))
                {
                    $image = $request->file('image');
                    $filename = $image->getClientOriginalName();
                    $request->image->move(public_path('/images/product'),$filename);
                    $product->image = $filename;
                }
                $product->save();
                
            }else{
                // adjust positions of existing categories and add new category with adjusted position
                foreach ($products as $product) {
                    if ($product->position >= $position && $position !== null) {
                        $product->position++;
                        $product->save();
                    }
                }
                $product = new Product();
                $product->name = $request->name;
                $product->price = $request->price;
                $product->ingredients = $request->ingredients;
                $product->estimated_time = $request->estimated_time;
                // $product->status = $request->status;
                $product->category_id = $request->category_id;
                $product->branch_id = $request->branch_id;
                $product->position = $position;
                if($request->hasFile('image'))
                {
                    $image = $request->file('image');
                    $filename = $image->getClientOriginalName();
                    $request->image->move(public_path('/images/product'),$filename);
                    $product->image = $filename;
                }
                $product->save();
    
              
            }
        }
        
        
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
            // 'status' => 'in:0,1',
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
            $product->ingredients = $request->ingredients;
            $product->estimated_time = $request->estimated_time;
            // $product->status = $request->status;
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
