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


include('phpquery.php');

$product = [];

$product_url = 'https://www.santech.ru'.$_GET['url'];

$contents = file_get_contents( $product_url,false, stream_context_create( array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false ) ) ) );

$product= [];

$document = phpQuery::newDocument($contents);

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

// title
echo $title;

// images

foreach ($img as $el) {
	echo "<p><img src='https://www.santech.ru".$el->getAttribute('href')."' width='' heigth=''></p>";
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

echo "<h2>Общее описание</h2>";
if(count($price) == 1)
{
	echo "<h2>Товар 1 его цена = ".$price[0]."</h2>";
}
echo "<table>";
foreach ($property_name as $k => $el) {
	echo "<tr>";
		echo "<td>";
		echo  $el->textContent;
		echo "</td>";
		echo "<td>";
		echo  $value[$k];
		echo "</td>";
	echo "</tr>";
}
echo "</table>";



echo "<h2>Товары</h2>";
echo "<table>";
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
echo "</table>";




