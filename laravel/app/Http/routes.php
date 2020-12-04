<?php
 
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::auth();
 

/**
 * auth wrapper for all internal pages.
 */
Route::group(['middleware'=>['auth']], function() {
   /******************************************************************************
   * Utilities (/utilities)
   ******************************************************************************/
   Route::group(
     [ 'prefix'=>'utilities',
        'as'=>'utilities::'], function () {
		//print-reports
            	Route::group([
                	'middleware'=>['permission:printing'],
                	'as'        => 'print-reports',
                	'prefix'    => 'print-reports'
                	], function() {
                    		Route::post('/', ['uses'=>'Utilities\PrintReportController@index', 'as'=>'reports']);
                    		Route::any('params', ['uses'=>'Utilities\PrintReportController@params', 'as'=>'params']);
                    		Route::post('report', ['uses'=>'Utilities\PrintReportController@report', 'as'=>'report']);
            	});

        }
   );


});
