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
    
    mysql_connect("localhost",$username,$password);
    @mysql_select_db($database) or die( "Unable to select database");
    //we also want to write population properties into the DB
    //store the properties of the population
    $query = "SELECT * from ".$table;
    
    $results = mysql_query($query);
    if (!$results){
        die('Invalid query: ' . mysql_error());
    }
    
    $mturk_arr = array();
    while ($row = mysql_fetch_assoc($results)){
        $tmp=array($row["id"], $row["text"], $row["mturk"], 0, "");
        array_push($mturk_arr,$tmp);
    }
    mysql_close();
    
    //we have all the files, now we ship
    foreach($mturk_arr as $key=>$value){
        //$value[3] = upload("./$table/$value[1].jpeg", "9e3a331523a35c307e5440d84204d704", true, "antigate.com");
    }
    
    $mturk_arr[0][3] = "152679189";
    $mturk_arr[1][3] = "152679193";
    $mturk_arr[2][3] = "152679200";
    $mturk_arr[3][3] = "152679209";
    $mturk_arr[4][3] = "152679212";
    $mturk_arr[5][3] = "152679218";
    $mturk_arr[6][3] = "152679225";
    $mturk_arr[7][3] = "152679229";
    $mturk_arr[8][3] = "152679232";
    $mturk_arr[9][3] = "152679239";
    $mturk_arr[10][3] = "152679242";
    $mturk_arr[11][3] = "152679246";
    $mturk_arr[12][3] = "152679251";
    $mturk_arr[13][3] = "152679255";
    $mturk_arr[14][3] = "152679261";
    $mturk_arr[15][3] = "152679264";
    $mturk_arr[16][3] = "152679271";
    $mturk_arr[17][3] = "152679276";
    $mturk_arr[18][3] = "152679280";
    $mturk_arr[19][3] = "152679284";
    $mturk_arr[20][3] = "152679289";
    $mturk_arr[21][3] = "152679292";
    $mturk_arr[22][3] = "152679295";
    $mturk_arr[23][3] = "152679297";
    $mturk_arr[24][3] = "152679300";
    $mturk_arr[25][3] = "152679305";
    $mturk_arr[26][3] = "152679310";
    $mturk_arr[27][3] = "152679313";
    $mturk_arr[28][3] = "152679317";
    $mturk_arr[29][3] = "152679321";
    $mturk_arr[30][3] = "152679324";
    $mturk_arr[31][3] = "152679329";
    $mturk_arr[32][3] = "152679331";
    $mturk_arr[33][3] = "152679334";
    $mturk_arr[34][3] = "152679337";
    $mturk_arr[35][3] = "152679344";
    $mturk_arr[36][3] = "152679347";
    $mturk_arr[37][3] = "152679351";
    $mturk_arr[38][3] = "152679356";
    $mturk_arr[39][3] = "152679358";
    $mturk_arr[40][3] = "152679362";
    $mturk_arr[41][3] = "152679369";
    $mturk_arr[42][3] = "152679373";
    $mturk_arr[43][3] = "152679376";
    $mturk_arr[44][3] = "152679379";
    $mturk_arr[45][3] = "152679385";
    $mturk_arr[46][3] = "152679390";
    $mturk_arr[47][3] = "152679399";
    $mturk_arr[48][3] = "152679402";
    $mturk_arr[49][3] = "152679404";

    
    
     foreach($mturk_arr as $key=>$value){
        //echo $value[3]."\n";
        $value[4] = query("./$table/$value[1].jpeg", $value[3],"9e3a331523a35c307e5440d84204d704", true, "antigate.com");
        
        if (!$value[4]){
            $value[4] = "~~~~~~~~~~";
        }
        
        $value[4] = mysql_escape_string($value[4]);
        $fitness = levenshtein(strtolower($value[2]), strtolower($value[4]));
        
        mysql_connect("localhost",$username,$password);
        @mysql_select_db($database) or die( "Unable to select database");
        
		$query = "UPDATE $table SET antigate='$value[4]' WHERE id=$value[0]";
        
        $result = mysql_query($query);
        if (!$result) {
            echo "Could not successfully run query ($query) from DB: " . mysql_error();
            exit;
        }
        
        $query = "UPDATE $table SET fitness=$fitness WHERE id=$value[0]";
        
        $result = mysql_query($query);
        if (!$result) {
            echo "Could not successfully run query ($query) from DB: " . mysql_error();
            exit;
        }

        
        mysql_close();

    }
    
    /*
    //we have all the files, now we ship
    foreach($files as $key=>$file){
        $tmp = array(0,0);
        $tmp[0] = upload($file[0], "9e3a331523a35c307e5440d84204d704", true, "antigate.com");
        //$tmp[1] = upload($file[0], "", true, "insecure.linshunghuang.com");
        
        array_push($ids, $tmp);
    }
    
    
    foreach($ids as $key=>$id){
        $result1 = query($files[$key][0], $id[0], "9e3a331523a35c307e5440d84204d704", true, "antigate.com");
		//$result1 = "antigate";
		//$result2 = query($files[$key][0], $id[1], "", true, "insecure.linshunghuang.com",10,999999);
        
        if (!$result1){
            $result1 = "~~~~~~~~~~";
        }
        
        if (!$result2){
            $result2 = array("~~~~~~~~~~", -1);
        }
        
        $result1 = mysql_escape_string($result1);
        $result2[0] = mysql_escape_string($result2[0]);
        $result2[1] = mysql_escape_string($result2[1]);
                
        $fitness = levenshtein(strtolower($result1), strtolower($result2[0]));
        
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
    
    */
    

    closedir($handle);
}
?>