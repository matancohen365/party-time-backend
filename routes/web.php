<?php


Route::group(
    ['prefix' => 'v2'],
    function () {
        Route::get('info', ['as' => 'APIv2.info', 'uses' => 'Api2Controller@info']);
        Route::get('download', ['as' => 'APIv2.download', 'uses' => 'Api2Controller@download']);
    }
);

Route::group(
    ['prefix' => 'android_api_v1'],
    function () {
        Route::get('search', 'ApiController@search');
    }
);

Route::any('/{any}', function(){
    return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>domain for sale</title></head><body><div style="text-align: center;">
<h1>This domain (baba.co.il) is for sale</h1>
<p>offer me: <a href="mailto:matan.cohen.365@gmail.com?subject=baba.co.il+new+offer">matan.cohen.365@gmail.com</a></p>
</div></body></html>';
})->where('any', '.*');