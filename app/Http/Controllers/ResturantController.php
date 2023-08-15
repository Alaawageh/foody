<?php

namespace App\Http\Controllers;

use App\Http\Resources\ResturantResource;
use App\Models\Branch;
use App\Models\Resturant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResturantController extends Controller
{
    use ApiResponseTrait;
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'resturant_name' => 'required|regex:/(^[A-Za-z ]+$)+/|between:2,100',
            "email" => "required|min:1|max:16|email|unique:resturants",
            "password" => "required|min:8|max:24|regex:/(^[A-Za-z0-9]+$)+/",
            'phone' => 'nullable|numeric|min:6',
            'address' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $resturant = new Resturant();
        $resturant->resturant_name = $request->resturant_name;
        $resturant->phone = $request->phone;
        $resturant->email = $request->email;
        $resturant->password = bcrypt($request->password);
        $resturant->address = $request->address;
        $resturant->save();

        if($resturant){
            $branch = new Branch();
            $branch->name = $request->resturant_name;
            $branch->address = $request->address;
            $branch->save();

            $user = new User();
            $user->name = $request->resturant_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->type = 'Super Admin';
            $user->branch_id = $branch->id;
            $user->save();

            return $this->apiResponse(new ResturantResource($resturant), 'Data successfully saved', 201);

        }
        return $this->apiResponse(null, 'Data Not saved', 400);

    }

    public function update(Request $request , $id)
    {
        $validator = Validator::make($request->all(), [
            'resturant_name' => 'regex:/(^[A-Za-z ]+$)+/|between:2,100',
            "email" => "min:1|max:16|email",
            "password" => "min:8|max:24|regex:/(^[A-Za-z0-9]+$)+/",
            'phone' => 'nullable|numeric|min:6',
            'address' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $resturant = Resturant::find($id);

        if($resturant){

            $resturant->resturant_name = $request->resturant_name;
            $resturant->phone = $request->phone;
            $resturant->email = $request->email;
            $resturant->password = bcrypt($request->password);
            $resturant->address = $request->address;
            $resturant->save();

            

            $branch = Branch::find($id);
            $branch->name = $request->resturant_name;
            $branch->address = $request->address;
            $branch->save();

            $user = User::find($id);
            $user->name = $request->resturant_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->type = 'Super Admin';
            $user->branch_id = $branch->id;
            $user->save();
            
            return $this->apiResponse(new ResturantResource($resturant), 'Data successfully saved', 201);
        }
        return $this->apiResponse(null, 'The resturant Not Found', 404);

    }

    public function delete($id)
    {
        $resturant = Resturant::find($id);
        $user = User::find($id);
        $branch = Branch::find($id);

        if($resturant){

            $resturant->delete();
            $user->delete();
            $branch->delete();

            return $this->apiResponse(null, 'The Data deleted', 200);

        }else{
            return $this->apiResponse(null, 'The resturant Not Found', 404);
        }
    }


}
