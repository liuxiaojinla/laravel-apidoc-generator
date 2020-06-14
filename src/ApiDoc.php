<?php

namespace Xin\ApiDoc;

use Illuminate\Support\Facades\Route;

class ApiDoc{

	/**
	 * Binds the ApiDoc routes into the controller.
	 *
	 * @param string $path
	 * @deprecated Use autoload routes instead (`config/apidoc.php`: `laravel > autoload`).
	 */
	public static function routes($path = '/doc'){
		Route::prefix($path)
			->namespace('\Xin\ApiDoc\Http')
			->middleware(static::middleware())
			->group(function(){
				Route::get('/', 'Controller@html')->name('apidoc');
				Route::get('.json', 'Controller@json')->name('apidoc.json');
			});
	}

	/**
	 * Get the middlewares for Laravel routes.
	 *
	 * @return array
	 */
	protected static function middleware(){
		return config('apidoc.laravel.middleware', []);
	}
}
