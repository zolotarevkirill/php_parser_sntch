<?php
require_once 'connection.php';
require_once 'connection_cscart.php';

$db_cscart = mysqli_connect($host, $user, $password, $database) 
    or die("Ошибка " . mysqli_error($link));

$db_local = mysqli_connect($host_local, $user_local, $password_local, $database_local) 
    or die("Ошибка " . mysqli_error($link));

$query ="SELECT * FROM category;";

$result = mysqli_query($db_local, $query) or die("Ошибка " . mysqli_error($link));

while($row = $result->fetch_row()) {
 	$result_category[]=$row;
}

foreach ($result_category as $key => $value) {
	$q_insert_category = "INSERT INTO `lnvkey5p_cscart`.`cscart_categories` (`category_id`, `id_path`, `position`, `parse_id`) VALUES ('{$value[0]}', '{$value[0]}', '{$key}', '{$value[0]}')";
	$result = mysqli_query($db_cscart, $q_insert_category) or die("Ошибка " . mysqli_error($link));
	 $q_insert_category_description = "INSERT INTO `lnvkey5p_cscart`.`cscart_category_descriptions` (`category_id`, `lang_code`, `category`) VALUES ('{$value[0]}', 'ru', '{$value[1]}');";
	 $result = mysqli_query($db_cscart, $q_insert_category_description) or die("Ошибка " . mysqli_error($link));
}