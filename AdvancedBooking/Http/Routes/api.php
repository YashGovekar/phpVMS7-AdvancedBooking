<?php

/**
 * This is publicly accessible
 */
Route::group(['middleware' => ['api.auth']], function() {
    Route::get('/', 'ApiController@index');
    Route::post('/findRoutes', 'ApiController@findRoutes');
});

/**
 * This is required to have a valid API key
 */
Route::group(['middleware' => [
    'api.auth'
]], function() {
    Route::get('/hello', 'ApiController@hello');
});
