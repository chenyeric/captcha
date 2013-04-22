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
	
	//population specific parameters
	private $equil_size, $mut_rate, $cross_rate, $dying_rate;
	
	public function init($rate, $layer_num, $equil_size, $mut_rate, $cross_rate, $dying_rate){
		
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
			for($i=0; $i<sizeof($this->layers);$i++){
				//we push the population from the current layer to the next layer
				if ($i+1 < $this->max_layer){
					//does the next layer exist yet?
					if(sizeof($this->layers) == $i+1){
						//the next layer doesn't exist, make a new layer!
						$pop = new Population();
						$pop->fill("layer".$i);
						$pop->switch_table("layer".$i+1);
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
echo "run() gets called with ".sizeof($this->layers)." layers<br>";
		//we will evolve and generate offspring/images for all layers
		foreach($this->layers as $key=>$value){
			$value->evolve();
			$value->generate_image((string)$key);
		}
		
		//then we put then up for evaluation on antigate, spin the loop until all images are solved
		mysql_connect("localhost",$this->username,$this->password);
		@mysql_select_db($this->database) or die( "Unable to select database");
		foreach($this->layers as $key=>$value){
			//first, we read all the captchas from the table
			$query = "SELECT * from ".$value->get_table()."_antigate";
			$result = mysql_query($query);
			if (!$result) {
			    echo "Could not successfully run query ($query) from DB: " . mysql_error();
			    exit;
			}
			
			while($row = mysql_fetch_assoc($result)){
				$id = $row["id"];
				$text = $row["captcha_text"];
				$file = $row["image_filename"];
				echo "image $file has the text $text <br>";
				
				//pass the image to anti-gate
				$user_output = "test";//dummy
				//function recognize($filename,$apikey,$is_verbose = true,$sendhost = "antigate.com",$rtimeout = 5,$mtimeout = 120, $is_phrase = 0, $is_regsense = 0, $is_numeric = 0, $min_len = 0, $max_len = 0, $is_russian = 0)
				//$user_output = recognize($file,"9e3a331523a35c307e5440d84204d704",true,"antigate.com");
				if ($user_output){
					echo "user output: $user_output for text $text.<br>";
				}
				
				//write result back to db
				$query = "UPDATE ".$value->get_table()."_antigate SET user_answer='$user_output' WHERE id=$id";

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
		}
		
	}
	
}

?>