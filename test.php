<?php
error_reporting(E_ALL);
include("ea_core.php");

$ea = new EA_Core();
$ea->clean();
$ea->init(3,1,2,0.1,0.5,1, 3);
$ea->start();
//	public function init($age_per_layer, $layer_num, $equil_size, $mut_rate, $cross_rate, $dying_rate, $max_age){


?>