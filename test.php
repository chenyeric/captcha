<?php
error_reporting(E_ALL);
include("ea_core.php");

//public function init($rate, $layer_num, $equil_size, $mut_rate, $cross_rate, $dying_rate)
$ea = new EA_Core();
$ea->init(10,10,3,0.8,0.8,1);
$ea->run();

/*
$pop = new Population();
$pop->init(20,0.1,0.1, 1, "layer_test");
$pop->evolve();
$pop->generate_image();

$pop_1 = new Population();
$pop_1->fill("layer_test");
$pop_1->dump();
$pop_1->evolve();
$pop_1->dump();

$pop_2 = new Population();
$pop_2->fill("layer_test");
$pop_2->cleanup();

$pop_3 = new Population();
$pop_3->fill("layer_test");
$pop_3->dump();
*/


/*$username="root";
$password="1234567";
$database="captcha";

//create a new table for this population
mysql_connect("localhost",$username,$password);
@mysql_select_db($database) or die( "Unable to select database");
$query = "CREATE TABLE "."test"."(id MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), geno BLOB, gfitness DOUBLE, bfitness DOUBLE)";
mysql_query($query);*/
?>