<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\IngredientResource;
use App\Models\Product;
use Illuminate\Support\Facades\File;

class IngredientController extends Controller
{
    use ApiResponseTrait;
    
    public function GetAll()
    {
        $ingredients = IngredientResource::collection(Ingredient::get());

        return $this->apiResponse($ingredients,'success',200);
    }
    
    public function index($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return $this->apiResponse(null ,'Product not found', 404);
        }
        $ingredients = $product->ingredients()->get();

        if($ingredients->isEmpty()){
            return $this->apiResponse(null ,'not found', 404);

        }
        return $this->apiResponse(IngredientResource::collection($ingredients), 'success', 200);
        
    }

    public function show($id){

        $ingredient = Ingredient::find($id);
        
        if(! $ingredient)
        {
            return $this->apiResponse(null,'The ingredient Not Found',404);
        }
        return $this->apiResponse(IngredientResource::make($ingredient),'success',200);

    }

    
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|string',
            'name_trans' => 'nullable|string',
            'price_by_piece' => 'required|numeric|min:0',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png',
            'branch_id' => 'integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }
        
        $ingredient = Ingredient::create($request->except('image'));

        if (! $ingredient)
        {
            return $this->apiResponse(null,'Data Not Saved',400);
        }else{
            return $this->apiResponse(new IngredientResource($ingredient),'Data successfully saved',201);
        }

        
    }

    
    public function update(Request $request ,$id){

        $validator = Validator::make($request->all(), [
            'name' => 'max:255|string',
            'name_trans' => 'nullable|string',
            'price_by_piece' => 'numeric|min:0',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png',
            'branch_id' => 'integer|exists:branches,id',
        ]);

        if ($validator->fails())
        {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $ingredient =Ingredient::find($id);
        
        if ($ingredient) {
            if ($request->hasFile('image')) {
                File::delete(public_path($ingredient->image));
            }
            $ingredient->update($request->all());
          
            return $this->apiResponse(new IngredientResource($ingredient),'Data successfully saved',201);
        }else{
            return $this->apiResponse(null,'The ingredient Not Found',404);
        }

    }

    
    public function destroy($id){

        $ingredient = Ingredient::find($id);

        if (! $ingredient) { 
            return $this->apiResponse(null,'The ingredient Not Found',404);
        }else{
            if ($ingredient->image) {
                File::delete(public_path($ingredient->image));
            }
            $ingredient->delete();

            return $this->apiResponse(null,'The Data deleted',200);
        }

    }
}
