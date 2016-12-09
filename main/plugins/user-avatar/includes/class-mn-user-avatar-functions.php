<?php
/**
 * Core user functions.
 * 
 * @package User Avatar
 * @version 1.9.13
 */

class MN_User_Avatar_Functions {
  /**
   * Constructor
   * @since 1.8
   * @uses add_filter()
   * @uses register_activation_hook()
   * @uses register_deactivation_hook()
   */
  public function __construct() {
    add_filter('get_avatar', array($this, 'mnua_get_avatar_filter'), 10, 5);
	// Filter to display User Avatar at Buddypress
	add_filter('bp_core_fetch_avatar', array($this, 'mnua_bp_core_fetch_avatar_filter'), 10, 5);
	// Filter to display User Avatar by URL at Buddypress
	add_filter('bp_core_fetch_avatar_url', array($this, 'mnua_bp_core_fetch_avatar_url_filter'), 10, 5);
	
  }
  
  /**
   * Returns User Avatar or Gravatar-hosted image if user doesn't have Buddypress-uploaded image
   * @param string $avatar
   * @param array $params
   * @param int $item_id
   * @param string $avatar_dir
   * @param string $css_id
   * @param int $html_width
   * @param int $html_height
   * @param string $avatar_folder_url
   * @param string $avatar_folder_dir
   * @uses object $mnua_functions
   * @uses mnua_get_avatar_filter()
  */
  public function mnua_bp_core_fetch_avatar_filter($gravatar,$params,$item_id='', $avatar_dir='', $css_id='', $html_width='', $html_height='', $avatar_folder_url='', $avatar_folder_dir=''){
	global $mnua_functions;
	if(strpos($gravatar,'gravatar.com',0)>-1){
		$avatar = $mnua_functions->mnua_get_avatar_filter($gravatar, ($params['object']=='user') ? $params['item_id'] : '', ($params['object']=='user') ? (($params['type']=='thumb') ? 50 :150) : 50, '', '');
		return $avatar;
    }
    else
		return $gravatar;
  }
  
  /**
   * Returns MN user default local avatar URL or Gravatar-hosted image URL if user doesn't have Buddypress-uploaded image
   * @param string $avatar
   * @param array $params
   * @uses object $mnua_functions
   * @uses mnua_get_avatar_filter()
  */
  public function mnua_bp_core_fetch_avatar_url_filter($gravatar,$params){
	global $mnua_functions;
	if(strpos($gravatar,'gravatar.com',0)>-1){
		$avatar = $mnua_functions->mnua_get_avatar_filter($gravatar, ($params['object']=='user') ? $params['item_id'] : '', ($params['object']=='user') ? (($params['type']=='thumb') ? 50 :150) : 50, '', '');
		return $avatar;
    }
    else
		return $gravatar;
  }
 
  /**
   * Returns true if user has Gravatar-hosted image
   * @since 1.4
   * @param int|string $id_or_email
   * @param bool $has_gravatar
   * @param int|string $user
   * @param string $email
   * @uses get_user_by()
   * @uses is_mn_error()
   * @uses mn_cache_get()
   * @uses mn_cache_set()
   * @uses mn_remote_head()
   * @return bool $has_gravatar
   */
  public function mnua_has_gravatar($id_or_email="", $has_gravatar=0, $user="", $email="") {
    global $mnua_hash_gravatar,$avatar_default, $mustache_admin, $mustache_avatar, $mustache_medium, $mustache_original, $mustache_thumbnail, $post, $mnua_avatar_default, $mnua_disable_gravatar, $mnua_functions;
    // User has MNUA
    //Decide if check gravatar required or not.
    if(trim($avatar_default)!='mn_user_avatar')
      return true;
   
    if(!is_object($id_or_email) && !empty($id_or_email)) {
      // Find user by ID or e-mail address
      $user = is_numeric($id_or_email) ? get_user_by('id', $id_or_email) : get_user_by('email', $id_or_email);
      // Get registered user e-mail address
      $email = !empty($user) ? $user->user_email : "";
    }

    if($email == ""){

      if(!is_numeric($id_or_email) and !is_object($id_or_email))
        $email = $id_or_email;
      elseif(!is_numeric($id_or_email) and is_object($id_or_email))
        $email = $id_or_email->comment_author_email;
    }
    if($email!="")
    {
      $hash = md5(strtolower(trim($email)));
      //check if gravatar exists for hashtag using options
      
      if(is_array($mnua_hash_gravatar)){
    
        
      if ( array_key_exists($hash, $mnua_hash_gravatar) and is_array($mnua_hash_gravatar[$hash]) and array_key_exists(date('m-d-Y'), $mnua_hash_gravatar[$hash]) )
      {
        return (bool) $mnua_hash_gravatar[$hash][date('m-d-Y')];
      } 
      
      }
      
      //end
       $gravatar = 'http://www.gravatar.com/avatar/'.$hash.'?d=404';
      
      $data = mn_cache_get($hash);

      if(false === $data) {
        $response = mn_remote_head($gravatar);
        $data = is_mn_error($response) ? 'not200' : $response['response']['code'];
        
        mn_cache_set($hash, $data, $group="", $expire=60*5);
        //here set if hashtag has avatar
        $has_gravatar = ($data == '200') ? true : false;
        if($mnua_hash_gravatar == false){
        $mnua_hash_gravatar[$hash][date('m-d-Y')] = (bool)$has_gravatar;
        add_option('mnua_hash_gravatar',serialize($mnua_hash_gravatar));
        }
        else{

          if (array_key_exists($hash, $mnua_hash_gravatar)){

              unset($mnua_hash_gravatar[$hash]);
              $mnua_hash_gravatar[$hash][date('m-d-Y')] = (bool)$has_gravatar;
              update_option('mnua_hash_gravatar',serialize($mnua_hash_gravatar));
            

          }
          else
          {
            $mnua_hash_gravatar[$hash][date('m-d-Y')] = (bool)$has_gravatar;
            update_option('mnua_hash_gravatar',serialize($mnua_hash_gravatar));

          }
          
        }
      //end
      }
      $has_gravatar = ($data == '200') ? true : false;
      
    }
    else
      $has_gravatar = false;

    // Check if Gravatar image returns 200 (OK) or 404 (Not Found)
    return (bool) $has_gravatar;
  }

   
  /**
   * Check if local image
   * @since 1.9.2
   * @param int $attachment_id
   * @uses apply_filters()
   * @uses mn_attachment_is_image()
   * @return bool
   */
  public function mnua_attachment_is_image($attachment_id) {
    $is_image = mn_attachment_is_image($attachment_id);
    /**
     * Filter local image check
     * @since 1.9.2
     * @param bool $is_image
     * @param int $attachment_id
     */
    $is_image = apply_filters('mnua_attachment_is_image', $is_image, $attachment_id);
    return (bool) $is_image;
  }

  /**
   * Get local image tag
   * @since 1.9.2
   * @param int $attachment_id
   * @param int|string $size
   * @param bool $icon
   * @param string $attr
   * @uses apply_filters()
   * @uses mn_get_attachment_image()
   * @return string
   */
  public function mnua_get_attachment_image($attachment_id, $size='thumbnail', $icon=0, $attr='') {
    $image = mn_get_attachment_image($attachment_id, $size, $icon, $attr);
    /**
     * Filter local image tag
     * @since 1.9.2
     * @param string $image
     * @param int $attachment_id
     * @param int|string $size
     * @param bool $icon
     * @param string $attr
     */
    return apply_filters('mnua_get_attachment_image', $image, $attachment_id, $size, $icon, $attr);
  }

  /**
   * Get local image src
   * @since 1.9.2
   * @param int $attachment_id
   * @param int|string $size
   * @param bool $icon
   * @uses apply_filters()
   * @uses mn_get_attachment_image_src()
   * @return array
   */
  public function mnua_get_attachment_image_src($attachment_id, $size='thumbnail', $icon=0) {
    $image_src_array = mn_get_attachment_image_src($attachment_id, $size, $icon);
    /**
     * Filter local image src
     * @since 1.9.2
     * @param array $image_src_array
     * @param int $attachment_id
     * @param int|string $size
     * @param bool $icon
     */
    return apply_filters('mnua_get_attachment_image_src', $image_src_array, $attachment_id, $size, $icon);
  }

  /**
   * Returns true if user has mn_user_avatar
   * @since 1.1
   * @param int|string $id_or_email
   * @param bool $has_mnua
   * @param object $user
   * @param int $user_id
   * @uses int $blog_id
   * @uses object $mndb
   * @uses int $mnua_avatar_default
   * @uses object $mnua_functions
   * @uses get_user_by()
   * @uses get_user_meta()
   * @uses get_blog_prefix()
   * @uses mnua_attachment_is_image()
   * @return bool
   */
  public function has_mn_user_avatar($id_or_email="", $has_mnua=0, $user="", $user_id="") {
    global $blog_id, $mndb, $mnua_avatar_default, $mnua_functions, $avatar_default;
    if(!is_object($id_or_email) && !empty($id_or_email)) {
      // Find user by ID or e-mail address

      $user = is_numeric($id_or_email) ? get_user_by('id', $id_or_email) : get_user_by('email', $id_or_email);
      // Get registered user ID
       $user_id = !empty($user) ? $user->ID : "";
    }
    $mnua = get_user_meta($user_id, $mndb->get_blog_prefix($blog_id).'user_avatar', true);
    // Check if avatar is same as default avatar or on excluded list
    $has_mnua = !empty($mnua) && ($avatar_default!='mn_user_avatar' or $mnua != $mnua_avatar_default) && $mnua_functions->mnua_attachment_is_image($mnua) ? true : false;
    return (bool) $has_mnua;
  }
  /**
  Retrive default image url set by admin. 
  */
  public function mnua_default_image($size)
  {
        global $avatar_default, $mustache_admin, $mustache_avatar, $mustache_medium, $mustache_original, $mustache_thumbnail, $post, $mnua_avatar_default, $mnua_disable_gravatar, $mnua_functions;
        
        $default_image_details = array();
        // Show custom Default Avatar
        if(!empty($mnua_avatar_default) && $mnua_functions->mnua_attachment_is_image($mnua_avatar_default)) {
          // Get image
          $mnua_avatar_default_image = $mnua_functions->mnua_get_attachment_image_src($mnua_avatar_default, array($size,$size));
          // Image src
          $default = $mnua_avatar_default_image[0];
          // Add dimensions if numeric size
          $default_image_details['dimensions'] = ' width="'.$mnua_avatar_default_image[1].'" height="'.$mnua_avatar_default_image[2].'"';
        
        } else {
          // Get mustache image based on numeric size comparison
          if($size > get_option('medium_size_w')) {
            $default = $mustache_original;
          } elseif($size <= get_option('medium_size_w') && $size > get_option('thumbnail_size_w')) {
            $default = $mustache_medium;
          } elseif($size <= get_option('thumbnail_size_w') && $size > 96) {
            $default = $mustache_thumbnail;
          } elseif($size <= 96 && $size > 32) {
            $default = $mustache_avatar;
          } elseif($size <= 32) {
            $default = $mustache_admin;
          }
          // Add dimensions if numeric size
          $default_image_details['dimensions'] = ' width="'.$size.'" height="'.$size.'"';
        }
        // Construct the img tag
        $default_image_details['size'] = $size;
        $default_image_details['src'] = $default;
         return $default_image_details;

  }
  /**
   * Replace get_avatar only in get_mn_user_avatar
   * @since 1.4
   * @param string $avatar
   * @param int|string $id_or_email
   * @param int|string $size
   * @param string $default
   * @param string $alt
   * @uses string $avatar_default
   * @uses string $mustache_admin
   * @uses string $mustache_avatar
   * @uses string $mustache_medium
   * @uses string $mustache_original
   * @uses string $mustache_thumbnail
   * @uses object $post
   * @uses int $mnua_avatar_default
   * @uses bool $mnua_disable_gravatar
   * @uses object $mnua_functions
   * @uses apply_filters()
   * @uses get_mn_user_avatar()
   * @uses has_mn_user_avatar()
   * @uses mnua_has_gravatar()
   * @uses mnua_attachment_is_image()
   * @uses mnua_get_attachment_image_src()
   * @uses get_option()
   * @return string $avatar
   */
  public function mnua_get_avatar_filter($avatar, $id_or_email="", $size="", $default="", $alt="") {
    
    global $avatar_default, $mustache_admin, $mustache_avatar, $mustache_medium, $mustache_original, $mustache_thumbnail, $post, $mnua_avatar_default, $mnua_disable_gravatar, $mnua_functions;
    // User has MNUA
    

	   $avatar = str_replace('gravatar_default','',$avatar);
    if(is_object($id_or_email)) {
      if(!empty($id_or_email->comment_author_email)) {
        $avatar = get_mn_user_avatar($id_or_email, $size, $default, $alt);
      } else {

        $avatar = get_mn_user_avatar('unknown@gravatar.com', $size, $default, $alt);
      }
    } else {
      if(has_mn_user_avatar($id_or_email)) {
        $avatar = get_mn_user_avatar($id_or_email, $size, $default, $alt);
      // User has Gravatar and Gravatar is not disabled
      } elseif((bool) $mnua_disable_gravatar != 1 && $mnua_functions->mnua_has_gravatar($id_or_email)) {
       // find our src
       if(!empty($avatar)) {
          $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $avatar, $matches, PREG_SET_ORDER);
          $mnua_avatar_image_src = !empty($matches) ? $matches [0] [1] : "";
          $default_image_details = $this->mnua_default_image($size); 
          $mnua_default_avatar_image_src = $default_image_details['src'];
          $mnua_final_avatar_image_src = str_replace('d=mn_user_avatar', 'd='.urlencode($mnua_default_avatar_image_src), $mnua_avatar_image_src);
        }

       //$avatar = $avatar;
       $avatar = '<img src="'.$mnua_final_avatar_image_src.'"'.$default_image_details['dimensions'].' alt="'.$alt.'" class="avatar avatar-'.$size.' mn-user-avatar mn-user-avatar-'.$size.' photo avatar-default" />';

      // User doesn't have MNUA or Gravatar and Default Avatar is mn_user_avatar, show custom Default Avatar
      } elseif($avatar_default == 'mn_user_avatar') {

       $default_image_details = $this->mnua_default_image($size); 
       $avatar = '<img src="'.$default_image_details['src'].'"'.$default_image_details['dimensions'].' alt="'.$alt.'" class="avatar avatar-'.$size.' mn-user-avatar mn-user-avatar-'.$size.' photo avatar-default" />';

       return $avatar;
        
         }
    }
    /**
     * Filter get_avatar filter
     * @since 1.9
     * @param string $avatar
     * @param int|string $id_or_email
     * @param int|string $size
     * @param string $default
     * @param string $alt
     */
    return apply_filters('mnua_get_avatar_filter', $avatar, $id_or_email, $size, $default, $alt);
  }

  /**
   * Get original avatar, for when user removes mn_user_avatar
   * @since 1.4
   * @param int|string $id_or_email
   * @param int|string $size
   * @param string $default
   * @param string $alt
   * @uses string $avatar_default
   * @uses string $mustache_avatar
   * @uses int $mnua_avatar_default
   * @uses bool $mnua_disable_gravatar
   * @uses object $mnua_functions
   * @uses mnua_attachment_is_image()
   * @uses mnua_get_attachment_image_src()
   * @uses mnua_has_gravatar()
   * @uses add_filter()
   * @uses apply_filters()
   * @uses get_avatar()
   * @uses remove_filter()
   * @return string $default
   */
  public function mnua_get_avatar_original($id_or_email="", $size="", $default="", $alt="") {
    global $avatar_default, $mustache_avatar, $mnua_avatar_default, $mnua_disable_gravatar, $mnua_functions;
    // Remove get_avatar filter
    remove_filter('get_avatar', array($mnua_functions, 'mnua_get_avatar_filter'));
    if((bool) $mnua_disable_gravatar != 1) {
      // User doesn't have Gravatar and Default Avatar is mn_user_avatar, show custom Default Avatar
      if(!$mnua_functions->mnua_has_gravatar($id_or_email) && $avatar_default == 'mn_user_avatar') {
        // Show custom Default Avatar
        if(!empty($mnua_avatar_default) && $mnua_functions->mnua_attachment_is_image($mnua_avatar_default)) {
          $mnua_avatar_default_image = $mnua_functions->mnua_get_attachment_image_src($mnua_avatar_default, array($size,$size));
          $default = $mnua_avatar_default_image[0];
        } else {
          $default = $mustache_avatar;
        }
      } else {
        // Get image from Gravatar, whether it's the user's image or default image
        $mnua_image = get_avatar($id_or_email, $size);
        // Takes the img tag, extracts the src
        $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $mnua_image, $matches, PREG_SET_ORDER);
        $default = !empty($matches) ? $matches [0] [1] : "";
      }
    } else {
      if(!empty($mnua_avatar_default) && $mnua_functions->mnua_attachment_is_image($mnua_avatar_default)) {
        $mnua_avatar_default_image = $mnua_functions->mnua_get_attachment_image_src($mnua_avatar_default, array($size,$size));
        $default = $mnua_avatar_default_image[0];
      } else {
        $default = $mustache_avatar;
      }
    }
    // Enable get_avatar filter
    add_filter('get_avatar', array($mnua_functions, 'mnua_get_avatar_filter'), 10, 5);
    /**
     * Filter original avatar src
     * @since 1.9
     * @param string $default
     */
    return apply_filters('mnua_get_avatar_original', $default);
  }

  /**
   * Find MNUA, show get_avatar if empty
   * @since 1.0
   * @param int|string $id_or_email
   * @param int|string $size
   * @param string $align
   * @param string $alt
   * @uses array $_mn_additional_image_sizes
   * @uses array $all_sizes
   * @uses string $avatar_default
   * @uses int $blog_id
   * @uses object $post
   * @uses object $mndb
   * @uses int $mnua_avatar_default
   * @uses object $mnua_functions
   * @uses apply_filters()
   * @uses get_the_author_meta()
   * @uses get_blog_prefix()
   * @uses get_user_by()
   * @uses get_query_var()
   * @uses is_author()
   * @uses mnua_attachment_is_image()
   * @uses mnua_get_attachment_image_src()
   * @uses get_option()
   * @uses get_avatar()
   * @return string $avatar
   */
  public function get_mn_user_avatar($id_or_email="", $size='96', $align="", $alt="") {
    global $all_sizes, $avatar_default, $blog_id, $post, $mndb, $mnua_avatar_default, $mnua_functions, $_mn_additional_image_sizes;
    $email='unknown@gravatar.com';
    // Checks if comment 
    
    if(is_object($id_or_email)) {
      // Checks if comment author is registered user by user ID
      if($id_or_email->user_id != 0) {
        $email = $id_or_email->user_id;
      // Checks that comment author isn't anonymous
      } elseif(!empty($id_or_email->comment_author_email)) {
        // Checks if comment author is registered user by e-mail address
        $user = get_user_by('email', $id_or_email->comment_author_email);
        // Get registered user info from profile, otherwise e-mail address should be value
        $email = !empty($user) ? $user->ID : $id_or_email->comment_author_email;
      }
      $alt = $id_or_email->comment_author;
    } else {
      if(!empty($id_or_email)) {
        // Find user by ID or e-mail address
        $user = is_numeric($id_or_email) ? get_user_by('id', $id_or_email) : get_user_by('email', $id_or_email);
      } else {
        // Find author's name if id_or_email is empty
        $author_name = get_query_var('author_name');
        if(is_author()) {
          // On author page, get user by page slug
          $user = get_user_by('slug', $author_name);
        } else {
          // On post, get user by author meta
          $user_id = get_the_author_meta('ID');
          $user = get_user_by('id', $user_id);
        }
      }
      // Set user's ID and name
      if(!empty($user)) {
        $email = $user->ID;
        $alt = $user->display_name;
      }
    }
    // Checks if user has MNUA
    $mnua_meta = get_the_author_meta($mndb->get_blog_prefix($blog_id).'user_avatar', $email);
    // Add alignment class
    $alignclass = !empty($align) && ($align == 'left' || $align == 'right' || $align == 'center') ? ' align'.$align : ' alignnone';
    // User has MNUA, check if on excluded list and bypass get_avatar
    if(!empty($mnua_meta) && $mnua_functions->mnua_attachment_is_image($mnua_meta)) {
      // Numeric size use size array
      $get_size = is_numeric($size) ? array($size,$size) : $size;
      // Get image src
      $mnua_image = $mnua_functions->mnua_get_attachment_image_src($mnua_meta, $get_size);
      // Add dimensions to img only if numeric size was specified
      $dimensions = is_numeric($size) ? ' width="'.$mnua_image[1].'" height="'.$mnua_image[2].'"' : "";
      // Construct the img tag
      $avatar = '<img src="'.$mnua_image[0].'"'.$dimensions.' alt="'.$alt.'" class="avatar avatar-'.$size.' mn-user-avatar mn-user-avatar-'.$size.$alignclass.' photo" />';
    } else {
      // Check for custom image sizes
      if(in_array($size, $all_sizes)) {
        if(in_array($size, array('original', 'large', 'medium', 'thumbnail'))) {
          $get_size = ($size == 'original') ? get_option('large_size_w') : get_option($size.'_size_w');
        } else {
          $get_size = $_mn_additional_image_sizes[$size]['width'];
        }
      } else {
        // Numeric sizes leave as-is
        $get_size = $size;
      }
      // User with no MNUA uses get_avatar
      $avatar = get_avatar($email, $get_size, $default="", $alt="");
      // Remove width and height for non-numeric sizes
      if(in_array($size, array('original', 'large', 'medium', 'thumbnail'))) {
        $avatar = preg_replace('/(width|height)=\"\d*\"\s/', "", $avatar);
        $avatar = preg_replace("/(width|height)=\'\d*\'\s/", "", $avatar);
      }
      $replace = array('mn-user-avatar ', 'mn-user-avatar-'.$get_size.' ', 'mn-user-avatar-'.$size.' ', 'avatar-'.$get_size, ' photo');
      $replacements = array("", "", "", 'avatar-'.$size, 'mn-user-avatar mn-user-avatar-'.$size.$alignclass.' photo');
      $avatar = str_replace($replace, $replacements, $avatar);
    }
    /**
     * Filter get_mn_user_avatar
     * @since 1.9
     * @param string $avatar
     * @param int|string $id_or_email
     * @param int|string $size
     * @param string $align
     * @param string $alt
     */
    return apply_filters('get_mn_user_avatar', $avatar, $id_or_email, $size, $align, $alt);
  }

  /**
   * Return just the image src
   * @since 1.1
   * @param int|string $id_or_email
   * @param int|string $size
   * @param string $align
   * @uses get_mn_user_avatar()
   * @return string
   */
  public function get_mn_user_avatar_src($id_or_email="", $size="", $align="") {
    $mnua_image_src = "";
    // Gets the avatar img tag
    $mnua_image = get_mn_user_avatar($id_or_email, $size, $align);
    // Takes the img tag, extracts the src
    if(!empty($mnua_image)) {
      $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $mnua_image, $matches, PREG_SET_ORDER);
      $mnua_image_src = !empty($matches) ? $matches [0] [1] : "";
    }
    return $mnua_image_src;
  }
}

/**
 * Initialize
 * @since 1.9.2
 */
function mnua_functions_init() {
  global $mnua_functions;
  $mnua_functions = new MN_User_Avatar_Functions();
}
add_action('plugins_loaded', 'mnua_functions_init');
