<?php

Route::prefix('v1')->group(function () {

    /**** Auth ****/
    Route::namespace('API\v1\Auth')->group(function () {
        Route::post('login', 'LoginController@login');
        Route::post('logout', 'LoginController@logout');
        // Route::post('token/refresh', 'LoginController@refresh');
        Route::post('register', 'RegistrationController@register');
        Route::post('forgot-password', 'ForgotPasswordController@sendResetLink');
        Route::post('reset-password', 'ForgotPasswordController@resetPassword');

        Route::get('verify-email', 'VerificationController@verifyEmail')->name('verification.verify');
        Route::post('verify-email', 'VerificationController@sendVerificationEmail');
    });

    /**** User ****/
    Route::namespace('API\v1\User')->group(function () {
        Route::get('users', 'UserController@list');
        Route::get('users/{user}', 'UserController@details');
        Route::get('profile', 'UserController@profile');
        Route::post('users/{user}/reset-password', 'UserController@resetPassword');
        Route::patch('profile', 'UserController@updateProfile');
        Route::patch('users/{user}', 'UserController@update');

        Route::patch('users/{user}/avatar', 'UserController@uploadUserAvatar');
        Route::patch('profile/avatar', 'UserController@uploadProfileAvatar');
    });

    /**** UserGroup ****/
    Route::namespace('API\v1\UserGroup')->group(function () {
        
        Route::get('usergroups', 'UserGroupController@list');
        Route::get('usergroups/{userGroup}', 'UserGroupController@details');
        Route::post('usergroups', 'UserGroupController@create');
        Route::post('usergroups/exists', 'UserGroupController@exists');
        Route::patch('usergroups/{userGroup}', 'UserGroupController@update');
        Route::delete('usergroups/{userGroup}', 'UserGroupController@delete');
    });

    /**** SystemSettings ****/
    Route::namespace('API\v1\SystemSetting')->group(function () {
        Route::get('systemsettings', 'SystemSettingController@list');
        Route::get('systemsettings/allowpublicregistration', 'SystemSettingController@allowPublicRegistration');
        Route::patch('systemsettings', 'SystemSettingController@update');
    });

    /**** Permissions ****/
    Route::namespace('API\v1\Permission')->group(function () {
        Route::get('permissions', 'PermissionController@list');
    });

    /**** Workflows ****/
    Route::namespace('API\v1\Workflow')->group(function () {
        Route::get('workflows', 'WorkflowController@list');
        Route::get('workflows/sidemenu', 'WorkflowController@workflow_menu');
        Route::get('workflows/{workflow}', 'WorkflowController@details');
        Route::post('workflows', 'WorkflowController@create');
        Route::post('workflows/exists', 'WorkflowController@exists');
        Route::patch('workflows/{workflow}', 'WorkflowController@update');
        Route::patch('workflows/{workflow}/activate', 'WorkflowController@activate');
        Route::patch('workflows/{workflow}/deactivate', 'WorkflowController@deactivate');
        Route::delete('workflows/{workflow}', 'WorkflowController@delete');
    });

    /**** Orders ****/
    Route::namespace('API\v1\Order')->group(function () {
        Route::get('workflows/{workflow}/orders', 'OrderController@list');
        Route::get('workflows/{workflow}/orders/{id}', 'OrderController@details');
        Route::post('workflows/{workflow}/orders', 'OrderController@create');
        Route::post('workflows/{workflow}/orders/exists', 'OrderController@exists');
        Route::patch('workflows/{workflow}/orders/{id}/updateprocess', 'OrderController@updateProcess');
        Route::delete('workflows/{workflow}/orders/{id}', 'OrderController@delete');
    });
});
