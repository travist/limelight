<?php

// Require the CachedRequest library.
require_once 'CachedRequest.php';

/**
 * This class implements a file caching mechanism for all requests.
 */
class HTTP_FileCachedRequest extends HTTP_CachedRequest {

  protected $cache_path = '';

  // The constructor.
  function __construct($url = null, $method = self::METHOD_GET, array $config = array()) {

    // Add the configuration for this class to the configuration.
    $this->config = array_merge($this->config, array(
      'cache_directory' => dirname(__FILE__) . '/cache'
    ));

    // Call the parent constructor.
    parent::__construct($url, $method, $config);
  }

  /**
   * Sets the cache name.
   */
  public function set_cache_name($cache_name) {
    parent::set_cache_name($cache_name);
    $this->cache_path = $this->config['cache_directory'] . '/' . $cache_name;
  }

  /**
   * Returns if the cache exists.
   *
   * @return type
   */
  public function cache_exists() {
    return file_exists($this->cache_path);
  }

  /**
   * Return if this cache is valid.
   *
   * @return type
   */
  public function cache_valid() {
    $valid = parent::cache_valid();
    if ($valid) {
      $valid &= ((filemtime($this->cache_path) + $this->config['cache_timeout']) >= time());
    }
    return $valid;
  }

  /**
   * Returns the cache.
   */
  public function get_cache() {
    return file_get_contents($this->cache_path);
  }

  /**
   * Clears the current cache for this name.
   */
  public function cache_clear() {
    if ($this->cache_exists()) {
      unlink($this->cache_path);
    }
  }

  /**
   * Cache the response.
   */
  public function cache_response($body) {
    $ret = FALSE;
    if ($fp = fopen($this->cache_path,"w")) {
      $ret = fwrite($fp, $body);
      fclose($fp);
    }
    return $ret;
  }
}
?>
