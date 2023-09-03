<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    use ApiResponseTrait;

    public function index() {
        $services = ServiceResource::collection(Service::get());
        return $this->apiResponse($services,'success',200);
    }

    public function show($id) {
        $service = Service::find($id);
        if(! $service) {
            return $this->apiResponse(null, 'Not Found', 404);
        }else{
            return $this->apiResponse(ServiceResource::make($service), 'success', 200);
        }
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'order_id' => 'exists:orders,id',
            'feedback' => 'nullable|string',
            'service_rate' => 'nullable|integer|between:1,5',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $service = Service::create($request->all());

        if ($service) {
            return $this->apiResponse(new ServiceResource($service),'Data successfully Saved',201);
        }
        return $this->apiResponse(null, 'Data Not saved', 400);
    }
}
