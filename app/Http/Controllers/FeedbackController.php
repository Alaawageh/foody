<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    use ApiResponseTrait;
    
    public function index()
    {
        $feedbacks = FeedbackResource::collection(Feedback::get());
        return $this->apiResponse($feedbacks,'success',200);
    }

    public function show($id){

        $feedback = Feedback::find($id);
        
        if($feedback)
        {
            return $this->apiResponse(new FeedbackResource($feedback),'success',200);
        }else{
            return $this->apiResponse(null,'The feedback Not Found',404);
        }
        

    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|min:3|max:2500',
            'order_id' => 'nullable|integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $feedback = Feedback::create($request->all());

        if($feedback)
        {
            return $this->apiResponse(new FeedbackResource($feedback),'The feedback Save',201);
        }else{
            return $this->apiResponse(null,'The feedback Not Save',400);
        }

        
    }

    

    
    public function update(Request $request ,$id){

        $validator = Validator::make($request->all(), [
            'text' => 'required|string|min:3|max:2500',
            'order_id' => 'nullable|integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $feedback = Feedback::find($id);
        
        if($feedback)
        {
            $feedback->update($request->all());

            return $this->apiResponse(new FeedbackResource($feedback),'The feedback update',201);
        }

        return $this->apiResponse(null,'The feedback Not Found',404);
    }

    
    public function destroy($id){

        $feedback = Feedback::find($id);

        if($feedback)
        {
            $feedback->delete();

            return $this->apiResponse(null,'The feedback deleted',200);
        }
        return $this->apiResponse(null,'The feedback Not Found',404);

    }
}
