<?php
error_reporting(E_ALL);
include("ea_basics.php");
include("antigate.php");

//In here, we will implement ALPS
Class EA_Core{
	private $username="root";
	private $password="1234567";
	private $database="captcha";
	
	private $layers = array();
	private $max_layer=0;
	private $current_layer =0;//current_layer contains the highest layer containing a population
	private $current_age = 0; //number of generations
	private $layer_rate =0; //how many generation is eacy layer?
	
	//anti-gate, mturk ids for each layer
	private $layer_ids;
	
	private $max_age = 0;
	
	//population specific parameters
	private $equil_size, $mut_rate, $cross_rate, $dying_rate;
	
	public function init($rate, $layer_num, $equil_size, $mut_rate, $cross_rate, $dying_rate, $max_age){
		
		$this->layer_rate = $rate;
		$this->max_layer = $layer_num;
		$this->current_layer =0; //initially we only have 1 layer
		
		//store population parameters so we can create new pop later on in run()
		$this->equil_size = $equil_size;
		$this->mut_rate = $mut_rate;
		$this->cross_rate = $cross_rate;
		$this->dying_rate = $dying_rate;
		
		//we only make the first layer for now
		$pop = new Population();
		$pop->init($equil_size,$mut_rate,$cross_rate, $dying_rate, "layer".$this->current_layer);
		//$pop->generate_image($this->current_layer);
		
		array_push($this->layers, $pop);
		
		$this->max_age = $max_age;
	}
	
	public function clean(){
		//create a new table for this population
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		$queries = array(
						 "drop table layer0",
						 "drop table layer1",
						 "drop table layer2",
						 "drop table layer3",
						 "drop table layer4",
						 "drop table layer0_antigate",
						 "drop table layer1_antigate",
						 "drop table layer2_antigate",
						 "drop table layer3_antigate",
						 "drop table layer4_antigate",
						 "drop table elitist");
		
		foreach($queries as $query){
			$result = mysql_query($query);
			if (!$result) {
				echo "Could not successfully run query 14 ($query) from DB: " . mysql_error();
				exit;
			}
		}
		mysql_close();
	}
	
	
	public function start(){
		while ($this->current_age < $this->max_age) $this->run();
	}
	
	//run does the following actions
	//1. check if it's time to jump layers
	//   -if so, push all layers up, and make new layer at layer 1
	//2. evolve every layer, put them up for evaluation
	//3. cleanup
	public function run(){
		//first, check if it's time to jump layers
		if ($this->current_age != 0 &&
			$this->current_age % $this->layer_rate ==0){
			for($i=sizeof($this->layers)-1; $i>=0 ;$i--){
				//we push the population from the current layer to the next layer
				if ($i+1 < $this->max_layer){ 
					//does the next layer exist yet?
					if(sizeof($this->layers) == $i+1){ 
						//the next layer doesn't exist, make a new layer!
						$pop = new Population();
						$pop->fill("layer".$i);
						$pop->switch_table("layer".($i+1));
						array_push($this->layers,$pop);
					}else{
						//the next layer exist, let's combine pops
						$this->layers[$i+1]->combine_pop($this->layers[$i]->extract_pop());
					}
				}
				
				//for the first layer we generate a new population
				if($i==0){
					$pop = new Population();
					$pop->init($this->equil_size, $this->mut_rate, $this->cross_rate, $this->dying_rate, $this->layers[$i]->get_table());
					$this->layers[$i] = $pop;
				}
				
			}
		}
		$this->current_age ++;
		

		
//echo "run() gets called with age: $this->current_age and ".sizeof($this->layers)." layers<br>\n";
		//we will evolve and generate offspring/images for all layers
		foreach($this->layers as $key=>$value){
			$value->dump();
			$value->evolve();
			$value->generate_image((string)$key);
		}
		
		//first, clear the layer_ids array
		$this->layer_ids = array();
		
		//then we put then up for evaluation on antigate, spin the loop until all images are solved
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		foreach($this->layers as $key=>$value){
			//first, we read all the captchas from the table
			$query = "SELECT * from ".$value->get_table()."_antigate";
			$result = mysql_query($query);
			if (!$result) {
			    echo "Could not successfully run query 11 ($query) from DB: " . mysql_error();
			    exit;
			}
			
			//allocate an array to store all the ids
			$tmp_array = array();
			array_push($this->layer_ids, $tmp_array);
			
			while($row = mysql_fetch_assoc($result)){
				$id = $row["id"];
				$text = $row["captcha_text"];
				$file = $row["image_filename"];
				//echo "image $file has the text $text <br>\n";
				
				$ids = array(0,0,0);
				$ids[0] = $id;
				//$ids[1] = upload($file, "9e3a331523a35c307e5440d84204d704", true, "antigate.com");
				$ids[2] = upload($file, "", true, "insecure.linshunghuang.com");
				array_push($this->layer_ids[$key], $ids);				
				
				//pass the image to anti-gate
				//$user_output = "test";//dummy
				//function recognize($filename,$apikey,$is_verbose = true,$sendhost = "antigate.com",$rtimeout = 5,$mtimeout = 120, $is_phrase = 0, $is_regsense = 0, $is_numeric = 0, $min_len = 0, $max_len = 0, $is_russian = 0)
				//$user_output = recognize($file,"9e3a331523a35c307e5440d84204d704",true,"antigate.com");
				
			}			
		}		
	
		//spin the loop until all images are solved
		foreach($this->layer_ids as $layer_num=>$layer_id){
			
			foreach($layer_id as $key=>$ids) {
			
			
				//$result1 = query($file, $ids[1], "9e3a331523a35c307e5440d84204d704", true, "antigate.com");
				$result1 = "antigate";
				$result2 = query($file, $ids[2], "", true, "insecure.linshunghuang.com");
				//$result2 = array("mturk", 12);
				//echo " id: ".$ids[0]." Mturk id: ".$ids[2].", antigate id: ".$id[1]."<br>\n";
	
				//if one of the captcha is unsolvable
				if (!$result1){
					$result1 = "~~~~~~~~~~";
				}
				if (!$result2){
					$result2 = "~~~~~~~~~~";
				}
				
				
				//write result back to db
				$query = "UPDATE ".$this->layers[$layer_num]->get_table()."_antigate SET antigate_answer='$result1' WHERE id=".$ids[0];
				$ret = mysql_query($query);
				if (!$ret){
					die('Invalid query: ' . mysql_error());
				}
				
				$query = "UPDATE ".$this->layers[$layer_num]->get_table()."_antigate SET mturk_answer='$result2[0]' WHERE id=".$ids[0];
				$ret = mysql_query($query);
				if (!$ret){
					die('Invalid query: ' . mysql_error());
				}
				
				$query = "UPDATE ".$this->layers[$layer_num]->get_table()."_antigate SET mturk_speed=$result2[1] WHERE id=".$ids[0];
				$ret = mysql_query($query);
				if (!$ret){
					die('Invalid query: ' . mysql_error());
				}
			}
		}
		mysql_close();
		
		//then, we cleanup the population and go for another generation
		foreach($this->layers as $key=>$pop){
			$pop->fill($pop->get_table());
			$pop->evaluate($key);
			$pop->fill($pop->get_table());
		}
		
	}
	
}

?>