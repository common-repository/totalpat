<?PHP
if (!defined(("DBCONFIG"))) {
  define("DBCONFIG","1");

date_default_timezone_set('America/Mexico_City');

 // Functions and Procedures
 function dbConnect(){
	 
  $link = mysql_pconnect((DB_HOST_TOTALPAT),(DB_USER_TOTALPAT),(DB_PASSWD_TOTALPAT));
 // mysql_query("SET NAMES 'utf8'", $link);
  
  //mysql_set_charset('utf8');
  if ($link){
     return true;
  }else{
     return mysql_error();
  }
 }//End dbConnect

 function dbSelect(){
  $dbSelect = mysql_select_db(DB_TOTALPAT);
  if ($dbSelect){
     return true;
  }else{
     return mysql_error();
  }
 }//End dbSelect

 function dbQuery($query){
  $result = mysql_query($query);
  if ($result){
	  
	  //mysql_free_result();
	  //ob_end_flush();
	  
	 return $result;

	 
	 
  }else{
     return false;
  }
 }//End dbQuery

 function dbQueryArray($query){
	 
  $result = mysql_query($query);
  $resultRow = array ();
  if ($result){
     $i=0;
     while ($row=dbFetchRow($result)) {
       $resultRow[$i]=$row;
       $i++;
     }
  } 
  
//  mysql_free_result();
//  ob_end_flush();
  
  return $resultRow;
  
  
 }//End dbQueryArray

 function dbFetchRow($result){
  $row = mysql_fetch_array($result);
  return $row;
 }//End dbFecthRow 
 
 
 function DBQuery_s($query){
 	$result = mysql_query($query);
 	if($result)
 	{
 		return $result;
 	}
 	else
 	{
 		return false;
 	}
 }
}//End Fucntions DB
?>