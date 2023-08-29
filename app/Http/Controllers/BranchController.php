<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Support\Facades\File;

class BranchController extends Controller
{
    use ApiResponseTrait;
    
    public function index()
    {
        $branches = BranchResource::collection(Branch::get());
        return $this->apiResponse($branches,'success',200);
    }

    public function show($id)
    {
        $branch = Branch::find($id);

        if(! $branch)
        {
            return $this->apiResponse(null,'The Branch Not Found',404);

        }else{

            return $this->apiResponse(new BranchResource($branch),'success',200);
        }
        
    }

    
    public function store(Request $request){
       

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|string',
            'about' => 'nullable|string|min:0|max:2500',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png',
            'address' => 'nullable|string|min:0|max:2500',
            'taxRate' => 'required|regex:/(^[0-9 ]+%)+/',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $branch = Branch::create($request->all());

        if(! $branch)
        {      
            return $this->apiResponse(null,'Data Not saved',400);

        }else{

            return $this->apiResponse(new BranchResource($branch),'Data successfully saved',201);
        }
    }

    

    
    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'max:255|string',
            'about' => 'nullable|string|min:0|max:2500',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png',
            'address' => 'nullable|string|min:0|max:2500',
            'taxRate' => 'regex:/(^[0-9 ]+%)+/',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $branch = Branch::find($id);
        
        if (! $branch)
        {
            return $this->apiResponse(null,'The branch Not Found',404);

        }else{
            if ($request->hasFile('image')) {
                File::delete(public_path($branch->image));
            }
            $branch->update($request->all());

            return $this->apiResponse(new BranchResource($branch),'Data successfully saved',201);
        }

    }

    
    public function destroy($id)
    {

        $branch = Branch::find($id);

        if(! $branch)
        {
            return $this->apiResponse(null,'The branch Not Found',404);

        }else{
            if ($branch->image) {
                File::delete(public_path($branch->image));
            }
            $branch->delete();
            
            return $this->apiResponse(null,'The Data deleted',200);
        }

    }

}
