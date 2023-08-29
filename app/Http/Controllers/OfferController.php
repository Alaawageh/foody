<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\OfferResource;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class OfferController extends Controller
{
    use ApiResponseTrait;
    
    public function index()
    {
        $offers = OfferResource::collection(Offer::get());
        return $this->apiResponse($offers,'success',200);
    }

    public function show($id)
    {
        $offer = Offer::find($id);

        if($offer)
        {
            return $this->apiResponse(new OfferResource($offer),'success',200);
        }else{
            return $this->apiResponse(null,'The offer Not Found',404);
        }
        

    }

    
    public function store(Request $request){

    $validator = Validator::make($request->all(), [
        
        'image' => 'required|file|image|mimes:jpeg,jpg,png',
    ]);

    if ($validator->fails()) {
        return $this->apiResponse(null,$validator->errors(),400);
    }

    $offer = Offer::create($request->all());
    if($offer)
    {
        return $this->apiResponse(new OfferResource($offer),'The offer Saved',201);
    }else{
        return $this->apiResponse(null,'The offer Not Save',400);
    }

        
    }

    // public function update(Request $request ,$id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'image' => 'required|file|image|mimes:jpeg,jpg,png',
    //     ]);
        
    //     if ($validator->fails()) {
    //         return $this->apiResponse(null,$validator->errors(),400);
    //     }

    //     $offer =Offer::find($id);

    //     if($offer)
    //     {
    //         File::delete(public_path($offer->image));
    //         $offer->update($request->all());

    //         return $this->apiResponse(new OfferResource($offer),'The offer update',201);
    //     }else{
    //         return $this->apiResponse(null,'The offer Not Found',404);
    //     }

    // }

    
    public function destroy($id){

        $offer=Offer::find($id);
        
        if($offer)
        {
            File::delete(public_path($offer->image));
            $offer->delete();

            return $this->apiResponse(null,'The offer deleted',200);
        }else{
            return $this->apiResponse(null,'The offer Not Found',404);
        }

    }
}
