<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\ResturantController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//resturant api
Route::post('/store_resturant', [ResturantController::class, 'store']);

//login api
Route::post('login', [UserController::class, 'login']);

//branch apis
Route::get('/branches', [BranchController::class, 'index']);
Route::get('/show_branch/{id}', [BranchController::class, 'show']);

//category apis
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/show_category/{id}', [CategoryController::class, 'show']);
Route::post('/update_position', [CategoryController::class, 'updatePosition']);

//ingrdient apis
Route::get('/ingredients', [IngredientController::class, 'index']);
Route::get('/show_ingredient/{id}', [IngredientController::class, 'show']);

//product apis
Route::get('/products', [ProductController::class, 'index']);
Route::get('/show_product/{id}', [ProductController::class, 'show']);


//order apis
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/show_order/{id}', [OrderController::class, 'show']);
Route::post('/store_order', [OrderController::class, 'store']);
Route::put('/update_order/{id}', [OrderController::class, 'update']);
Route::post('/delete_order/{id}', [OrderController::class, 'destroy']);

//Rating apis
Route::get('/avgRating/{id}', [RatingController::class, 'avgRating']);// معدل تقييم العملاء
Route::get('/ratings', [RatingController::class, 'index']);
Route::get('/mostRatedProduct', [RatingController::class, 'mostRatedProduct']);//منتج اكثر تقييم
Route::get('/leastRatedProduct', [RatingController::class, 'leastRatedProduct']);//منتج اقل تقييم
Route::get('/show_rating/{id}', [RatingController::class, 'show']);
Route::post('/store_rating', [RatingController::class, 'store']);
Route::post('/delete_rating/{id}', [RatingController::class, 'destroy']);

//offer apis
Route::get('/offers', [OfferController::class, 'index']);
Route::get('/show_offer/{id}', [OfferController::class, 'show']);


// feedback_Apis
Route::get('/feedbacks', [FeedbackController::class, 'index']);
Route::get('/show_feedback/{id}', [FeedbackController::class, 'show']);
Route::post('/store_feedback', [FeedbackController::class, 'store']);
Route::post('/update_feedback/{id}', [FeedbackController::class, 'update']);
Route::post('/delete_feedback/{id}', [FeedbackController::class, 'destroy']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', [UserController::class, 'logout']);  
    
    //resturant api 
    Route::post('/update_resturant/{id}', [ResturantController::class, 'update'])->middleware('SuperAdmin');
    Route::post('/delete_resturant/{id}', [ResturantController::class, 'delete'])->middleware('SuperAdmin');


    //Category_Apis
    Route::post('/store_category', [CategoryController::class, 'store']);
    Route::post('/update_category/{id}', [CategoryController::class, 'update']);
    Route::post('/delete_category/{id}', [CategoryController::class, 'destroy']);


    //Ingredient_Apis
    Route::post('/store_ingredient', [IngredientController::class, 'store']);
    Route::post('/update_ingredient/{id}', [IngredientController::class, 'update']);
    Route::post('/delete_ingredient/{id}', [IngredientController::class, 'destroy']);

    // product_Apis
    Route::post('/store_product', [ProductController::class, 'store']);
    Route::post('/update_product/{id}', [ProductController::class, 'update']);
    Route::post('/delete_product/{id}', [ProductController::class, 'destroy']);
    Route::get('/edit_status/{id}', [ProductController::class, 'edit']);// on off
    
  
    //Order_Apis
    Route::get('ready_order/{id}',[OrderController::class,'readyOrder']);//قدي اخد الاوردر وقت
    Route::get('peakTimes',[OrderController::class,'peakTimes']);//اوقات الذروة
    Route::get('/export-order-report', [OrderController::class, 'exportOrderReport']);
    Route::get('getStatus/{id}',[OrderController::class,'getStatus']);
    Route::post('change_status/{id}',[OrderController::class,'changeStatus'])->middleware('kitchen');
    Route::get('CheckPaid/{id}',[OrderController::class,'CheckPaid']);
    Route::post('ChangePaid/{id}',[OrderController::class,'ChangePaid'])->middleware('casher');
    Route::get('/mostRequestedProduct',[OrderController::class,'mostRequestedProduct']);//منتجات اكثر طلبا
    Route::get('/leastRequestedProduct',[OrderController::class,'leastRequestedProduct']);//منتجات اقل طلبا

    
    //Offer_Apis
    Route::post('/store_offer', [OfferController::class, 'store']);
    Route::post('/update_offer/{id}', [OfferController::class, 'update']);
    Route::post('/delete_offer/{id}', [OfferController::class, 'destroy']);


});
Route::group(['middleware' =>  ['auth:api', 'SuperAdmin']], function () {
    Route::get('users', [UserController::class, 'index']);
    Route::post('add_user', [UserController::class, 'store']);
    Route::get('show_user/{id}', [UserController::class, 'show']);
    Route::post('update_user/{id}', [UserController::class, 'update']);
    Route::post('delete_user/{id}', [UserController::class, 'destroy']);  
    
    // branch_Apis
    Route::post('/store_branch', [BranchController::class, 'store']);
    Route::post('/update_branch/{id}', [BranchController::class, 'update']);
    Route::post('/delete_branch/{id}', [BranchController::class, 'destroy']);
});

//forget & reset password
// Route::post('forgotPassword',[NewPasswordController::class,'forgotPassword']);
// Route::post('resetpassword',[NewPasswordController::class,'passwordReset']);
// Route::get('/reset-password/{token}', function (string $token) {
//     return $token;
// });
