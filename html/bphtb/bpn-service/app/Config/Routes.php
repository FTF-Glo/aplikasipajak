<?php
	/*
		\d+ = One or more digits (0-9)
		\w+ = One or more word characters (a-z 0-9 _)
		[a-z0-9_-]+ = One or more word characters (a-z 0-9 _) and the dash (-)
		.* = Any character (including /), zero or more
		[^/]+ = Any character but /, one or more
	*/
	
	$router->get('/', '\Controller\AppController@index');
	$router->post('/getBPHTBService', '\Controller\AppController@inqueryBPHTB');
	$router->post('/getPBBService', '\Controller\AppController@inquiryPBB');
	$router->post('/postDataBPN', '\Controller\AppController@addDataBPN');
	$router->post('/getPPAT', '\Controller\AppController@getPPAT');
	
	$router->run();
?>
