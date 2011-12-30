<?php

/**
 * Configuration class to easily add the Limelight configurations.
 *
 * To get these configurations, log into your Limelight CDN account and then
 * go to:
 *
 *   Settings > Developer Tools
 *
 * If you don't wish to set these settings here, but define them elsewhere,
 * you can always create a global variable called $limelight_config and do it there.
 *
 * For example:
 *
 *   global $limelight_config;
 *   $limelight_config = array(
 *     'organization_id' => '12345',
 *     'access_key' => '1234',
 *     'secret' => '1234',
 *     'cache_timeout' => 4500
 *   );
 *
 *   $media = new LimelightMedia();
 *   ...
 *
 */
class LimelightConfig {

  /**
   * You can either add your settings here, or define them
   * elsewhere using example code above.
   */
  public static $config = array(
    'base_url' => 'http://api.videoplatform.limelight.com/rest/organizations',
    'organization_id' => '',
    'access_key' => '',
    'secret' => '',
    'cache_timeout' => 4800,
    'request_class' => 'LimelightCachedRequest',
    'authenticate' => FALSE
  );

  /**
   * Returns the limelight configuration.
   *
   * @global type $limelight_config
   * @return type
   */
  public static function getConfig($defaults = array()) {
    global $limelight_config;
    if ($limelight_config) {
      return array_merge(self::$config, array_filter($limelight_config));
    }
    else if ($defaults) {
      return array_merge(self::$config, array_filter($defaults));
    }
    else {
      return self::$config;
    }
  }
}
?>
