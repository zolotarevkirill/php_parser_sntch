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



// title
echo $title;

// images
foreach ($img as $el) {
	echo "<p><img src='https://www.santech.ru".$el->getAttribute('href')."' width='' heigth=''></p>";
}


echo "<h2>Варианты</h2>";
$price = [];
$availability = [];

foreach ($variant_price as $el) {
		$price[] =$el->textContent;
}

foreach ($variant_availability as $el) {
		$availability[] =$el->textContent;
}

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
	echo "</tr>";
}
echo "</table>";

echo "<h2>Характеристики</h2>";
// table property
$value = [];
foreach ($property_value as $el) {
		$value[] =$el;
}
echo "<table>";
foreach ($property_name as $k => $el) {
	echo "<tr>";
		echo "<td>";
		echo  $el->textContent;
		echo "</td>";
		echo "<td>";
		echo  $value[$k]->textContent;
		echo "</td>";
	echo "</tr>";
}
echo "</table>";