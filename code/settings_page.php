<div class="dos__loader">
  
</div>

<div class="dos__page row">
  
  <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">

    <div class="dos__message"></div>

    <div class="row">
      
      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h2>DigitalOcean Spaces Sync <?php _e('Settings', 'dos'); ?></h2>
      </div>

    </div>

    <div class="row">
      
      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <?php _e('Type in your DigitalOcean Spaces container access information.', 'dos'); ?>
        <?php _e('Don\'t have an account? <a href="https://goo.gl/SX2UwH">Create it</a>', 'dos'); ?>
      </div>

    </div>

    <form method="POST" action="options.php">

      <?php settings_fields('dos_settings'); ?>

      <div class="row">
        
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <h4>
            <?php _e('Connection settings', 'dos'); ?>
          </h4>
        </div>

      </div>

      <div class="dos__block">

        <div class="row">
          
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
            <label for="dos_key">
              <?php _e('DO Spaces Key', 'dos'); ?>:
            </label>
          </div>

          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <input id="dos_key" name="dos_key" type="text"
                   value="<?php echo esc_attr( get_option('dos_key') ); ?>" 
                   class="regular-text code"/>
          </div>

        </div>

        <div class="row">
          
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
            <label for="dos_key">
              <?php _e('DO Spaces Secret', 'dos'); ?>:
            </label>
          </div>

          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <input id="dos_secret" name="dos_secret" type="password"
                   value="<?php echo esc_attr( get_option('dos_secret') ); ?>" 
                   class="regular-text code"/>
          </div>

        </div>

        <div class="row">
          
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
            <label for="dos_key">
              <?php _e('DO Spaces Container', 'dos'); ?>:
            </label>
          </div>

          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <input id="dos_container" name="dos_container"
                   type="text" size="15" value="<?php echo esc_attr( get_option('dos_container') ); ?>" 
                   class="regular-text code"/>
          </div>

        </div>

        <div class="row">
          
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
            <label for="dos_key">
              <?php _e('Endpoint', 'dos'); ?>:
            </label>
          </div>

          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <input id="dos_endpoint" name="dos_endpoint" type="text"
                   value="<?php echo esc_attr(get_option('dos_endpoint')); ?>" class="regular-text code"/>
            <div class="dos__description">
              <?php _e('By default', 'dos'); ?>: <code>https://ams3.digitaloceanspaces.com</code>
            </div>
          </div>

        </div>

        <div class="row">
          
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
            <input type="button" name="test" id="submit" class="button button-primary dos__test__connection"
                   value="<?php _e('Check the connection', 'dos'); ?>" />
          </div>

        </div>

      </div>

      <div class="row">
        
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <h4>
            <?php _e('File & Path settings', 'dos'); ?>
          </h4>
        </div>

      </div>

      <div class="dos__block">

        <div class="row larger">
          
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
            <label for="dos_key">
              <?php _e('Full URL-path to files', 'dos'); ?>:
            </label>
          </div>

          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <input id="upload_url_path" name="upload_url_path" type="text"
                   value="<?php echo esc_attr( get_option('upload_url_path') ); ?>"
                   class="regular-text code"/>
            <div class="dos__description">
              <?php _e('Enter storage public domain or subdomain if the files are stored only in the cloud storage', 'dos'); ?>
              <code>(http://uploads.example.com)</code>, 
              <?php _e('or full URL path, if are kept both in cloud and on the server.','dos'); ?>
              <code>(http://example.com/wp-content/uploads)</code>.</p>
              <?php _e('In that case duplicates are created. If you change one, you change and the other,','dos'); ?>
            </div>
          </div>

        </div>

        <div class="row">
          
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
            <label for="dos_key">
              <?php _e('Local path', 'dos'); ?>:
            </label>
          </div>

          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <input id="upload_path" name="upload_path" type="text"
                   value="<?php echo esc_attr( get_option('upload_path') ); ?>"
                   class="regular-text code"/>
            <div class="dos__description">
              <?php _e('Local path to the uploaded files. By default', 'dos'); ?>: <code>wp-content/uploads</code>
              <?php _e('Setting duplicates of the same name from the mediafiles settings. Changing one, you change and other', 'dos'); ?>.
            </div>
          </div>

        </div>

        <div class="row">
          
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
            <label for="dos_key">
              <?php _e('Storage prefix', 'dos'); ?>:
            </label>
          </div>

          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <input id="dos_storage_path" name="dos_storage_path"
                   type="text"
                   value="<?php echo esc_attr( get_option('dos_storage_path') ); ?>" class="regular-text code"/>
            <div class="dos__description">
              <?php _e( 'The path to the file in the storage will appear as a prefix / path.<br />For example, in your case:', 'dos' ); ?>
              <code><?php echo get_option('dos_storage_path') . $first_file; ?></code>
            </div>
          </div>

        </div>

        <div class="row">
          
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
            <label for="dos_key">
              <?php _e('Filemask/Regex for ignored files', 'dos'); ?>:
            </label>
          </div>

          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <input id="dos_filter" name="dos_filter" type="text"
                   value="<?php echo esc_attr(get_option('dos_filter')); ?>" class="regular-text code"/>
            <div class="dos__description">
              <?php _e('By default empty or', 'dos'); ?><code>*</code>
              <?php _e('Will upload all the files by default, you are free to use any Regular Expression to match and ignore the selection you need, for example:', 'dos'); ?>
              <code>/^.*\.(zip|rar|docx)$/i</code>
            </div>
          </div>

        </div>

      </div>

      <div class="row">
        
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <h4>
            <?php _e('Sync settings', 'dos'); ?>
          </h4>
        </div>

      </div>

      <div class="row">
        
        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1" style="width: 50px;">
          <input id="onlystorage" type="checkbox" name="dos_storage_file_only"
                 value="1" <?php checked( get_option('dos_storage_file_only'), 1); ?> />
        </div>

        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11">
          <?php _e('Store files only in the cloud and delete after successful upload.', 'dos'); ?>
          <?php _e('In that case file will be removed from your server after being uploaded to cloud storage, that saves you space.', 'dos'); ?>
        </div>

      </div>

      <div class="row">
        
        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
          <input id="dos_storage_file_delete" type="checkbox" name="dos_storage_file_delete"
                 value="1" <?php checked( get_option('dos_storage_file_delete'), 1); ?> />
        </div>

        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11">
          <?php _e( 'Delete file from cloud storage as soon as it was removed from your library.', 'dos' ); ?>
        </div>

      </div>

      <div class="row">
        
        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
          <input id="dos_debug" type="checkbox" name="dos_debug"
                 value="1" <?php checked( get_option('dos_debug'), 1); ?> />
        </div>

        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11">
          <?php _e('Enable debug mode. Do not enable unless you know what it is.', 'dos'); ?>
        </div>

      </div>

      <div class="row">
        
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <input type="hidden" name="action" value="update"/>
          <?php submit_button(); ?>
        </div>

      </div>

    </form>

  </div>

  <div class="col-xs-12 col-xs-12 col-md-4 col-lg-4">
    
    <p>
      <img id="img-spinner" 
           src="<?php echo plugins_url() . '/' . dirname( plugin_basename(__FILE__) ); ?>/../assets/images/do_logo.png" alt="DigitalOcean"
           style="width: 150px;"/>
    </p>

    <p>
      This plugin syncs your WordPress library with DigitalOcean Spaces Container. It may be buggy, it may sometimes fail, feel free to write issues on github and PR.
    </p>

  </div>

</div>
