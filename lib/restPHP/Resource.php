<?php

// Require the restPHP_Server class
require_once 'Server.php';

class restPHP_Resource {

  /** The ID of this resource. */
  public $id = NULL;

  /** The resource type. */
  public $type = '';

  /** The Server object. */
  public $server = NULL;

  /** A variable to keep track if this object represents the server object. */
  public $synced = FALSE;

  /** Keeps track of differences between local and server data. */
  public $diff = array();

  /** Constructor */
  function __construct($params = null) {

    // Convert the params to an array.
    $params = (array)$params;

    // Create the server.
    $this->createServer(isset($params['server']) ? $params['server'] : array());
    unset($params['server']);

    // Define an empty type.
    $this->type = $this->getType();

    // If the ID was the only parameter provided, then we can assume that
    // they wish to get the object within the construct.
    if (isset($params['id']) && (count($params) === 1)) {
      $this->id = $params['id'];
      $this->get();
    }
    else {

      // Otherwise just update the object with the params.
      $this->update($params);
    }
  }

  /**
   * Create the server object.
   */
  public function createServer($config = array()) {

    // Create the new server class.
    $this->server = new restPHP_Server($config);
  }

  /**
   * Return the type.
   *
   * Derived classes should define a type here.
   */
  public function getType() {
    return '';
  }

  /**
   * Returns a filter with provided defaults.
   *
   * @return The correct filter based on defaults and provided values.
   */
  protected function getFilter($filter, $defaults) {
    return $filter ? array_merge($defaults, $filter) : $defaults;
  }

  /**
   * Returns the default index filter.
   */
  protected function getIndexDefaults() {
    return array();
  }

  /**
   * Parse function to parse out resources returned by list functions.
   *
   * @param type $resources
   */
  protected function parse($resources, $className) {

    // Check to make sure there are resources.
    if ($resources) {

      // Now iterate through all the channels.
      foreach ($resources as &$resource) {

        // Convert it to a resource object.
        $resource = new $className($resource);
      }
    }

    // Return the resource array.
    return $resources;
  }

  /**
   * Returns the list of resources.
   */
  protected function getIndex($endpoint, $filter, $className) {

    // Get the resources from the server.
    if ($resources = $this->server->get($endpoint, $filter)) {

      // Now return the parsed resources.
      return $this->parse($resources, $className);
    }
    else {

      // Return FALSE that the request failed.
      return FALSE;
    }
  }

  /**
   * A public static accessor to return an index of resources.
   * Requires PHP 5.3.
   *
   * @param type $filter
   * @return type
   */
  public static function index($filter = array(), $params = array()) {
    $class = get_called_class();
    $resource = new $class($params);
    return $resource->__index($filter);
  }

  /**
   * Returns a list of self() objects.
   */
  protected function __index($filter = array()) {

    // You must have an resource type to continue.
    if (!$this->type) {
      return FALSE;
    }

    // Get a filter with provided defaults.
    $filter = $this->getFilter($filter, $this->getIndexDefaults());

    // Return an index list.
    return $this->getIndex($this->endpoint(), $filter, get_class($this));
  }

  /**
   * Returns the endpoint for this resource to perform both get and set
   * operations.
   */
  protected function endpoint() {
    $endpoint = $this->type;
    $endpoint .= $this->id ? ('/' . $this->id) : '';
    return $endpoint;
  }

  /**
   * Gets the properties of this resource.
   */
  public function get() {
    if ($this->type && $this->id) {
      $params = $this->server->get($this->endpoint(), NULL, FALSE);
      $error = isset($params->errors) && $params->errors;
      if ($params && !$error) {

        // If this is a valid response and there is an ID, update.
        $this->update($params);

        // Set that this object is synced.
        $this->synced = TRUE;
      }
      else {

        // Set the ID to NULL so that set will create new...
        $this->id = NULL;
      }
    }
    return $this;
  }

  /**
   * Return the filtered object.
   *
   * @return type
   */
  public function getFilteredObject() {
    $obj = $this->getObject();
    return array_filter($obj, create_function('$x', 'return $x !== NULL;'));
  }

  /**
   * Generic function to set this complete object or properties within this object.
   *
   * Examples:
   *
   *   // To create a new object, which won't have an ID set.
   *   $resource->set();
   *
   *   // To update an existing object, which would have an ID set.
   *   $resource->set();
   *
   *   // To set a specific parameters within a resource.
   *   $resource->set(array('param' => 'value'));
   */
  public function set($params = array()) {
    if ($this->type) {
      $params = $params ? $params : $this->getFilteredObject();
      $method = $this->id ? 'put' : 'post';
      $response = $this->server->{$method}($this->endpoint(), $params);
      $this->update($response);
    }
    return $this;
  }

  /**
   * Delete's an resource.
   */
  public function delete() {
    if ($this->type && $this->id) {
      $this->server->delete($this->endpoint());
    }
    return $this;
  }

  /**
   * Updates the data model based on the response.
   */
  public function update($params = array()) {

    // If the params are set then update the data model.
    if ($params) {

      // Set the sync flag to false.
      $this->synced = FALSE;

      // Convert this to an array.
      $params = (array)$params;

      // Iterate through each of the parameters.
      foreach ($params as $key => $value) {

        // Check to see if this parameter exists.
        if (property_exists($this, $key)) {

          // Update the data model.
          $this->{$key} = $value;
        }
      }
    }

    return $this;
  }

  /**
   * Returns the object sent to the server.
   */
  public function getObject() {

    // Never need to return the id since it is implied in the REST path.
    return array();
  }
}
?>
