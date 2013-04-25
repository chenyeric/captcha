<?php
include("antigate.php");

$username="root";
$password="1234567";
$database="captcha";
$table = "recaptcha";

if ($handle = opendir('./'.$table)) {
    //create a new table for this population
	mysql_connect("localhost",$username,$password);
	@mysql_select_db($database) or die( "Unable to select database");
	$query = "CREATE TABLE IF NOT EXISTS ".$table."(id MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), text VARCHAR(30),".
             " mturk VARCHAR(30), mturk_time int, antigate VARCHAR(30), fitness int)";
	mysql_query($query);
	mysql_close();
		
    $files = array();
    $ids = array();

    while (false !== ($entry = readdir($handle))) {
        
        $suffix = "jpeg";
        if(substr_compare($entry, $suffix, strlen($entry)-strlen($suffix), strlen($suffix)) === 0){
            $name = explode(".", $entry);
            $file = array("./$table/".$entry, (string)$name[0]);
            array_push($files, $file);
        }
    }
    
    //we have all the files, now we ship
    foreach($files as $key=>$file){
        $tmp = array(0,0);
        //$tmp[0] = upload($file[0], "9e3a331523a35c307e5440d84204d704", true, "antigate.com");
        $tmp[1] = upload($file[0], "", true, "insecure.linshunghuang.com");
        
        array_push($ids, $tmp);
    }
    
    
    foreach($ids as $key=>$id){
        //$result1 = query($files[$key][0], $id[0], "9e3a331523a35c307e5440d84204d704", true, "antigate.com");
		$result1 = "antigate";
		$result2 = query($files[$key][0], $id[1], "", true, "insecure.linshunghuang.com",2,999999);
        
        if (!$result1){
            $result1 = "~~~~~~~~~~";
        }
        
        if (!$result2){
            $result2 = array("~~~~~~~~~~", -1);
        }
        
        $result1 = mysql_escape_string($result1);
        $result2[0] = mysql_escape_string($result2[0]);
        $result2[1] = mysql_escape_string($result2[1]);
                
        $fitness = levenshtein($result1, $result2[0]);
        
        mysql_connect("localhost",$username,$password);
        @mysql_select_db($database) or die( "Unable to select database");
        
        //	$query = "CREATE TABLE IF NOT EXISTS ".$table."(id MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), text VARCHAR(30),".
        //     " mturk VARCHAR(30), antigate VARCHAR(30), fitness int)";
        $query = "INSERT INTO $table (text, mturk, mturk_time, antigate, fitness) VALUE ('".$files[$key][1]."', '".$result2[0]."', ".$result2[1].", '$result1', $fitness)";
        $result = mysql_query($query);
        if (!$result) {
            echo "Could not successfully run query ($query) from DB: " . mysql_error();
            exit;
        }
        
        mysql_close();
    }
    
    
    

    closedir($handle);
}
?>