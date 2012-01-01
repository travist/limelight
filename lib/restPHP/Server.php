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

  /** The current request. */
  protected $request = null;

  /** The current response. */
  protected $response = '';

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
   * Public accessor to request.
   */
  public function request() {
    return $this->request;
  }

  /**
   * Public accessor to the response.
   */
  public function response() {
    return $this->response;
  }

  /**
   * Sets a configuration variable.
   */
  public function setConfig($param, $value) {
    if (isset($this->config[$param])) {
      if (is_array($this->config[$param]) && is_array($value)) {
        $this->config[$param] = array_merge($this->config[$param], $value);
      }
      else {
        $this->config[$param] = $value;
      }
    }
    return $this;
  }

  /**
   * Returns the request object.
   */
  protected function newRequest($url, $method) {
    $request_class = $this->config['request_class'];
    $config = isset($this->config['request']) ? $this->config['request'] : array();
    $this->request = new $request_class($url, $method, $config);
    return $this;
  }

  /**
   * Add params to the request.
   *
   * @param type $request
   * @param type $params
   */
  protected function addParams($params) {

    // Iterate through the params and add them to the request.
    if ($params) {
      foreach ($params as $key => $value) {
        $this->request->addPostParameter($key, $value);
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
  protected function addQuery($query) {

    // Iterate through our query and add them to the request url.
    if ($query) {
      foreach ($query as $key => $value) {
        $this->request->getUrl()->setQueryVariable($key, $value);
      }
    }

    return $this;
  }

  /**
   * Perform an authentication on this request.
   */
  protected function authenticate() {
    return $this;
  }

  /**
   * Decode a response to PHP based on the return type.
   *
   * @param type $response
   * @return type
   */
  protected function decode() {

    // Switch based on the format.
    switch ($this->config['format']) {

      case 'json':
        $this->response = json_decode($this->response);
        break;

      default:
        // TO-DO: IMPLEMENT OTHER FORMATS!
        break;
    }

    return $this;
  }

  /**
   * Send the request and set the response.
   */
  protected function send() {
    $this->response = $this->request->send()->getBody();
    return $this;
  }

  /**
   * Called to validate the response of the request.
   */
  protected function validate() {

    // If errors occur, then call the onError method in the request object.
    if ($errors = $this->errors()) {
      if (method_exists($this->request, 'onError')) {
        $this->request->onError($errors);
      }
    }

    return $this;
  }

  /**
   * Returns errors, if any.
   */
  public function errors() {
    if (isset($this->response->errors) && $this->response->errors) {
      return $this->response->errors;
    }
    return array();
  }

  /**
   * Debugs the current call.
   */
  protected function debug() {
    
    // If they wish to debug the call, then do so here.
    if (isset($this->config['debug']) && ($debug = $this->config['debug'])) {
      $debug(array(
        'method' => $this->request->getMethod(),
        'path' => $this->request->getUrl()->getPath(),
        'query' => $this->request->getUrl()->getQuery(),
        'params' => $this->request->getBody(),
        'errors' => $this->errors()
      ));
    }

    return $this;
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

    // Create a new request.
    $this->newRequest($url, $method)
      ->addParams($params)            /** Add Parameters. */
      ->addQuery($query)              /** Add Query */
      ->authenticate()                /** Authenticate the request */
      ->send()                        /** Send the request. */
      ->decode()                      /** Decode the response. */
      ->validate()                    /** Validate the response. */
      ->debug();                      /** Debug the call. */

    // Return the this pointer.
    return $this;
  }

  /**
   * Performs a get call.
   */
  public function get($endpoint, $query, $has_format = TRUE) {
    $query = (array)$query;
    return $this->call($endpoint, HTTP_Request2::METHOD_GET, NULL, $query, $has_format);
  }

  /**
   * Performs a put call.
   */
  public function put($endpoint, $params, $has_format = TRUE) {
    $params = (array)$params;
    return $this->call($endpoint, HTTP_Request2::METHOD_PUT, $params, NULL, $has_format);
  }

  /**
   * Performs a post call.
   */
  public function post($endpoint, $params, $has_format = TRUE) {
    $params = (array)$params;
    return $this->call($endpoint, HTTP_Request2::METHOD_POST, $params, NULL, $has_format);
  }

  /**
   * Deletes an resource.
   */
  public function delete($endpoint) {
    return $this->call($endpoint, HTTP_Request2::METHOD_DELETE, NULL, NULL, FALSE);
  }
}
?>
