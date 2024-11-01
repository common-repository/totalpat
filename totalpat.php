<?php
/*
Plugin Name: Totalpat
Plugin URI: http://www.totalpat.com
Description: Comunicación entre la WP y Totalpat para la retroalimentación de estadísticas, formularios y cookies.
Version: 2.0
Author: Totalpat, S.A. de C.V.
Author URI: http://www.totalpat.com
License: Sistema Totalpat
*/

/*  
Copyright 2015 TOTALPAT, S.A. DE C.V.  (email : develop@totalpat.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
*	TRAKING COOKIE
*/
require_once "data.config.php";
require_once("webservice/totalpat_lib/soap/nusoap.php");
require_once("webservice/totalpat_lib/browser.lib.php");
define('WP_MEMORY_LIMIT', '9600M');
ini_set('memory_limit', '6400M');

//define('WP_DEBUG', true);
//define('WP_DEBUG_DISPLAY', true);

add_action('init', function() {
	
    //información de browser
    $browser=new Browser();
    $plataforma=$browser->getPlatform();
    
    if(isset($_GET['campingTotalpat'])){
	$toCookie = array(
 	   array('campingTotalpat'=>$_GET['campingTotalpat'], 'botonTotalpat'=>$_GET['botonTotalpat'], 'linkTotalpat'=>$_GET['linkTotalpat'])
	);
	$json = json_encode($toCookie);
	setcookie("totalpat_user", $json, time() + (86400 * 3000), "/", false, 0);
    }
	
	
	/*
	*		SE GUARDA LA INFO PARA LAS ESTADISTICAS
	*/
	//IP
	$client  = @$_SERVER['HTTP_CLIENT_IP'];
	$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	$remote  = $_SERVER['REMOTE_ADDR'];
	if(filter_var($client, FILTER_VALIDATE_IP))			$ip = $client;
	elseif(filter_var($forward, FILTER_VALIDATE_IP))		$ip = $forward;
	else								$ip = $remote;
		
	//se guarda la info en la base de datos
	//nombre de la tabla
	global $wpdb;
	$table_name = $wpdb->prefix . 'totalpat_stats';
	
	/*//revisa si hay sesion o algo asi
	$get_content_info=get_content_info();
	$resource=get_request_uri();
	$referer=$_SERVER[ 'HTTP_REFERER' ];
	
	
	if($get_content_info['content_type']=='unknown' && 
			strpos( $resource, 'wp-admin/admin-ajax.php' ) === false && 
			strpos( $resource, 'wp-content' ) === false && 
			$referer!="" && 
			strpos( str_replace('/', '', $referer), str_replace('/', '', $resource) )=== false){
		
		//variables
		$arr_ip=get_remote_ip();
		$browser_traking=get_browser_totalpat();
		//$language = _get_language();
		
		
		//se guarda la info
		$wpdb->insert( 
			$table_name, 
			array( 
				'ip' => $arr_ip[0],
				'referer' => $referer,
				'resource' => $resource, 
				'browser_type'=>$browser_traking['browser_type'],
				'dt'=>date_i18n('U')
			) 
		);
	}
	*/
	

    if (!isset($_COOKIE['totalpat_traking'])) {
	
		//IP
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];
		if(filter_var($client, FILTER_VALIDATE_IP))			$ip = $client;
		elseif(filter_var($forward, FILTER_VALIDATE_IP))		$ip = $forward;
		else								$ip = $remote;
		
		//URL actual
		$url_cookie=$_SERVER['SCRIPT_URI'];
		if(isset($_SERVER['REDIRECT_QUERY_STRING'])){
			$url_cookie.='?'.$_SERVER['REDIRECT_QUERY_STRING'];
		}
		
		$toCookie = array(
		   array('date'=>date('Y-m-d H:i:s'), 'session'=>session_id(), 'url'=>$url_cookie, 'url_referer'=>$_SERVER['HTTP_REFERER'], 'ip'=>$ip, 'plataforma'=>$plataforma, 'campingTotalpat'=>$_GET['campingTotalpat'], 'botonTotalpat'=>$_GET['botonTotalpat'], 'linkTotalpat'=>$_GET['linkTotalpat'], 'nivel'=>0)
		);
		
		$json = json_encode($toCookie);
		setcookie("totalpat_traking", $json, time() + (86400 * 3000), "/", false, 0);
    }
    else{
		//se lee, limpia y desencripta la cookie
		$cookieValueArr=array();
		$cookieValue = $_COOKIE['totalpat_traking'];
		$cookieValue=implode("",explode("\\",$cookieValue));
		$cookieValue=stripslashes(trim($cookieValue));
		$cookieValueArr=json_decode( $cookieValue, TRUE );
		
		//IP
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];
		if(filter_var($client, FILTER_VALIDATE_IP))		$ip = $client;
		elseif(filter_var($forward, FILTER_VALIDATE_IP))		$ip = $forward;
		else		$ip = $remote;	
	
		//URL
		$url_cookie=$_SERVER['SCRIPT_URI'];
		if(isset($_SERVER['REDIRECT_QUERY_STRING'])){
			$url_cookie.='?'.$_SERVER['REDIRECT_QUERY_STRING'];
		}
	
		$texto_nuevo=array();
		for($i=0; $i<count($cookieValueArr); $i++){
			$texto_nuevo[$i]['date']=$cookieValueArr[$i]['date'];
			$texto_nuevo[$i]['session']=$cookieValueArr[$i]['session'];
			$texto_nuevo[$i]['url']=$cookieValueArr[$i]['url'];
			$texto_nuevo[$i]['url_referer']=$cookieValueArr[$i]['url_referer'];
			$texto_nuevo[$i]['ip']=$cookieValueArr[$i]['ip'];
			$texto_nuevo[$i]['plataforma']=$cookieValueArr[$i]['plataforma'];
			$texto_nuevo[$i]['campingTotalpat']=$cookieValueArr[$i]['campingTotalpat'];
			$texto_nuevo[$i]['botonTotalpat']=$cookieValueArr[$i]['botonTotalpat'];
			$texto_nuevo[$i]['linkTotalpat']=$cookieValueArr[$i]['linkTotalpat'];
			$texto_nuevo[$i]['nivel']=$cookieValueArr[$i]['nivel'];
		}
		
		if(!isset($_SERVER['HTTP_REFERER'])){
			$_SERVER['HTTP_REFERER']="";
		}
		if(!isset($_GET['botonTotalpat'])){
			$_GET['botonTotalpat']="";
		}
		if(!isset($_GET['campingTotalpat'])){
			$_GET['campingTotalpat']="";
		}
		if(!isset($_GET['linkTotalpat'])){
			$_GET['linkTotalpat']="";
		}
		
		$texto_nuevo[$i]['date']=date('Y-m-d H:i:s');
		$texto_nuevo[$i]['session']=session_id();
		$texto_nuevo[$i]['url']=$url_cookie;
		$texto_nuevo[$i]['url_referer']=$_SERVER['HTTP_REFERER'];
		$texto_nuevo[$i]['ip']=$ip;
		$texto_nuevo[$i]['plataforma']=$plataforma;
		$texto_nuevo[$i]['campingTotalpat']=$_GET['campingTotalpat'];
		$texto_nuevo[$i]['botonTotalpat']=$_GET['botonTotalpat'];
		$texto_nuevo[$i]['linkTotalpat']=$_GET['linkTotalpat'];
		$texto_nuevo[$i]['nivel']=$i;
	
		$json = json_encode($texto_nuevo);
		setcookie("totalpat_traking", $json, time() + (86400 * 3000), "/", false, 0);
    }
});

/*
*	CREACION DE LA BASE DE DATOS
*/
register_activation_hook( __FILE__, 'totalpat_install' );

global $totalpat_db_version;
$totalpat_db_version = '2.0';

function totalpat_install() {
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global $wpdb;
	global $jal_db_version;
	
	//tabla credenciales Totalpat
	$table_name = $wpdb->prefix . 'totalpat';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		activo mediumint(9) NOT NULL,
		token varchar(200) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";
	dbDelta( $sql );
	
	/*
	//tabla registro visitantes
	$table_name = $wpdb->prefix . 'totalpat_stats';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		ip varchar(39) NOT NULL,
		referer varchar(300) NOT NULL,
		resource varchar(300) NOT NULL,
		browser_type tinyint(3) NOT NULL,
		dt int(10) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";
	
	dbDelta( $sql );
	*/
	
	add_option( 'totalpat_db_version', $totalpat_db_version );
}



/*
* CREACION DEL MENU
*/
add_action('admin_menu', 'totalpat_plugin_setup_menu');
function totalpat_plugin_setup_menu(){
        add_menu_page( 'Totalpat Plugin Page', 'Totalpat', 'manage_options', 'totalpat-plugin', 'totalpat_console_init', plugin_dir_url( __FILE__ ).'totalPat_icono.ico', 80 );
}

function totalpat_console_init(){
	
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
	if($_POST['ssid']=="realizarDesconexionTokenTotalpat"){
		realizarDesconexionTokenTotalpat($_POST['inputTokenTotalpat']);
	}
	else if($_POST['ssid']=="realizarActivacionTokenTotalpat"){
		realizarActivacionTokenTotalpat($_POST['inputTokenTotalpat']);
	}
		
	
	//nombre de la tabla
	global $wpdb;
	$table_name = $wpdb->prefix . 'totalpat';
	
	//se busca en la tabla
	$results = $wpdb->get_results( "SELECT * FROM ".$table_name, OBJECT );
	
	$http_prefix="http://";
	if($_SERVER['HTTPS']=="on"){
		$http_prefix="https://";
	}
	$http_prefix.=$_SERVER['SERVER_NAME'];
					
	echo "<br><br><center><img src='../../wp-content/plugins/totalpat/images/totalPat_logo.png'><br><br><br></center>";

	echo '<center>
			<table width="450px" border="0" cellspacing="5" cellpadding="5">
			  <tr>
				<td colspan="2"><h2>Administración de acceso y control Totalpat</h2></td>
			  </tr>
			  <tr>
				<td style="width:200px;">Estatus de la licencia</td>
				<td id="banderaToken">';
			if($results[0]->activo==1){
				echo '<div style="background-color:#4ed771; padding:5px; color:#FFFFFF; width:100px;"><center>Activado</center></div></td>';
			}
			else{
				echo '<div style="background-color:#e73636; padding:5px; color:#FFFFFF; width:100px;"><center>Desactivado</center></div></td>';
			}
		echo '</tr>';
			if($results[0]->activo==1){
				echo '<td>';
				
				echo '<form action="'.$http_prefix.'/wp-admin/admin.php?page=totalpat-plugin" method="post">
  <input type="hidden" name="ssid" value="realizarDesconexionTokenTotalpat">
  <input type="hidden" name="inputTokenTotalpat" value="'.$results[0]->token.'">
  <input type="text" id="inputTokenTotalpat" value="'.'***************'.substr($results[0]->token,15).'" style="width:250px;" disabled />
  <input type="submit" value="Desconectar">
</form>';
				
				echo '</td>';
			}
			else{
				echo '<td>';
				echo '<form action="'.$http_prefix.'/wp-admin/admin.php?page=totalpat-plugin" method="post">
  <input type="hidden" name="ssid" value="realizarActivacionTokenTotalpat">
 
 <table width="100%" border="0" cellspacing="5" cellpadding="5">
  <tr>
    <td>Token</td>
    <td><input type="text" name="inputTokenTotalpat" value="" style="width:250px;" /></td>
  </tr>
</table>

  
  
  <input type="submit" value="Conectar">
</form>';
				echo'</td>';
				
			}
		echo '<td>&nbsp;</td>
			  </tr>';
		
		if($results[0]->activo==1){
			echo '<tr>
						<td colspan="2"><h3>Plugin necesarios</h3></td>
					</tr>';
			if(is_plugin_active( 'hotspots/hotspots.php' )){
				echo '<tr>
							<td>Hotspot</td>
							<td><div style="background-color:#4ed771; padding:5px; color:#FFFFFF; width:100px;"><center>Activado</center></div></td>
						</tr>';
			}
			else{
				echo '<tr>
							<td>Hotspot</td>
							<td><div style="background-color:#e73636; padding:5px; padding-right:10px; color:#FFFFFF; width:180px;"><center>Desactivado <a href="'.$http_prefix.'/wp-admin/plugin-install.php?tab=search&type=term&s=hotspot" style="float:right; color:#FFFFFF;">instalar</a></center></div></td>
						</tr>';
			}
			
			
			if(is_plugin_active( 'wp-slimstat/wp-slimstat.php' )){
				echo '<tr>
						<td>Slimstat</td>
						<td><div style="background-color:#4ed771; padding:5px; color:#FFFFFF; width:100px;"><center>Activado</center></div></td>
					</tr>';
			}
			else{
				echo '<tr>
						<td>Slimstat</td>
						<td><div style="background-color:#e73636; padding:5px; padding-right:10px; color:#FFFFFF; width:180px;"><center>Desactivado <a href="'.$http_prefix.'/wp-admin/plugin-install.php?tab=search&type=term&s=wp+slimstat" style="float:right; color:#FFFFFF;">instalar</a></center></div></td>
					</tr>';
			}
			
		}
			  
		echo '<tr>
				<td colspan="2">';
				
			if($results[0]->activo==1){
				$date = date_create($results[0]->time);
				echo '<br><br>La licencia ha sido activada desde el '.date_format($date, 'd-m-Y').'</td>';
			}
			else{
				echo '</td>';
			}
				
				
			  echo '</tr>
			</table></center>';
	
}


add_action( 'wpcf7_mail_sent', 'your_wpcf7_mail_sent_function' );
function your_wpcf7_mail_sent_function($contact_form) {
	
	//mail('iarellano@totalpat.com', 'WP detectado', "El titulo: " . print_r($_POST, true));
	
	//librerias
	require_once "data.config.php";
	require_once("webservice/totalpat_lib/soap/nusoap.php");
	
	//se limpia datos
	unset($datos);
	
	
	//se genera el arreglo de envio
	$i=0;
	$banderaGuardar=0;
	foreach($_POST as $field_id => $user_value ){
		if(strtolower($field_id)=="cliente" or $banderaGuardar==1){
			$banderaGuardar=1;
			
			$datos[0]['label_'.$i]=$field_id;
			$datos[0]['id_'.$i]=$user_value;
			$i++;
		}
	}

	
	//mail('iarellano@totalpat.com', 'WP detectado', "El titulo: " . print_r($datos, true));
	
	//cookies
	$cookieValue = $_COOKIE['totalpat_traking'];
	$datos[0]['cookie']=$cookieValue;
	//se limpia la cookie
	setcookie("totalpat_traking", '', time() + (86400 * 3000), "/", false, 0);
	
	if(isset($_COOKIE['totalpat_user'])){
	     $datos[0]['cookie_user']=$_COOKIE['totalpat_user'];
	}

	//llave
	$datos[0]['API_USER']=API_USER;
	$datos[0]['API_KEY']=API_KEY;

	//Conexión SOAP
	$client=new nusoap_client(SERVER_URL."/".SERVER_SCRIPT."?wsdl");
	$client->setCredentials(API_USER,API_KEY, SERVER_TYPE);

	//se manda el error
	$err = $client->getError();
	if ($err) {
		$mensaje="<h2>Mensaje de error en la conexión</h2><br>Sitio: ".$_SERVER['HTTP_HOST'].'<br>Fecha: ' . date('Y-m-d');
		mail('soporte@totalpat.com', 'WP desconectado', $mensaje);
	}
	else{
		$result = $client->call('addTotalpatComment', $datos);
	}
	
	
}


/*
*	DETECT NINJA FORMS
*/
add_action('ninja_forms_email_admin', 'enviarFormularioTotalpat');
function enviarFormularioTotalpat(){
	
	//librerias
	require_once "data.config.php";
	require_once("webservice/totalpat_lib/soap/nusoap.php");	
	
	//variables globales de ninja form
	global $ninja_forms_loading, $ninja_forms_processing;

	//Se obtienen las variables enviadas por el usuario
	$all_fields = $ninja_forms_processing->get_all_fields();
	
	//se limpia datos
	unset($datos);

	//se genera el arreglo de envio
	foreach( $all_fields as $field_id => $user_value ){
		$datos[0]['label_'.$field_id]=$ninja_forms_processing->get_field_setting($field_id, 'label');
		$datos[0]['id_'.$field_id]=$user_value;
	}
	
	//cookies
	$cookieValue = $_COOKIE['totalpat_traking'];
	$datos[0]['cookie']=$cookieValue;
	//se limpia la cookie
	setcookie("totalpat_traking", '', time() + (86400 * 3000), "/", false, 0);
	
	if(isset($_COOKIE['totalpat_user'])){
	     $datos[0]['cookie_user']=$_COOKIE['totalpat_user'];
	}

	//llave
	$datos[0]['API_USER']=API_USER;
	$datos[0]['API_KEY']=API_KEY;

	//Conexión SOAP
	$client=new nusoap_client(SERVER_URL."/".SERVER_SCRIPT."?wsdl");
	$client->setCredentials(API_USER,API_KEY, SERVER_TYPE);

	//se manda el error
	$err = $client->getError();
	if ($err) {
		$mensaje="<h2>Mensaje de error en la conexión</h2><br>Sitio: ".$_SERVER['HTTP_HOST'].'<br>Fecha: ' . date('Y-m-d');
		mail('soporte@totalpat.com', 'WP desconectado', $mensaje);
	}
	else{
		$result = $client->call('addTotalpatComment', $datos);
	}
}


function realizarActivacionTokenTotalpat($token){
	//llave
	$datos[0]['API_USER']=API_USER;
	$datos[0]['API_KEY']=API_KEY;

	//Conexión SOAP
	$client=new nusoap_client(SERVER_URL."/".SERVER_SCRIPT."?wsdl");
	$client->setCredentials(API_USER,API_KEY, SERVER_TYPE);
	
	//se manda el error
	$err = $client->getError();
	
	if ($err) {
		$mensaje="<h2>Mensaje de error en la conexión</h2><br>Sitio: ".$_SERVER['HTTP_HOST'].'<br>Fecha: ' . date('Y-m-d');
		mail('soporte@totalpat.com', 'WP desconectado', $mensaje);
		return 0;
	}
	else{
		$datos_activacion[0]['token_acceso']=$token;
		$datos_activacion[0]['url_peticion']=$_SERVER['HTTP_HOST'];			//desarrollo.totalpat.com
		$datos_activacion[0]['url_plugin_totalpat']=plugin_dir_url( __FILE__ );
		$result = $client->call('activateTotalpat', $datos_activacion);
		
		//se guarda en la base de datos
		if($result==1){
			
			//nombre de la tabla
			global $wpdb;
			$table_name = $wpdb->prefix . 'totalpat';
			
			//se limpia la tabla
			$delete = $wpdb->query("TRUNCATE TABLE `".$table_name."`");


			//se guarda la info
			$token = $_POST['inputTokenTotalpat'];
			$activo = 1;
			$wpdb->insert( 
				$table_name, 
				array( 
					'time' => current_time( 'mysql' ), 
					'activo' => $activo, 
					'token' => $token, 
				) 
			);
			return 1;
		}
		else{
			return 2;
		}
	}
}

function realizarDesconexionTokenTotalpat($token){
	
	//Conexión SOAP
	$client=new nusoap_client(SERVER_URL."/".SERVER_SCRIPT."?wsdl");
	$client->setCredentials(API_USER,API_KEY, SERVER_TYPE);
	
	//se manda el error
	$err = $client->getError();
	if ($err) {
		$mensaje="<h2>Mensaje de error en la conexión</h2><br>Sitio: ".$_SERVER['HTTP_HOST'].'<br>Fecha: ' . date('Y-m-d');
		mail('soporte@totalpat.com', 'WP desconectado', $mensaje);
		echo 0;
	}
	else{
		$datos_activacion[0]['token_acceso']=$token;
		$datos_activacion[0]['url_peticion']=$_SERVER['HTTP_HOST'];			//desarrollo.totalpat.com
		$result = $client->call('desactivateTotalpat', $datos_activacion);
	}
	
	//nombre de la tabla
	global $wpdb;
	$table_name = $wpdb->prefix . 'totalpat';
	
	//se limpia la tabla
	$delete = $wpdb->query("TRUNCATE TABLE `".$table_name."`");
}


/**
 * Tries to find the user's REAL IP address
 */
function get_remote_ip(){
	$ip_array = array( '', '' );

	if ( !empty( $_SERVER[ 'REMOTE_ADDR' ] ) && filter_var( $_SERVER[ 'REMOTE_ADDR' ], FILTER_VALIDATE_IP ) !== false ) {
		$ip_array[ 0 ] = $_SERVER["REMOTE_ADDR"];
	}

	if ( !empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) && filter_var( $_SERVER[ 'HTTP_CLIENT_IP' ], FILTER_VALIDATE_IP ) !== false ) {
		$ip_array[ 1 ] = $_SERVER["HTTP_CLIENT_IP"];
	}

	if ( !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) {
		foreach ( explode( ',', $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) as $a_ip ) {
			if ( filter_var( $a_ip, FILTER_VALIDATE_IP ) !== false ) {
				$ip_array[ 1 ] = $a_ip;
				break;
			}
		}
	}

	if ( !empty( $_SERVER[ 'HTTP_FORWARDED' ] ) && filter_var( $_SERVER[ 'HTTP_FORWARDED' ], FILTER_VALIDATE_IP ) !== false ) {
		$ip_array[ 1 ] = $_SERVER[ 'HTTP_FORWARDED' ];
	}

	if ( !empty( $_SERVER[ 'HTTP_X_FORWARDED' ] ) && filter_var( $_SERVER[ 'HTTP_X_FORWARDED' ], FILTER_VALIDATE_IP ) !== false ) {
		$ip_array[ 1 ] = $_SERVER[ 'HTTP_X_FORWARDED' ];
	}

	return $ip_array;
}
// end _get_remote_ip

/**
	 * Retrieves some information about the user agent; relies on browscap.php database (included)
	 */
function get_browser_totalpat(){
	$browser = array( 'browser' => 'Default Browser', 'browser_version' => '', 'browser_type' => 1, 'platform' => 'unknown', 'user_agent' => '' );

	// Automatically detect the useragent
	if ( !isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) {
		return $browser;
	}

	$browser['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	$search = array();

	for ( $idx_cache = 1; $idx_cache <= 8; $idx_cache++ ) {
		@include(plugin_dir_path( __FILE__ )."browscap/browscap-$idx_cache.php");
		foreach ($patterns as $pattern => $pattern_data){
			if (preg_match($pattern . 'i', $_SERVER['HTTP_USER_AGENT'], $matches)){
				if (1 == count($matches)) {
					$key = $pattern_data;
					$simple_match = true;
				}
				else{
					$pattern_data = unserialize($pattern_data);
					array_shift($matches);
					
					$match_string = '@' . implode('|', $matches);

					if (!isset($pattern_data[$match_string])){
						continue;
					}

					$key = $pattern_data[$match_string];

					$simple_match = false;
				}

				$search = array(
					$_SERVER['HTTP_USER_AGENT'],
					trim(strtolower($pattern), '@'),
					_preg_unquote($pattern, $simple_match ? false : $matches)
				);

				$search = $value = $search + unserialize($browsers[$key]);

				while (array_key_exists(3, $value)) {
					$value = unserialize($browsers[$value[3]]);
					$search += $value;
				}

				if (!empty($search[3]) && array_key_exists($search[3], $userAgents)) {
					$search[3] = $userAgents[$search[3]];
				}

				break;
			}
		}

		unset( $browsers );
		unset( $userAgents );
		unset( $patterns );

		// Add the keys for each property
		$search_normalized = array();
		foreach ($search as $key => $value) {
			if ($value === 'true') {
				$value = true;
			} elseif ($value === 'false') {
				$value = false;
			}
			$search_normalized[strtolower($properties[$key])] = $value;
		}

		if (!empty($search_normalized) && $search_normalized['browser'] != 'Default Browser' && $search_normalized['browser'] != 'unknown'){
			$browser['browser'] = $search_normalized['browser'];
			$browser['browser_version'] = floatval($search_normalized['version']);
			$browser['platform'] = strtolower($search_normalized['platform']);
			$browser['user_agent'] =  $search_normalized['browser_name'];

			// browser Types:
			//		0: regular
			//		1: crawler
			//		2: mobile
			//		3: syndication reader
			if ($search_normalized['ismobiledevice'] || $search_normalized['istablet']){
				$browser['browser_type'] = 2;
			}
			elseif ($search_normalized['issyndicationreader']){
				$browser['browser_type'] = 3;
			}
			elseif (!$search_normalized['crawler']){
				$browser['browser_type'] = 0;
			}

			if ($browser['browser_version'] != 0 || $browser['browser_type'] != 0){
				return $browser;
			}
		}
	}

	// Let's try with the heuristic approach ( portions of code from https://github.com/donatj/PhpUserAgent )
	$browser['browser_type'] = 0;
	
	if( preg_match('/\((.*?)\)/im', $_SERVER['HTTP_USER_AGENT'], $parent_matches) ) {

		preg_match_all('/(?P<platform>BB\d+;|Android|CrOS|iPhone|iPad|Linux|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|(New\ )?Nintendo\ (WiiU?|3DS)|Xbox(\ One)?)
				(?:\ [^;]*)?
				(?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);

		$priority = array( 'Xbox One', 'Xbox', 'Windows Phone', 'Tizen', 'Android' );
		$result['platform'] = array_unique($result['platform']);
		if (count($result['platform']) > 1 && ($keys = array_intersect($priority, $result['platform']))){
			$browser['platform'] = reset($keys);
		}
		elseif (isset($result['platform'][0]) && in_array($result['platform'][0], $priority)){
			$browser['platform'] = $result['platform'][0];
		}
	}

	preg_match_all('%(?P<browser>Camino|Kindle(\ Fire\ Build)?|Firefox|Iceweasel|Safari|MSIE|Trident|AppleWebKit|TizenBrowser|Chrome|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|Baiduspider|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|NintendoBrowser|PLAYSTATION\ (\d|Vita)+)(?:\)?;?)(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
		$_SERVER['HTTP_USER_AGENT'], $match, PREG_PATTERN_ORDER);

	// If nothing matched, return null (to avoid undefined index errors)
	if (!isset($match['browser'][0]) || !isset($match['version'][0])){
		return $browser;
	}

	if (preg_match('/rv:(?P<version>[0-9A-Z.]+)/si', $_SERVER['HTTP_USER_AGENT'], $rv_result)){
		$rv_result = $rv_result['version'];
	}

	$browser['browser'] = $match['browser'][0];
	$browser['browser_version'] = $match['version'][0];

	$ekey = 0;
	if ($browser['browser'] == 'Iceweasel'){
		$browser['browser'] = 'Firefox';
	}
	elseif (_heuristic_find('Playstation Vita', $match)){
		$browser['platform'] = 'CellOS';
		$browser['browser'] = 'PlayStation';
		$browser['browser_type'] = 2;
	}
	elseif ( _heuristic_find( 'Kindle Fire Build', $match ) || _heuristic_find( 'Silk', $match ) ) {
		$browser['browser'] = $match['browser'][$heuristic_key] == 'Silk' ? 'Silk' : 'Kindle';
		$browser['platform'] = 'Android';
		if ($browser['browser_version'] != $match['version'][$heuristic_key] || !is_numeric($browser['browser_version'][0])){
			$browser['browser_version'] = $match['version'][array_search('Version', $match['browser'])];
		}
		$browser['browser_type'] = 2;
	}
	elseif ( _heuristic_find( 'Kindle', $match ) ) {
		$browser['browser'] = $match['browser'][$heuristic_key];
		$browser['platform'] = 'Android';
		$browser['browser_version']  = $match['version'][$heuristic_key];
		$browser['browser_type'] = 2;
	}
	elseif ( _heuristic_find( 'OPR', $match ) ) {
		$browser['browser'] = 'Opera';
		$browser['browser_version'] = $match['version'][$heuristic_key];
	}
	elseif ( _heuristic_find( 'Opera', $match ) ) {
		$browser['browser'] = 'Opera';
		_heuristic_find('Version', $match);
		$browser['browser_version'] = $match['version'][$heuristic_key];
	}
	elseif (_heuristic_find('Midori', $match)){
		$browser['browser'] = 'Midori';
		$browser['browser_version'] = $match['version'][$heuristic_key];
	}
	elseif ($browser['browser'] == 'MSIE' || ($rv_result && _heuristic_find('Trident', $match)) || _heuristic_find('Edge', $match)){
		$browser['browser'] = 'IE';
		if( _heuristic_find('IEMobile', $match) ) {
			$browser['browser'] = 'IE';
			$browser['browser_version'] = $match['version'][$heuristic_key];
			$browser['browser_type'] = 2;
		} else {
			$browser['browser_version'] = $rv_result ? $rv_result : $match['version'][$heuristic_key];
		}
	} elseif( _heuristic_find('Vivaldi', $match) ) {
		$browser['browser'] = 'Vivaldi';
		$browser['browser_version'] = $match['version'][$heuristic_key];
	} elseif( _heuristic_find('Chrome', $match) ) {
		$browser['browser'] = 'Chrome';
		$browser['browser_version'] = $match['version'][$heuristic_key];
	} elseif( $browser['browser'] == 'AppleWebKit' ) {
		if( ($browser['platform'] == 'Android' && !($heuristic_key = 0)) ) {
			$browser['browser'] = 'Android Browser';
			$browser['browser_type'] = 2;
		} elseif( strpos($browser['platform'], 'BB') === 0 ) {
			$browser['browser']  = 'BlackBerry';
			$browser['platform'] = 'RIM OS';
			$browser['browser_type'] = 2;
		} elseif( $browser['platform'] == 'BlackBerry' || $browser['platform'] == 'PlayBook' ) {
			$browser['browser'] = 'BlackBerry';
			$browser['browser_type'] = 2;
		} elseif( _heuristic_find('Safari', $match) ) {
			$browser['browser'] = 'Safari';
		} elseif( _heuristic_find('TizenBrowser', $match) ) {
			$browser['browser'] = 'TizenBrowser';
		}

		_heuristic_find('Version', $match);

		$browser['browser_version'] = $match['version'][$heuristic_key];
	} elseif( $heuristic_key = preg_grep('/playstation \d/i', array_map('strtolower', $match['browser'])) ) {
		$heuristic_key = reset($heuristic_key);
		$heuristic_key = reset($heuristic_key);

		$browser['platform'] = 'CellOS';
		$browser['browser']  = 'NetFront';
	}

	if( $browser['platform'] == 'linux-gnu' ) {
		$browser['platform'] = 'Linux';
	} elseif( $browser['platform'] == 'CrOS' ) {
		$browser['platform'] = 'ChromeOS';
	}

	$browser['browser_version'] = floatval($browser['browser_version']);
	$browser['platform'] = strtolower($browser['platform']);

	if ($browser['platform'] == 'unknown'){
		$browser['browser_type'] = 1;
		$browser['browser_version'] = 0;
	}

	return $browser;
}
// end _get_browser

/**
 * Helper function for get_browser [ courtesy of: https://github.com/donatj/PhpUserAgent ]
 */
function _heuristic_find( $search, $match ) {
	$xkey = array_search( strtolower( $search ), array_map( 'strtolower', $match[ 'browser' ] ) );
	if ( $xkey !== false ) {
		$heuristic_key = $xkey;
		return true;
	}
	return false;
}
/**
 * Helper function for get_browser [ courtesy of: GaretJax/PHPBrowsCap ]
 */
function _preg_unquote($pattern, $matches){
	$search = array('\\@', '\\.', '\\\\', '\\+', '\\[', '\\^', '\\]', '\\$', '\\(', '\\)', '\\{', '\\}', '\\=', '\\!', '\\<', '\\>', '\\|', '\\:', '\\-', '.*', '.', '\\?');
	$replace = array('@', '\\?', '\\', '+', '[', '^', ']', '$', '(', ')', '{', '}', '=', '!', '<', '>', '|', ':', '-', '*', '?', '.');

	$result = substr(str_replace($search, $replace, $pattern), 2, -2);

	if (!empty($matches)){
		foreach ($matches as $one_match){
			$num_pos = strpos($result, '(\d)');
			$result = substr_replace($result, $one_match, $num_pos, 4);
		}
	}

	return $result;
}


/**
 * Returns details about the resource being accessed
 */
function get_content_info(){
	$content_info = array( 'content_type' => 'unknown' );

	// Mark 404 pages
	if ( is_404() ) {
		$content_info[ 'content_type' ] = '404';
	}

	// Type
	else if ( is_single() ) {
		if (($post_type = get_post_type()) != 'post') {
			$post_type = 'cpt:'.$post_type;
		}

		$content_info['content_type'] = $post_type;
		$content_info_array = array();
		foreach (get_object_taxonomies($GLOBALS['post']) as $a_taxonomy){
			$terms = get_the_terms($GLOBALS['post']->ID, $a_taxonomy);
			if (is_array($terms)){
				foreach ($terms as $a_term) $content_info_array[] = $a_term->term_id;
				$content_info['category'] = implode(',', $content_info_array);
			}
		}
		$content_info['content_id'] = $GLOBALS['post']->ID;
	}
	elseif (is_page()){
		$content_info['content_type'] = 'page';
		$content_info['content_id'] = $GLOBALS['post']->ID;
	}
	elseif (is_attachment()){
		$content_info['content_type'] = 'attachment';
	}
	elseif (is_singular()){
		$content_info['content_type'] = 'singular';
	}
	elseif (is_post_type_archive()){
		$content_info['content_type'] = 'post_type_archive';
	}
	elseif (is_tag()){
		$content_info['content_type'] = 'tag';
		$list_tags = get_the_tags();
		if (is_array($list_tags)){
			$tag_info = array_pop($list_tags);
			if (!empty($tag_info)) $content_info['category'] = "$tag_info->term_id";
		}
	}
	elseif (is_tax()){
		$content_info['content_type'] = 'taxonomy';
	}
	elseif (is_category()){
		$content_info['content_type'] = 'category';
		$list_categories = get_the_category();
		if (is_array($list_categories)){
			$cat_info = array_pop($list_categories);
			if (!empty($cat_info)) $content_info['category'] = "$cat_info->term_id";
		}
	}
	elseif (is_date()){
		$content_info['content_type']= 'date';
	}
	elseif (is_author()){
		$content_info['content_type'] = 'author';
	}
	elseif (is_archive()){
		$content_info['content_type'] = 'archive';
	}
	elseif (is_search()){
		$content_info['content_type'] = 'search';
	}
	elseif (is_feed()){
		$content_info['content_type'] = 'feed';
	}
	elseif ( is_home() || is_front_page() ){
		$content_info['content_type'] = 'home';
	}
	elseif ( !empty( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] == 'wp-login.php' ) {
		$content_info['content_type'] = 'login';
	}
	elseif ( !empty( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] == 'wp-register.php' ) {
		$content_info['content_type'] = 'registration';
	}
	// WordPress sets is_admin() to true for all ajax requests ( front-end or admin-side )
	elseif ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
		$content_info[ 'content_type' ] = 'admin';
	}

	if (is_paged()){
		$content_info[ 'content_type' ] .= ',paged';
	}

	// Author
	if ( is_singular() ) {
		$author = get_the_author_meta( 'user_login', $GLOBALS[ 'post' ]->post_author );
		if ( !empty( $author ) ) {
			$content_info[ 'author' ] = $author;
		}
	}

	return $content_info;
}
// end _get_content_info


/**
 * Extracts the accepted language from browser headers
 */
function _get_language(){
	if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){

		// Capture up to the first delimiter (, found in Safari)
		preg_match("/([^,;]*)/", $_SERVER["HTTP_ACCEPT_LANGUAGE"], $array_languages);

		// Fix some codes, the correct syntax is with minus (-) not underscore (_)
		return str_replace( "_", "-", strtolower( $array_languages[0] ) );
	}
	return 'xx';  // Indeterminable language
}
// end _get_language
	
/**
	 * Decodes the permalink
	 */
function get_request_uri(){
	if (isset($_SERVER['REQUEST_URI'])){
		return urldecode($_SERVER['REQUEST_URI']);
	}
	elseif (isset($_SERVER['SCRIPT_NAME'])){
		return isset($_SERVER['QUERY_STRING'])?$_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING']:$_SERVER['SCRIPT_NAME'];
	}
	else{
		return isset($_SERVER['QUERY_STRING'])?$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']:$_SERVER['PHP_SELF'];
	}
}
// end get_request_uri
	
?>