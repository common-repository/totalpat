<?php
	
	//Librerias
	require_once("../data.config.webservice.php");
	require_once "totalpat_lib/base.lib.php";
	require_once "totalpat_lib/soap/nusoap.php";
		
	//FUNCIONES
	function getEstadisticas($datos){
		
		//variables
		$datosFinales=array();
		$totalIP=0;
		$totalDividendo=0;
		$totalDivisor=0;
		$totalDispositivos=0;
		$totalClick=0;
		$totalVisitas=0;
		
		
		if($datos['API_KEY']==API_KEY){
			
			
			//SE PONDRA LA LISTA DE IPS PARA SACAR EL NUMERO DE DIFERENTES
			$query="SELECT count(*) AS total, ip, DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d') AS dt, DATE_FORMAT(FROM_UNIXTIME(dt), '%Y%m%d') AS dt_simple 
					FROM ".TABLA_SLIM." 
					WHERE FROM_UNIXTIME(dt)>'".$datos['fecha']."' AND DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d')<>DATE_FORMAT(now(), '%Y-%m-%d')
					GROUP BY ip, DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d')
					ORDER BY dt";
			$lista=consultar($query);
			
			for($i=0; $i<count($lista); $i++){
				$datosFinales['ip'][$i]['total']=$lista[$i]['total'];
				$datosFinales['ip'][$i]['ip']=$lista[$i]['ip'];
				$datosFinales['ip'][$i]['date']=$lista[$i]['dt'];
				$totalIP+=$lista[$i]['total'];
				//datos generales
				$datosFinales['general'][$lista[$i]['dt_simple']]['ip']+=$lista[$i]['total'];
				$datosFinales['general'][$lista[$i]['dt_simple']]['fecha']=$lista[$i]['dt'];
				$datosFinales['general'][$lista[$i]['dt_simple']]['count']++;
				
			}
			
			//RESULTADOS PARA EL PROMEDIO POR CLICK
			$query="SELECT count(*) AS total,  DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d') AS dt, DATE_FORMAT(FROM_UNIXTIME(dt), '%Y%m%d') AS dt_simple    
					FROM ".TABLA_SLIM."  
					WHERE FROM_UNIXTIME(dt)>'".$datos['fecha']."' AND DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d')<>DATE_FORMAT(now(), '%Y-%m-%d')
					GROUP BY visit_id,  DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d')";
			$lista=consultar($query);
			
			for($i=0; $i<count($lista); $i++){
				//datos generales
				$datosFinales['general'][$lista[$i]['dt_simple']]['totalDividendo']+=$lista[$i]['total'];
				$datosFinales['general'][$lista[$i]['dt_simple']]['totalDivisor']++;
				
								
				$totalDividendo+=$lista[$i]['total'];
				$totalDivisor++;
			}
			
			//DISPOSITIVO (COMPU, TABLES O CELULAR) 0-ESCRITORIO, 2-MOVIL
			$query="SELECT count(*) AS total, browser_type, DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d') AS dt, DATE_FORMAT(FROM_UNIXTIME(dt), '%Y%m%d') AS dt_simple   
					FROM ".TABLA_SLIM."  
					WHERE FROM_UNIXTIME(dt)>'".$datos['fecha']."' AND DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d')<>DATE_FORMAT(now(), '%Y-%m-%d')
					GROUP BY browser_type, DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d') 
					ORDER BY dt";
			$lista=consultar($query);
			for($i=0; $i<count($lista); $i++){
				$datosFinales['dispositivo'][$i]['browser_type']=$lista[$i]['browser_type'];
				$datosFinales['dispositivo'][$i]['total']=$lista[$i]['total'];
				$datosFinales['dispositivo'][$i]['date']=$lista[$i]['dt'];
				$totalDispositivos+=$lista[$i]['total'];
				//datos generales
				$datosFinales['general'][$lista[$i]['dt_simple']]['dispositivo_'.$lista[$i]['browser_type']]+=$lista[$i]['total'];
								
			}
			
			//CLICK POR DIA
			$query="SELECT COUNT(*) AS total, page_width, url, DATE_FORMAT(record_date, '%Y-%m-%d') AS dt, DATE_FORMAT(record_date, '%Y%m%d') AS dt_simple  
						FROM ".TABLA_HOTSPOT."  
						WHERE event_type='mouse_click' AND record_date>'".$datos['fecha']."' AND DATE_FORMAT(record_date, '%Y-%m-%d')<>DATE_FORMAT(now(), '%Y-%m-%d')
						GROUP BY page_width, DATE_FORMAT(record_date, '%Y-%m-%d') 
						ORDER BY record_date";
			$lista=consultar($query);
			for($i=0; $i<count($lista); $i++){
				$datosFinales['click'][$i]['page_width']=$lista[$i]['page_width'];
				$datosFinales['click'][$i]['url']=$lista[$i]['url'];
				$datosFinales['click'][$i]['total']=$lista[$i]['total'];
				$datosFinales['click'][$i]['fecha']=$lista[$i]['dt'];
				$totalClick+=$lista[$i]['total'];
				//datos generales
				$datosFinales['general'][$lista[$i]['dt_simple']]['click']+=$lista[$i]['total'];
				
			}
			
			
			//ESTADISTICA DE VISITAS DIARIAS Y SU TRAFICO
			$query="SELECT t2.referer AS domain, t2.resource, count(*) AS total, DATE_FORMAT(FROM_UNIXTIME(t2.dt), '%Y-%m-%d') AS dt, DATE_FORMAT(FROM_UNIXTIME(dt), '%Y%m%d') AS dt_simple
					FROM ".TABLA_SLIM." AS t2 
					WHERE FROM_UNIXTIME(t2.dt)>'".$datos['fecha']."' AND DATE_FORMAT(FROM_UNIXTIME(t2.dt), '%Y-%m-%d')<>DATE_FORMAT(now(), '%Y-%m-%d')
					GROUP BY domain, t2.resource, DATE_FORMAT(FROM_UNIXTIME(t2.dt), '%Y-%m-%d') 
					ORDER BY t2.dt";
			$lista=consultar($query);
			for($i=0; $i<count($lista); $i++){
				$datosFinales['trafico'][$i]['domain']=$lista[$i]['domain'];
				$datosFinales['trafico'][$i]['resource']=$lista[$i]['resource'];
				$datosFinales['trafico'][$i]['fecha']=$lista[$i]['dt'];
				$datosFinales['trafico'][$i]['total']=$lista[$i]['total'];
				$totalVisitas+=$lista[$i]['total'];
				//datos generales
				$datosFinales['general'][$lista[$i]['dt_simple']]['trafico']+=$lista[$i]['total'];
			}
			
			
			//ESTADISTICA DE VISITAS DIARIAS Y SU TRAFICO
			$query="SELECT COUNT(*) AS total, DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d') AS dt, DATE_FORMAT(FROM_UNIXTIME(dt), '%Y%m%d') AS dt_simple   
						FROM ".TABLA_SLIM." 
						WHERE FROM_UNIXTIME(dt)>'".$datos['fecha']."' AND DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d')<>DATE_FORMAT(now(), '%Y-%m-%d')
						GROUP BY visit_id, DATE_FORMAT(FROM_UNIXTIME(dt), '%Y-%m-%d')  
						ORDER BY dt";	
			$lista=consultar($query);
			for($i=0; $i<count($lista); $i++){
				//$datosFinales['general'][$lista[$i]['dt_simple']]['profundidadMax']=0;
				if($datosFinales['general'][$lista[$i]['dt_simple']]['profundidadMax']<$lista[$i]['total']){	
					$datosFinales['general'][$lista[$i]['dt_simple']]['profundidadMax']=$lista[$i]['total'];
				}
			}
 		}
		else{
			$datosFinales['error']="Usuario y contraseña incorrectos ".$datos['API_KEY']."==".API_KEY." and ".$_SERVER['REMOTE_ADDR']."==".REMOTE_ADDR."";
		}
				
		//SE ENVIA EL ARREGLO A TOTALPAT
		return $datosFinales;
	
	}
	
	$server= new nusoap_server();
	$namespace = 'http://'.$_SERVER['HTTP_HOST']."/wp-content/plugins/totalpat/webservice/";
	// create a new soap server
	$server = new nusoap_server();
	// configure our WSDL
	$server->configureWSDL('Totalpat_SincronizacionEstadisticas_API', $namespace);
	// set our namespace
	$server->wsdl->schemaTargetNamespace = $namespace;          
	
	$server->register(
		// method name:
		'getEstadisticas',          
		// parameter list:
		array('id'=>'xsd:int'), 
		// return value(array()):
		array('return'=>'xsd:Array'),
		// namespace:
		$namespace,
		// soapaction: (use default)
		false,
		// style: rpc or document
		'rpc',
		// use: encoded or literal
		'encoded',
		// description: documentation for the method
		'documentation'
	);
	
	$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
	
	// pass our posted data (or nothing) to the soap   service                    
	$server->service($POST_DATA);


?>