<?php
// error_reporting(0);
set_time_limit(3600);
require_once 'connection.php';
include('phpquery.php');

$link = mysqli_connect($host, $user, $password, $database) 
    or die("Ошибка " . mysqli_error($link));

$STEP = '';
if($STEP == '' or $_GET['step'] == '')
{
	ob_start();

	//Очищаем категории
	$query ="TRUNCATE TABLE category;";
	$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link)); 
	if($result)
	{
	    // echo "Удаление успешно";
	}


	//Заполняем категории
	$catalog_url = 'https://www.santech.ru/catalog/';

	$contents = file_get_contents( $catalog_url,false, stream_context_create( array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false ) ) ) );

	$document = phpQuery::newDocument($contents);

	$category = $document->find('.title');
	
	foreach ($category as $el) 
	{
		$id = str_replace('/catalog/', '', $el->getAttribute('href'));
		$id = str_replace('/', '', $id);
		$name = $el->textContent;
		$query ="INSERT INTO category (id, name) VALUES ('{$id}', '{$name}');";
		$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link)); 
	}

	
	$STEP = 'section';
	ob_end_flush();
}

if($STEP == 'section' or $_GET['step'] == 'section')
{
	ob_start();

	//Очищаем категории
	$query ="TRUNCATE TABLE section;";
	$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link)); 

	$query ="SELECT * FROM category;";
	$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));

	while($row = $result->fetch_row()) {
 		$result_category[]=$row;
	}
	if(is_array($result_category))
	{
		foreach ($result_category as $key => $value) {

			$catalog_url = 'https://santech.ru/catalog/'.$value[0].'/';
			$contents = file_get_contents( $catalog_url,false, stream_context_create( array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false ) ) ) );

			$document = phpQuery::newDocument($contents);

			$section = $document->find('.title');

			foreach ($section as $el) {
				$id = $el->getAttribute('href');
				$id = str_replace('/catalog/', '', $el->getAttribute('href'));
				$id = explode('/', $id);
				$id_category = $id[0];
				$id_section = $id[1];
				$name = $el->textContent;
				$query ="INSERT INTO section (id_category, id_section, name) VALUES ('{$id_category}', '{$id_section}', '{$name}');";
				$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link)); 
			}
		}

	}

	$STEP = 'links';
	ob_start();	
}

if($STEP == 'links' or $_GET['step'] == 'links')
{
	ob_start();
	$query ="TRUNCATE TABLE link_product;";
	$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link)); 

	$query ="SELECT * FROM section;";
	$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));

	while($row = $result->fetch_row()) {
 		$result_section[]= $row;
	}

	$products = [];

	foreach($result_section as $value)
	{
		$catalog_url = 'https://santech.ru/catalog/'.$value[0].'/'.$value[1].'/';
		$contents = file_get_contents( $catalog_url,false, stream_context_create( array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false ) ) ) );

		$document = phpQuery::newDocument($contents);
		$product_link = $document->find('.products__info-block > a');

		foreach ($product_link as $el) {
			if(!in_array($el->getAttribute('href'), $products))
			{
				array_push($products, $el->getAttribute('href'));
			}
		}

		$page = 1;
		$max_page = 30;

		while ($page < $max_page) {
			$page = $page + 1;
			$catalog_url = 'https://santech.ru/catalog/'.$value[0].'/'.$value[1].'/';
			$document = phpQuery::newDocument($contents);
			$product_link = $document->find('.products__info-block > a');

			if(strlen($product_link))
			{
				foreach ($product_link as $el) {
					if(!in_array($el->getAttribute('href'), $products))
					{
						 array_push($products, $el->getAttribute('href'));
					}
				 
			    }	
			}
		}	
	}

	foreach ($products as $key => $value) {
		$id = str_replace('/catalog/', '', $value);
		$id = explode('/', $id);
		$id_category = $id[0];
		$id_section = $id[1];
		$id_product = $id[2];
		$query ="INSERT INTO link_product (id_category, id_section, id_product) VALUES ('{$id_category}', '{$id_section}', '{$id_product}');";
		$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link)); 
	}
  
	ob_start();	
}


// mysqli_close($link);
