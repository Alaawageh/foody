<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    use ApiResponseTrait;
    
    public function index()
    {
        $cats = Category::orderByRaw('position IS NULL ASC, position ASC')->get();
        $categories = CategoryResource::collection($cats);

        return $this->apiResponse($categories,'success',200);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if($category)
        {
            return $this->apiResponse(new CategoryResource($category),'success',200);
        }else{
            return $this->apiResponse(null,'The Category Not Found',404);
        }
        
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|regex:/(^[A-Za-z ]+$)+/',
            'position' => 'nullable|integer|min:0',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $category = new Category();
        $category->name = $request->name;
        if($request->position){
            $category->position = $request->position;
            Category::where('position', '>=',  $request->position)->increment('position');
        }
        if($request->hasFile('image')){
            $image = $request->file('image');
            $filename = $image->getClientOriginalName();
            $image->move(public_path('/images/category'),$filename);
            $category->image = $filename;
        }
        $category->save();
        
        if($category){

            return $this->apiResponse(new CategoryResource($category),'Data successfully saved',201);

        }else{

            return $this->apiResponse(null,'Data Not Save',400);
        }
    }

    

    
    public function update(Request $request ,$id){

        $validator = Validator::make($request->all(), [
            'name' => 'max:255|regex:/(^[A-Za-z ]+$)+/',
            'position' => 'nullable|integer|min:0',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $category=Category::find($id);
       
        if($category){

            $category->name = $request->name;
            if($request->position){
                $category->position = $request->position;
                Category::where('position', '>=',  $request->position)->increment('position');
            }
           
            if($request->hasFile('image')){
                 File::delete(public_path('/images/category/'.$category->image));
                $image = $request->file('image');
                $filename = $image->getClientOriginalName();
                $image->move(public_path('/images/category'),$filename);
                $category->image = $filename;
            }
            $category->save();

            return $this->apiResponse(new CategoryResource($category),'Data successfully saved',201);
        }else{
            return $this->apiResponse(null,'The Category Not Found',404);
        }

    }

    
    public function destroy($id){

        $category=Category::find($id);

        if($category)
        {
            $category->delete();
            
            File::delete(public_path('/images/category/'.$category->image));

            return $this->apiResponse(null,'The Data deleted',200);
        }else{
            return $this->apiResponse(null,'The Category Not Found',404);
        }

    }

    
}
