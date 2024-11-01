<?php
	if(!defined("BASE_LIB")){
		define("BASE_LIB",1);
		
		require("server.mysql.php");
		date_default_timezone_set('America/Mexico_City');
		/*
		*	funcion para conectar a la base de datos, no recibe datos
		*/
		function conectar(){
			if(dbConnect() and dbSelect()){
		//		mysql_query("SET time_zone = '-6:00';");
				mysql_set_charset('utf8');
				
				return true;
			}
			else{
				return false;
			}
		}
		
		
		/*
		*	funcion para agregar un elemento, recibe:
		*	datos: es un arreglo
		*	tabla: la tabla a donde se va agregar
		*	return el numero del key frame recien puesto
		*/
		function agregar($datos, $tabla){
			if(conectar()){
				if(is_array($datos)){
					$dato="";
					$k="";
					$i=0;
					foreach($datos as $key => $value){
						
						$value= trim($value);
						
						if($i==0){
							$dato = "'$value'";
							$k = $key;
							$i++;
						}
						else{
							$dato .= ", '$value'";
							$k .= ", $key";
						}
					}
					
				//	mysql_query("SET time_zone = '-6:00';");
					
					$query = "INSERT INTO $tabla ($k) VALUES ($dato)";
					$result = dbQuery($query);
					return mysql_insert_id();
				}
			}
		}
		
		/*
		*	funcion para actualizar la info, recibe:
		*	datos: es un arreglo
		*	tabla: la tabla a donde se va a actualizar la info
		*	id: el id de la primary key de la tabla
		*	nombre_primary: es el nombre de la primary key
		*/
		function actualizar($datos, $tabla, $id, $nombre_primary){
			if(conectar()){
				if(is_array($datos)){
					$dato="";
					$k="";
					$i=0;
					foreach( $datos as $key => $value){
						
						$value= trim($value);
						
						if($i==0){
							$dato = "$key='$value'";
							$i++;
						}
						else{
							$dato .= ", $key = '$value'";
						}
					}
					
					
			//		mysql_query("SET time_zone = '-6:00';");
					
					$query = "UPDATE $tabla SET $dato WHERE $nombre_primary ='$id'";
					$result = dbQuery($query);
					return $result;
				}
			}
			
		}
		

		/*
		*	funcion para eliminar la info, recibe:
		*	tabla: la tabla a donde se va a eliminar la info
		*	id: el id de la primary key de la tabla
		*	nombre_primary: es el nombre de la primary key
		*/
		function eliminar($tabla, $id, $nombre_primary){
			if(conectar()){
				$query = "DELETE FROM $tabla WHERE $nombre_primary='$id'";
				$result = dbQuery($query);
			}
		}
		
		
		/*
		*	funcion para consultar la info, recibe:
		*	$query: es la instucción de la consulta
		*	regresa el resultado del $query.
		*/
		function consultar($query){
			if(conectar()){
				$result = dbQueryArray($query);
				return $result;
			}
		}
		
		function contar($query){
			if(conectar()){
				$result = dbQueryArray($query);
				if(count($result) != 0){ $result = $result[0]['suma'];  }else{ $result=0; }
				return $result;
			}
		}
		
		
		function pullQuery($query){
			if(conectar()){
				$result = dbQueryArray($query);
				return $result;
			}
		}
		
		function ejecutarQuery($query){
			if(conectar()){
				$result =dbQuery($query);
				return $result;
			}
		}
		
		function mandarQuery($query){
			if(conectar()){
				$result =dbQuery($query);
				return $result;
			}
		}
	}
		
?>