<?php

// Require the CachedRequest library.
require_once 'CachedRequest.php';

/**
 * This class implements a file caching mechanism for all requests.
 */
class HTTP_FileCachedRequest extends HTTP_CachedRequest {

  protected $cache_path = '';
  protected $cache_exists = FALSE;

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
    $this->cache_exists = file_exists($this->cache_path);
  }

  /**
   * Return if this cache is valid.
   *
   * @return type
   */
  public function cache_valid() {
    $valid = $this->cache_exists;
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
   * Cache the response.
   */
  public function cache_response($body) {
    $ret = FALSE;

    // Delete the file, and create a new one with the contents of this response.
    if ($this->cache_exists) {
      unlink($this->cache_path);
    }
    if ($fp = fopen($this->cache_path,"w")) {
      $ret = fwrite($fp, $body);
      fclose($fp);
    }
    return $ret;
  }
}
?>
