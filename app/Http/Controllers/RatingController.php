<?php

namespace App\Http\Controllers;

use App\Http\Resources\RatingResource;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class RatingController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $ratings = RatingResource::collection(Rating::get());
        return $this->apiResponse($ratings,'success',200);
    }
    

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'value' => 'required|integer|between:1,5',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $rating = Rating::create($request->all());

        if($rating)
        {
            return $this->apiResponse(new RatingResource($rating),'The Rating Save',201);
        }

        return $this->apiResponse(null,'The Rating Not Save',400);
        
    }

    public function show($id)
    {
        $rating = Rating::find($id);
   
        if($rating)
        {
            return $this->apiResponse(RatingResource::collection($rating),'success',200);
        }
        return $this->apiResponse(null,'The rating Not Found',404);
    }

    public function destroy($id){

        $rating = Rating::find($id);

        if($rating)
        {
            $rating->delete();
            return $this->apiResponse(null,'The rating deleted',200);
        }
        return $this->apiResponse(null,'The rating Not Found',404);


    }

    
    public function avgRating()
    {
        $average_rating = Rating::selectRaw('AVG(value) as average_rating , product_id')->get();

        if($average_rating)
        {
            return $this->apiResponse(round($average_rating),'this rating from all user for this product',200);
        }
        return $this->apiResponse(null,'No product has been requested yet',404);
    }



}
