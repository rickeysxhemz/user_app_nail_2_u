<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\CardController;
use Illuminate\Support\Facades\Route;

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
Route::prefix('auth')->group(function () {

//    Social Lite Routes
    Route::get('login/{provider}', [AuthController::class, 'redirectToProvider']);
    Route::get('login/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

    //Public Routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('verify-email/{token}/{email}', [AuthController::class, 'verifyEmail']);
    Route::get('resend/{id}', [AuthController::class, 'resendOtpCode']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('verify-code', [AuthController::class, 'verifyCode']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('verify-phone', [AuthController::class, 'verifyPhone']);
    Route::post('verify-email', [AuthController::class, 'emailExist']);
    Route::post('social-login', [AuthController::class, 'socialLogin']);
    Route::group(['middleware' => ['auth:api', 'role:user']], function () {
        Route::get('logout', [AuthController::class, 'logout']);
    });
});

//Services Routes
Route::group(['middleware' => ['auth:api', 'role:user', 'check-user-status']], function () {

    Route::prefix('service')->group(function () {
        Route::get('all', [ServiceController::class, 'all']);
    });

    Route::prefix('dashboard')->group(function () {
        Route::get('get-all-artists', [DashboardController::class, 'getAllArtists']);
        Route::get('get-suggested-artists', [DashboardController::class, 'getSuggestedArtists']);
        Route::get('get-new-artists', [DashboardController::class, 'getNewArtists']);
        Route::post('get-artist-portfolio', [DashboardController::class, 'getArtistPortfolio']);
        Route::post('get-artist-reviews', [DashboardController::class, 'getArtistReviews']);
        Route::get('get-carousel-images', [DashboardController::class, 'getCarouselImages']);
        Route::post('track-booking', [DashboardController::class, 'trackBooking']);
        Route::get('get-track-booking', [DashboardController::class, 'getTrackBooking']);
        Route::get('get-artist/{id}', [DashboardController::class, 'getArtist']);
        Route::post('user-device-token', [DashboardController::class, 'deviceToken']);
    });

    Route::prefix('user')->group(function () {
        Route::get('get-profile-details', [UserController::class, 'getProfileDetails']);
        Route::post('edit-profile', [UserController::class, 'editProfile']);
        Route::post('register-as-artist', [UserController::class, 'registerAsArtist']);
        Route::post('post-your-service', [UserController::class, 'postYourService']);
        Route::get('get-post-your-service', [UserController::class, 'getPostYourService']);
        Route::post('save-address', [UserController::class, 'saveAddress']);
        Route::get('get-addresses', [UserController::class, 'getAddresses']);
        Route::get('job/start/{id}', [UserController::class, 'jobStart']);
        Route::get('job/end/{id}', [UserController::class, 'jobEnd']);
        Route::get('delete', [UserController::class, 'delete']);
    });

    Route::prefix('payments')->group(function () {
        Route::get('get-details', [PaymentController::class, 'getDetails']);
        Route::post('send', [PaymentController::class, 'sendPayments']);
        Route::post('issue', [PaymentController::class, 'paymentIssues']);
    });

    Route::prefix('booking')->group(function () {
        Route::post('create', [BookingController::class, 'create']);
        Route::post('get-available-artist-time', [BookingController::class, 'getAvailableArtistTime']);
        Route::get('all', [BookingController::class, 'all']);
        Route::post('detail', [BookingController::class, 'bookingDetail']);
        Route::put('cancel/{id}', [BookingController::class, 'cancelBooking']);
        Route::post('get-available-slots', [BookingController::class, 'getAvailableSlots']);
        Route::post('update-schedular', [BookingController::class, 'updateSchedular']);
    });

    Route::prefix('contact')->group(function () {
        Route::post('contact-us', [ContactUsController::class, 'contactUs']);
    });
    
    Route::prefix('review')->group(function () {
        Route::post('send', [ReviewController::class, 'send']);
        Route::get('get/{id}', [ReviewController::class, 'getReview']);
    });

    Route::prefix('settings')->group(function () {
        Route::post('update', [SettingController::class, 'update']);
        Route::get('get', [SettingController::class, 'getSetting']);
        Route::post('reset-password', [SettingController::class, 'resetPassword']);
    });
    
    Route::prefix('deals')->group(function () {
        Route::get('all', [DealController::class, 'all']);
        Route::get('artist/{id}', [DealController::class, 'getDealArtist']);
        Route::get('service/{id}', [DealController::class, 'getDealService']);
    });
    
    Route::prefix('message')->group(function () {
        Route::post('send', [MessageController::class, 'create']);
        Route::post('all', [MessageController::class, 'all']);
    });
    
    Route::prefix('favourite')->group(function () {
        Route::get('all', [FavouriteController::class, 'all']);
        Route::post('send', [FavouriteController::class, 'create']);
        Route::post('delete', [FavouriteController::class, 'deleteFavourite']);
    });

    Route::prefix('card')->group(function () {
        Route::post('add', [CardController::class, 'create']);
        Route::get('all', [CardController::class, 'all']);
    });
});

Route::any(
    '{any}',
    function () {
        return response()->json([
            'status_code' => 404,
            'message' => 'Page Not Found. Check method type Post/Get or URL',
        ], 404);
    }
)->where('any', '.*');
