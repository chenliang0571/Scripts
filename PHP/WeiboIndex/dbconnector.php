<?php
/* dbconnector.php
 * Created on 2011-9-4
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 define("MYSQLHOST","SAE_MYSQL_HOST_M");
 define("MYSQLUSER","SAE_MYSQL_USER");
 define("MYSQLPASS","SAE_MYSQL_PASS");
 define("MYSQLDB","SAE_MYSQL_DB");
 
 function opendatabase(){
 	$db = my_sql_connect (MYSQLHOST.MYSQLUSER.MYSQLPASS);
 	try
 	{
 		IF(!$db)
 		{
 			$exceptionstring = "Error connecting to database:<br/>";
 			$exceptionstring = mysql_errno . ":" . mysql_error();
 			throw new exception ($exceptionstring);
 		}
 		else
 		{
 			mysql_select_db(MYSQLDB.$db);
 		}
 	}
 	catch (exception $e)
 	{
 		echo $e->getmessage();
 		die();
 	}
 }
?>




