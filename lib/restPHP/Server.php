<?php

/**
 * Defines a common RESTAPI class that can be easily derived and modified, and
 * provides an easy RESTful interface to web services.
 */

// Require the HTTP_Request2 class.
require_once 'HTTP/Request2.php';

class restPHP_Server {

  // The configuration for this API.
  protected $config = array();

  /** Constructor */
  function __construct($config = array()) {

    // Make sure the defaults are set.
    $this->config = array_merge($config, array(
      'base_url' => '',
      'format' => 'json',
      'request_class' => 'HTTP_Request2'
    ));
  }

  /**
   * Sets a configuration variable.
   */
  public function setConfig($param, $value) {
    if (isset($this->config[$param])) {
      $this->config[$param] = $value;
    }
    return $this;
  }

  /**
   * Returns the request object.
   */
  protected function getRequest($url, $method) {
    $request_class = $this->config['request_class'];
    $config = isset($this->config['request']) ? $this->config['request'] : array();
    return new $request_class($url, $method, $config);
  }

  /**
   * Add params to the request.
   *
   * @param type $request
   * @param type $params
   */
  protected function addParams(&$request, $params) {

    // Iterate through the params and add them to the request.
    if ($params) {
      foreach ($params as $key => $value) {
        $request->addPostParameter($key, $value);
      }
    }

    return $this;
  }

  /**
   * Adds query params to the request.
   *
   * @param type $request
   * @param type $query
   */
  protected function addQuery(&$request, $query) {

    // Iterate through our query and add them to the request url.
    if ($query) {
      foreach ($query as $key => $value) {
        $request->getUrl()->setQueryVariable($key, $value);
      }
    }

    return $this;
  }

  /**
   * Perform an authentication on this request.
   */
  protected function authenticate(&$request) {
    return $this;
  }

  /**
   * Get a response from a request.
   *
   * @param type $request
   */
  protected function getResponse($request) {
    return $request->send()->getBody();
  }

  /**
   * Decode a response to PHP based on the return type.
   *
   * @param type $response
   * @return type
   */
  protected function decode($response) {

    // Switch based on the format.
    switch ($this->config['format']) {

      case 'json':
        return json_decode($response);
        break;

      default:
        // TO-DO: IMPLEMENT OTHER FORMATS!
        break;
    }
  }

  /**
   * Performs a server call.
   */
  public function call($endpoint, $method = HTTP_Request2::METHOD_GET, $params = array(), $query = array(), $has_format = TRUE) {

    // Normalize the params.
    $params = (array)$params;
    $query = (array)$query;

    // Create the URL defined by the endpoint.
    $format = $has_format ? ('.' . $this->config['format']) : '';
    $url = $this->config['base_url'] . '/' . $endpoint . $format;

    // Create the request object.
    $request = $this->getRequest($url, $method);

    // Add parameters, add the query, and authenticate.
    $this->addParams($request, $params)->addQuery($request, $query)->authenticate($request);

    // Return the decoded response.
    return $this->decode($this->getResponse($request));
  }

  /**
   * Performs a get call.
   */
  public function get($endpoint, $query) {
    $query = (array)$query;
    return $this->call($endpoint, HTTP_Request2::METHOD_GET, NULL, $query);
  }

  /**
   * Performs a put call.
   */
  public function put($endpoint, $params) {
    $params = (array)$params;
    return $this->call($endpoint, HTTP_Request2::METHOD_PUT, $params, NULL);
  }

  /**
   * Performs a post call.
   */
  public function post($endpoint, $params) {
    $params = (array)$params;
    return $this->call($endpoint, HTTP_Request2::METHOD_POST, $params, NULL);
  }

  /**
   * Deletes an resource.
   */
  public function delete($endpoint) {
    return $this->call($endpoint, HTTP_Request2::METHOD_DELETE, NULL, NULL);
  }

  /**
   * Performs a set call, which is POST without an ID, and PUT with an ID.
   */
  public function set($endpoint, $params) {
    if ($params) {
      $params = (array)$params;
      if (isset($params['id']) && $params['id']) {
        return $this->put($endpoint, $params);
      }
      else {
        return $this->post($endpoint, $params);
      }
    }
    else {
      return FALSE;
    }
  }
}
?>
