<?php

use App\Http\Controllers\Api\Address\AddressController;
use App\Http\Controllers\Api\Banner\BannerController;
use App\Http\Controllers\Api\Blog\BlogController;
use App\Http\Controllers\Api\ActivityLog\ActivityLogController;
use App\Http\Controllers\Api\Contact\ContactController;
use App\Http\Controllers\Api\Contact\ContactMailController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Faq\FaqController;
use App\Http\Controllers\Api\GlobalSearch\GlobalSearchController;
use App\Http\Controllers\Api\Key\KeyController;
use App\Http\Controllers\Api\Part\PartController;
use App\Http\Controllers\Api\Part\PartImageController;
use App\Http\Controllers\Api\Partner\PartnerController;
use App\Http\Controllers\Api\Service\ServiceController;
use App\Http\Controllers\Api\Service\ServiceImageController;
use App\Http\Controllers\Api\ServiceType\ServiceTypeController;
use App\Http\Controllers\Api\ServiceTypeSpecification\ServiceTypeSpecificationController;
use App\Http\Controllers\Api\SideBar\MenuItemController;
use App\Http\Controllers\Api\SideBar\MenuModuleController;
use App\Http\Controllers\Api\SideBar\SidebarController;
use App\Http\Controllers\Api\Solution\SolutionController;
use App\Http\Controllers\Api\Solution\SolutionImageController;
use App\Http\Controllers\Api\Subject\SubjectController;
use App\Http\Controllers\Api\SubPart\SubPartController;
use App\Http\Controllers\Api\SubpartApplication\SubpartApplicationController;
use App\Http\Controllers\Api\SubpartDoc\SubpartDocController;
use App\Http\Controllers\Api\SubpartFeature\SubpartFeatureController;
use App\Http\Controllers\Api\SubpartModel\SubpartModelController;
use App\Http\Controllers\Api\SubpartReview\SubpartReviewController;
use App\Http\Controllers\Api\SubpartSpecification\SubpartSpecificationController;
use App\Http\Controllers\Api\SubService\SubServiceController;
use App\Http\Controllers\Api\SubService\SubServiceImageController;
use App\Http\Controllers\Api\SubServiceApplication\SubServiceApplicationController;
use App\Http\Controllers\Api\SubServiceFeature\SubServiceFeatureController;
use App\Http\Controllers\Api\SubServiceModel\SubServiceModelController;
use App\Http\Controllers\Api\SubservienceDoc\SubservienceDocController;
use App\Http\Controllers\Api\SubservienceReview\SubservienceReviewController;
use App\Http\Controllers\Api\SubservienceSpecification\SubservienceSpecificationController;
use App\Http\Controllers\Api\Task\TaskCommentController;
use App\Http\Controllers\Api\Task\TaskController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::get('/allEmployees', [UserController::class, 'allEmployees']);
    Route::get('/allTechs', [UserController::class, 'allTechs']);
    Route::get('/allCustomers', [UserController::class, 'allCustomers']);
    Route::get('/{id}', [UserController::class, 'find']);
    // Route::put('/{id}', [UserController::class, 'update']);
    // update user data
    Route::put('/{user}', [UserController::class, 'update']);
    // update user status
    Route::post('/{user}/status', [UserController::class, 'updateUserStatus']);

    Route::post('/', [UserController::class, 'store']);
    // single user appointments
    Route::get('/{id}/appointments', [UserController::class, 'singleUserAppointments']);
    // single user tasks
    Route::get('/{id}/tasks', [UserController::class, 'singleUserTasks']);

    // Route::post('/auth/register', [UserController::class, 'register']);
});
// Route::post('/auth/register', [UserController::class, 'register']);

Route::post('/auth/send-otp', [UserController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [UserController::class, 'verifyOtp']);
Route::post('/auth/verify-pin', [UserController::class, 'verifyPinCode']);
Route::post('/auth/login', [UserController::class, 'login']);

Route::post('auth/update/pin', [UserController::class, 'updatePinCode'])->middleware('auth:sanctum');
// Route to request pin reset

Route::post('auth/pin-reset/request', [UserController::class, 'requestPinReset']);

Route::middleware(['auth:api', 'can:update users'])->group(function () {
    Route::post('auth/pin-reset/approve', [UserController::class, 'approveResetRequest']);
});

//  Customers routes
Route::middleware('auth:sanctum')->prefix('customers')->group(function () {
    Route::get('/all', [CustomerController::class, 'index']);
    Route::get('/{id}', [CustomerController::class, 'show']);
});

// Technicians routes
Route::middleware('auth:sanctum')->prefix('technician')->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::get('/all', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
});

// Address routes
Route::middleware('auth:sanctum')->prefix('addresses')->group(function () {
    Route::get('/', [AddressController::class, 'index']);
    Route::post('/', [AddressController::class, 'store']);
    Route::get('/{id}', [AddressController::class, 'show']);
    Route::put('/{id}', [AddressController::class, 'update']);
    Route::delete('/{id}', [AddressController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->get('/customer/addresses', [AddressController::class, 'getAuthCustomerAddresses']);
Route::middleware('auth:sanctum')->get('/customer/addresses/{id}', [AddressController::class, 'getCustomerAddresses']);

// Services routes
Route::apiResource('services', ServiceController::class);

// Service Types routes

Route::prefix('service-types')->controller(ServiceTypeController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::post('/{id}', 'update'); // أو put/patch حسب ستايل المشروع
    Route::delete('/{id}', 'destroy');
});

// Service Type Specifications routes
Route::apiResource('service-type-specifications', ServiceTypeSpecificationController::class);

// Service Images routes
Route::middleware('auth:sanctum')->prefix('services')->group(function () {
    Route::post('/{id}/images', [ServiceImageController::class, 'upload']);
    Route::delete('/images/{imageId}', [ServiceImageController::class, 'destroy']);
    Route::patch('/images/{imageId}/primary', [ServiceImageController::class, 'setPrimary']);
});

// Solutions routes

// Solutions CRUD
Route::apiResource('solutions', SolutionController::class);

// Solution images endpoints
Route::middleware('auth:sanctum')->prefix('solutions')->group(function () {
    Route::post('/{id}/images', [SolutionImageController::class, 'upload']);
    Route::delete('/images/{imageId}', [SolutionImageController::class, 'destroy']);
    Route::patch('/images/{imageId}/primary', [SolutionImageController::class, 'setPrimary']);
    Route::post('/{id}/icon', [SolutionController::class, 'updateIcon']);
});

// Parts routes
Route::apiResource('parts', PartController::class);
Route::apiResource('sub-parts', SubPartController::class);
Route::apiResource('subpart-specifications', SubpartSpecificationController::class);
Route::apiResource('subpart-reviews', SubpartReviewController::class);
Route::apiResource('subpart-docs', SubpartDocController::class);
Route::apiResource('subpart-features', SubpartFeatureController::class);
Route::apiResource('subpart-applications', SubpartApplicationController::class);
Route::apiResource('subpart-models', SubpartModelController::class);
// delete subpart image
Route::delete('sub-parts/images/{imageId}', [SubPartController::class, 'destroyImage']);
// Part images endpoints
Route::post('parts/{id}/images', [PartImageController::class, 'upload']);
Route::delete('parts/images/{imageId}', [PartImageController::class, 'destroy']);
Route::patch('parts/images/{imageId}/primary', [PartImageController::class, 'setPrimary']);

// Subjects routes
Route::apiResource('subjects', SubjectController::class);
// Keys routes
Route::apiResource('keys', KeyController::class);
// Contacts routes
Route::apiResource('contacts', ContactController::class);
// Contact Mail routes
Route::post('/contacts/send-mail', [ContactMailController::class, 'send'])->middleware('auth:sanctum');

// Generic activity log routes — works for any model using HasActivityLogs
// (currently: contact, task). Extend ActivityLogRepository::LOGGABLE_TYPES
// to add more, no new controller/route needed.
Route::get('/activity-logs', [ActivityLogController::class, 'index'])->middleware('auth:sanctum');

// Tasks routes — internal feature, everything requires auth.
Route::middleware('auth:sanctum')->group(function () {
    // These three must be registered before apiResource('tasks', ...) —
    // otherwise "assignees"/"user" would be captured by the {task} show
    // route as if they were a task ID.
    Route::get('tasks/assignees', [TaskController::class, 'usersWithTasks']);
    Route::get('tasks/assignees/{userId}', [TaskController::class, 'singleUserWithTasks']);
    Route::get('tasks/user/{userId}', [TaskController::class, 'userTasks']);

    Route::apiResource('tasks', TaskController::class);

    Route::get('tasks/{task}/comments', [TaskCommentController::class, 'index']);
    Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store']);
    Route::delete('task-comments/{comment}', [TaskCommentController::class, 'destroy']);
});

// Sub Services routes
Route::apiResource('sub-services', SubServiceController::class)->parameters([
    'sub-services' => 'sub_service',
]);

Route::post('sub-services/{id}/images', [SubServiceImageController::class, 'upload']);
Route::delete('sub-services/images/{imageId}', [SubServiceImageController::class, 'destroy']);
Route::patch('sub-services/images/{imageId}/primary', [SubServiceImageController::class, 'setPrimary']);

// FAQs routes for services and sub-services
Route::get('{type}/{id}/faqs', [FaqController::class, 'index']);
Route::post('{type}/{id}/faqs', [FaqController::class, 'store']);

Route::put('faqs/{id}', [FaqController::class, 'update']);
Route::delete('faqs/{id}', [FaqController::class, 'destroy']);
Route::get('faqs/{id}', [FaqController::class, 'show']);
Route::post('{type}/{id}/faqs/bulk', [FaqController::class, 'bulkStore']);
Route::put('{type}/{id}/faqs/bulk', [FaqController::class, 'bulkUpdate']);
Route::post('{type}/{id}/faqs/replace', [FaqController::class, 'bulkReplace']);

// sidebar menu modules and items routes
Route::apiResource('menu-modules', MenuModuleController::class)->parameters([
    'menu-modules' => 'menu_module',
]);

Route::apiResource('menu-items', MenuItemController::class)->parameters([
    'menu-items' => 'menu_item',
]);

Route::get('sidebar', [SidebarController::class, 'sidebar']);
Route::apiResource('subservience-specifications', SubservienceSpecificationController::class);
Route::post('subservience-specifications/bulk', [SubservienceSpecificationController::class, 'bulkStore']);
Route::apiResource('subservience-reviews', SubservienceReviewController::class);
Route::apiResource('subservience-docs', SubservienceDocController::class);
Route::apiResource('sub-service-features', SubServiceFeatureController::class);
Route::apiResource('sub-service-applications', SubServiceApplicationController::class);
Route::apiResource('sub-service-models', SubServiceModelController::class);
Route::get('sub-services/{id}/module/{module}', [SubServiceController::class, 'showByModule']);

// Banners routes for parts and sub-parts
Route::get('{type}/{id}/banners', [BannerController::class, 'index']);
Route::post('{type}/{id}/banners', [BannerController::class, 'store']);

Route::get('banners/{id}', [BannerController::class, 'show']);
Route::put('banners/{id}', [BannerController::class, 'update']);
Route::delete('banners/{id}', [BannerController::class, 'destroy']);

// Blogs routes
Route::prefix('blogs')->controller(BlogController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::post('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});

// Partners routes
Route::prefix('partners')->controller(PartnerController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{id}', 'show');
    Route::post('/', 'store');
    Route::post('/bulk', 'bulk');
    Route::post('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});

// routes/api.php
Route::post('/debug/s3-upload', [SidebarController::class, 'upload']);

// active items for select options
Route::post('/active/items', [SidebarController::class, 'index']);

// global search route
Route::get('search', [GlobalSearchController::class, 'search']);
