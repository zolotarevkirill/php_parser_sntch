<?php
include('phpquery.php');

$catalog_url = 'https://www.santech.ru/catalog/';

$contents = file_get_contents( $catalog_url,false, stream_context_create( array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false ) ) ) );

$document = phpQuery::newDocument($contents);
  
$hentry = $document->find('.title');

foreach ($hentry as $el) {
	$link = str_replace('/catalog/', '', $el->getAttribute('href'));
	$link = str_replace('/', '', $link);
	echo "<a href='section.php?section=".$link."'>".$el->textContent."</a><br/>";
}

