<?php
require_once 'connection.php';
require_once 'connection_cscart.php';

$db_cscart = mysqli_connect($host, $user, $password, $database) 
    or die("Ошибка " . mysqli_error($db_cscart));

$db_local = mysqli_connect($host_local, $user_local, $password_local, $database_local) 
    or die("Ошибка " . mysqli_error($db_local));

$query ="SELECT * FROM section;";

$result = mysqli_query($db_local, $query) or die("Ошибка " . mysqli_error($db_local));

while($row = $result->fetch_row()) {
 	$result_section[]=$row;
}

foreach ($result_section as $key => $value) {
	$path = $value[0].'/'.$value[1];
	$q_insert_category = "INSERT INTO `lnvkey5p_cscart`.`cscart_categories` (`category_id`, `company_id`, `parent_id`, `id_path`, `position`, `parse_id`) VALUES ('{$value[1]}', '1', '{$value[0]}', '{$path}', '{$key}', '{$value[1]}')";
	$result = mysqli_query($db_cscart, $q_insert_category) or die("Ошибка " . mysqli_error($db_cscart));
	 $q_insert_category_description = "INSERT INTO `lnvkey5p_cscart`.`cscart_category_descriptions` (`category_id`, `lang_code`, `category`) VALUES ('{$value[1]}', 'ru', '{$value[2]}');";
	 $result = mysqli_query($db_cscart, $q_insert_category_description) or die("Ошибка " . mysqli_error($db_cscart));
}