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
        $categories = Category::orderByRaw('position IS NULL ASC, position ASC')->get();

        return $this->apiResponse(CategoryResource::collection($categories),'success',200);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if(! $category)
        {
            return $this->apiResponse(null,'The Category Not Found',404);
            
        }else {
            return $this->apiResponse(CategoryResource::make($category),'success',200);
        }
        
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|string',
            'position' => 'nullable|integer',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }
        $category = new Category();
        $category->name = $request->name;
        if($request->hasFile('image'))
        {
            $image = $request->file('image');
            $category->setImageAttribute($image);
        }
        
        if ($request->position) {
            

            $categories = Category::orderBy('position')->get();
            if ($categories->isNotEmpty()) {
                $highest_position = $categories->last()->position;
                if ($request->position > $highest_position) {
                    $category->position = $highest_position+1;
                } else {
                    foreach ($categories as $cat) {
                        if ($cat->position >= $request->position && $request->position !== null) {
                            $cat->position++;
                            $cat->save();
                        }
                    }
                }
            }
        }
        $category->position = $request->position;
        $category->save();
        
        if(! $category) {
            return $this->apiResponse(null,'Data Not Save',400);
        } else {
            return $this->apiResponse(new CategoryResource($category),'Data successfully saved',201);
        }
    }

    

    
    public function update(Request $request ,$id){

        $validator = Validator::make($request->all(), [
            'name' => 'max:255|string',
            'position' => 'nullable|integer|min:0',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $category = Category::find($id);

        if(! $category) {
            return $this->apiResponse(null,'The Category Not Found',404);
        }
        $position = $request->position;
        if ($category)
        {
            $category->name = $request->name;
            if ($request->hasFile('image')) {
                File::delete(public_path($category->image));
                $image = $request->file('image');
                $category->setImageAttribute($image);
            }
            if ($position && $position != $category->position) {
                $categories = Category::orderBy('position')->get();
                $highest_position = $categories->last()->position;

                if ($position > $highest_position ) {
                    $category->position = $highest_position+1;
                } else {
                    foreach ($categories as $cat) {
                        if ($cat->id != $id && $cat->position >= $position && $position !== null) {
                            $cat->position++;
                            $cat->save();
                        }
                        
                    }
                    $category->position = $position;
                }
            }
            // $category->position = $position;
            $category->save();

            return $this->apiResponse(new CategoryResource($category),'Data successfully saved',201);
        }

    }

    
    public function destroy($id){

        $category=Category::find($id);

        if(! $category)
        {
            return $this->apiResponse(null,'The Category Not Found',404);
            
        }else{
            
            if ($category->image) {
                File::delete(public_path($category->image));
            }
            $category->delete();
            return $this->apiResponse(null,'The Data deleted',200);
        }

    }

    
}
