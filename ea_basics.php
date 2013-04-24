<?php
error_reporting(E_ALL);
include("ea_phenotype_generation.php");
include("cryptographp.cfg.php");


Class Individual{
	
	//num_geno should equal to the length of the $geno array
	private $num_geno = 16;
	
	private $answers= array();
	
	private $geno = Array (
		0,      //0- width
		0,      //1- height
		0,		//2- Intensity of transparency characters (0 -> 127) // 0 = opaque, 127 = invisible
		0,		//3- easy to read?
		0,		//4- font
		0,		//5- # of chars
		0,		//6- Space between characters (in pixels)
		0,		//7- min font size
		0,		//8- max font size
		0,		//9- max rotation angle
		0,		//10- vertical displacement ( ture/false)
		0, 		//11- gaussian blur
		0,		//12- grayscale 
		0,		//13- Number of random pixels
		0,		//14- Number of random lines
		0,		//15- Number of random circles
		0, 		//16- bg r
		0, 		//17- bg g
		0, 		//18- bg b
		0, 		//19- font r
		0,		//20- font g
		0,		//21- font b
		0		//22- color difference 0-2
	);
	
	//the possible range values for our genotypes
	private $geno_range = Array(
		Array(140,160),		//0- width
		Array(40,60),		//1- height
		Array(0,100),		//2-  Intensity of transparency characters (0 -> 127) // 0 = opaque, 127 = invisible
		Array(0,1),		//3- Create cryptograms "easy to read" (true / false) // Alternatively compounds consonants and vowels.
		Array(0,6), 	//4- font: 0-6
		Array(6, 6),	//5- # of chars
		Array(0,30),    //6-  Space between characters (in pixels)
		Array(8,16),   //7-  min font size
		Array(16,22),   //8- max font size
		Array(0, 360),	//9- max angle of rotation
		Array(0,1), //10-vertical displacement
		Array(0,0), //11- gausssian blur
		Array(0,0), //12-grayscale
		Array(0,2000), //13-random pixel noise
		Array(0,20), //14-random line noise
		Array(0,10), //15-random circle noise
		Array(0,255), 		//16- bg r
		Array(0,255), 		//17- bg g
		Array(0,255), 		//18- bg b
		Array(0,255), 		//19- font r
		Array(0,255),		//20- font g
		Array(0,255),		//21- font b
		Array(1,3)		//22- color difference 0-2
		
	);
	public function getMaxStrlen(){
		return $this->geno_range[5][1];
	}
	
	public function dump(){
		foreach($this->geno as $ele){
			echo "$ele | ";
		}
		echo "<br>\n";
	}
	
	public function addAnswers($text, $mturk, $antigate){
		$new_arr = array();
		array_push($new_arr, $text, $mturk, $antigate);
		array_push($this->answers, $new_arr);
	}
	
	public function init($seed){
		$this->num_geno = sizeof($this->geno);
		
		for ($i=0; $i<$this->num_geno; $i++){
			//We manually set the value of initial genotype based on seed
			//we are doing this to make our first population more stable and easy to solve
			if ($i==2){
				$this->geno[$i] = 0;	
			}else if($i == 4){ // font
				$this->geno[$i] = 3;
			}else if($i == 6){
				$this->geno[$i] = 22;
			}else if($i == 9){
					$this->geno[$i] = 0;
			}else if($i == 11 || $i == 12){
					$this->geno[$i] = 0;
			}else if ($i >=16 && $i <=18){//background colors
				if ($seed % 2 == 0) $this->geno[$i] = 255;
				else $this->geno[$i] = 0;
			}else if($i >=19 && $i <=21){//font colors
				if($seed % 2 == 0) $this->geno[$i] = 0;
				else $this->geno[$i] = 255;
			}else{
				//randomize the initial state
				$this->geno[$i] = rand($this->geno_range[$i][0], $this->geno_range[$i][1]);
			}
		}
	}
	
	public function getSize(){
		return $this->num_geno;
	}
	
	public function getGene($i){
		return $this->geno[$i];
	}
	
	public function setGene($i, $val){
		$this->geno[$i] = $val;
	}
	
	public function mutateGene($i){
		$rand = rand($this->geno_range[$i][0], $this->geno_range[$i][1]);
		//echo "....".$this->geno_range[$i][0]." : ". $this->geno_range[$i][1] ."-". $rand ." ....<br>\n";
		$this->geno[$i] = $rand;
	}
	
}

//==================================================================
//***** To start a new population from scratch******/
//1. Call init()

// ***** To prepare offsprings for fitness evaluation *****/
//1. Call fill()
//2. Call evolve()
//3. Call generate_image(layer)

//***** Fitness evaluation finished, prepare DB for next geneartion *****/
//1. Call fill()
//2. Call evaluate(layer)
//==================================================================
Class Population{
	
	//test flag must be off for deployment
	private $test_flag = 1;
	
	private $username="root";
	private $password="1234567";
	private $database="captcha";
	
	private $indivs = array();
	private $offsprings = array();
	
	//the fitness for the individuals
	//the key is going to be the key of indivs, and the value is their fitness
	//e.g.,, indivs = [0:a, 1:b, 2:c]
	//       fitness = [0:10, 1:11, 2:4]
	//	     asort(fitness) = [2:4, 0:10, 1:11]
	private $fitness = array(); 
	
	//equilibrium size for the population
	private $equil_size = 0;
	
	//the population will continue to shrink at dying_rate until equil_size is reached
	private $dying_rate =0;
	
	private $pop_size=0;
	private $mut_rate=0;
	private $cross_rate=0;
	
	//Name of the table in the DB
	private $table = "";
	
	//pick an index at random, swap the two genes
	private function cross_over($ind_1, $ind_2){
		$geno_size = $ind_1->getSize();
		$rand_index = rand(0, $geno_size-1);
		$temp_val = $ind_1->getGene($rand_index);
		$ind_1->setGene($rand_index, $ind_2->getGene($rand_index));
		$ind_2->setGene($rand_index, $temp_val);
	}
	
	public function dump(){
		echo "<br><br><br>\n|||||||||||||||||||||||||||||| dump start |||||||||||||||||||||||||||||||<br>\n";
		echo "Size of the population is: $this->pop_size<br>\n";
		echo "Size of the \$indivs array is: ".sizeof($this->indivs)."<br>\n";
		echo "mut_rate is $this->mut_rate<br>\n";
		echo "cross rate is $this->cross_rate<br>\n";
		echo "table is $this->table<br>\n";
		
		echo "=========== population dump ============<br>\n";
		foreach($this->indivs as $indiv){
			$indiv->dump();
		}
		echo "==============================================<br><br><br>\n";
		
		echo "=========== offspring dump ============<br>\n";
		foreach($this->offsprings as $offspring){
			$offspring->dump();
		}
	    echo "|||||||||||||||||||||||||||||| dump end |||||||||||||||||||||||||||||||<br>\n<br>\n<br>\n";
		
	}
	
	public function init($_equil_size,$_mut_rate,$_cross_rate, $_dying_rate, $table_name){
		$this->pop_size = 0; //pop size starts with 0 until populate() is called
		$this->equil_size = $_equil_size;
		$this->mut_rate = $_mut_rate;
		$this->cross_rate = $_cross_rate;
		$this->dying_rate = $_dying_rate;
		$this->table = $table_name;
		
		//create a new table for this population
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		$query = "CREATE TABLE IF NOT EXISTS ".$this->table."(id MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), geno BLOB)";
		mysql_query($query);
		mysql_close();
		
		$this->populate();
		
		//create the elitist table to keep track of best performing individuals
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		$query = "CREATE TABLE IF NOT EXISTS elitist(id MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), geno BLOB, fitness int)";
		mysql_query($query);
		mysql_close();
	}
	
	private function populate(){
		for ($i=0; $i< $this->equil_size; $i++){
			$indiv = new Individual();
			$indiv->init($i);
			array_push($this->indivs,$indiv);
			$this->pop_size++;
		}
		
		//store the properties of the population
		$handle = mysql_connect("localhost",$this->username,$this->password);
		mysql_select_db($this->database, $handle) or die( "Unable to select database");
		$query = "REPLACE INTO pop_config (table_name, equil_size, pop_size, mut_rate, cross_rate, dying_rate) ".
				"VALUE ('".$this->table."',".$this->equil_size.",".$this->pop_size.",".$this->mut_rate.",".$this->cross_rate.",".$this->dying_rate.");";
		$results = mysql_query($query, $handle);
		if (!$results){
			die('Invalid query 2: ' . mysql_error());
		}
		
		//store the population into the DB and send it for fitness evaluation
		foreach($this->indivs as $ele){
			$str_ele = mysql_real_escape_string(serialize($ele));
			$query = "INSERT INTO ".$this->table." (geno) VALUE ('".$str_ele."')";
			//echo($query);
			$results = mysql_query($query, $handle);

			if (!$results){
				die('Invalid query 3: ' . mysql_error());
			}
			mysql_close();	
		}
	}
	
	public function extract_pop(){
		return $this->indivs;
	}
	
	public function get_table(){
		return $this->table;
	}
	
	public function combine_pop($ele_arr){
		
		foreach ($ele_arr as $ele){
			array_push($this->indivs, $ele);
		}
		$this->pop_size += sizeof ($ele_arr);
	}
	
	public function switch_table($new_table){
		$this->table = $new_table;
		//create a new table for this population
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		$query = "CREATE TABLE IF NOT EXISTS ".$this->table."(id MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), geno BLOB)";
		mysql_query($query);
		$results = mysql_query($query);
		if (!$results){
			die('Invalid query 13: ' . mysql_error());
		}
		//we also want to write population properties into the DB
		//store the properties of the population
		$query = "REPLACE INTO pop_config (table_name, equil_size, pop_size, mut_rate, cross_rate, dying_rate) VALUE ('$this->table', $this->equil_size, $this->pop_size, $this->mut_rate, $this->cross_rate, $this->dying_rate);";
		
		$results = mysql_query($query);
		if (!$results){
			die('Invalid query 14: ' . mysql_error());
		}
		
		mysql_close();
		
		//echo "------------------------------------------------$this->table<br>\n";
	}
	
	//**evolve() will be called by ea_core
	// Returns: array of possible offsprings that needs to be checked for fitness
	public function evolve(){
		$this->offsprings = $this->indivs;
		foreach($this->indivs as $indiv){
			//mutation
			$num = mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
			if ($num < $this->mut_rate){
				$new_indiv = clone $indiv;
				$new_indiv->mutateGene(rand(0,$indiv->getSize()-1));
				array_push($this->offsprings, $new_indiv);
			}
			//crossover
			$num = mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
			if ($num<$this->cross_rate){
				//first, randomly select another individual to cross
				$mom = clone $this->indivs[rand(0,$this->pop_size-1)];
				$dad = clone $indiv;
				if ($mom != $dad){//only breed if both parents are unique
					$this->cross_over($mom,$dad);
				}
				array_push($this->offsprings, $mom, $dad);
			}
		}
		
		//$offsprings will now include all the offsprings
		//we want to store it in our DB and send it for fitness evaluation
		//echo("~~~~~~~~~~~~~~~~~~~~~~~~~~ table: $this->table size of offspring is ".sizeof($this->offsprings)."type: ".gettype($this->offsprings)."<br>\n");

		$this->db_rewrite($this->offsprings, $this->table);
		
	}
	
	//db_write will 
	private function db_rewrite($arr, $table, $fitness = array()){
		$handle = mysql_connect("localhost",$this->username,$this->password);
		mysql_select_db($this->database, $handle) or die( "Unable to select database");
		//first, we drop the original table
		$query = "TRUNCATE TABLE $table";
		$results = mysql_query($query, $handle);
		if (!$results){
			die('Invalid query 4: '.$query." error:" . mysql_error());
		}
	
		//now we add new element
		foreach($arr as $k=>$ele){
			//check if we are inserting to the elite db
			if($table == "elitist"){
				$str_ele = mysql_real_escape_string(serialize($ele));
				$query = "INSERT INTO $table (geno, fitness) VALUE ('$str_ele', $fitness[$k])";
				$results = mysql_query($query, $handle);

				if (!$results){
					die('Invalid query 5: ' . mysql_error());
				}
			}else{
				$str_ele = mysql_real_escape_string(serialize($ele));
				$query = "INSERT INTO $table (geno) VALUE ('$str_ele')";
				$results = mysql_query($query, $handle);

				if (!$results){
					die('Invalid query 6: ' . mysql_error());
				}
			}
		}
		
		mysql_close();
	}
	
	public function fill($_table){
		
		
		//destroy the current population
		$this->indivs = array();
		
		//copy the population properties into the object
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		//first, we drop the original table
		$query = "SELECT * from pop_config where table_name='".$_table."'";
		$results = mysql_query($query);
		if (!$results){
			die('Invalid query 7: ' . mysql_error());
		}
		
		$row = mysql_fetch_assoc($results);
		$this->table = $row["table_name"];
		$this->equil_size = (int)$row["equil_size"];
		//$this->pop_size=(int)$row["pop_size"];
		$this->mut_rate=(float)$row["mut_rate"];
		$this->cross_rate=(float)$row["cross_rate"];
		$this->dying_rate =(float)$row["dying_rate"];
		
		//deserialize the genotype
		//fill up $indivs array
		$query = "SELECT geno from ".$_table;
		$results = mysql_query($query);
		if (!$results){
			die('Invalid query 8 table: '.$_table.', query: ' . mysql_error());
		}
		while ($row = mysql_fetch_assoc($results)){
			
			$str_geno = $row["geno"];
			$geno = new Individual();
			$geno = unserialize($str_geno);
			array_push($this->indivs, $geno);
	
		}
		$this->pop_size = sizeof($this->indivs);
		
		$this->offsprings = array();
		
		mysql_close();
		
		
	}
	
	
	//this function will look at the data from our user evaluation
	//and write to $this->fitness
	//precondition: $this->indivs is filled
	public function calculate_fitness(){
		//empty fitness array		
		$this->fitness = array();
		
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		
		//First, we fetch all the offsprings,
		foreach($this->indivs as $key=>$indiv){			
			
			$query = "SELECT * from $this->table"."_antigate"." where geno_id=$key";
			$result = mysql_query($query);
			if (!$result) {
			    echo "Could not successfully run query 12 ($query) from DB: " . mysql_error();
			    exit;
			}
			
			$acc_fitness = array();
			while($row = mysql_fetch_assoc($result)){
				
				//first, add the user study info to our indivs
				$indiv->addAnswers($row['captcha_text'], $row['mturk_answer'], $row['antigate_answer']);
				
				//$lev_mturk = levenshtein(strtolower($row["captcha_text"]), strtolower($row["mturk_answer"]));
				//$lev_antigate = levenshtein(strtolower($row["captcha_text"]), strtolower($row["antigate_answer"]));
				
				//$lev_mturk = ($lev_mturk > strlen($row["captcha_text"])) ? strlen($row["captcha_text"]) : $lev_mturk;
				//$lev_antigate = ($lev_antigate > strlen($row["captcha_text"])) ? strlen($row["captcha_text"]) : $lev_antigate;
				
				echo("lev_mturk: ".$row['mturk_answer'].", lev_antigate: ".$row['antigate_answer'].", str: ".$row['captcha_text']." <br>\n\n");
				
				//TODO: include solving speed into calculation		
				//$avg_speed = $result["mturk_speed"]/strlen($result["captcha_text"]);
				
				//$fit = ($lev_antigate-$lev_mturk)/strlen($row["captcha_text"]);
				//if ($fit<0)$fit =0;
				
				$fit = levenshtein(strtolower($row["mturk_answer"]), strtolower($row["antigate_answer"]));
				
				array_push($acc_fitness, $fit);
			}
			//average multiple runs to get the average fitness
			$cur_fitness = array_sum($acc_fitness)/sizeof($acc_fitness);
		
			array_push($this->fitness, $cur_fitness);
		}
		
		
		//after fitness is calculated, we want to clean the table with user data
		$query = "TRUNCATE $this->table"."_antigate";
		$results = mysql_query($query);
		if (!$results){
			die('Invalid query 9: ' . mysql_error());
		}
		mysql_close();
		
		
	}
	
	public function print_html($var){
		?><pre><?
		print_r($var);
		?><pre><?
	}
	
	
	//evaluate needs to:
	//1. reduce pop size if pop size != equil size
	//2. find the best fit individuals from user evaluation
	//3. write it back to the db
	//4. rewrite the elitist table
	public function evaluate($subfolder){
		
		//calculate the fitness
		$this->calculate_fitness();
		
		//fix the population 
		if ($this->pop_size > $this->equil_size +$this->dying_rate){
			$this->pop_size-=$this->dying_rate;
		}else if ($this->pop_size >$this->equil_size){
			$this->pop_size = $this->equil_size;
		}
		
		
		//at this point, we want to fetch our elitist genotypes
		$elitist = array();
		$elitist = $this->indivs;
		$efitness = array();
		$efitness = $this->fitness;
		
		//deserialize the genotype
		//fill up $elitist array
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		$query = "SELECT * from elitist";
		$results = mysql_query($query);
		if (!$results){
			die('Invalid query 10: ' . mysql_error());
		}
		while ($row = mysql_fetch_assoc($results)){
			$str_geno = $row["geno"];
			$geno = new Individual();
			$geno = unserialize($str_geno);
			array_push($elitist, $geno);
			array_push($efitness, $row["fitness"]);
		}
		mysql_close();
		
		//first, we calculate our elite population
		
		//sort our elitist fitness array
		arsort($efitness);
		$result = array_slice($efitness, 0, 20, true);
		foreach($efitness as $key=>$value){
			if (!array_key_exists($key, $result))
				unset($elitist[$key]);
		}
		
		$this->db_rewrite($elitist, "elitist", $efitness);
		
		//re-shuffle the population
		//first, we want to sort the fitness
		arsort($this->fitness);
		
		//after fitness is sorted, we want to weed out weaker offsprings
		$result = array_slice($this->fitness,0, $this->equil_size, true);
		
		//$result now contains the offsprings that needs to be removed
		foreach($this->fitness as $key=>$value){
			if (!array_key_exists($key, $result))
				unset($this->indivs[$key]);
		}
		
		//echo("~~~~~~~~~~~~~~~~~~~~~~~~~~ table: $this->table size of indiv is ".sizeof($this->indivs)."type: ".gettype($this->indivs)."<br>\n");
		$this->db_rewrite($this->indivs, $this->table);
		
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		//we also want to write population properties into the DB
		//store the properties of the population
		$query = "REPLACE INTO pop_config (table_name, equil_size, pop_size, mut_rate, cross_rate, dying_rate) VALUE ('$this->table', $this->equil_size, $this->pop_size, $this->mut_rate, $this->cross_rate, $this->dying_rate);";
		
		$results = mysql_query($query);
		if (!$results){
			die('Invalid query 1: ' . mysql_error());
		}
		
		mysql_close();
		
		
		//delete all the captcha image files generated in he pervious run
		$files = glob('./captcha/'.$subfolder.'/*'); // get all file names
		foreach($files as $file){ // iterate files
		  if(is_file($file))
		    unlink($file); // delete file
		}
		
	}
	
	//must be called after evolve!
	public function generate_image($subfolder){
		
		global  $cryptwidth, $cryptheight, $bgR, $bgG, $bgB, $bgclear, $bgimg, $bgframe, $tfont, $charel, $crypteasy, $charelc, $charelv, $difuplow, $charnbmin, $charnbmax, $charsizemin, $charsizemax, $charanglemax, $charup, $cryptgaussianblur, $cryptgrayscal, $noisepxmin, $noisepxmax, $noiselinemin, $noiselinemax,$nbcirclemin, $nbcirclemax, $noisecolorchar, $brushsize, $noiseup, $cryptformat, $cryptsecure, $cryptusetimer, $cryptusertimererror, $cryptusemax, $cryptoneuse, $img, $ink, $charR, $charG, $charB, $charclear, $xvariation, $charnb, $charcolorrnd, $charcolorrndlevel, $tword, $charspace;
		
		//create a new table to store the mapping between captchas and their solutions
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		$query = "CREATE TABLE IF NOT EXISTS ".$this->table."_antigate"."(id MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), geno_id MEDIUMINT, captcha_text VARCHAR(30), image_filename VARCHAR(20), antigate_answer VARCHAR(30), mturk_answer VARCHAR(30), mturk_speed INT)";
		mysql_query($query);
		mysql_close();
		
		foreach ($this->offsprings as $key=>$indiv){
			
			
			$cryptwidth  = $indiv->getGene(0);  // ​​Width of the cryptogram (in pixels)
			$cryptheight = $indiv->getGene(1);   // Height of the cryptogram (in pixels)

			$bgR  = $indiv->getGene(16);         // background color to RGB: Red (0 -> 255)
			$bgG  = $indiv->getGene(17);         // Couleur du fond au format RGB: Green (0->255)
			$bgB  = $indiv->getGene(18);         // Couleur du fond au format RGB: Blue (0->255)

			// ----------------------------
			// Set the character
			// ----------------------------

			// Color basic character

			$charR = $indiv->getGene(19);     // Font color in RGB: Red (0 -> 255)
			$charG = $indiv->getGene(20);     // Couleur des caractères au format RGB: Green (0->255)
			$charB = $indiv->getGene(21);     // Couleur des caractères au format RGB: Blue (0->255)

			$charclear = $indiv->getGene(2);  // Intensity of transparency characters (0 -> 127)
											// 0 = opaque, 127 = invisible
			// Interesting if you use an image $ bgimg
			// Only if PHP> = 3.2.1
			
		
			// Fonts
			if ($indiv->getGene(4) == 0)
				$tfont[] = 'Alanden_.ttf';       // The fonts will be used randomly.
			elseif($indiv->getGene(4)==1)	
				$tfont[] = 'bsurp___.ttf';       // You must copy the corresponding files
			elseif($indiv->getGene(4)==2)
				$tfont[] = 'ELECHA__.TTF';       //  on the server.
			elseif($indiv->getGene(4)==3)
				$tfont[] = 'luggerbu.ttf';         // Add as many rows as you want  
			elseif($indiv->getGene(4)==4)
				$tfont[] = 'RASCAL__.TTF';       // case-sensitive!
			elseif($indiv->getGene(4)==5)	
				$tfont[] = 'SCRAWL.TTF';  
			else
				$tfont[] = 'WAVY.TTF';   

			// Allowed Caracteres
			// Note that some fonts do not distinguish (or difficult) the upper
			// Sensitive. Some characters are easy to confuse, it is
			// Recommended to choose the characters used.

			$charel = 'abcdefghijklmnopqrstuvwxyz';       // Caractères autorisés

			if ($indiv->getGene(3)==0) $crypteasy = false;
			else $crypteasy = true;       // Create cryptograms "easy to read" (true / false) 
										// Alternatively compounds consonants and vowels.
			$charelc = 'bcdfghjklmnpqrstvwxyz';   // consonants to use when $crypteasy = true
			$charelv = 'aeiouy';              // Vowels to use when $crypteasy = true

		//	echo "charnbmin: $charnbmin | $charnbmax | ".$indiv->getGene(5)." <br>\n";
		
			$charnbmin = $indiv->getGene(5);         // min number of characters
			$charnbmax = $indiv->getGene(5);         // max num of chars

			$charspace = $indiv->getGene(6);        // Space between characters (in pixels)
			$charsizemin = $indiv->getGene(7);      // Minimum size characters
			$charsizemax = $indiv->getGene(8);      // Maximum size of characters

			$charanglemax  = $indiv->getGene(9);     // Maximum angle of rotation of the characters (0-360)
			if($indiv->getGene(10) ==0 )$charup = false;
			else $charup = true;        // Vertical displacement random characters (true / false)

		
			// Special Effects

			if ($indiv->getGene(11)==0) $cryptgaussianblur = false;
			else $cryptgaussianblur = true; // Transform the final image blurring: Gauss (true / false)
			
			if ($indiv->getGene(12)==0) $cryptgrayscal = false;
			else $cryptgrayscal = true;     // Transform the final image in grayscale (true / false)
			
			
			//noise
			$noisepxmin = $indiv->getGene(13);      // Noise: Minimum Number of random pixels
			$noisepxmax = $indiv->getGene(13);      // Noise: Maximum Number of random pixels

			$noiselinemin = $indiv->getGene(14);     // Noise: minimum Number of random rows
			$noiselinemax = $indiv->getGene(14);     // Noise: Maximum Number of random lines

			$nbcirclemin = $indiv->getGene(15);      // Noise: Nb minimum random circles 
			$nbcirclemax = $indiv->getGene(15);      // Noise: Number max of random circles


			$noisecolorchar  = $indiv->getGene(22);  // Noise: writing pixel color, lines, circles:
			                       // 1: Color writing characters
			                       // 2: Background Color
			                       // 3: Random color
//echo "subfolder:$subfolder<br>\n";
	        if (!is_dir("./captcha/$subfolder")){
//echo "creating folder<br>\n";
				mkdir("./captcha/$subfolder");
			}
			$image_name_1 = "./captcha/$subfolder/".(string) $key."_1.jpg";
			$image_name_2 = "./captcha/$subfolder/".(string) $key."_2.jpg";
			$text1 = generate_captcha($image_name_1);
			$text2 = generate_captcha($image_name_2);
//echo "Generating image: $image_name<br>\n";
			//We want to store the image<->text mapping into our table 
			mysql_connect("localhost",$this->username,$this->password);
			@mysql_select_db($this->database) or die( "Unable to select database");
			$query = "CREATE TABLE IF NOT EXISTS ".$this->table."_antigate".
				"(id MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), geno_id MEDIUMINT, captcha_text VARCHAR(30), image_filename VARCHAR(20), antigate_answer VARCHAR(30), mturk_answer VARCHAR(30), mturk_speed INT)";
			mysql_query($query);
			
			$safe_img_name = mysql_real_escape_string($image_name_1);
			$query = "INSERT INTO $this->table"."_antigate"." (geno_id, captcha_text, image_filename) VALUE ($key, '$text1','$safe_img_name')";
			$result = mysql_query($query);
			if (!$result) {
			    echo "Could not successfully run query ($query) from DB: " . mysql_error();
			    exit;
			}
			
			$safe_img_name = mysql_real_escape_string($image_name_2);
			$query = "INSERT INTO $this->table"."_antigate"." (geno_id, captcha_text, image_filename) VALUE ($key, '$text2','$safe_img_name')";
			$result = mysql_query($query);
			if (!$result) {
			    echo "Could not successfully run query ($query) from DB: " . mysql_error();
			    exit;
			}
			
			mysql_close();
			
		}
		
		
		
	}
	
}