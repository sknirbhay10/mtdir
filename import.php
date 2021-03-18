 <?php

  /*
    Template Name: Import Data
  */

//get_header();

function category($catID,$subcatid=0)
{
    global $wpdb;
    $sql = "SELECT catslug FROM category where catid=$catID";
    $results = $wpdb->get_results($sql);
    
    foreach( $results as $result ) {
      $data[]= $result->catslug;
    
    }
	if($subcatid){
	
		$sql1 = "SELECT catslug FROM category where catid=$subcatid";
		$results1 = $wpdb->get_results($sql1);
		
		foreach( $results1 as $result1 ) {
		  $data[]= $result1->catslug;		
		}
	}
    return $data;
}

/* User Details */
function user_data($userID)
{
    global $wpdb;
    $sql = "SELECT *FROM profile_users where uid=$userID";
    $results = $wpdb->get_results($sql);
    
    foreach( $results as $result ) {
      $data['fname']= $result->fname;
      $data['lname']= $result->lname;
      $data['email']= $result->email;
    }
    return $data;
}



function importDB($offset, $limit)
  {
    global $wpdb;
    //$where='Where listing_type="Paid" and payment_status="Paid"';
    $sql = "SELECT *FROM listing ORDER BY busiid ASC limit $offset, $limit";
    $results = $wpdb->get_results($sql);


    //print_r($results);


    $lp_listingpro_options=array();
    $listing_plan_data=array();
     
    foreach( $results as $result ) {

      $lp_listingpro_options['gAddress']=$result->address;
      $lp_listingpro_options['latitude']=$result->lat;
      $lp_listingpro_options['longitude']=$result->lng;
      $lp_listingpro_options['mappin']='';
      $lp_listingpro_options['phone']=$result->phone;
      $lp_listingpro_options['email']=$result->email;
      $lp_listingpro_options['website']=$result->website;
      $lp_listingpro_options['twitter']=$result->twitter;
      $lp_listingpro_options['facebook']=$result->facebook;
      $lp_listingpro_options['linkedin']=$result->linkedin;
      $lp_listingpro_options['youtube']=$result->youtube;
      $lp_listingpro_options['Plan_id']='114';
      $lp_listingpro_options['claimed_section']='not_claimed';
      $lp_listingpro_options['price_status']='notsay';



      $listing_plan_data['price']='';
      $listing_plan_data['menu']='true';
      $listing_plan_data['deals']='true';
      $listing_plan_data['competitor_campaigns']='true';
      $listing_plan_data['events']='true';
      $listing_plan_data['bookings']='true';
    


      $tags=explode(",",$result->services);

      // print_r($tags);

       $userData=user_data($result->uid);
      // print_r($userData);
       $username=$userData['fname'].''.$userData['lname'];

      // Check User Exist or not
      $user = get_user_by( 'email', $userData['email'] );
      if ( $user ) {
          $user_id = $user->ID;
      } else {
          //$user_id = false;
          $userdata = array(
            'first_name' => $userData['fname'],   
            'last_name'  => $userData['lname'],
            'user_login' => $username,
            'user_url'   =>  '',
            'user_pass'  =>  NULL,
            'role'       => 'subscriber',
            'user_email' => $userData['email'],

          );
          
        $user_id = wp_insert_user( $userdata ) ;
      }

     // echo $user_id;

        $tablename = $wpdb->prefix . "posts";
        $listindData=array(
          'post_title'=>$result->title,
          'post_content'=>$result->description,
          'post_date'=>$result->added_date,
          'post_date_gmt'=>$result->added_date,
          'post_type'=>'listing',
          'post_name'=>$result->slug,
          'post_status'=>'publish',
          'comment_status'=>'closed',
          'ping_status'=>'closed',
          'post_modified'=>$result->added_date,
          'post_modified_gmt'=>$result->added_date,
          'post_author'=>$user_id
      );
      $format=array( '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d');

     	 $wpdb->insert( $tablename, $listindData, $format );
    	 $postID = $wpdb->insert_id; 
      //  $postID =238;

          add_post_meta( $postID, 'listing_plan_data', $listing_plan_data, true );
          add_post_meta( $postID, 'lp_listingpro_options', $lp_listingpro_options, true );

         $catID=$result->category;
         $subcatid=$result->subcatid;
         $cats=category($catID, $subcatid);
         wp_set_object_terms( $postID, $cats, 'listing-category' );
         wp_set_object_terms( $postID, $tags, 'list-tags' ); 
       
        $mainImage=$result->main_image;

         // Add Featured Image to Post
        $image_url        = 'https://mediatimes.com.au/directory/oldwebsite/uploads/'. $mainImage; // Define the image URL here
        $image_name       =  $mainImage;
        $upload_dir       = wp_upload_dir(); // Set upload folder
        $image_data       = file_get_contents($image_url); // Get image data
        $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
        $filename         = basename( $unique_file_name ); // Create image file name

        // Check folder permission and define file location
        if( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents( $file, $image_data );

        $wp_filetype = wp_check_filetype( $filename, null );

         $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name( $filename ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Create the attachment
        $attach_id = wp_insert_attachment( $attachment, $file,$postID );
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

        wp_update_attachment_metadata( $attach_id, $attach_data );

        set_post_thumbnail( $postID, $attach_id ); 



        ////Gallery Images
        ///add_post_meta( $postID, 'gallery_image_ids', $galleryIDs, true );

        echo "Succesfully Imported";


    } 

  }

   //$cats=user_data(1);
   //  print_r($cats);
  importDB(1576, 100);

?> 