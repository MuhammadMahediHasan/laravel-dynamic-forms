<?php

use Illuminate\Support\Facades\Route;
use MuhammadMahediHasan\Df\Http\Controllers\Api\FormBuilderApiController;
use MuhammadMahediHasan\Df\Http\Controllers\Api\FormSubmissionApiController;

$prefix     = config('dynamic-forms.route_prefix', 'api/v1');
$middleware = config('dynamic-forms.middleware', ['api']);
$authGuard  = config('dynamic-forms.auth_guard');

// Append the auth guard middleware when configured
if ($authGuard) {
    $middleware[] = 'auth:' . $authGuard;
}

Route::group(['prefix' => $prefix, 'middleware' => $middleware], function () {
    Route::prefix('df')->group(function () {
        // Config & input catalog
        Route::get('config', [FormBuilderApiController::class, 'getConfig']);
        Route::get('inputs', [FormBuilderApiController::class, 'getInputs']);

        // Builder CRUD
        Route::get('/', [FormBuilderApiController::class, 'index']);
        Route::post('/', [FormBuilderApiController::class, 'store']);
        Route::get('{form}', [FormBuilderApiController::class, 'show']);
        Route::put('{form}', [FormBuilderApiController::class, 'update']);
        Route::delete('{form}', [FormBuilderApiController::class, 'destroy']);

        // Submission
        Route::post('{form}/submissions', [FormSubmissionApiController::class, 'store']);
    });
});
