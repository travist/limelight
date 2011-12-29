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

  /** Keeps track of differences between local and server data. */
  public $diff = array();

  /** Keep track of the response from the server. */
  public $response = '';

  /** Constructor */
  function __construct($params = null, $sync = TRUE) {

    // Convert the params to an array.
    $params = (array)$params;

    // Create the server.
    $this->createServer(isset($params['server']) ? $params['server'] : array());
    unset($params['server']);

    // Define an empty type.
    $this->type = $this->getType();

    // Update the object.
    $this->update($params);

    // If the ID was provided, then we also need to get the object from the
    // server to determine differences between what was provided in the
    // constructor vs. what data is on the server.
    if ($sync && isset($params['id'])) {
      $this->get();
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
   * Returns a query with provided defaults.
   *
   * @return The correct query based on defaults and provided values.
   */
  protected function getQuery($query, $defaults) {
    return $query ? array_merge($defaults, $query) : $defaults;
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
        $resource = new $className($resource, FALSE);
      }
    }

    // Return the resource array.
    return $resources;
  }

  /**
   * Returns the list of resources.
   */
  protected function getIndex($endpoint, $query, $className) {

    // Get the resources from the server.
    if ($resources = $this->server->get($endpoint, $query)) {

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
   * @param type $query
   * @return type
   */
  public static function index($query = array(), $params = array()) {
    $class = get_called_class();
    $resource = new $class($params, FALSE);
    return $resource->__index($query);
  }

  /**
   * Returns a list of self() objects.
   */
  protected function __index($query = array()) {

    // You must have an resource type to continue.
    if (!$this->type) {
      return FALSE;
    }

    // Get a query with provided defaults.
    $query = $this->getQuery($query, $this->getIndexDefaults());

    // Return an index list.
    return $this->getIndex($this->endpoint('index'), $query, get_class($this));
  }

  /**
   * Returns the endpoint for this resource to perform both get and set
   * operations.
   */
  protected function endpoint($type) {
    $endpoint = $this->type;
    $endpoint .= $this->id ? ('/' . $this->id) : '';
    return $endpoint;
  }

  /**
   * Gets the properties of this resource.
   */
  public function get() {

    // Only get if there is a type and an id.
    if ($this->type && $this->id) {

      // Get the response from the server.
      $this->response = $this->server->get($this->endpoint('get'), NULL, FALSE);

      // Check to make sure the response is valid.
      if ($this->response && !$this->getLastErrors()) {

        // If this is a valid response and there is an ID, update.
        $this->update($this->response);
      }
      else {

        // Set the ID to NULL so that set will create new...
        $this->id = NULL;
      }
    }

    // Allow chaining.
    return $this;
  }

  /**
   * Return the filtered object that will be sent to the server.  If a diff or
   * params are provided, then it will only send the values that exist within
   * the diff or params that also exist in the data model. Wheras if there is
   * no diff or params, then it will return only the parameters with a valid
   * value.
   *
   * @return type
   */
  protected function getFilteredObject($params = array()) {

    // Get the filtered object based on what values are actually set.
    $obj = $this->getObject();
    $obj = array_filter($obj, create_function('$x', 'return $x !== NULL;'));

    // If there is an ID, then we want the params to only be what is different
    // or what they provided within the params argument.
    if ($this->id) {

      // Use either what they provided, or the diff.
      $params = $params ? $params : $this->diff;
      $params = $params ? array_intersect_key($params, $obj) : array();
    }
    else {

      // Return all the values that are valid within this data model for insert.
      $params = $obj;
    }

    // Return the parameters.
    return $params;
  }

  /**
   * Returns the errors (if any) of the last request made.
   *
   * @return array An array of errors that occured.  FALSE if no errors.
   */
  public function getLastErrors() {
    if (isset($response->errors) && $response->errors) {
      return $response->errors;
    }
    else {
      return FALSE;
    }
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

    // Make sure that we check the type.
    if ($this->type) {

      // Only update if the filtered parameters returns something to update.
      if ($params = $this->getFilteredObject($params)) {

        // If the resource has an ID, then we PUT otherwise POST.
        $method = $this->id ? 'put' : 'post';

        // Make the call to the server.
        $this->response = $this->server->{$method}($this->endpoint('set'), $params);

        // Check to make sure the response is valid.
        if ($this->response && !$this->getLastErrors()) {

          // Update the resource if the response is valid.
          $this->update($this->response);
        }
      }
    }

    // Allow chaining.
    return $this;
  }

  /**
   * Delete's an resource.
   */
  public function delete() {

    // Check to make sure we have a type and an id.
    if ($this->type && $this->id) {

      // Make a delete call to the server.
      $this->server->delete($this->endpoint('delete'));
    }
    return $this;
  }

  /**
   * Updates the data model based on the response.
   */
  public function update($params = array()) {

    // If the params are set then update the data model.
    if ($params) {

      // Convert this to an array.
      $params = (array)$params;

      // Iterate through each of the parameters.
      foreach ($params as $key => $value) {

        // Check to see if this parameter exists.
        if (property_exists($this, $key)) {

          // If the current value is already set, and the value from this update
          // is different from the value that is already in this object, then
          // we need to keep track of this difference so that when we push an
          // update, we don't send everything.
          if (isset($this->{$key}) && ($this->{$key} != $value)) {

            // Store this value in the diff array.
            $this->diff[$key] = $this->{$key};
          }

          // Update the data model with this value.
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
