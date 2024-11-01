<?php
	
	
	/*****************
	****	VERSION TOTALPAT SINCRONIZAR SERVER
	*****************/
	define('versionTotal', "1.0");
	
	/*****************
	****	HEADERS
	***************** /
	header("Content-Type: text/html; charset=UTF-8");
	header("Pragma: public"); // required
	header("Expires: 0"); 
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	header("Cache-Control: private",false); // required for certain browsers 
	*/
	date_default_timezone_set('America/Mexico_City');
	
	
	//nombre de la tabla
	global $wpdb;
	$table_name = $wpdb->prefix . 'totalpat';
	//se busca en la tabla
	$results = $wpdb->get_results( "SELECT * FROM ".$table_name, OBJECT );
	
	
	//LLAVES DE ACCESO
	define("API_USER", "TOTALPAT_TOKEN");
	define("API_KEY", $results[0]->token);
	define("REMOTE_ADDR", "208.113.205.189");
	
	//TABLAS DE LAS BASE DE DATOS
	define("TABLA_SLIM", $table_prefix."slim_stats");
	define("TABLA_HOTSPOT", $table_prefix."ha_user_event");
	
	//variables SOAP
	define('SERVER_URL' , 'http://webservice.totalpat.com');
	define('SERVER_SCRIPT' , 'totalpatWP.php');
	define('SERVER_TYPE' , 'basic');
	//$serverURL = "http://webservice.totalpat.com";
	//$serverScript = 'contactForm.php';
	//$authtype = "basic";
	
	/*****************
	****	BASE DE DTAOS
	*****************/
	define("DB_HOST_TOTALPAT",DB_HOST);
	define("DB_TOTALPAT",DB_NAME);
	define("DB_USER_TOTALPAT",DB_USER);
	define("DB_PASSWD_TOTALPAT",DB_PASSWORD);
	
	include("webservice/totalpat_lib/base.lib.php");
	
?>