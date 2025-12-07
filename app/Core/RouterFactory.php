<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->addRoute('api/<table [a-z0-9-_]+>', 'Api:read');
		$router->addRoute('api/<table [a-z0-9-_]+>[/<id \d+>]', 'Api:read');
		// $router->addRoute('api/responses', 'Api:read');
		// $router->addRoute('api/<action>', 'Api:default');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
		return $router;
	}
}
