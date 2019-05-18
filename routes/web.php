<?php

Route::group(
    ['prefix' => 'v2'],
    function () {
        Route::get('info', ['as' => 'APIv2.info', 'uses' => 'Api2Controller@info']);
        Route::get('download', ['as' => 'APIv2.download', 'uses' => 'Api2Controller@download']);
    }
);
