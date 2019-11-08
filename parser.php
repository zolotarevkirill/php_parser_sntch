<style type="text/css">
	table{
		border: 1px solid;
		border-collapse: collapse;
	}
	table tr{
		border: 1px solid;
	}
	table td{
		border: 1px solid;
	}
</style>
<?php
error_reporting(E_ALL ^ E_WARNING);
ini_set("memory_limit","30000M");
set_time_limit(14800);
require_once 'connection.php';
include('phpquery.php');

$link = mysqli_connect($host, $user, $password, $database) 
    or die("Ошибка " . mysqli_error($link));

if($_GET['step'] == 'category')
{
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

	
}

if($_GET['step'] == 'section')
{
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
}

if($_GET['step'] == 'links')
{
	$query ="TRUNCATE TABLE link_product;";
	$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link)); 

	// $query ="SELECT * FROM section WHERE `check`=0 LIMIT 1";
	$query ="SELECT * FROM section WHERE `check`=0";
	$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));

	while($row = $result->fetch_row()) {
 		$result_section[]= $row;
	}


	foreach($result_section as $value)
	{
		$products = [];

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

		$update_q = "UPDATE `santech`.`section` SET `check`='1' WHERE  `id_category`={$value[0]} AND `id_section`={$value[1]} AND `check`=0 LIMIT 1;";
		echo $update_q;
		mysqli_query($link, $update_q) or die("Ошибка " . mysqli_error($link));


		foreach ($products as $k => $v) {
			$query ="INSERT INTO link_product (link) VALUES ('{$v}');";
			$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link)); 
		}	
	}	
}


function clear_str($str)
{	
	$str = strip_tags($str);
	return $str;
}

echo "<table>";
if($_GET['step'] == 'csv')
{

	$query ="SELECT * FROM link_product";
	$result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));

	while($row = $result->fetch_row()) {
 		$product_link[]= $row;
	}


	foreach ($product_link as $k => $v) {
		$product_url = 'https://santech.ru'.$v[1];

		$contents = file_get_contents( $product_url,false, stream_context_create( array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false ) ) ) );
		$document = phpQuery::newDocument($contents);
		$product= [];

		$title = $document->find('.product__title');
		$img = $document->find('.fancybox');

		$property_name = $document->find('.property__table-name');
		$property_value = $document->find('.property__table-value');
		$variant_title = $document->find('.variant-list__info-title');
		$variant_price = $document->find('.js-price-inner');
		$variant_availability = $document->find('.variant-list__price-availability');

		$inner_title = [];
		$inner_title_count_arr = 0;
		$inner_title_property = $document->find('.item-specs-col');

		$step = 0;
		$count = 0;

		foreach ($inner_title_property as $el) {

			$count++;
			if(in_array($el->textContent, $inner_title[$step]))
		    {
		    	$step++;
		    }

		    if ($count % 2)
		    {
		    	$inner_title[$step][$count] = $el->textContent; 
		    }
		    else
		    {
		    	$inner_title[$step][$count] = $el->textContent;
		    }
    
		}


		$price = [];
		$availability = [];
		$value= [];

		foreach ($variant_price as $el) {
			$price[] =$el->textContent;
		}

		foreach ($variant_availability as $el) {
			$availability[] =$el->textContent;
		}

		foreach ($property_value as $el) {
			$value[] =$el->textContent;
		}

		echo "<tr>";
		// $fp = fopen('test.csv', 'a');
		// fwrite($fp,  clear_str($title).';');
		echo "<td>";
		echo  clear_str($title);
		echo "<td>";
		foreach ($img as $el) {
			echo "<td>";
				echo  clear_str($el->getAttribute('href'));
			echo "<td>";
			// fwrite($fp,  $el->getAttribute('href').';');
		}

		if(count($price) == 1)
		{

			echo "<td>";
				echo  clear_str($price[0]);
			echo "<td>";
			// fwrite($fp,  $price[0].';'. PHP_EOL);
		}
		echo "</tr>";

		echo "<tr>";
			echo "<td>";
				echo  'Описание';
			echo "<td>";
		// fwrite($fp, 'Описание;'. PHP_EOL);
		echo "</tr>";

		echo "<tr>";
		foreach ($property_name as $k => $el) {
			echo "<td>";
				echo  $el->textContent;
			echo "<td>";
			// fwrite($fp,  $el->textContent.';');
		}
		echo "</tr>";

		echo "<tr>";
		foreach ($property_name as $k => $el) {
			echo "<td>";
				echo  $value[$k];
			echo "<td>";
			// fwrite($fp,  $el->textContent.';');
		}
		echo "</tr>";
		

		echo "<tr>";
			echo "<td>";
				echo  'Варианты';
			echo "<td>";
		// fwrite($fp, 'Варианты;'. PHP_EOL);
		echo "</tr>";

		foreach ($variant_title as $k => $el) {
			echo "<tr>";
				echo "<td>";
				echo  $el->textContent;
				echo "</td>";
				echo "<td>";
				echo  $price[$k];
				echo "</td>";
				echo "<td>";
				echo  $availability[$k];
				echo "</td>";
				foreach ($inner_title[$k] as $k1 => $value) {
					if($k1 % 2)
					{
						echo "<td>";
						echo $value;
						echo "</td>";
					}
				}
			echo "</tr>";
			echo "<tr>";
			echo "<td>";
				echo  '';
				echo "</td>";
				echo "<td>";
				echo  '';
				echo "</td>";
				echo "<td>";
				echo  '';
				echo "</td>";
				foreach ($inner_title[$k] as $key => $v) {
					if($key % 2 == 0)
					{
						echo "<td>";
						echo $v;
						echo "</td>";
					}
				}
			echo "</tr>";
			
		}

		echo "<tr>";
		echo "<td>";
				echo  '-';
				echo "</td>";
				echo "<td>";
				echo  '-';
				echo "</td>";
				echo "<td>";
				echo  '-';
				echo "</td>";
		echo "</tr>";

		// fclose($fp);
		// die();
	}
}

echo "<table>";