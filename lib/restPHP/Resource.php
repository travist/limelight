<?php

// Require the restPHP_Server class
require_once 'Server.php';

class restPHP_Resource {

  /** The ID of this resource. */
  public $id = '';

  /** The resource type. */
  public $type = '';

  /** The Server object. */
  public $server = null;

  /** Constructor */
  function __construct($params = null) {

    // Convert the params to an array.
    $params = (array)$params;

    // Create the server.
    $this->createServer();

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
  public function createServer() {

    // Create the new server class.
    $this->server = new restPHP_Server();
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

    // Now iterate through all the channels.
    foreach ($resources as &$resource) {

      // Convert it to a resource object.
      $resource = new $className($resource);
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
   * Returns a list of self() objects.
   */
  public function index($filter = array()) {

    // You must have an resource type to continue.
    if (!$this->type) {
      return FALSE;
    }

    // Get a filter with provided defaults.
    $filter = $this->getFilter($filter, $this->getIndexDefaults());

    // Get the endpoint.
    $endpoint = $this->type;

    // Return an index list.
    return $this->getIndex($endpoint, $filter, get_class($this));
  }

  /**
   * Gets the properties of this resource.
   */
  public function get() {
    if ($this->type && $this->id) {
      $endpoint = $this->type . '/' . $this->id;
      $this->update($this->server->get($endpoint, NULL, FALSE));
    }
  }

  /**
   * Sets the object.
   */
  public function set() {
    if ($this->type) {
      $endpoint = $this->type;
      $endpoint .= $this->id ? ('/' . $this->id) : '';
      $this->server->set($endpoint, $this->getObject());
    }
  }

  /**
   * Delete's an resource.
   */
  public function delete() {
    if ($this->type && $this->id) {
      $endpoint = $this->type . '/' . $this->id;
      $this->server->delete($endpoint);

    }
  }

  /**
   * Updates the data model based on the response.
   */
  public function update($params) {

    // If the params are set then update the data model.
    if ($params) {

      // Convert this to an array.
      $params = (array)$params;

      // Iterate through each of the parameters.
      foreach ($params as $key => $value) {

        // Check to see if this parameter exists.
        if (isset($this->{$key})) {

          // Update the data model.
          $this->{$key} = $value;
        }
      }
    }
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
