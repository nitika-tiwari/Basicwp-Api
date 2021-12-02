<?php
    use \Firebase\JWT\JWT;
    /**
    * @wordpress-plugin
    * Plugin Name: Mobile app API
    * Description: All functions which is used in mobile app with JWT Auth.
    * Version: 1.0
    * Author: Su
    */
    function custom_error(){
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
    // If this file is called directly, abort.
    if (!defined('WPINC')) {
        die;
    }
    add_action( 'rest_api_init', function() {
    	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	    add_filter( 'rest_pre_serve_request', function( $value ) {
    		header( 'Access-Control-Allow-Origin: *' );
    		header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
    		header( 'Access-Control-Allow-Credentials: true' );
    		return $value;
    	});
    },15 );

    function test_jwt_auth_expire($issuedAt){
        return $issuedAt + (62732 * 10000);
    }add_filter('jwt_auth_expire', 'test_jwt_auth_expire');

    add_action('rest_api_init', function () {
        register_rest_route('mobileapi/v1', '/register', array(
            'methods'   => 'POST',
            'callback'  => 'register_user_callback',
        ));
        register_rest_route('mobileapi/v1', '/change_password', array(
            'methods'   => 'POST',
            'callback'  => 'change_password_callback',
        ));
        
        register_rest_route('mobileapi/v1', '/forgot_password', array(
            'methods'   => 'POST',
            'callback'  => 'forgot_password_callback',
        ));
        
        register_rest_route('mobileapi/v1', '/user_update', array(
            'methods'   => 'POST',
            'callback'  => 'user_update_callback',
        ));
        
        
       register_rest_route('mobileapi/v1', '/tracks', array(
            'methods'   => 'POST',
            'callback'  => 'tracks_callback',
        ));  
        
         register_rest_route('mobileapi/v1', '/add_track', array(
            'methods'   => 'POST',
            'callback'  => 'add_track_callback',
        ));  
        
        
        register_rest_route('mobileapi/v1', '/albums', array(
            'methods'   => 'POST',
            'callback'  => 'albums_callback',
        ));  
        
        
    });
    
 
 
 function albums_callback($request){
     
    global $wpdb;
    $data  = array(
        "status" => "ok",
        "errormsg" => "",
        'error_code' => ""
    );
    
   
    
    $param    = $request->get_params();
    $token    = $param['token'];
    $user_id  = GetMobileAPIUserByIdToken($token);
    
    
     if($user_id){
         
          $taxonomies = get_terms( array(
        'taxonomy' => 'taxonomy_name',
        'hide_empty' => false
    ) );
    
     }else{ 
         $data  = array(
            "status" => "error",
            "errormsg" => "user token expired",
            "msg" => "User token expired",
            'error_code' => "user_expire"
        );
    }
    return new WP_REST_Response($data, 403);
    
 }   
    
    // Create new user
function MobileApiMakeNewStaff($request)
{
    $param = $request->get_params();
   
    $username = $param['email'];
    $first_name    = $param['first_name'];
    $last_name     = $param['last_name'];
    $email         = $param['email'];

    $staff_id = $param['staff_id'];
   
    $token = $param['token'];
	$company_id  = GetMobileAPIUserByIdToken($token);
    $user_id = email_exists($email);
    
        
       if(isset($param['staff_id']) && $param['staff_id'] !=''){
        $user_id = $param['staff_id'];
        
        }else{
            
            
            if($user_id == true){
                $data['status'] = "error";
                $data['errormsg'] = __('Account exists with this email or username.');
                $data['error_code'] = "user_already";
               return new WP_REST_Response($data, 403);
            }
            
            
             $password = wp_generate_password( 10, true, true );
             $password =  'sR123456';
             $user_id = wp_create_user($username, $password, $email);
             $user = new WP_User($user_id);
             $user->set_role('subscriber');
             
        }
        
        if($user_id){
        
      
        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);
          if(isset($param['companyName']) && $param['companyName'] !='' ){
            update_user_meta($user_id, 'company_name', $param['companyName']); 
        }
        
        if(isset($param['phone']) && $param['phone'] !='' ){
            update_user_meta($user_id, 'phone', $param['phone']); 
        }
        
         if(isset($param['address']) && $param['address'] !='' ){
            update_user_meta($user_id, 'address', $param['address']); 
        }
        
         
       if(isset($param['city']) && $param['city'] !=''){
         update_user_meta($user_id,'city',$param['city']);  
       }
       
        if(isset($param['state']) && $param['state'] !=''){
         update_user_meta($user_id,'state',$param['state']);  
       }
       
        if(isset($param['zip']) && $param['zip'] !=''){
         update_user_meta($user_id,'zip',$param['zip']);  
       }
       
       if(isset($param['lat']) && $param['lat'] !=''){
         update_user_meta($user_id,'lat',$param['lat']);  
       }
       
       if(isset($param['long']) && $param['long'] !=''){
         update_user_meta($user_id,'long',$param['long']);  
       }
       
       if(isset($param['title']) && $param['title'] !=''){
         update_user_meta($user_id,'title',$param['title']);  
       }
       
       if(isset($param['address']) && $param['address'] !=''){
         update_user_meta($user_id,'address',$param['address']);  
       }
       
       if(isset($param['apt_suit']) && $param['apt_suit'] !=''){
         update_user_meta($user_id,'apt_suit',$param['apt_suit']);  
       }
       
       update_user_meta($user_id,'company_id',$company_id);  
       
        $message =  "<p>Your Staff Account has been created successfully.</p>";
        $message .= '<p>Username- '.$email.'</p>';
        $message .= '<p>Password- '.$password.'</p>';
        $message .= '<p>Thank you- '.'</p>';
        
        $to = $email;
        $subject = 'The subject';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($email,$subject,$message,$headers); 
             
        $data['status'] = "success";
        $data['success'] = "You have been successfully registered";
        
         if(isset($param['staff_id']) && $param['staff_id'] !=''){
        
          $data['msg'] = "Staff Updated Successfully";
         }else{
            $data['msg'] = "Staff Added Successfully";
         }
        
        
        $data['data'] = $param;
        
        return new WP_REST_Response($data, 200);      
        }
}

    
    
    function add_track_callback(){
        
        
    
    global $wpdb;
    
    $data  = array(
        "status" => "ok",
        "errormsg" => "",
        'error_code' => ""
    );
    
    $param = $request->get_params();
    
    $token = $param['token'];
    $job_title = utf8_encode($param['jobTitle']);
    $job_desc = utf8_encode($param['description']);
    
    $user_id = GetMobileAPIUserByIdToken($token);
    $job_type =  $param['jobType'];
    $job_category = $param['jobCategory'];
    
    
    
    $user_data = get_userdata($user_id);
    $user_email =  $user_data->user_email;
    $user_roles = $user_data->roles;
   
    if(in_array('staff',$user_roles)){
       $company_id = get_user_meta($user_id,'company_id',true);
       $args['author'] = $company_id;
    }else{
       $company_id = $user_id;
    }
    
    
    if($user_id){
       
        if(isset($param['id']) && $param['id'] !=''){
            
             $job_id = $param['id'];
             
              // Update post 37
              $old_post = array(
                  'ID'           => $job_id,
                  'post_title'   => $job_title,
                  'post_content'  => $job_desc,
                  'post_status'   => 'publish'
              );
             
            // Update the post into the database
             $post_id = wp_update_post($old_post);
                        
        }else{
            
             $new_post = array(
                                'post_title'    => $job_title,
                                'post_content'  => $job_desc,
                                'post_status'   => 'publish',
                                'post_author'   => $user_id,
                                'post_type'     => 'awsm_job_openings',
                              );
                              
             $post_id = wp_insert_post($new_post);                    
            
        }
        
        
        if($post_id){
            
               $data['post']=$post_id;
            
                if(isset($param['jobExpiry']) && $param['jobExpiry'] !=''){
                    update_post_meta($post_id, 'awsm_job_expiry',date('Y-m-d',strtotime($param['jobExpiry'])) );
                } 
                
                
                 if(isset($param['jobCategory']) && $param['jobCategory'] !=''){
                     
                     update_post_meta($post_id, 'job_category',$param['jobCategory']);
                     $job_category_name = get_term_name_string( $param['jobCategory']);
                     update_post_meta($post_id, 'job_category_string',$job_category_name);
                } 
                
                
                if(isset($param['jobType']) && $param['jobType'] !=''){
                    
                     update_post_meta($post_id, 'job_type',$param['jobType'] );
                     $job_category_type = get_term_name_string( $param['jobType']);
                     update_post_meta($post_id, 'job_type_string',$job_category_type );
                }
                
    
                if(isset($param['skills']) && $param['skills'] !=''){
                    update_post_meta($post_id, 'job_skills',$param['skills'] );
                }
                
                
               update_post_meta($post_id, 'awsm_job_position',$job_title );
               update_post_meta($post_id, 'awsm_set_exp_list','set_listing' );
               update_post_meta($post_id, 'awsm_exp_list_display','list_display');
               update_post_meta($post_id, 'job_status','Active');
               update_post_meta($post_id, 'company_id',$company_id);
               
               
               $lat =  get_user_meta($user_id,'lat',true);
               $long =  get_user_meta($user_id,'long',true);
               $address = get_user_meta($user_id,'address',true);
               $city = get_user_meta($user_id,'city',true);
               $state = get_user_meta($user_id,'state',true);
               $zip = get_user_meta($user_id,'zip',true);
               
               $company_name = get_user_meta($user_id,'company_name',true);
              
              
              if(isset($param['address']) && $param['address'] ==''){ 
               
               if($lat !=''){
                   update_post_meta($post_id, 'lat',$lat );
               }
              
               if($long !=''){
                   update_post_meta($post_id, 'long',$long );
               }
               
                if($address !=''){
                   update_post_meta($post_id, 'address',$address );
               }
               
                if($city !=''){
                   update_post_meta($post_id, 'city',$city );
               }
               
                if($state !=''){
                   update_post_meta($post_id, 'state',$state );
               }
                if($zip !=''){
                   update_post_meta($post_id, 'zip',$zip );
               }
               
          }else{
              
              if($param['lat'] !=''){
                  update_post_meta($post_id, 'lat',$param['lat'] );
              }
              
              if($param['long'] !=''){
                  update_post_meta($post_id, 'long',$param['long'] );
              }
               
                if($param['address'] !=''){
                  update_post_meta($post_id, 'address',$param['address'] );
              }
               
                if($param['city'] !=''){
                  update_post_meta($post_id, 'city',$param['city'] );
              }
               
                if($param['state'] !=''){
                  update_post_meta($post_id, 'state',$param['state'] );
              }
                if($param['zip'] !=''){
                  update_post_meta($post_id, 'zip',$param['zip'] );
              }
              
          }
          
               if($company_name != ''){
                    update_post_meta($post_id, 'zip',$company_name );
               }
               
               $data['msg'] = "Job published successfully..";
               return new WP_REST_Response($data, 200);
        }else{
          $data['post']= $new_post;    
          $data['errormsg']= "Unable to post your job, please try again.";  
          $data['msg'] = "Unable to post your job, please try again.";
          return new WP_REST_Response($data, 403);   
        }
    }else{
        $data  = array(
            "status" => "error",
            "errormsg" => "user token expired",
            "msg" => "User token expired",
            'error_code' => "user_expire"
        );
    }
    return new WP_REST_Response($data, 403);
    

        
    }
    
    
    
    
    
   function tracks_callback($request){
    
    global $wpdb;
    
    $data  = array(
        "status" => "ok",
        "errormsg" => "",
        'error_code' => ""
    );
    
    $meta_query_arr =  array();
    $search_query_arr =  array();
    
    $param    = $request->get_params();
    $token    = $param['token'];
   
    
   
    $user_id  = GetMobileAPIUserByIdToken($token);
    
    $user_data = get_userdata($user_id);
    $user_email =  $user_data->user_email;
    $user_roles = $user_data->roles;
    
    
    if($param['cat_id']){
        
                $args = array(
                     'numberposts' => -1,
                     'post_type'   => 'track',
                     'post_status'   => 'publish',
                     'orderby' => 'publish_date',
                     'order' => 'DESC',
                     'tax_query' => array(
                        array(
                        'taxonomy' => 'featured_album',
                        'field' => 'term_id',
                        'terms' => $param['cat_id']
                         )
                      )
                  );
        
    }else{
         $args = array(
                     'numberposts' => -1,
                     'post_type'   => 'track',
                     'post_status'   => 'publish',
                     'orderby' => 'publish_date',
                     'order' => 'DESC',
                  );
        
    }
    
   
                  
    
    if($user_id){
      
        $query = new WP_Query( $args );
        $data['track'] = $query->posts;
        
        if(count($data['track']) != 0){
        
        foreach($data['track'] as $tempjobkey => $tempjob){
            
            $track_id = $tempjob->ID; 
            $auther_id = $tempjob->post_author; 
            
            $data['track'][$tempjobkey]->location = get_post_meta($track_id,'location',true);
            $data['track'][$tempjobkey]->lat = get_post_meta($track_id,'lat',true);
            $data['track'][$tempjobkey]->long = get_post_meta($track_id,'long',true);
           $data['track'][$tempjobkey]->video_url = get_post_meta($track_id,'video_url',true);
           $data['track'][$tempjobkey]->video_embed = wp_oembed_get( get_post_meta($track_id,'video_url',true) );
          
          
            $image = wp_get_attachment_url( get_post_thumbnail_id($track_id) );
            
            if($image !=''){
              
               $data['track'][$tempjobkey]->image = $image;
            }else{
               $data['track'][$tempjobkey]->image = site_url().'/wp-content/uploads/2019/12/images.jpg';
            }
            
          
            $file_id = get_post_meta($track_id,'file',true);
            if($file_id !=''){
                
               $data['track'][$tempjobkey]->file_url = wp_get_attachment_url($file_id);
            }else{
               $data['track'][$tempjobkey]->file_url = site_url().'/wp-content/uploads/2019/12/images.jpg';
            }
            
           
            
            
            
            // $data['track'][$tempjobkey]->company_phone = get_user_meta($auther_id,'phone',true);
            // $data['track'][$tempjobkey]->status = get_user_meta($job_id,'job_status',true);
            
        }
        
        
        if($param['cat_id']){
       
                         
        $taxonomies = get_term( $param['cat_id'], 'featured_album' );                    
                       
       
        $data['term_id'] =  $taxonomies->term_id;
        $data['term_name'] =  $taxonomies->name;
       
        $data['image'] = 'http://africkiko.betaplanets.com/wp-content/uploads/2020/03/Todd-Dulaney-To-Africa-With-Love-album-cover.jpg';
       
        
        }else{
            
            $taxonomies = get_terms( array(
                                        'taxonomy' => 'featured_album',
                                        'hide_empty' => false
                            ) );
                       
        $data['albums'] = $taxonomies;
        
        foreach($data['albums'] as $tempalbumkey => $album){
            
          $data['albums'][$tempalbumkey]->image = 'http://africkiko.betaplanets.com/wp-content/uploads/2020/03/Todd-Dulaney-To-Africa-With-Love-album-cover.jpg';
            
            
        }
            
            
        }
       
        return new WP_REST_Response($data, 200);
        }else{
            
             $data  = array(
                            "status" => "error",
                            "errormsg" => "No Track Found ",
                            "msg" => "No Track Found",
                            'error_code' => "no_track"
                        );
        return new WP_REST_Response($data, 403);
            
        }
    
    }else{
        $data  = array(
                        "status" => "error",
                        "errormsg" => "user token expired",
                        "msg" => "User token expired",
                        'error_code' => "user_expire"
                      );
        return new WP_REST_Response($data, 403);
    }
   
   } 
    
    
    
    
    
    class Custom_helper{
        var $default_user_profile = "http://1.gravatar.com/avatar/1aedb8d9dc4751e229a335e371db8058?s=96&d=mm&r=g";
        function __construct($wpdb) {
            $this->wpdb = $wpdb;
        }
        //[START]=> This Function is help to update Post & User Meta Data
        public function UpdateMeta($id = null,$meta_type = null, array $MetaData = null){
            foreach($MetaData as $meta_key => $meta_value){
                if($meta_type == 'user'){
                    update_user_meta($id,$meta_key,$meta_value);
                }elseif($meta_type == 'post'){
                    update_post_meta($id,$meta_key,$meta_value);
                }
                
            }
        }
        
        //[START]=> Name Validation
        public function validateName($name = null, array $arg = null){
            $regular_expression =  '/^[a-zA-Z 0-9]{2,60}$/';
            if(preg_match($regular_expression,$name)){
                return $name;
            }else{
                throw new Exception("Invalid name. It should be only string.");
            }
        }
        //[END]=> Name Validation
        
        //[START]=> Email Validation
        public function validateEmail($email = null, array $arg = null){
            $regular_expression = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$/";
            if(preg_match($regular_expression,$email)){
                return $email;
            }else{
                throw new Exception("Invalid email ID('$email').");
            }
        }
        //[END]=> Email Validation
        
        //[START]=> Password Validation
        public function validatePassword($password = null,array $arg = null){
            $regular_expression = "/^.*(?=.{8,})(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*$/";
            if(preg_match($regular_expression,$password)){
                return $password;
            }else{
                throw new Exception("Invalid password('$password'). It must be at least 8 characters and must contain at least one lower case letter, one upper case letter and one digit");
            }
        }
        //[END]=> Password Validation
        
    }
    global $wpdb;
    global $helper_obj;
    $helper_obj = new Custom_helper($wpdb);
    
    
    //[START]=> Update User
    function user_update_callback($request){
        $data = array("status"=>"ok","code"=>200, "error" =>"", "msg" => "");
        $param = $request->get_params();
        $token = $param['token'];
        $user_id = GetMobileAPIUserByIdToken($token);
        if($user_id){
            $user_results = get_userdata($user_id);
            $userData = $user_results->data;
            global $helper_obj;
            $user_data = array();
            $user_meta = array();
            if(isset($param['first_name']) && !empty($param['first_name']) && $param['first_name'] != ''){
                $user_meta['first_name'] = $param['first_name'];
            }
            if(isset($param['last_name']) && !empty($param['last_name']) && $param['last_name'] != ''){
                $user_meta['last_name'] = $param['last_name'];
            }
            if(isset($param['phone']) && !empty($param['phone']) && $param['phone'] != ''){
                $user_meta['phone'] = $param['phone'];
            }
            $user_data['ID'] = $user_id;
            // update Use data
            $updated = wp_update_user($user_data);
            if($updated){
                if(!empty($_FILES['profile_picture']["name"])) { 
                    $new_filename = $user_id."_".time()."_".$_FILES["profile_picture"]["name"];
                    $_FILES["profile_picture"]["name"] = $new_filename;
            		
            		require_once(ABSPATH . 'wp-admin/includes/image.php');
            		require_once(ABSPATH . 'wp-admin/includes/file.php');
            		require_once(ABSPATH . 'wp-admin/includes/media.php');
            		$attachment_id = media_handle_upload('profile_picture', $user_id);
            		if($attachment_id){
            		    $old_attachment_id = get_user_meta($user_id,'wp_user_avatar',true );
            		    if(!empty($old_attachment_id)){
            		        wp_delete_attachment($old_attachment_id);
            		    }$user_meta['wp_user_avatar'] = $attachment_id;
            		} 
            	} 
            	//Updating User Meta Values
                $helper_obj->UpdateMeta($user_id,'user',$user_meta);
                $user_data = array(
                    'ID' => $user_id,
                    'display_name' => trim(get_user_meta($user_id,'first_name',true)." ".get_user_meta($user_id,'last_name',true)),
                );
                wp_update_user($user_data);
                $data['user_id']    = $updated;
                $data['msg']        = __("user updated successfully.");
                return new WP_REST_Response($data, $data['code']);
            }else{
                $data['status'] = "error";
                $data['code']   = 403;
                $data['msg']        = __("user is not update. Try again.");
                $data['error'] = "user_not_update";
                return new WP_REST_Response($data,$data['code']);
            }
        }else{
            $data['status'] = "error";
            $data['code']   = 403;
            $data['msg']    = __("Token is expired.");
            $data['error'] = "token_expired.";
            return new WP_REST_Response($data,$data['code']);
        }
    }
    //[END]=> Update User
    
    //[START]=> Forget Password
    function forgot_password_callback($request){
        global $wpdb, $current_site;
        // $data = array("status" => "ok", "msg" => "you will be recive login instructions.");
        $data = array("status"=>"ok","code"=>200, "error"=>"", "msg"=>"");
        $param = $request->get_params();
        
        if(!isset($param['user_login']) || empty($param['user_login']) || $param['user_login']==''){
            $data['status']     = 'error';
            $data['code']   = 403;
            $data['error'] = 'missing_parameters';
            $data['msg']        = 'Empty Or Missing parameters';
            return new WP_REST_Response($data, $data['code']);
        }
        
        $user_login = sanitize_text_field($param['user_login']);
        if(!is_email($user_login)) {
            $data['status']     = 'error';
            $data['code']   = 403;
            $data['error'] = 'invalid_email';
            $data['msg']        = 'Please provide valid email.';
            return new WP_REST_Response($data, $data['code']);
        }
        if(strpos($user_login,'@')){
            $user_results = get_user_by('email', trim($user_login));
        }else{
            $login = trim($user_login);
            $user_results = get_user_by('login', $login);
        }
        if(!$user_results){
            $data['status'] = 'error';
            $data['code']   = 403;
            $data['error']  = 'user_not_exist';
            $data['msg']    = 'User not found using email.';
            return new WP_REST_Response($data, $data['code']);
        }
        // redefining user_login ensures we return the right case in the email
        $user_login = $user_results->user_login;
        $user_email = $user_results->user_email;
       
        $allow = apply_filters('allow_password_reset', true, $user_results->ID);
        if(!$allow){
            $data['status']     = 'error';
            $data['code']   = 403;
            $data['error'] = 'not_allow.';
            $data['msg']        = 'Password reset not allowed.';
            return new WP_REST_Response($data, $data['code']);
        }elseif (is_wp_error($allow)) {
            $data['status']     = 'error';
            $data['code']   = 403;
            $data['error'] = 'not_allow.';
            $data['msg']        = 'Something went wrong';
            return new WP_REST_Response($data, $data['code']);
        }
        // Generate something random for a key...
        $key = get_password_reset_key($user_results);
        $password = wp_generate_password(8, false);
        
        wp_set_password($password, $user_results->ID);
        $message = __('Hello ,') . "\r\n\r\n";
        $message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
        //$message .= network_home_url( '/' ) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= sprintf(__('New Password : %s'), $password) . "\r\n\r\n";
        //$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
        $message .= __('Thank you') . "\r\n\r\n";
        // $message .= network_site_url("resetpass/?key=$key&login=" . rawurlencode($user_login), 'login') . "\r\n";
        /* <http://vipeel.testplanets.com/resetpass/?key=wDDY0rDxwfaWPOFZrrmf&login=ajaytest%40gmail.com> */
        if (is_multisite()) {
            $blogname = $GLOBALS['current_site']->site_name;
        }else{
            // The blogname option is escaped with esc_html on the way into the database in sanitize_option
            // we want to reverse this for the plain text arena of emails.
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        }
        $title  = sprintf(__('[%s] Password Reset'), $blogname);
        $title  = apply_filters('retrieve_password_title', $title);
        $message= apply_filters('retrieve_password_message', $message, $key);
        
        if ($message && !wp_mail($user_email, $title, $message)) {
            $data['status']     = 'error';
            $data['code']   = 403;
            $data['error']  = 'May be email is not enable from server.';
            $data['msg']    = 'The e-mail could not be sent..';
            return new WP_REST_Response($data, $data['code']);
        }
        // wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );
        return new WP_REST_Response($data, $data['code']);
    }
    //[END]=> Forget Password
    
    //[START]=> Change Password
    function change_password_callback($request){
        $data = array("status"=>"ok", "code"=>200, "error" =>"", "msg" => "");
        $param = $request->get_params();
        $token = @$param['token'];
        $user_id = GetMobileAPIUserByIdToken($token);
        if($user_id){
            if(!isset($param['old_password']) || empty($param['old_password']) || !isset($param['new_password']) || empty($param['new_password']) || !isset($param['confirm_new_password']) || empty($param['confirm_new_password']) ){
                $data['status'] = "error";
                $data['code']   = 403;
                $data['msg'] = "Please check required parameters('old_password','new_password','confirm_password')";
                $data['error'] = "'missing_parameters.";
                return new WP_REST_Response($data, $data['code']);
            }
            global $helper_obj;
            try{  
                $old_password           = $param['old_password'];
                $new_password           = $helper_obj->validatePassword($param['new_password'],array());    
                $confirm_new_password   = $helper_obj->validatePassword($param['confirm_new_password'],array());    
            }catch(Exception $error){
                $data['status'] = "error";
                $data['code']   = 403;
                $data['msg']    = __($error->getMessage());
                $data['error'] = "validation_error";
                return new WP_REST_Response($data, $data['code']);
            }
            if($new_password != $confirm_new_password){
                $data['status'] = "error";
                $data['code']   = 403;
                $data['msg']    = __("New Password and Confirm New password are not match.");
                $data['error'] = "confirm_new_password_not_match.";
                return new WP_REST_Response($data, $data['code']);
            }
            
            $user_results = get_user_by('ID',$user_id);
            $userData = $user_results->data;
            if($user_results && wp_check_password($old_password,$userData->user_pass,$userData->ID)){
                if($old_password == $new_password){
                    $data['status'] = "error";
                    $data['code']   = 403;
                    $data['msg'] = "Your new password is same as old password. Try another new password.";
                    $data['error'] = "old_password_is_same_as_new_password.";
                    return new WP_REST_Response($data, $data['code']); 
                }
                $success = wp_set_password( $new_password, $user_id );
                $data['msg'] = "Your password is changed successfully.";
                return new WP_REST_Response($data, $data['code']);
            }else{
                $data['status'] = "error";
                $data['code']   = 403;
                $data['msg'] = "Old Password is not match. Try Again";
                $data['error'] = "invalid_old_password.";
                return new WP_REST_Response($data, $data['code']);
            }
        }else{
            $data['status'] = "error";
            $data['code']   = 403;
            $data['msg']    = __("Token is expired.");
            $data['error'] = "token_expired.";
            return new WP_REST_Response($data, $data['code']);
        }
    }
    //[END]=> Change Password
    
    //[START]=> Create New user
    function register_user_callback($request){
        $data = array("status"=>"ok","code"=>200, "error" =>"", "msg" => "");
        $param = $request->get_params();
        if(!isset($param['first_name']) || empty($param['first_name']) || $param['first_name']=='' || !isset($param[last_name]) || empty($param['last_name']) || $param['last_name']=='' || !isset($param['email']) || empty($param['email']) || ($param['email'] =='') || !isset($param['password']) || empty($param['password']) || $param['password']==''){
            $data['status'] = "error";
            $data['code']   = 403;
            $data['msg'] = __('Please check require parameter(first_name,last_name, email, password)');
            $data['error'] = "some_parameter_missing";
            return new WP_REST_Response($data, $data['code']);
        }
        global $helper_obj;
        try{
            $first_name = $param['first_name'];
            $last_name = $param['last_name'];
            // $name           = ucwords(trim($helper_obj->validateName($param['name'],array())));    
            $name           = ucwords(trim($first_name." ".$last_name));    
            $user_email     = strtolower(trim($helper_obj->validateEmail($param['email'],array())));    
            $user_password  = $helper_obj->validatePassword($param['password'],array());
        }catch(Exception $error){
            $data['status'] = "error";
            $data['code']   = 403;
            $data['msg']    = __($error->getMessage());
            $data['error'] = "validation_failed.";
            return new WP_REST_Response($data, $data['code']);
        }
        $user_role      = (isset($param['role']) && !empty($param['role']))? $param['role'] : '';  
        $user_name      = $user_email;
        if(username_exists($user_name)){
            $data['status'] = "error";
            $data['code']   = 403;
            $data['msg'] = __('Username is already exist.');
            $data['error'] = "username_exist.";
            return new WP_REST_Response($data, $data['code']);
        }
        
        if(email_exists($user_email)){
            $data['status'] = "error";
            $data['code']   = 403;
            $data['msg'] = __('Email id is already exist.');
            $data['error'] = "email_exist.";
            return new WP_REST_Response($data, $data['code']);
        }
        //$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        $user_id = wp_create_user($user_name, $user_password, $user_email);
        if($user_id){
            $user = new WP_User($user_id);
            $user->set_role($user_role);
            
            $user_data = array(
                'ID' => $user_id,
                'display_name' => trim($name),
            );
            wp_update_user($user_data);
            
            update_user_meta($user_id, 'first_name',$first_name);
            update_user_meta($user_id, 'last_name',$last_name);
            $data['user_id']       = $user_id;
            $data['msg'] = "User created successfully.";
            return new WP_REST_Response($data, $data['code']);
        }else{
            $data['status'] = "error";
            $data['code']   = 403;
            $data['msg']    = __('User registration failed. Try Again');
            $data['error'] = "registration_failed.";
            return new WP_REST_Response($data, $data['code']);
        }
    }
    //[END]=> Create New user
    
    
    
    function user_id_exists($user = null){
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user));
        if($count == 1){
            return true;
        }else{
            return false;
        }
    }
    
    //GET_USERID_BY_TOKEN
    function GetMobileAPIUserByIdToken($token = null){
        if(!$token){ return false; }
        $decoded_array = array();
        $user_id = 0;
        if($token){
            $decoded = JWT::decode($token, JWT_AUTH_SECRET_KEY, array('HS256'));
            $decoded_array = (array) $decoded;
        }
        if(count($decoded) > 0){
            $user_id = $decoded_array['data']->user->id;
        }
        if(user_id_exists($user_id)){
            return $user_id;
        }else{
            return false;
        }
    }  
   
   
    add_action('rest_insert_attachment','func_rest_insert_attachment',10,3);
    function func_rest_insert_attachment($attachment, $request,$is_create){
      
      if(isset($request['post']) && $request['post']!=''){
         
          if($request['ext']=="mov" || $request['ext']=="mp4"){
               update_post_meta($request['post'],'type','video'); 
          }else{
          update_post_meta($request['post'],'type','image'); 
          }
         set_post_thumbnail($request['post'],$attachment->ID);
          update_post_meta($request['post'],'media_attachment',$attachment->ID);
      }
      if(isset($request['type']) && $request['type']=="edit"){
          if(isset($request['old_image']) && $request['old_image']!=""){
               wp_delete_attachment($request['old_image'],true); 
          }
      }
     if(isset($request['type']) && $request['type']=="userimage"){
          //wp_user_avatar
          update_user_meta($request['user'], 'wp_user_avatar', $attachment->ID);
      }
    }
   
   
    //[START]=> Add More Parameter Help of HOOK When User Get Logined
    add_filter('jwt_auth_token_before_dispatch', 'return_more_field_On_login', 10, 2);
    function return_more_field_On_login($data,$user){
        $roles = $user->roles;
        $user_id = $user->ID;
        $data['user_id'] = $user_id;
        $first_name = get_user_meta($user_id, "first_name", true);
        $last_name = get_user_meta($user_id, "last_name", true);
        if(!empty($first_name) && !empty($last_name)) {
            $data['user_display_name'] = ucfirst(trim($first_name." ".$last_name));
        }else{
            $data['user_display_name'] = ucfirst($data['user_display_name']);
        }
        $useravatar = get_user_meta($user_id,'wp_user_avatar',true);
        if(isset($useravatar) && !empty($useravatar)) {
            $img = wp_get_attachment_image_src($useravatar, array('150', '150'), true);
            $data['user_avatar'] = $img[0];
        }else{
            $data['user_avatar'] = "http://1.gravatar.com/avatar/1aedb8d9dc4751e229a335e371db8058?s=96&d=mm&r=g";
        }
        return $data;
    }
    //[END]=> Add More Parameter Help of HOOK When User Get Logined