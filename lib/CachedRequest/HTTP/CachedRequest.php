<?php

// Include the HTTP_Request2 class.
include_once 'HTTP/Request2.php';
if (!class_exists('HTTP_Request2', FALSE)) {
  // Add the included pear library in the include path.
  ini_set('include_path',ini_get('include_path').':' . dirname(__FILE__) . '/PEAR:');
  include_once 'HTTP/Request2.php';
}

require_once 'CachedResponse.php';

class HTTP_CachedRequest extends HTTP_Request2 {

  // The name of the cache.
  protected $cache_name = '';

  // By default, this simply implements a static cache.  Other classes can
  // derive from this class to implement more persistent caching like File,
  // APC, or MemCache.
  protected $static_cache = array();

  // 0 for miss, 1 for cache hit.
  public $cache_state = 0;

  // The constructor.
  function __construct($url = null, $method = self::METHOD_GET, array $config = array()) {

    // Add the configuration for this class to the configuration. Default the timeout to 1hr.
    $this->config = array_merge($this->config, array(
      'cache' => TRUE,        /** If we should cache. */
      'force_cache' => FALSE, /** If we should force the cache. */
      'cache_seed' => '',     /** A seed to generate the cache name. */
      'cache_timeout' => 3600 /** The amount of time to wait to invalidate the cache. */
    ));

    // Call the parent constructor.
    parent::__construct($url, $method, $config);
  }

  /**
   * Returns the name of the cache.
   */
  public function get_cache_name() {
    return md5($this->url->getURL() . $this->config['cache_seed']);
  }

  /**
   * Sets the cache name.
   *
   * Derived classes can implement this hook when the cache name is set and before
   * certain checks are made.
   */
  public function set_cache_name($cache_name) {
    $this->cache_name = $cache_name;
  }

  /**
   * Return if we should cache.
   */
  public function should_cache() {
    return (($this->method == HTTP_Request2::METHOD_GET) && $this->config['cache']) || $this->config['force_cache'];
  }

  /**
   * Returns if the cache exists.
   */
  public function cache_exists() {
    return isset($this->static_cache[$this->cache_name]);
  }

  /**
   * Return if this cache is valid.
   */
  public function cache_valid() {
    return $this->cache_exists();
  }

  /**
   * Cache the response.
   */
  public function cache_response($body) {
    $this->static_cache[$this->cache_name] = $body;
  }

  /**
   * Called to clear the cache for this cache_name.
   */
  public function cache_clear() {
    unset($this->static_cache[$this->cache_name]);
  }

  /**
   * Called when an error occurs.
   */
  public function onError() {
    // We need to clear the cache if the last request was an error.
    $this->cache_clear();
  }

  /**
   * Returns the cache.
   */
  public function get_cache() {
    return $this->static_cache[$this->cache_name];
  }

  // Override the send function to only send if the cache exists and is valid.
  public function send() {

    // Sets the cache name.
    $this->set_cache_name($this->get_cache_name());

    // Only send if we shouldn't cache or if the cache is invalid.
    $response = null;
    if (!$this->should_cache() || !$this->cache_valid()) {
      $this->cache_state = 0;
      $response = parent::send();
    }
    else {
      $this->cache_state = 1;
    }

    // Return our cached response.
    return new HTTP_CachedResponse($this, $response);
  }
}
?>
