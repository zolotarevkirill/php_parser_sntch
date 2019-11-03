<?php
include('phpquery.php');

$catalog_url = 'https://*/catalog/'.$_GET['section'].'/';

$contents = file_get_contents( $catalog_url,false, stream_context_create( array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false ) ) ) );

$document = phpQuery::newDocument($contents);
  
$hentry = $document->find('.title');

echo "<a href='category.php'>Назад</a><br/>";

foreach ($hentry as $el) {
	$link = $el->getAttribute('href');
	echo "<a href='products.php?secion_id=".$link."'>".$el->textContent."</a><br/>";
}

echo "<a href='category.php'>Назад</a><br/>";
