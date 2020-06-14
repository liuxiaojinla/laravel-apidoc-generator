<?php

namespace Xin\ApiDoc;

use Illuminate\Support\ServiceProvider;
use Xin\ApiDoc\Commands\GenerateDocumentation;
use Xin\ApiDoc\Matching\RouteMatcher;
use Xin\ApiDoc\Matching\RouteMatcherInterface;

class ApiDocGeneratorServiceProvider extends ServiceProvider{

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot(){
		$this->loadViewsFrom(__DIR__.'/../resources/views/', 'apidoc');

		$this->publishes([
			__DIR__.'/../resources/views' => $this->app->basePath('resources/views/vendor/apidoc'),
			__DIR__.'/../resources/assets' => $this->app->basePath('public/vendor/apidoc'),
		], 'apidoc-views');

		$this->publishes([
			__DIR__.'/../config/apidoc.php' => $this->app->configPath('apidoc.php'),
		], 'apidoc-config');

		$this->mergeConfigFrom(__DIR__.'/../config/apidoc.php', 'apidoc');

		$this->bootRoutes();

		if($this->app->runningInConsole()){
			$this->commands([
				GenerateDocumentation::class,
			]);
		}

		// Bind the route matcher implementation
		$this->app->bind(RouteMatcherInterface::class, config('apidoc.routeMatcher', RouteMatcher::class));
	}

	/**
	 * Initializing routes in the application.
	 */
	protected function bootRoutes(){
		if(config('apidoc.laravel.autoload', false)){
			$this->loadRoutesFrom(
				__DIR__.'/../routes/laravel.php'
			);
		}
	}
}
