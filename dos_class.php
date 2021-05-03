<?php

class DOS {

  private static $instance;
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
	 *
	 * @return DOS
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new DOS(
				defined( 'DOS_KEY' ) ? DOS_KEY : null,
				defined( 'DOS_SECRET' ) ? DOS_SECRET : null,
        defined( 'DOS_CONTAINER' ) ? DOS_CONTAINER : null,
        defined( 'DOS_ENDPOINT' ) ? DOS_ENDPOINT : null,
        defined( 'DOS_STORAGE_PATH' ) ? DOS_STORAGE_PATH : null,
        defined( 'DOS_STORAGE_FILE_ONLY' ) ? DOS_STORAGE_FILE_ONLY : null,
        defined( 'DOS_STORAGE_FILE_DELETE' ) ? DOS_STORAGE_FILE_DELETE : null,
        defined( 'DOS_FILTER' ) ? DOS_FILTER : null,
        defined( 'UPLOAD_URL_PATH' ) ? UPLOAD_URL_PATH : null,
        defined( 'UPLOAD_PATH' ) ? UPLOAD_PATH : null
			);
		}
		return self::$instance;
  }

	public function __construct( $key, $secret, $container, $endpoint, $storage_path, $storage_file_only, $storage_file_delete, $filter, $upload_url_path, $upload_path ) {
		$this->key                 = empty($key) ? get_option('dos_key') : $key;
		$this->secret              = empty($secret) ? get_option('dos_secret') : $secret;
    $this->endpoint            = empty($endpoint) ? get_option('dos_endpoint') : $endpoint;
    $this->container           = empty($container) ? get_option('dos_container') : $container;
    $this->storage_path        = empty($storage_path) ? get_option('dos_storage_path') : $storage_path;
    $this->storage_file_only   = empty($storage_file_only) ? get_option('dos_storage_file_only') : $storage_file_only;
    $this->storage_file_delete = empty($storage_file_delete) ? get_option('dos_storage_file_delete') : $storage_file_delete;
    $this->filter              = empty($filter) ? get_option('dos_filter') : $filter;
    $this->upload_url_path     = empty($upload_url_path) ? get_option('upload_url_path') : $upload_url_path;
    $this->upload_path         = empty($upload_path) ? get_option('upload_path') : $upload_path;
	}

  // SETUP
  public function setup () {

    $this->register_actions();
    $this->register_filters();

  }

  // REGISTRATIONS
  private function register_actions () {

    add_action('admin_menu', array($this, 'register_menu') );
    add_action('admin_init', array($this, 'register_settings' ) );
    add_action('admin_enqueue_scripts', array($this, 'register_scripts' ) );
    add_action('admin_enqueue_scripts', array($this, 'register_styles' ) );

    add_action('wp_ajax_dos_test_connection', array($this, 'test_connection' ) );

    add_action('add_attachment', array($this, 'action_add_attachment' ), 10, 1);
    add_action('delete_attachment', array($this, 'action_delete_attachment' ), 10, 1);

  }

  private function register_filters () {

    add_filter('wp_generate_attachment_metadata', array($this, 'filter_wp_generate_attachment_metadata'), 20, 1);
    // add_filter('wp_save_image_editor_file', array($this,'filter_wp_save_image_editor_file'), 10, 5 );
    add_filter('wp_unique_filename', array($this, 'filter_wp_unique_filename') );

    //SUPPORT IMAGE EDITION (CROP, RESIZE, ROTATE)
    add_filter( 'image_make_intermediate_size', 'filter_wp_save_image_editor_file', 10, 1 );

  }

  public function register_scripts () {

    wp_enqueue_script('dos-core-js', plugin_dir_url( __FILE__ ) . '/assets/scripts/core.js', array('jquery'), '1.4.0', true);

  }

  public function register_styles () {

    wp_enqueue_style('dos-flexboxgrid', plugin_dir_url( __FILE__ ) . '/assets/styles/flexboxgrid.min.css' );
    wp_enqueue_style('dos-core-css', plugin_dir_url( __FILE__ ) . '/assets/styles/core.css' );

  }

  public function register_settings () {

    register_setting('dos_settings', 'dos_key');
    register_setting('dos_settings', 'dos_secret');
    register_setting('dos_settings', 'dos_endpoint');
    register_setting('dos_settings', 'dos_container');
    register_setting('dos_settings', 'dos_storage_path');
    register_setting('dos_settings', 'dos_storage_file_only');
    register_setting('dos_settings', 'dos_storage_file_delete');
    register_setting('dos_settings', 'dos_filter');
    // register_setting('dos_settings', 'dos_debug');
    register_setting('dos_settings', 'upload_url_path');
    register_setting('dos_settings', 'upload_path');

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
  public function filter_wp_generate_attachment_metadata ($metadata) {

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

  public function filter_wp_save_image_editor_file($filename) {

    if (!file_exists($filename)) {
      $this->file_upload($filename);
    }

    return $filename;
  }

  public function filter_wp_unique_filename ($filename) {

    $upload_dir = wp_upload_dir();
    $subdir = $upload_dir['subdir'];

    $filesystem = DOS_Filesystem::get_instance($this->key, $this->secret, $this->container, $this->endpoint);

    $number = 1;
    $new_filename = $filename;
    $fileparts = pathinfo($filename);
    $cdnPath = rtrim($this->storage_path,'/') . '/' . ltrim($subdir,'/') . '/' . $new_filename;
    while ( $filesystem->has( $cdnPath ) ) {
      $new_filename = $fileparts['filename'] . "-$number." . $fileparts['extension'];
      $number = (int) $number + 1;
      $cdnPath = rtrim($this->storage_path,'/') . '/' . ltrim($subdir,'/') . '/' . $new_filename;
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

        if ( !in_array($path, $paths) ) {
          array_push($paths, $path);
        }

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
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $postData = $_POST;
        $keys = ['key' => 'dos_key','secret' => 'dos_secret','endpoint' => 'dos_endpoint','container' => 'dos_container'];
        foreach ($keys as $prop => $key) {
            if(isset($postData[$key])){
                $this->$prop = $postData[$key];
            }
        }
    }
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

    $path = strlen($this->upload_path) ? str_replace($this->upload_path, '', $file)
                                       : str_replace(wp_upload_dir()['basedir'], '', $file);

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
