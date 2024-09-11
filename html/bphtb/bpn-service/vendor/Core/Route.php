<?php
namespace Core;

Class Route{
	
	/*
	private static function verifyCsrf(){
		if(Request::type('post')){
			
			$csrf = @$_SERVER['HTTP_X_CSRF_TOKEN'];
			$csrf = empty($csrf)? @$_POST['_token'] : $csrf;
			
			if(empty($csrf) || csrf_token() !=$csrf){
				//die('warning: csrf token not valid!');
			}
		}
	}
	*/
	
	public static function autoload(){
		//self::verifyCsrf();
		
		// In case one is using PHP 5.4's built-in server
		$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
		if (php_sapi_name() === 'cli-server' && is_file($filename)) {
			return false;
		}

		// Create a Router
		$router = new \Bramus\Router\Router();

		// Custom 404 Handler
		$router->set404(function () {
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
			echo '404, service/route not found!';
		});

		// Before Router Middleware
		$router->before('GET', '/.*', function () {
			header('X-Powered-By: bramus/router');
		});
	
		require_once PATH.'/app/Config/Routes.php';
	}
}
	
?>
