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


Route::post('/store_resturant', [ResturantController::class, 'store']);


Route::post('login', [UserController::class, 'login']);


// Api's For Client
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/show_category/{id}', [CategoryController::class, 'show']);

Route::get('/ingredients', [IngredientController::class, 'GetAll']);
Route::get('/ingredients/{productId}', [IngredientController::class, 'index']);
Route::get('/show_ingredient/{id}', [IngredientController::class, 'show']);

Route::get('/products/{categoryId}', [ProductController::class, 'index']);
Route::get('/products', [ProductController::class, 'AllProducts']);
Route::get('/show_product/{id}', [ProductController::class, 'show']);

Route::get('/offers', [OfferController::class, 'index']);
Route::get('/show_offer/{id}', [OfferController::class, 'show']);

Route::post('/cart/add', [OrderController::class, 'store']);
Route::get('GetStatusOrder/{table_num}',[OrderController::class,'GetStatusOrder']);
Route::put('/cart/update/{id}', [OrderController::class, 'update']);

Route::post('/store_rating', [RatingController::class, 'store']);
Route::post('/delete_rating/{id}', [RatingController::class, 'destroy']);


Route::post('/store_feedback', [FeedbackController::class, 'store']);
Route::post('/update_feedback/{id}', [FeedbackController::class, 'update']);
Route::post('/delete_feedback/{id}', [FeedbackController::class, 'destroy']);

Route::group(['middleware' => 'kitchen'], function() {
    Route::get('getStatus/{id}',[OrderController::class,'getStatus']);
    Route::post('change_status/{id}',[OrderController::class,'changeStatus']);
});

Route::group(['middleware' =>  'casher'],function(){
    Route::get('CheckPaid/{id}',[OrderController::class,'CheckPaid']);
    Route::post('ChangePaid/{id}',[OrderController::class,'ChangePaid']);
});

Route::group(['middleware' => 'auth:api'],function() {

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/show_order/{id}', [OrderController::class, 'show']);

    Route::post('logout', [UserController::class, 'logout']);
});

Route::group(['middleware' => ['auth:api' ,'admin']], function () {
    
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
    Route::get('/product/totalSales',[ProductController::class,'TotalSalesByMonth']);
    Route::get('/product/maxSales',[ProductController::class,'maxSales']);
    Route::get('/product/avgSales',[ProductController::class,'avgSalesByYear']);
    Route::get('/product/mostRequestedProduct',[ProductController::class,'mostRequestedProduct']);
    Route::get('/product/leastRequestedProduct',[ProductController::class,'leastRequestedProduct']);
  
    //Order_Apis
    Route::post('/delete_order/{id}', [OrderController::class, 'destroy']);
    Route::get('/ready_order/{id}',[OrderController::class,'readyOrder']);//قدي اخد الاوردر وقت
    Route::get('/peakTimes',[OrderController::class,'peakTimes']);//اوقات الذروة
    Route::get('/export-order-report', [OrderController::class, 'exportOrderReport']);//تصدير اكسل 

    Route::get('/order/totalOrders',[OrderController::class,'TotalOrderByMonth']);//اجمالي الاوردرات لكل شهر
    Route::get('/order/Ratedorder',[OrderController::class,'mostRatedorder']);//الاوردرات الاكثر تقييما
    Route::get('/order/orderByDay',[OrderController::class,'ordersByDay']);
    Route::get('/order/mostFeedbackedOrder',[OrderController::class,'mostFeedbackedOrder']);

    Route::post('/store_offer', [OfferController::class, 'store']);
    Route::post('/update_offer/{id}', [OfferController::class, 'update']);
    Route::post('/delete_offer/{id}', [OfferController::class, 'destroy']);

    Route::get('/mostRatedProduct', [RatingController::class, 'mostRatedProduct']);//منتج اكثر تقييم
    Route::get('/leastRatedProduct', [RatingController::class, 'leastRatedProduct']);//منتج اقل تقييم
    Route::get('/avgRating/{id}', [RatingController::class, 'avgRating']);// معدل تقييم المنتج
    Route::get('/ratings', [RatingController::class, 'index']);
    Route::get('/show_rating/{id}', [RatingController::class, 'show']);

    Route::get('/feedbacks/{orderId}', [FeedbackController::class, 'index']);
    Route::get('/show_feedback/{id}', [FeedbackController::class, 'show']);

});
Route::group(['middleware' =>  ['auth:api', 'SuperAdmin']], function () {

    Route::post('/update_resturant/{id}', [ResturantController::class, 'update']);
    Route::post('/delete_resturant/{id}', [ResturantController::class, 'delete']);
    
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{branchId}', [UserController::class, 'GetUserByBranch']);
    Route::post('add_user', [UserController::class, 'store']);
    Route::get('show_user/{id}', [UserController::class, 'show']);
    Route::post('update_user/{id}', [UserController::class, 'update']);
    Route::post('delete_user/{id}', [UserController::class, 'destroy']);  
    
    //branch apis
    Route::get('/branches', [BranchController::class, 'index']);
    Route::get('/show_branch/{id}', [BranchController::class, 'show']);
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
