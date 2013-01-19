<?php
/*
 * Plugin Name: Cirrus
 * Plugin URI: https://github.com/mattkirman/cirrus
 * Description: CloudApp powered WordPress shortlinks.
 * Author: Matt Kirman
 * Version: 0.0.2
 * Author URI: http://mattkirman.com
 */
require_once dirname(__FILE__) . '/Cloud/API.php';
require_once dirname(__FILE__) . '/settings.php';

add_filter('get_shortlink', array('Cirrus', 'get_shortlink'), 1, 4);
add_action('publish_post', array('Cirrus', 'set_shortlink'), 1, 1);


class Cirrus
{

  /**
   * The custom post field that we use to store shortlink data in.
   *
   * @access private
   * @static
   */
  private static $META_KEY = 'cirrus_shortlink';


  /**
   * The CloudApp client object. Don't reference this directly, use the public
   * Cirrus::client() method instead.
   *
   * @access private
   * @static
   */
  private static $CLOUD_CLIENT;


  /**
   * Creates shortlinks for every post. This will take a long time on large blogs,
   * so should be used sparingly.
   *
   * @access public
   * @static
   */
  public static function add_shortlinks_to_all_posts()
  {
    // Is this really the only way to get all posts?
    $query = new WP_Query(array('posts_per_page' => '999999', 'order' => 'asc'));

    while ($query->have_posts()) {
      $query->the_post();
      self::set_shortlink($query->post->ID);
    }
  }


  /**
   * Replaces the default shortlink (i.e. example.com/?p=123) with super awesome
   * CloudApp URLs. Both default cl.ly and custom domains are supported.
   *
   * @access public
   * @static
   * @param string $shortlink The original post shortlink
   * @param integer $id The post Id
   * @param string $context
   * @param bool $allow_slugs
   */
  public static function get_shortlink($shortlink, $id, $context, $allow_slugs)
  {
    if ($context == 'post') {
      return self::get_shortlink_for_post($id, $shortlink);
    }

    return $shortlink;
  }


  /**
   * 
   *
   * @access public
   * @static
   * @param integer $id The post Id
   * @param bool $update If the post already has a shortlink, do we update it?
   */
  public static function set_shortlink($id, $update=false)
  {
    $permalink = get_permalink($id);

    if (self::post_has_shortlink($id) && !$update) return false;

    $title = get_the_title($id);
    $shortlink = self::client()->addBookmark($permalink, $title, false)->url;

    if (self::post_has_shortlink($id)) {
      $old_shortlink = self::get_shortlink_for_post($id);
      update_post_meta($id, self::$META_KEY, $shortlink, $old_shortlink);
    } else {
      add_post_meta($id, self::$META_KEY, $shortlink, true);
    }
  }


  /**
   * Does this post already have a Cirrus shortlink?
   *
   * @access private
   * @static
   * @param integer $post_id
   */
  private static function post_has_shortlink($post_id)
  {
    $fields = get_post_custom($post_id);
    if (array_key_exists(self::$META_KEY, $fields) && !empty($fields[self::$META_KEY]) && !empty($fields[self::$META_KEY][0])) return true;
    return false;
  }


  /**
   * Returns the Cirrus shortlink for a post. If the post doesn't have a Cirrus
   * shortlink then either return boolean false or the value passed to |default|.
   *
   * @access private
   * @static
   * @param integer $post_id
   * @param string $default If the post doesn't have a shortlink, use this
   */
  private static function get_shortlink_for_post($post_id, $default=false)
  {
    $fields = get_post_custom($id);
    if (self::post_has_shortlink($id)) return $fields[self::$META_KEY][0];
    if ($default !== false) return $default;
    return false;
  }


  /**
   * 
   *
   * @access public
   * @static
   */
  public static function client()
  {
    if (isset(self::$CLOUD_CLIENT)) return self::$CLOUD_CLIENT;

    self::$CLOUD_CLIENT = new Cloud_API(CirrusSettings::get_setting('cloud_username'), CirrusSettings::get_setting('cloud_password'), 'Cirrus');

    return self::$CLOUD_CLIENT;
  }

}
