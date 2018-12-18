<?php
global $accesstoken;
function cm_create_featured_image($image_name,$image_data,$post_id)
{
	$upload_dir = wp_upload_dir();
   // $image_data = file_get_contents($image_url);
    //$filename = basename($image_url);
	$filename =time()."-".$image_name.".jpg";
    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file, $image_data);
	
	
    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    $res2= set_post_thumbnail( $post_id, $attach_id );
	
}
function cm_create_category_image($image_name,$image_data,$cat_id)
{
	$upload_dir = wp_upload_dir();
   // $image_data = file_get_contents($image_url);
    //$filename = basename($image_url);
	$filename =time()."-".$image_name.".jpg";
    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file, $image_data);
	
    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );	
	
    update_woocommerce_term_meta( $cat_id, 'thumbnail_id', absint( $attach_id ) );	
	
}
function retrieve_my_terms()
{	
	global $productcategory;
		$productcategory  = get_terms( array(
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
) );
return $productcategory;
}
add_action('init', 'retrieve_my_terms', 9);

function fetch_product_information()
{
		$username  = get_option( 'dexpro_user_name' );
		$password  = get_option( 'dexpro_password' );
		$url =  get_option( 'dexpro_product_listing_url' );	 
		if($url=='')
			{
				 $url = "https://learn.dexpro.io/api/warehouse/product";
			}
		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $username.':' . $password);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);		
		$result=json_decode($result);
				
		////// Code Start to Get Product Image /////////
	$counter=0;
	foreach($result as $productinfo)
	{
				////////// Code Start to Get product Category image //////////
				$categoryimage=$productinfo->productCategory->productCategoryImageURL;
				if($categoryimage!='')
				{
						$catimagekeyarray = explode("/",$categoryimage);
					
						$catimagejobid= $catimagekeyarray[3];	
						if($catimagejobid!='')
						{
							$imageurl = "https://learn.dexpro.io/api/core/jobdocuments/".$catimagejobid."/download";
							////// Code Start to Get Image Data ////////////
							$catchimage = curl_init($imageurl);  
							curl_setopt($catchimage, CURLOPT_USERPWD, $username.':' . $password);
							curl_setopt($catchimage, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($catchimage, CURLOPT_TIMEOUT, 10);
							curl_setopt($catchimage, CURLOPT_CUSTOMREQUEST, 'GET');
							curl_setopt($catchimage, CURLOPT_HTTPGET, 1);

							curl_setopt($catchimage, CURLOPT_SSL_VERIFYPEER, false);     
							$catimagedata = curl_exec($catchimage);
							$result[$counter]->catimagedata = $catimagedata;						
							///// End of Code to Get Image Data ////////////
						}
				}
				///////// End of Code to Get Product Category image /////////
		
		if($productinfo->id!='')
		{
			$productfetchurl  = "https://learn.dexpro.io/api/warehouse/product/".$productinfo->id;			
			
			/////////Code Start to Set Product Image /////////////
				$ch = curl_init($productfetchurl);
				curl_setopt($ch, CURLOPT_USERPWD, $username.':' . $password);
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
				curl_setopt($ch, CURLOPT_HTTPGET, 1);    
				$productresult = curl_exec($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);	
				$productinfos=json_decode($productresult);
				
				
			
				if(isset($productinfos->productImageURL))
				{
					
					$result[$counter]->productImageURL = $productinfos->productImageURL;
					$imagekeyarray = explode("/",$productinfos->productImageURL);
					
					$imagejobid= $imagekeyarray[3];	
					if($imagejobid!='')
					{
						$imageurl = "https://learn.dexpro.io/api/core/jobdocuments/".$imagejobid."/download";
						////// Code Start to Get Image Data ////////////
						$chimage = curl_init($imageurl);  
						curl_setopt($chimage, CURLOPT_USERPWD, $username.':' . $password);
						curl_setopt($chimage, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($chimage, CURLOPT_TIMEOUT, 10);
						curl_setopt($chimage, CURLOPT_CUSTOMREQUEST, 'GET');
						curl_setopt($chimage, CURLOPT_HTTPGET, 1);

						curl_setopt($chimage, CURLOPT_SSL_VERIFYPEER, false);     
						$imagedata = curl_exec($chimage);
						$result[$counter]->imagedata = $imagedata;						
						///// End of Code to Get Image Data ////////////
					}
				
				}	
				$counter++;
			//////// End of Code to Set Product Image ////////////
			
		}
	}
	////// End of Code to Get Product Image ////////		
	
		return $result;
}
function get_access_token_oauth()
{
		///////// Code Start to Get Oauth Access Token ///////
		$username  = get_option( 'dexpro_user_name' );
		$password  = get_option( 'dexpro_password' );
	
		
	$url = get_option( 'dexpro_auth_url' );
	
	if($url=='')
	{
		$url= "https://learn.dexpro.io/oauth/v2/token";
	}
	$client_id = get_option('dexpro_client_id');
	
	if($client_id=='')
	{
		$client_id = '2_ewkgi8rafhw8kw8gowgssw0okswg0s0088ocgwccwkcso40gs';
	}
	$client_secret = get_option('dexpro_client_secret');
	if($client_secret=='')
	{
		$client_secret = '1bzsegotmklc4c8kckc840c4c44kwcw8o48g8cwwoco884oo08';
	}	
		 $json = json_encode([
            'grant_type' => 'password',
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'username' => $username,
			'password' => $password
				]);
				
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_HTTPGET, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     
	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);		
	$result=json_decode($result);
	
	
	return $result;
	 ////// End of Code to Get Oauth Access Token ////////
}
function fetch_product_information_oauth()
{
	$result = get_access_token_oauth();
	if(isset($result->error))
	{
		return $result;
	}		
	global $accesstoken;
	$accesstoken = $result->access_token;	
	
	$authorization = "Authorization: Bearer ".$accesstoken;
	$url =  get_option( 'dexpro_product_listing_url' );	 
	if($url=='')
		{
			 $url = "https://learn.dexpro.io/api/warehouse/product";
		}
	 $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_HTTPGET, 1);
	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);	
	curl_close($ch);
	$result=json_decode($result);
	
	////// Code Start to Get Product Image /////////
	$counter=0;
	foreach($result as $productinfo)
	{
				////////// Code Start to Get product Category image //////////
				$categoryimage=$productinfo->productCategory->productCategoryImageURL;
				if($categoryimage!='')
				{
					$catimagekeyarray = explode("/",$categoryimage);
				
					$catimagejobid= $catimagekeyarray[3];	
					if($catimagejobid!='')
					{
						$imageurl = "https://learn.dexpro.io/api/core/jobdocuments/".$catimagejobid."/download";
						////// Code Start to Get Image Data ////////////
						$catchimage = curl_init($imageurl);  
						curl_setopt($catchimage, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
						curl_setopt($catchimage, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($catchimage, CURLOPT_TIMEOUT, 10);
						curl_setopt($catchimage, CURLOPT_CUSTOMREQUEST, 'GET');
						curl_setopt($catchimage, CURLOPT_HTTPGET, 1);

						curl_setopt($catchimage, CURLOPT_SSL_VERIFYPEER, false);     
						$catimagedata = curl_exec($catchimage);
						$result[$counter]->catimagedata = $catimagedata;						
						///// End of Code to Get Image Data ////////////
					}
				}
				///////// End of Code to Get Product Category image /////////
		
		if($productinfo->id!='')
		{
			$productfetchurl  = "https://learn.dexpro.io/api/warehouse/product/".$productinfo->id;			
			
			/////////Code Start to Set Product Image /////////////
				$ch = curl_init($productfetchurl);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
				curl_setopt($ch, CURLOPT_HTTPGET, 1);    
				$productresult = curl_exec($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);	
				$productinfos=json_decode($productresult);
				if(isset($productinfos->productImageURL))
				{
					
					$result[$counter]->productImageURL = $productinfos->productImageURL;
					$imagekeyarray = explode("/",$productinfos->productImageURL);
					
					$imagejobid= $imagekeyarray[3];	
					if($imagejobid!='')
					{
						$imageurl = "https://learn.dexpro.io/api/core/jobdocuments/".$imagejobid."/download";
						////// Code Start to Get Image Data ////////////
						$chimage = curl_init($imageurl);  
						curl_setopt($chimage, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
						curl_setopt($chimage, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($chimage, CURLOPT_TIMEOUT, 10);
						curl_setopt($chimage, CURLOPT_CUSTOMREQUEST, 'GET');	
						curl_setopt($chimage, CURLOPT_HTTPGET, 1);

						curl_setopt($chimage, CURLOPT_SSL_VERIFYPEER, false);     
						$imagedata = curl_exec($chimage);
						$result[$counter]->imagedata = $imagedata;						
						///// End of Code to Get Image Data ////////////
					}
				
				}	
				$counter++;
			//////// End of Code to Set Product Image ////////////
			
		}
	}
	////// End of Code to Get Product Image ////////
	return $result;
}
////////// Function to get Stock Availability //////
function fetch_product_stock_information()
{
	$username  = get_option( 'dexpro_user_name' );
	$password  = get_option( 'dexpro_password' );
	$url =  get_option( 'dexpro_product_inventory_url' );	 
		
		if($url=='')
			{
				 $url = "https://learn.dexpro.io/api/warehouse/availableinventory/generalavailableinventory";
			}
		
		
	$ch = curl_init($url);
	if( get_option( 'dexpro_authentication_type' )=='basic')
		{		
			
			 curl_setopt($ch, CURLOPT_USERPWD, $username.':' . $password);
			 curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		}elseif( get_option( 'dexpro_authentication_type' )=='oauth2')
		{	
			global $accesstoken;
			//$accesstoken = $_SESSION['accesstoken'];			
			
			$authorization = "Authorization: Bearer ".$accesstoken;						
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
		}        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);		
		$result=json_decode($result);
		return $result;
}
////////// End of Function to get Stock Availability ////////

add_action('init','dexpro_import_product',11);
function dexpro_import_product()
{
	if(isset($_POST['action']) && $_POST['action']=='product_import' ) {
		
		if( get_option( 'dexpro_authentication_type' )=='basic')
		{		
			
			$returninfo = fetch_product_information();
			
		}elseif( get_option( 'dexpro_authentication_type' )=='oauth2')
		{			
			$returninfo = fetch_product_information_oauth();
		}
		global $stockinfo;
		$stockinfo = fetch_product_stock_information();
		
		if(isset($returninfo->error))
		{
			global $apierror;
			$apierror= $returninfo->error_description;
		}else
		{
			$productinfos = array();
			$productinfos = $returninfo;
			global 	$newaddedcat;
			$newaddedcat = array();
			foreach($productinfos as $productinfo)
			{			
				$code= $productinfo->code;
				if($code!='')
				{
					set_product_info($productinfo);
				}
				
			}
			global $totalproduct;
			$totalproduct=count($productinfos);			
		}
	}
}
function set_product_info($productinfo)
{
	global $wpdb;	
		$code= $productinfo->code;	
		$post_id = $wpdb->get_var(" select post_id from ".$wpdb->prefix."postmeta where meta_key='product_code' and meta_value='$code' ");
		
		if($post_id!='')
		{
				cm_update_product($post_id,$productinfo,$stockinfo);		
		}else
		{			
				cm_create_product($productinfo,$stockinfo);
		}		
}
function cm_create_product($productinfo)
{	
	if($productinfo->name!='')
	{			
		$productcat=$productinfo->productCategory->name;
		global $productcategory;
		$selectedcategory='';
		
		foreach ($productcategory as $procat)
		{
			if($productcat==$procat->name)
			{
				$selectedcategory=$procat->term_id;
			}
			
		}
		global 	$newaddedcat;
		$counter=0;
		if($selectedcategory=='')
		{
			
			foreach ($newaddedcat as $newcat)
			{
				if($newcat['name']!='')
				{
					if($productcat==$newcat['name'])
					{
						$selectedcategory=$newcat['term_id'];
					}
					$counter++;
				}		
			}
		}
		if($selectedcategory=='')
		{
			$productcategoryinfo=wp_insert_term($productcat,'product_cat');	
			
			if(!isset($productcategoryinfo->errors))
			{				
				if(is_array($productcategoryinfo))
				{								
					$selectedcategory=$productcategoryinfo['term_id'];					
					$newaddedcat[$counter]['name']=$productcat;					
					$newaddedcat[$counter]['term_id']=$selectedcategory;				
				}				
			
			}
		}	
			if(isset($productinfo->catimagedata) && $_REQUEST['import_image']=='yes' )
			{
				if($selectedcategory!='' && $productinfo->catimagedata!='' )
				{
					cm_create_category_image($productcat,$productinfo->catimagedata,$selectedcategory);
				}
			}
		
		$desc='';
	
		$postinfo= array(
		'post_title'    => wp_strip_all_tags( $productinfo->name ),
		'post_content'  => $desc,
		'post_type'   => 'product',
		'post_status'   => 'publish',			
		'post_author'   => 1);
		$post_id = wp_insert_post( $postinfo );
		$catarray =array($selectedcategory);
		wp_set_object_terms( $post_id,$catarray, 'product_cat' );	
		update_post_meta($post_id,'product_code',$productinfo->code);
		update_post_meta($post_id,'_sku',$productinfo->code);
		update_post_meta($post_id,'_height',$productinfo->packHeight);
		update_post_meta($post_id,'_length',$productinfo->packLength);
		update_post_meta($post_id,'_width',$productinfo->packWidth);
		update_post_meta($post_id,'_weight',$productinfo->tareWeight);
		update_post_meta($post_id,'_regular_price',$productinfo->salesPrice);
		update_post_meta($post_id,'_price',$productinfo->salesPrice);
		update_post_meta($post_id,'unit_of_measurement',$productinfo->salesUnitOfMeasure->code);
		
		if(isset($productinfo->productImageURL) && $productinfo->productImageURL!='' && $_REQUEST['import_image']=='yes' )
		{			
			cm_create_featured_image($productinfo->name,$productinfo->imagedata,$post_id  ); 						
		}
		
		
		if($productinfo->isNonStocked==1)
		{
			update_post_meta($post_id,'_manage_stock','no');
		}else
		{
			update_post_meta($post_id,'_manage_stock','yes');
			$instock = 'false';
			global $stockinfo;
			foreach($stockinfo->products as $productstock)
			{
				if($productstock->productId==$productinfo->id)
				{
					update_post_meta($post_id,'_stock_status','instock');
					update_post_meta($post_id,'_stock',$productstock->quantity);
					$instock="true";
				}
				
			}
			if($instock=='false')
			{
				update_post_meta($post_id,'_stock_status','outofstock');
			}
		
		}
		//////// End of Code to manage stock information /////////
	}
	
	
}

function cm_update_product($post_id,$productinfo)
{
	if($productinfo->name!='')
	{			
		$productcat=$productinfo->productCategory->name;
		global $productcategory;
		$selectedcategory='';
		
		foreach ($productcategory as $procat)
		{
			if($productcat==$procat->name)
			{
				$selectedcategory=$procat->term_id;
			}
			
		}
		global 	$newaddedcat;
		$counter=0;
		if($selectedcategory=='')
		{
			
			foreach ($newaddedcat as $newcat)
			{
				if($newcat['name']!='')
				{
					if($productcat==$newcat['name'])
					{
						$selectedcategory=$newcat['term_id'];
					}
					$counter++;
				}		
			}
		}
		if($selectedcategory=='')
		{
			$productcategoryinfo=wp_insert_term($productcat,'product_cat');	
			
			if(!isset($productcategoryinfo->errors))
			{				
				if(is_array($productcategoryinfo))
				{			
					
						$selectedcategory=$productcategoryinfo['term_id'];					
						$newaddedcat[$counter]['name']=$productcat;					
						$newaddedcat[$counter]['term_id']=$selectedcategory;	
				
				}				
			
			}
		}		
		
			if(isset($productinfo->catimagedata) && $_REQUEST['import_image']=='yes')
			{
				if($selectedcategory!='' && $productinfo->catimagedata!='' )
				{
					cm_create_category_image($productcat,$productinfo->catimagedata,$selectedcategory);
				}
			}
			
		$desc='';
	
		$postinfo= array(
		'ID'   => $post_id,
		'post_title'    => wp_strip_all_tags( $productinfo->name ),
		'post_content'  => $desc,
		'post_type'   => 'product',
		'post_status'   => 'publish',			
		'post_author'   => 1);
		$post_id = wp_update_post( $postinfo );
		$catarray =array($selectedcategory);
	
		wp_set_object_terms( $post_id,$catarray, 'product_cat' );		 
		update_post_meta($post_id,'product_code',$productinfo->code);
		update_post_meta($post_id,'_sku',$productinfo->code);
		update_post_meta($post_id,'_height',$productinfo->packHeight);
		update_post_meta($post_id,'_length',$productinfo->packLength);
		update_post_meta($post_id,'_width',$productinfo->packWidth);
		update_post_meta($post_id,'_weight',$productinfo->tareWeight);
		update_post_meta($post_id,'_regular_price',$productinfo->salesPrice);
		update_post_meta($post_id,'_price',$productinfo->salesPrice);
		update_post_meta($post_id,'unit_of_measurement',$productinfo->salesUnitOfMeasure->code);
		
		if(isset($productinfo->productImageURL) && $productinfo->productImageURL!='' && $_REQUEST['import_image']=='yes' )
		{			
			cm_create_featured_image($productinfo->name,$productinfo->imagedata,$post_id  ); 						
		}
		
		///////// Code Start to manage stock information /////////		
		if($productinfo->isNonStocked==1)
		{
			update_post_meta($post_id,'_manage_stock','no');
		}else
		{
			update_post_meta($post_id,'_manage_stock','yes');
			$instock = 'false';
			global $stockinfo;
			foreach($stockinfo->products as $productstock)
			{
				if($productstock->productId==$productinfo->id)
				{
					update_post_meta($post_id,'_stock_status','instock');
					update_post_meta($post_id,'_stock',$productstock->quantity);
					$instock="true";
				}
				
			}
			if($instock=='false')
			{
				update_post_meta($post_id,'_stock_status','outofstock');
			}
		
		}
		//////// End of Code to manage stock information /////////
		
	}
}

if ( is_admin() ) {
		add_action( 'admin_menu', 'dexpro_admin_menu' );
}

function dexpro_admin_menu() {
	add_menu_page( 'Dexpro Data Integration', 'Dexpro Integration', 'administrator', 'dexpro-data-integration-page', 'dexpro_data_integration_admin_function', plugins_url( 'dexpro-data-api-integration/images/menu_icon.png' ) );	
	add_submenu_page( 'dexpro-data-integration-page', 'Dexpro Product Import', 'Dexpro Product Import', 'manage_options', 'dexpro_product_import', 'dexpro_product_import_function' );
}

function dexpro_data_integration_admin_function() {
	
	
	
	?>
<style type="text/css">
.wrap select {
	width: 333px;
}
</style>
<div class="wrap">
  <h2 style='margin-bottom:25px;font-size:26px;'>
	<?php _e( 'Dexpro Data Integration Settings', 'dexpro_data' ); ?>
  </h2>
  <?php if (!is_plugin_active('woocommerce/woocommerce.php' ) ) { ?>
   <div id="message" class="notice notice-error is-dismissible"><p><?php  _e("Dexpro Integration Plugin requires woocommerce plugin to work properly, please <a href='".admin_url()."plugins.php'>install woocommerce plugin</a> .");?></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
  <?php } ?>
  <form method="post" action="options.php">
	<?php wp_nonce_field( 'update-options' ); ?>
	<div class="inside">
	  <h2>
		<?php _e( 'General Settings', 'dexpro_data' ); ?>
	  </h2>
	  <div class="table">
		<table class="form-table">
		 <tr valign="top">
			<td width="300" align="right"><label for="dexpro_authentication_type">
				<?php _e( 'Authentication Type', 'dexpro_data' ); ?>
			  </label></td>
			<td  size="50%"><select   id="dexpro_authentication_type" name="dexpro_authentication_type"  >
			<option value="basic" <?php if( get_option( 'dexpro_authentication_type' )=='basic') { _e('selected');}?> >Basic</option>
			<option value="oauth2" <?php if( get_option( 'dexpro_authentication_type' )=='oauth2') { _e('selected');}?> >Oauth2</option>
			</select></td>
		  </tr>
		   <tr valign="top">
			<td width="300" align="right"><label for="dexpro_auth_url">
				<?php _e( 'Config. Auth URL', 'dexpro_data' ); ?>
			  </label></td>
			<td><input type="text" id="dexpro_auth_url" size="50%"  readonly 
onfocus="this.removeAttribute('readonly');" name="dexpro_auth_url" value="<?php _e( get_option( 'dexpro_auth_url' ) ); ?>"/></td>
		  </tr>
		  <tr valign="top">
			<td width="300" align="right"><label for="dexpro_product_listing_url">
				<?php _e( 'Config. Product URL', 'dexpro_data' ); ?>
			  </label></td>
			<td><input type="text" id="dexpro_product_listing_url" size="50%"  readonly 
onfocus="this.removeAttribute('readonly');" name="dexpro_product_listing_url" value="<?php _e( get_option( 'dexpro_product_listing_url' ) ); ?>"/></td>
		  </tr>
		  <tr valign="top">
			<td width="300" align="right"><label for="dexpro_product_inventory_url">
				<?php _e( 'Config. Product Inventory URL', 'dexpro_data' ); ?>
			  </label></td>
			<td><input type="text" id="dexpro_product_inventory_url" size="50%"  readonly 
onfocus="this.removeAttribute('readonly');" name="dexpro_product_inventory_url" value="<?php _e( get_option( 'dexpro_product_inventory_url' ) ); ?>"/></td>
		  </tr> 
		   <tr valign="top">
			<td width="300" align="right"><label for="dexpro_user_name">
				<?php _e( 'Username', 'dexpro_data' ); ?>
			  </label></td>
			<td><input type="text" id="dexpro_user_name" size="50%"  readonly 
onfocus="this.removeAttribute('readonly');" name="dexpro_user_name" value="<?php _e( get_option( 'dexpro_user_name' ) ); ?>"/></td>
		  </tr>
		    <tr valign="top">
			<td width="300" align="right"><label for="dexpro_password">
				<?php _e( 'Password', 'dexpro_data' ); ?>
			  </label></td>
			<td><input type="password" id="dexpro_password" autocomplete="off" readonly 
onfocus="this.removeAttribute('readonly');" size="50%" name="dexpro_password" value="<?php _e( get_option( 'dexpro_password' ) ); ?>"/></td>
		  </tr>
		    <tr valign="top">
			<td width="300" align="right"><label for="dexpro_client_id">
				<?php _e( 'Client ID', 'dexpro_data' ); ?>
			  </label></td>
			<td><input type="text" id="dexpro_client_id" size="50%"  readonly 
onfocus="this.removeAttribute('readonly');" name="dexpro_client_id" value="<?php _e( get_option( 'dexpro_client_id' ) ); ?>"/></td>
		  </tr>
		  <tr valign="top">
			<td width="300" align="right"><label for="dexpro_client_secret">
				<?php _e( 'Client Secret', 'dexpro_data' ); ?>
			  </label></td>
			<td><input type="text" id="dexpro_client_secret" size="50%"  readonly 
onfocus="this.removeAttribute('readonly');" name="dexpro_client_secret" value="<?php _e( get_option( 'dexpro_client_secret' ) ); ?>"/></td>
		  </tr>
		  
	   </table>
	  </div>
	
	</div>
	<table width="800" border="0" cellspacing="0" cellpadding="0">
	  <tr><br />
		<td width="334"><div align="right"></div></td>
		<td width="460"><input type="submit" class="button button-primary button-large" value="<?php _e( 'Save Settings' ); ?>" name="submit_options" id="submit_options" /></td>
	  </tr>
	</table>
	</td>
	<br />
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="dexpro_authentication_type,dexpro_auth_url,dexpro_product_listing_url,dexpro_product_inventory_url,dexpro_user_name,dexpro_password,dexpro_client_id,dexpro_client_secret"/>
  </form>

</div>
	<?php

}
function dexpro_product_import_function()
{

	?>
	<style type="text/css">
.wrap select {
	width: 333px;
}
</style>
<div class="wrap">
  <h2 style='margin-bottom:25px;font-size:26px;'>
	<?php _e( 'Dexpro Product Import', 'dexpro_data' ); ?>
  </h2> <?php if (!is_plugin_active('woocommerce/woocommerce.php' ) ) { ?>
   <div id="message" class="notice notice-error is-dismissible"><p><?php  _e("Dexpro Integration Plugin requires woocommerce plugin to work properly, please <a href='".admin_url()."plugins.php'>install woocommerce plugin</a> .");?></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
  <?php } ?>
   <?php
   global $apierror;
   global $totalproduct;
	if(isset($apierror) && $apierror!='')
	{ ?>
  <div id="message" class="notice notice-error is-dismissible"><p><strong>Error:</strong> <?php  _e($apierror);?></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
<?php
		
	}else if($totalproduct>0 ) { ?>	
	 <div id="message" class="updated notice notice-success is-dismissible"><p><?php  _e($totalproduct." Products imported or updated successfully.");?></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
	 <?php } ?>
  <form method="post" action="">	
  <input type="hidden" name="action" value="product_import">
	<div class="inside">
	
	  <div class="table">
		<table class="form-table">
		 <tr valign="top">
			<td width="300" align="right"><label for="dexpro_authentication_type">
				<?php _e( 'Authentication Type', 'dexpro_data' ); ?>
			  </label></td>
			<td  size="50%"><?php _e( get_option( 'dexpro_authentication_type' ) ); ?></td>
		  </tr>
		  <tr valign="top">
			<td width="300" align="right"><label for="dexpro_config_url">
				<?php _e( 'Config. Auth URL', 'dexpro_data' ); ?>
			  </label></td>
			<td><?php _e( get_option( 'dexpro_auth_url' ) ); ?></td>
		  </tr>
		   <tr valign="top">
			<td width="300" align="right"><label for="dexpro_config_url">
				<?php _e( 'Config. Product URL', 'dexpro_data' ); ?>
			  </label></td>
			<td><?php _e( get_option( 'dexpro_product_listing_url' ) ); ?></td>
		  </tr>
		  	<td width="300" align="right"><label for="dexpro_config_url">
				<?php _e( 'Config. Inventory URL', 'dexpro_data' ); ?>
			  </label></td>
			<td><?php _e( get_option( 'dexpro_product_inventory_url' ) ); ?></td>
		  </tr>
		   <tr valign="top">
			<td width="300" align="right"><label for="dexpro_user_name">
				<?php _e( 'Username', 'dexpro_data' ); ?>
			  </label></td>
			<td><?php _e( get_option( 'dexpro_user_name' ) ); ?></td>
		  </tr>
		  		   <tr valign="top">
			<td width="300" align="right"><label for="dexpro_user_name">
				<?php _e( 'Import Images', 'dexpro_data' ); ?>
			  </label></td>
			<td><input type="checkbox" name="import_image" value="yes" ></td>
		  </tr>
	   </table>
	  </div>
	
	</div>
	<table width="800" border="0" cellspacing="0" cellpadding="0">
	  <tr><br />
		<td width="334"><div align="right"></div></td>
		<td width="460"><input type="submit" class="button button-primary button-large" value="<?php _e( 'Synchronize Products' ); ?>" name="import_product" id="import_product" /></td>
	  </tr>
	</table>
	</td>
	
  </form>

</div>
	<?php
}
