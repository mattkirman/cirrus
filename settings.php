<?php
// Create settings
add_action('admin_init', array('CirrusSettings', 'init'));
add_action('admin_menu', array('CirrusSettings', 'create_menu'));

class CirrusSettings
{
  
  public static function init()
  {
    register_setting('cirrus', 'cirrus', array('CirrusSettings', 'validate'));

    add_settings_section('cirrus_cloud_creds', 'CloudApp Credentials', array('CirrusSettings', 'cloud_creds_description'), 'cirrus');
    add_settings_field('cirrus_cloud_username', 'Email Address', array('CirrusSettings', 'cloud_username_field'), 'cirrus', 'cirrus_cloud_creds', array('label_for' => 'cirrus_cloud_username'));
    add_settings_field('cirrus_cloud_password', 'Password', array('CirrusSettings', 'cloud_password_field'), 'cirrus', 'cirrus_cloud_creds', array('label_for' => 'cirrus_cloud_password'));
  }


  public static function create_menu()
  {
    add_options_page('Cirrus', 'Cirrus', 'administrator', 'cirrus', array('CirrusSettings', 'settings_page'));
  }


  public static function validate($input)
  {
    $output = array();
  
    $output['cloud_username'] = $input['cloud_username'];

    if (!empty($input['cloud_password'])) {
      $output['cloud_password'] = $input['cloud_password'];
    }
    
    return $output;
  }


  public static function cloud_creds_description()
  {
?>
<p>
  Cirrus creates shortlinks by adding your blog posts as bookmarks to your CloudApp account.
  Haven't got a CloudApp account? <a href="http://getcloudapp.com/" target="_blank">You can get one here</a>.
</p>
<?php
  }


  public static function cloud_username_field()
  {
    $username = (isset($_POST['cirrus']['cloud_username'])) ? $_POST['cirrus']['cloud_username'] : self::get_setting('cloud_username');
    echo '<input type="text" id="cirrus_cloud_username" name="cirrus[cloud_username]" value="' . $username . '" />';
  }


  public static function cloud_password_field()
  {
    echo '<input type="password" id="cirrus_cloud_password" name="cirrus[cloud_password]" />';
  }


  public static function settings_page()
  {
    if (isset($_GET['add_shortlinks'])) {
      Cirrus::add_shortlinks_to_all_posts();
    }

?>
<div class="wrap">
  <h2>Cirrus</h2>
  
  <form action="options.php" method="post">
    <?php settings_fields('cirrus'); ?>
    <?php do_settings_sections('cirrus'); ?>
    
    <p class="submit">
      <input type="submit" class="button-primary" value="Save Changes" />
      <span style="margin-left:20px"><a href="?page=cirrus&add_shortlinks">Add shortlinks to existing posts</a></span>
    </p>
  </form>
</div>
<?php
    Cirrus::add_shortlinks_to_all_posts();
  }


  public static function get_setting($setting)
  {
    $settings = get_option('cirrus');
    if (array_key_exists($setting, $settings)) {
      return $settings[$setting];
    }
    return false;
  }

}
