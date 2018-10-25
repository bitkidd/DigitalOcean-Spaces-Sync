<?php

class DOS {

  private        $keys;
  private        $key;
  private        $secret;
  private        $endpoint;
  private        $container;
  private        $storage_path;
  private        $storage_file_only;
  private        $storage_file_delete;
  private        $filter;
  private        $upload_url_path;
  private        $upload_path;

  /**
   * Initialize DOS with the constants if defined
   */
  public function __construct() {
    $this->keys = [
      'dos_key',
      'dos_secret',
      'dos_endpoint',
      'dos_container',
      'dos_storage_path',
      'dos_storage_file_only',
      'dos_storage_file_delete',
      'dos_filter',
      'upload_url_path',
      'upload_path'
    ];

    foreach ($this->keys as $key) {
      $trimmed_key = str_replace('dos_', '', $key);
      $this->{$trimmed_key} = $this->get_setting($key);
    }
  }

  /**
   * Retrive the required key from CONSTANT if is defined
   * or fron the options if exists
   *
   * @param string $setting
   * @return string|void
   */
  public function get_setting($setting)
  {
      $upper_setting = strtoupper($setting);

      if (defined($upper_setting) && !empty($upper_setting))
          return constant($upper_setting);
      
      return get_option('dos_key');
  }
  
  // SETUP
  public function setup () {

    $this->register_actions();
    $this->register_filters();

  }

  // REGISTRATIONS
  private function register_actions () {

    add_action('admin_menu', array($this, 'register_menu') );

    add_action('admin_enqueue_scripts', array($this, 'register_scripts' ) );
    add_action('admin_enqueue_scripts', array($this, 'register_styles' ) );

    add_action('wp_ajax_dos_test_connection', array($this, 'test_connection' ) );

    add_action('add_attachment', array($this, 'action_add_attachment' ), 10, 1);
    add_action('delete_attachment', array($this, 'action_delete_attachment' ), 10, 1);

  }

  private function register_filters () {

    add_filter('wp_update_attachment_metadata', array($this, 'filter_wp_update_attachment_metadata'), 20, 1);
    // add_filter('wp_save_image_editor_file', array($this,'filter_wp_save_image_editor_file'), 10, 5 );
    add_filter('wp_unique_filename', array($this, 'filter_wp_unique_filename') );
    
  }

  public function register_scripts () {

    wp_enqueue_script('dos-core-js', plugin_dir_url( __FILE__ ) . '/assets/scripts/core.js', array('jquery'), '1.4.0', true);

  }

  public function register_styles () {

    wp_enqueue_style('dos-flexboxgrid', plugin_dir_url( __FILE__ ) . '/assets/styles/flexboxgrid.min.css' );
    wp_enqueue_style('dos-core-css', plugin_dir_url( __FILE__ ) . '/assets/styles/core.css' );

  }

  public function register_setting_page () {
    include_once('dos_settings_page.php');
  }

  public function register_menu () {

    add_options_page(
      'DigitalOcean Spaces Sync',
      'DigitalOcean Spaces Sync',
      'manage_options',
      'setting_page.php',
      array($this, 'register_setting_page')
    );

  }

  // FILTERS
  public function filter_wp_update_attachment_metadata ($metadata) {

    $paths = array();
    $upload_dir = wp_upload_dir();

    // collect original file path
    if ( isset($metadata['file']) ) {

      $path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $metadata['file'];
      array_push($paths, $path);

      // set basepath for other sizes
      $file_info = pathinfo($path);
      $basepath = isset($file_info['extension'])
          ? str_replace($file_info['filename'] . "." . $file_info['extension'], "", $path)
          : $path;

    }

    // collect size files path
    if ( isset($metadata['sizes']) ) {

      foreach ( $metadata['sizes'] as $size ) {

        if ( isset($size['file']) ) {

          $path = $basepath . $size['file'];
          array_push($paths, $path);

        }

      }

    }

    // process paths
    foreach ($paths as $filepath) {

      // upload file
      $this->file_upload($filepath, 0, true);

    }

    return $metadata;

  }

  public function filter_wp_unique_filename ($filename) {
    
    $upload_dir = wp_upload_dir();
    $subdir = $upload_dir['subdir'];

    $filesystem = DOS_Filesystem::get_instance($this->key, $this->secret, $this->container, $this->endpoint);

    $number = 1;
    $new_filename = $filename;
    $fileparts = pathinfo($filename);

    while ( $filesystem->has( $subdir . "/$new_filename" ) ) {
      $new_filename = $fileparts['filename'] . "-$number." . $fileparts['extension'];
      $number = (int) $number + 1;
    }

    return $new_filename;

  }

  // ACTIONS
  public function action_add_attachment ($postID) {

    if ( wp_attachment_is_image($postID) == false ) {
  
      $file = get_attached_file($postID);
  
      $this->file_upload($file);
  
    }
  
    return true;

  }

  public function action_delete_attachment ($postID) {

    $paths = array();
    $upload_dir = wp_upload_dir();

    if ( wp_attachment_is_image($postID) == false ) {
  
      $file = get_attached_file($postID);
  
      $this->file_delete($file);
  
    } else {

      $metadata = wp_get_attachment_metadata($postID);

      // collect original file path
      if ( isset($metadata['file']) ) {

        $path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $metadata['file'];
        array_push($paths, $path);

        // set basepath for other sizes
        $file_info = pathinfo($path);
        $basepath = isset($file_info['extension'])
            ? str_replace($file_info['filename'] . "." . $file_info['extension'], "", $path)
            : $path;

      }

      // collect size files path
      if ( isset($metadata['sizes']) ) {

        foreach ( $metadata['sizes'] as $size ) {

          if ( isset($size['file']) ) {

            $path = $basepath . $size['file'];
            array_push($paths, $path);

          }

        }

      }

      // process paths
      foreach ($paths as $filepath) {

        // upload file
        $this->file_delete($filepath);

      }

    }

  }

  // METHODS
  public function test_connection () {

    try {
    
      $filesystem = DOS_Filesystem::get_instance($this->key, $this->secret, $this->container, $this->endpoint);
      $filesystem->write('test.txt', 'test');
      $filesystem->delete('test.txt');
      // $exists = $filesystem->has('photo.jpg');

      $this->show_message(__('Connection is successfully established. Save the settings.', 'dos')); 
      exit();
  
    } catch (Exception $e) {
  
      $this->show_message( __('Connection is not established.','dos') . ' : ' . $e->getMessage() . ($e->getCode() == 0 ? '' : ' - ' . $e->getCode() ), true);
      exit();
  
    }

  }

  public function show_message ($message, $errormsg = false) {

    if ($errormsg) {
  
      echo '<div id="message" class="error">';
  
    } else {
  
      echo '<div id="message" class="updated fade">';
  
    }
  
    echo "<p><strong>$message</strong></p></div>";
  
  }

  // FILE METHODS
  public function file_path ($file) {

    $path = str_replace($this->upload_path, '', $file);
  
    return $this->storage_path . $path;

  }

  public function file_upload ($file) {

    // init cloud filesystem
    $filesystem = DOS_Filesystem::get_instance($this->key, $this->secret, $this->container, $this->endpoint);
    $regex_string = $this->filter;

    // prepare regex
    if ( $regex_string == '*' || !strlen($regex_string)) {
      $regex = false;
    } else {
      $regex = preg_match( $regex_string, $file);
    }

    try {

      // check if readable and regex matched
      if ( is_readable($file) && !$regex ) {

        // create fiel in storage
        $filesystem->put( $this->file_path($file), file_get_contents($file), [
          'visibility' => 'public'
        ]);

        // remove on upload
        if ( $this->storage_file_only == 1 ) {
          
          unlink($file);

        }
        
      }

      return true;

    } catch (Exception $e) {

      return false;

    }

  }

  public function file_delete ($file) {

    if ( $this->storage_file_delete == 1 ) {

      try {

        $filepath = $this->file_path($file);
        $filesystem = DOS_Filesystem::get_instance($this->key, $this->secret, $this->container, $this->endpoint);

        $filesystem->delete( $filepath );

      } catch (Exception $e) {

        error_log( $e );

      }      

    }

    return $file;   

  }

}