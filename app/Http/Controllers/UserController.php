<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource; 
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{
    use HttpResponses , ApiResponseTrait;
    

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {

        try {
            $rules = [
                "email" => "required|min:1|max:16|email",
                "password" => "required|min:8|max:24|regex:/(^[A-Za-z0-9]+$)+/"
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $credentials = $request->only(['email', 'password']);

            $token = Auth::guard('api')->attempt($credentials);


            if (!$token)
            {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $user = Auth::guard('api')->user();

            return response()->json(['token' => $token, 'user' => $user]);

        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], $ex->getCode());
        }
    }

    public function logout()
    {
        auth()->logout();

        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|regex:/(^[A-Za-z ]+$)+/|between:2,100',
            'email' => 'required|email|min:1|max:16|unique:users',
            'password' => "required|min:8|max:24|regex:/(^[A-Za-z0-9]+$)+/",
            'type' => 'in:admin,Casher,Kitchen',
            'branch_id' => 'nullable|integer|exists:branches,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return $this->apiResponse(new UserResource($user), 'User successfully registered', 201);

    }


    public function index()
    {
        $users = UserResource::collection(User::get());
        return $this->apiResponse($users, 'success', 200);
    }

    public function show($id)
    {
        $user = User::find($id);

        if ($user)
        {
            return $this->apiResponse(new UserResource($user), 'ok', 200);
        }
        return $this->apiResponse(null, 'The user Not Found', 404);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'regex:/(^[A-Za-z ]+$)+/|between:2,100',
            'email' => 'email|min:1|max:16|unique:users',
            'password' => "min:8|max:24|regex:/(^[A-Za-z0-9]+$)+/",
            'type' => 'in:admin,Casher,Kitchen',
            'branch_id' => 'nullable|integer|exists:branches,id'
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        $user = User::find($id);

        if ($user)
        {
            $user->update(array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)]
            ));

            return $this->apiResponse(new UserResource($user), 'The user updated', 201);
        }else{

            return $this->apiResponse(null, 'The user Not Found', 404);

        }
    }


    public function destroy($id)
    {
        $user = User::find($id);

        if ($user)
        {
            $user->delete();

            return $this->apiResponse(null, 'The user deleted', 200);
        }else{

            return $this->apiResponse(null, 'The user Not Found', 404);

        }
    }

    

    // protected function respondWithToken($token, $user)
    // {
    //     return response()->json([
    //         'token' => $token,
    //         'user' => $user
    //     ]);
    // }

   
    
}
