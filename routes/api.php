<?php

use Illuminate\Support\Facades\Route;
use MuhammadMahediHasan\Df\Http\Controllers\Api\FormBuilderApiController;
use MuhammadMahediHasan\Df\Http\Controllers\Api\FormSubmissionApiController;

$prefix = config('dynamic-forms.route_prefix', 'api/v1');
$middleware = config('dynamic-forms.middleware', ['api']);

Route::group(['prefix' => $prefix, 'middleware' => $middleware], function () {
    // Builder endpoints
    Route::get('form-inputs', [FormBuilderApiController::class, 'getInputs']);
    Route::post('dynamic-forms', [FormBuilderApiController::class, 'store']);
    Route::put('dynamic-forms/{form}', [FormBuilderApiController::class, 'update']);
    Route::get('dynamic-forms/{form}', [FormBuilderApiController::class, 'show']);

    // Submission endpoints
    Route::post('dynamic-forms/{form}/submissions', [FormSubmissionApiController::class, 'store']);
});
