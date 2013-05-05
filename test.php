<?php
error_reporting(E_ALL);
include("ea_core.php");

$ea = new EA_Core();
$ea->clean();
$ea->init(10,3,20,0.1,0.8,1, 50);
$ea->start();
//	public function init($age_per_layer, $layer_num, $equil_size, $mut_rate, $cross_rate, $dying_rate, $max_age){


?>
