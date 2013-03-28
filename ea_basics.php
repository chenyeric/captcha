<?php
error_reporting(E_ALL);
include("ea_phenotype_generation.php");
include("cryptographp.cfg.php");


Class Individual{
	
	//num_geno should equal to the length of the $geno array
	private $num_geno = 16;
	
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
		0		//15- Number of random circles
	);
	
	//the possible range values for our genotypes
	private $geno_range = Array(
		Array(80,160),		//width
		Array(30,60),		//height
		Array(0,125),		// Intensity of transparency characters (0 -> 127) // 0 = opaque, 127 = invisible
		Array(0,1),		//Create cryptograms "easy to read" (true / false) // Alternatively compounds consonants and vowels.
		Array(0,6), 	//font: 0-6
		Array(4, 10),	//# of chars
		Array(0,30),    // Space between characters (in pixels)
		Array(8,16),   // min font size
		Array(16,22),   // max font size
		Array(0, 360),	// max angle of rotation
		Array(0,1), //vertical displacement
		Array(0,1), //gausssian blur
		Array(0,1), //grayscale
		Array(0,2000), //random pixel noise
		Array(0,20), //random line noise
		Array(0,10) //random circle noise
		
	);
	
	public function dump(){
		foreach($this->geno as $ele){
			echo "$ele | ";
		}
		echo "<br>";
	}
	
	public function init(){
		$this->num_geno = sizeof($this->geno);
		
		for ($i=0; $i<$this->num_geno; $i++){
			//randomize the initial state
			$this->geno[$i] = rand($this->geno_range[$i][0], $this->geno_range[$i][1]); 
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
		//echo "....".$this->geno_range[$i][0]." : ". $this->geno_range[$i][1] ."-". $rand ." ....<br>";
		$this->geno[$i] = $rand;
	}
	
}

//==================================================================
//***** To start a new population from scratch******/
//1. Call init()
//2. call populate()

// ***** To prepare offsprings for fitness evaluation *****/
//1. Call fill()
//2. Call evolve()

//***** Fitness evaluation finished, prepare DB for next geneartion *****/
//1. Call fill()
//2. Call cleanup()
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
		echo "Size of the population is: $this->pop_size<br>";
		echo "Size of the \$indivs array is: ".sizeof($this->indivs)."<br>";
		
		echo "=========== population dump ============<br>";
		foreach($this->indivs as $indiv){
			$indiv->dump();
		}
		echo "==============================================<br><br><br>";
		
		echo "=========== offspring dump ============<br>";
		foreach($this->offsprings as $offspring){
			$offspring->dump();
		}
		echo "==============================================<br><br><br>";
		
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
		$query = "CREATE TABLE IF NOT EXISTS ".$this->table."(id MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), geno BLOB, gfitness DOUBLE, bfitness DOUBLE)";
		mysql_query($query);

		mysql_close();
		
	}
	
	public function populate(){
		for ($i=0; $i< $this->equil_size; $i++){
			$indiv = new Individual();
			$indiv->init();
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
			die('Invalid query: ' . mysql_error());
		}
		
		//store the population into the DB and send it for fitness evaluation
		foreach($this->indivs as $ele){
			$str_ele = mysql_real_escape_string(serialize($ele));
			$query = "INSERT INTO ".$this->table." (geno) VALUE ('".$str_ele."')";
			//echo($query);
			$results = mysql_query($query, $handle);

			if (!$results){
				die('Invalid query: ' . mysql_error());
			}
			mysql_close();	
		}
	}
	
	public function resize($new_size){
		$this->pop_size = $new_size;
	}
	
	public function add_elements($ele_arr){
		
		foreach ($ele_arr as $ele){
			array_push($this->indivs, $ele);
		}
		$this->pop_size += sizeof ($ele_arr);
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
		$this->db_rewrite($this->offsprings);
		
	}
	
	//db_write will 
	private function db_rewrite($arr){
		$handle = mysql_connect("localhost",$this->username,$this->password);
		mysql_select_db($this->database, $handle) or die( "Unable to select database");
		//first, we drop the original table
		$query = "TRUNCATE TABLE ".$this->table;
		$results = mysql_query($query, $handle);
		if (!$results){
			die('Invalid query: ' . mysql_error());
		}
		
		//when test flag is set, we generate the fitness values at randoms
		if ($this->test_flag){
			foreach($arr as $k=>$ele){
				$g_rand = mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
				$b_rand = mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
				
				$str_ele = mysql_real_escape_string(serialize($ele));
				$query = "INSERT INTO $this->table (geno, gfitness, bfitness) VALUE ('$str_ele', $g_rand, $b_rand)";
				$results = mysql_query($query, $handle);

				if (!$results){
					die('Invalid query: ' . mysql_error());
				}
			}
		}else{
		//now we add new element
			foreach($arr as $k=>$ele){
				$str_ele = mysql_real_escape_string(serialize($ele));
				$query = "INSERT INTO ".$this->table." (geno) VALUE ('".$str_ele."')";
				$results = mysql_query($query, $handle);

				if (!$results){
					die('Invalid query: ' . mysql_error());
				}
			}
		}
		
		mysql_close();
	}
	
	public function fill($_table){
		//copy the population properties into the object
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		//first, we drop the original table
		$query = "SELECT * from pop_config where table_name='".$_table."'";
		$results = mysql_query($query);
		if (!$results){
			die('Invalid query: ' . mysql_error());
		}
		
		$row = mysql_fetch_assoc($results);
		$this->table = $row["table_name"];
		$this->equil_size = (int)$row["equil_size"];
		$this->pop_size=(int)$row["pop_size"];
		$this->mut_rate=(float)$row["mut_rate"];
		$this->cross_rate=(float)$row["cross_rate"];
		$this->dying_rate =(float)$row["dying_rate"];
		
		//deserialize the genotype
		//fill up $indivs array
		$query = "SELECT geno,gfitness,bfitness from ".$this->table;
		$results = mysql_query($query);
		if (!$results){
			die('Invalid query: ' . mysql_error());
		}
		while ($row = mysql_fetch_assoc($results)){
			
			$str_geno = $row["geno"];
			$geno = new Individual();
			$geno = unserialize($str_geno);
			array_push($this->indivs, $geno);
			
			//calculate the fitness
			//fill up $fitness array
			$fit = ($row["gfitness"]+$row["bfitness"]) / 2;
			array_push($this->fitness, $fit);
		}
		
		mysql_close();
		
	}
	
	public function print_html($var){
		?><pre><?
		print_r($var);
		?><pre><?
	}
	//cleanup needs to:
	//1. reduce pop size if pop size != equil size
	//2. find the best fit individuals
	//3. write it back to the db
	public function cleanup(){
		
		//fix the population 
		if ($this->pop_size > $this->equil_size +$this->dying_rate){
			$this->pop_size-=$this->dying_rate;
		}else if ($this->pop_size >$this->equil_size){
			$this->pop_size = $this->equil_size;
		}
		
		//re-shuffle the population
		//first, we want to sort the fitness
		arsort($this->fitness);
		
		//after fitness is sorted, we want to weed out weaker offsprings
		$result = array_slice($this->fitness,0, $this->pop_size, true);
		
		//$result now contains the offsprings that needs to be removed
		foreach($this->fitness as $key=>$value){
			if (!array_key_exists($key, $result))
				unset($this->indivs[$key]);
		}
		
		$this->db_rewrite($this->indivs);
		
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		//we also want to write population properties into the DB
		//store the properties of the population
		$query = "REPLACE INTO pop_config (table_name, equil_size, pop_size, mut_rate, cross_rate, dying_rate) VALUE ('$this->table', $this->equil_size, $this->pop_size, $this->mut_rate, $this->cross_rate, $this->dying_rate);";
		
		$results = mysql_query($query);
		if (!$results){
			die('Invalid query: ' . mysql_error());
		}
		
		mysql_close();
		
		
		//delete all the captcha image files generated in he pervious run
		$files = glob('captcha/*'); // get all file names
		foreach($files as $file){ // iterate files
		  if(is_file($file))
		    unlink($file); // delete file
		}
		
	}
	
	public function generate_image(){
		
		global  $cryptwidth, $cryptheight, $bgR, $bgG, $bgB, $bgclear, $bgimg, $bgframe, $tfont, $charel, $crypteasy, $charelc, $charelv, $difuplow, $charnbmin, $charnbmax, $charsizemin, $charsizemax, $charanglemax, $charup, $cryptgaussianblur, $cryptgrayscal, $noisepxmin, $noisepxmax, $noiselinemin, $noiselinemax,$nbcirclemin, $nbcirclemax, $noisecolorchar, $brushsize, $noiseup, $cryptformat, $cryptsecure, $cryptusetimer, $cryptusertimererror, $cryptusemax, $cryptoneuse, $img, $ink, $charR, $charG, $charB, $charclear, $xvariation, $charnb, $charcolorrnd, $charcolorrndlevel, $tword, $charspace;
		foreach ($this->indivs as $key=>$indiv){
			
			$cryptwidth  = $indiv->getGene(0);  // ​​Width of the cryptogram (in pixels)
			$cryptheight = $indiv->getGene(1);   // Height of the cryptogram (in pixels)

			$bgR  = 255;         // background color to RGB: Red (0 -> 255)
			$bgG  = 255;         // Couleur du fond au format RGB: Green (0->255)
			$bgB  = 255;         // Couleur du fond au format RGB: Blue (0->255)

			$bgclear = true;     // Transparent background (true / false)
			                     // Only valid for PNG

			$bgimg = '';                					// The bottom of the cryptogram may be an image

			$bgframe = false;    // Add a picture frame (true / false)


			// ----------------------------
			// Set the character
			// ----------------------------

			// Color basic character

			$charR = 0;     // Font color in RGB: Red (0 -> 255)
			$charG = 0;     // Couleur des caractères au format RGB: Green (0->255)
			$charB = 255;     // Couleur des caractères au format RGB: Blue (0->255)

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

			$charel = 'ABCDEFGHKLMNPRTWXYZ234569';       // Caractères autorisés

			if ($indiv->getGene(3)==0) $crypteasy = false;
			else $crypteasy = true;       // Create cryptograms "easy to read" (true / false) 
										// Alternatively compounds consonants and vowels.
			$charelc = 'BCDFGHKLMNPRTVWXZ';   // consonants to use when $crypteasy = true
			$charelv = 'AEIOUY';              // Vowels to use when $crypteasy = true

			$difuplow = false;          // Differentiates Maj / Min when entering the code (true, false)
			
		//	echo "charnbmin: $charnbmin | $charnbmax | ".$indiv->getGene(5)." <br>";
		
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

			$noisecolorchar  = 1;  // Noise: writing pixel color, lines, circles:
			                       // 1: Color writing characters
			                       // 2: Background Color
			                       // 3: Random color

			$brushsize = 1;        // Font size of princeaiu (in pixels)
			                       // 1 to 25 (the higher values ​​may cause
			                       // Internal Server Error on some versions of PHP / GD)
			                       // Does not work on older configurations PHP / GD

			$noiseup = true;      // noise is it above the write (true) or below (false)


			$image_name = "./captcha/".(string) $key.".png";
			generate_captcha($image_name);
			
		}
		
		
		
	}
	
}

?>