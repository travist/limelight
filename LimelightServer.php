<?php

require_once 'lib/restPHP/Server.php';
require_once 'LimelightCachedRequest.php';
require_once 'LimelightConfig.php';

/**
 * This is the LimelightServer class.
 */
class LimelightServer extends restPHP_Server {

  public $limelight_params = array();

  /**
   * Constructor
   */
  function __construct($config = array()) {

    // Make sure the request adaptor is Curl.
    if (!isset($config['request'])) {
      $config['request'] = array();
    }
    $config['request']['adapter'] = 'HTTP_Request2_Adapter_Curl';

    // Call the constructor.
    parent::__construct($config);

    // Override the config based on the Limelight configuration.
    $this->config = array_merge($this->config, LimelightConfig::getConfig());

    // Add the organization ID to the base_url.
    $this->config['base_url'] .= ('/' . $this->config['organization_id']);
  }

  protected function send() {

    /**
     * Limelight has bug where they ignore all PUT methods because,
     * they expect the command to be CURLOPT_CUSTOMREQUEST, whereas PEAR's
     * HTTP_Request2 uses CURLOPT_UPLOAD.  They seem to be ignoring this type
     * which is causing all the PUT commands to be ignored when using the
     * HTTP_Request2 libraries.  We will do this hack for the time being until
     * they fix it.
     */
    if ($this->request->getMethod() == HTTP_Request2::METHOD_PUT) {
      $session = curl_init($this->request->getUrl()->getUrl());
      curl_setopt($session, CURLOPT_CUSTOMREQUEST, "PUT");
      curl_setopt($session, CURLOPT_HEADER, false);
      curl_setopt($session, CURLOPT_POSTFIELDS, $this->limelight_params);
      curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
      $this->response = curl_exec($session);
      curl_close($session);
      return $this;
    }
    else {
      return parent::send();
    }
  }

  /**
   * Add params to the request.
   *
   * @param type $params
   */
  protected function addParams($params) {

    // Save these for later.
    $this->limelight_params = $params;

    // Iterate through the params and add them to the request.
    if ($params) {
      foreach ($params as $key => $value) {
        if ($key == 'media_file') {
          $this->request->addUpload($key, $value);
        }
        else {
          $this->request->addPostParameter($key, $value);
        }
      }
    }

    return $this;
  }

  /**
   * Perform an authentication on this request.
   */
  protected function authenticate() {

    // Only authenticate under certain conditions.
    if ($this->config['authenticate'] && $this->config['access_key'] && $this->config['secret']) {
      $parsed_url = parse_url($this->request->getUrl()->getUrl());
      $str_to_sign = strtolower($this->request->getMethod() . '|' . $parsed_url['host'] . '|' . $parsed_url['path']) . '|';

      // Get the query variables, and make sure the required ones are set for authentication.
      $params = $this->request->getUrl()->getQueryVariables();
      if (!isset($params['access_key'])) {
        $params['access_key'] = $this->config['access_key'];
        $this->request->getUrl()->setQueryVariable('access_key', $params['access_key']);
      }
      if (!isset($params['expires'])) {
        $params['expires'] = time() + 300;
        $this->request->getUrl()->setQueryVariable('expires', $params['expires']);
      }

      // Sort them in alphabetical order.
      $keys = array_keys($params);
      sort($keys);

      // Iterate through the keys.
      foreach ($keys as $key) {
        $str_to_sign .= $key . '=' .$params[$key] . '&';
      }

      // Remove the last & from the path.
      $str_to_sign = rtrim($str_to_sign,'&');
      $signature = base64_encode(hash_hmac('sha256', $str_to_sign, $this->config['secret'], true));
      $this->request->getUrl()->setQueryVariable('signature', $signature);
    }

    return $this;
  }

  /**
   * Only send debug statemenets when it isn't cached.
   */
  protected function debug() {

    // If this was a cache miss, then we wish to debug... otherwise skip it.
    if (isset($this->request->cache_state) && !$this->request->cache_state) {
      return parent::debug();
    }

    return $this;
  }

  /**
   * Performs a put call.
   */
  public function put($endpoint, $params, $has_format = TRUE) {
    $this->config['authenticate'] = TRUE;
    $ret = parent::put($endpoint, $params, $has_format);
    $this->config['authenticate'] = FALSE;
    return $ret;
  }

  /**
   * Performs a post call.
   */
  public function post($endpoint, $params, $has_format = TRUE) {
    $this->config['authenticate'] = TRUE;
    $ret = parent::post($endpoint, $params, $has_format);
    $this->config['authenticate'] = FALSE;
    return $ret;
  }

  /**
   * Deletes an resource.
   */
  public function delete($endpoint) {
    $this->config['authenticate'] = TRUE;
    $ret = parent::delete($endpoint);
    $this->config['authenticate'] = FALSE;
    return $ret;
  }
}
?>
