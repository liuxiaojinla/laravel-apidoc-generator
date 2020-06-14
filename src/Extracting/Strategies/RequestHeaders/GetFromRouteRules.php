<?php

namespace Xin\ApiDoc\Extracting\Strategies\RequestHeaders;

use Illuminate\Routing\Route;
use ReflectionClass;
use ReflectionMethod;
use Xin\ApiDoc\Extracting\Strategies\Strategy;

class GetFromRouteRules extends Strategy{

	public function __invoke(Route $route, ReflectionClass $controller, ReflectionMethod $method, array $routeRules, array $context = []){
		return $routeRules['headers'] ?? [];
	}
}
