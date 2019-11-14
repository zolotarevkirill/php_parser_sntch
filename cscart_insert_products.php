<?php
require_once 'connection_cscart.php';
require_once 'connection.php';
error_reporting(E_ALL ^ E_WARNING );
ini_set("memory_limit","50000M");
ini_set('error_reporting', E_ALL);
set_time_limit(29600);
include('phpquery.php');


	$link_local = mysqli_connect($host_local, $user_local, $password_local, $database_local) or die("Ошибка " . mysqli_error($link_local));
	$query_link ="SELECT * FROM link_product";
	$result_local = mysqli_query($link_local, $query_link) or die("Ошибка " . mysqli_error($link_local));

	while($row_local = $result_local->fetch_row()) {
 		$product_link[]= $row_local;
	}


	foreach ($product_link as $v_link) {
		$product_url = 'https://santech.ru'.$v_link[1];

		$db_cscart = mysqli_connect($host, $user, $password, $database) 
    	or die("Ошибка " . mysqli_error($db_cscart));

		// $product_url = 'https://www.santech.ru/catalog/259/260/i786/';
			$contents = file_get_contents( $product_url,false, stream_context_create( array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false ) ) ) );

			$document = phpQuery::newDocument($contents);
			$product= [];

			$title = $document->find('.variant-list__info-title');
			$img = $document->find('.fancybox');
			$property_name = $document->find('.property__table-name');
			$property_value = $document->find('.property__table-value');
			$variant_title = $document->find('.variant-list__info-title');
			$variant_price = $document->find('.js-price-inner');
			$variant_code = $document->find('.variant-list__jde-code');
			$variant_availability = $document->find('.variant-list__price-availability');




			$inner_title = [];
			$inner_title_count_arr = 0;
			$inner_title_property = $document->find('.item-specs-col');

			$step = 0;
			$count = 0;

			foreach ($inner_title_property as $el) {
				
				// if(isset($inner_title[$step]))
				// {
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

				// }
			}


			$title_product = [];
			$price = [];
			$availability = [];
			$value= [];
			$code= [];

			foreach ($variant_code as $el) {
				$code[] =$el->textContent;
			}

			foreach ($variant_price as $el) {
				$price[] =$el->textContent;
			}

			foreach ($variant_availability as $el) {
				$availability[] =$el->textContent;
			}

			foreach ($property_value as $el) {
				$value[] =$el->textContent;
			}

			foreach ($title as $el) {
				$title_product[] = strip_tags($el->textContent);
			}


			$table = "<table>";
			foreach ($property_name as $property_k => $property_el) {
				$table = $table."<tr>";
					$table = $table."<td>".$property_el->textContent."</td>";
					$table = $table."<td>". $value[$property_k]."</td>";
					$table = $table."</tr>";
			}
			
			$table = $table."</table>";


			foreach ($title_product as $title_key => $title_value) {
				//Последний ID  продука + 1
				$last_row_q = "SELECT * FROM `cscart_products` ORDER BY product_id DESC LIMIT 1";
				$r = mysqli_query($db_cscart, $last_row_q) or die("Ошибка " . mysqli_error($db_cscart));
				$last_row_product = $r->fetch_row();

				$id = (int)$last_row_product[0] + 1;
				
				echo "<h1>".$title_key."</h1>";
				echo "<h1>".$id."</h1>";
			
				// availability
				$av = explode(" — ", $availability[$title_key]);
				$av = str_replace("м", "", $av);
				$av = str_replace(" ", "", $av);
				$amount = $av;


				$amount = $amount[1];

				$tmp = time();

				$price_result = strip_tags(trim($price[$title_key]));
				$price_result = str_replace(" ", "", $price_result);
				$price_result = str_replace("Р", "", $price_result);
				$price_result = str_replace(",", ".", $price_result);
				echo "<br><h2>".$price_result."</h2></br>";
				echo "<br><h2>".(int)$price_result."</h2></br>";
				echo "<br><h2>".round($price_result)."</h2></br>";
				$price_result = round($price_result);

				$titile = $title_value;
				$product_code = $code[$title_key];
				$p_url = $product_url;

				$q_insert_products = ("INSERT INTO `lnvkey5p_cscart`.`cscart_products` (`product_id`, `product_code`, `company_id`, `amount`, `timestamp`, `updated_timestamp`, `details_layout`, `facebook_obj_type`, `yml2_brand`, `yml2_origin_country`, `buy_now_url`, `parse_url`) VALUES ('{$id}', '{$product_code}', '1', '{$amount}', '{$tmp}', '{$tmp}', 'default', 'activity', '', '', '', '{$p_url}')");

				$q_insert_product_descriptions = ("INSERT INTO `lnvkey5p_cscart`.`cscart_product_descriptions` (`product_id`, `lang_code`, `product`, `full_description`) VALUES ('{$id}', 'ru', '{$titile}', '{$table}')");

				$limit = 1;
				$q_insert_products_property = ("INSERT INTO `cscart_product_prices` (`product_id`, `price`, `lower_limit`) VALUES ({$id}, {$price_result}, {$limit})");

				$parse_category_id =explode("/", $product_url);
				if(count($parse_category_id) == 8)
				{
					$parse_id = $parse_category_id[5];
				}
				if(count($parse_category_id) == 9)
				{
					$parse_id = $parse_category_id[6];
				}
				$q_parse = "SELECT * FROM `cscart_categories` WHERE parse_id = ${parse_id} LIMIT 1";
				$r_parse = mysqli_query($db_cscart, $q_parse) or die("Ошибка " . mysqli_error($db_cscart));
				$parse_arr = $r_parse->fetch_row();
				$parse_arr = $parse_arr[0];
				$q_insert_products_category = ("INSERT INTO `lnvkey5p_cscart`.`cscart_products_categories` (`product_id`, `category_id`) VALUES ({$id}, {$parse_arr})");

				echo $q_insert_products."<br/>";
				echo $q_insert_product_descriptions."<br/>";
				echo $q_insert_products_property."<br/>";

				$result_insert_products = mysqli_query($db_cscart, $q_insert_products) or die("Ошибка 1" . mysqli_error($db_cscart));
				$result = mysqli_query($db_cscart, $q_insert_product_descriptions) or die("Ошибка 2" . mysqli_error($db_cscart));
				$result = mysqli_query($db_cscart, $q_insert_products_property) or die("Ошибка 3" . mysqli_error($db_cscart));
				$result = mysqli_query($db_cscart, $q_insert_products_category) or die("Ошибка 4" . mysqli_error($db_cscart));

				$real_id = $result_insert_products->insert_id;

				foreach ($inner_title[$title_key] as $k1 => $value) {
					if($k1 % 2)
					{
						$inner_title_text[] = strip_tags(trim($value));
					}
				}

				foreach ($inner_title[$title_key] as $key => $v) {
					if($key % 2 == 0)
					{
						$inner_title_value[] = strip_tags($v);
					}
				}

				$product_id =  $id;

				foreach ($inner_title_text as $k_variant => $v_variant) {

					//  _____________
					$last_feature = "SELECT * FROM `cscart_product_features` ORDER BY feature_id DESC LIMIT 1";
					$r_last_feature = mysqli_query($db_cscart, $last_feature) or die("Ошибка 5 " . mysqli_error($db_cscart));
					$id_last_feature = $r_last_feature->fetch_row();

					$last_variant = "SELECT * FROM `cscart_product_feature_variants` ORDER BY variant_id DESC LIMIT 1";
					$r_last_variant = mysqli_query($db_cscart, $last_variant) or die("Ошибка 6 " . mysqli_error($db_cscart));
					$id_last_variant = $r_last_variant->fetch_row();

					$feature_id = (int)$id_last_feature[0] + 1;
					$variant_id = (int)$id_last_variant[0] + 1;
					// ______________

					$description = $inner_title_text[$k_variant];
					$variant = $inner_title_value[$k_variant];

					$row = [];
					$q_select_features = "SELECT * FROM `cscart_product_features_descriptions` WHERE description LIKE '%{$description}%'";
					$result = mysqli_query($db_cscart, $q_select_features) or die("Ошибка 7 " . mysqli_error($db_cscart));
					$row = $result->fetch_row();

					if(count($row) > 0)
					{
						$feature_id = $row[0];	
					}
			
					$q_variant = "INSERT INTO `lnvkey5p_cscart`.`cscart_product_features` (`feature_id`, `company_id`, `purpose`, `feature_style`, `filter_style`, `feature_type`, `display_on_product`, `display_on_catalog`) VALUES ('{$feature_id}', '1', 'find_products', 'text', 'checkbox', 'S', 'N', 'N')";
					echo $q_variant."<br/>";

					$q_varian_name = "INSERT INTO `lnvkey5p_cscart`.`cscart_product_features_descriptions` (`feature_id`, `description`, `lang_code`) VALUES ('{$feature_id}', '{$description}', 'ru')";
					echo $q_varian_name."<br/>";

					$q_insert_index = "INSERT INTO `lnvkey5p_cscart`.`cscart_ult_objects_sharing` (`share_company_id`, `share_object_id`, `share_object_type`) VALUES ('1', '{$feature_id}', 'product_features')";

					$q_variant_name_add_value = "INSERT INTO `lnvkey5p_cscart`.`cscart_product_feature_variants` (`variant_id`, `feature_id`, `color`) VALUES ('{$variant_id}', '{$feature_id}', '#ffffff')";

					$q_variant_value = "INSERT INTO `lnvkey5p_cscart`.`cscart_product_feature_variant_descriptions` (`variant_id`, `variant`, `lang_code`) VALUES ('{$variant_id}', '{$variant}', 'ru')";

					$q_varian_add_product = "INSERT INTO `lnvkey5p_cscart`.`cscart_product_features_values` (`feature_id`, `product_id`, `variant_id`, `lang_code`) VALUES ('{$feature_id}', '{$product_id}', '{$variant_id}', 'ru')";


					echo "<br><br>";
					echo $q_variant_name_add_value."<br/>";
					echo $feature_id."<br/>";
					echo $variant_id."<br/>";

				

					if(count($row) == 0)
					{
						$result_variant = mysqli_query($db_cscart, $q_variant) or die("Ошибка 8" . mysqli_error($db_cscart));
						$result = mysqli_query($db_cscart, $q_varian_name) or die("Ошибка  9" . mysqli_error($db_cscart));
						$result = mysqli_query($db_cscart, $q_insert_index) or die("Ошибка 10" . mysqli_error($db_cscart));
					}
					

					$result_variant_value = mysqli_query($db_cscart, $q_variant_name_add_value) or die("Ошибка 13" . mysqli_error($db_cscart));
					$result = mysqli_query($db_cscart, $q_variant_value) or die("Ошибка 11" . mysqli_error($db_cscart));
					$result = mysqli_query($db_cscart, $q_varian_add_product) or die("Ошибка 12" . mysqli_error($db_cscart));
				}

			}
			
		// die();
	}



	