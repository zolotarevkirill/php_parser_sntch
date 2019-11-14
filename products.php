<?php

include('phpquery.php');

$products = [];

$catalog_url = 'https://www.santech.ru/catalog/'.$_GET['secion_id'];

$contents = file_get_contents( $catalog_url,false, stream_context_create( array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false ) ) ) );

$products= [];
$products_links = [];

$document = phpQuery::newDocument($contents);

$title = $document->find('.products__name');
$link = $document->find('.products__info-block > a');

foreach ($title as $el) {
	if(!in_array($el->textContent, $products))
	{
		array_push($products, $el->textContent);
	}
	
}

foreach ($link as $el) {
	if(!in_array($el->getAttribute('href'), $products_links))
			{
				 array_push($products_links, $el->getAttribute('href'));
			}
}

$page = 1;
$max_page = 30;


while ($page < $max_page) {
	$page = $page + 1;
	$catalog_url = 'https://www.santech.ru'.$_GET['secion_id'];
	$document = phpQuery::newDocument($contents);

	$title = $document->find('.products__name');
	$link = $document->find('.products__info-block > a');

	if(strlen($title))
	{
	  foreach ($title as $el) {
		if(!in_array($el->textContent, $products))
			{
				array_push($products, $el->textContent);
			}
	  }

	  if(strlen($link))
	  {
		foreach ($link as $el) {
			if(!in_array($el->getAttribute('href'), $products_links))
			{
				 array_push($products_links, $el->getAttribute('href'));
			}
		 
	    }	
	  }

	}
	else
	{
		echo "Last page: ".$page."<br/>";
		echo $catalog_url;
		echo "<br/>";
		var_dump(is_array($title));
		echo "<br/>";
		echo count($title);
		echo "<br/>";
		echo $title[0]->textContent;
	   	break;
	}
}

foreach ($products as $key => $value) {
	echo "<p><a href='product_detail.php?url=".$products_links[$key]."'>".$value."</a></p>";
}
  



