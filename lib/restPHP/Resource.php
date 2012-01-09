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

  /** Constructor */
  function __construct($params = array(), $sync = TRUE) {

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
   * Add a method to return if any errors occured on last update.
   */
  public function errors() {
    return $this->server->errors();
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
   * Function to get the response from the server.
   */
  protected function getResponse() {

    // Return the response only if there were no errors and if the response exists.
    if (($resp = $this->server->response()) && !$this->errors()) {
      return $resp;
    }

    return '';
  }

  /**
   * Returns the list of resources.
   */
  protected function getIndex($endpoint, $query, $className) {

    // Get the resources from the server.
    $this->server->get($endpoint, $query);

    // Get the resources from the response.
    if ($resources = $this->getResponse()) {

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
  public function index($query = array()) {

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

      // Get the object from the server.
      $this->server->get($this->endpoint('get'), NULL, FALSE);

      // Get the response from the server.
      if ($resp = $this->getResponse()) {
        $this->update($resp);
      }
      else {

        // Set to NULL since this object doesn't exist.
        $this->id = NULL;
      }
    }

    // Allow chaining.
    return $this;
  }

  /**
   * Return the filtered object of only valid values.
   */
  protected function getFilteredObject($params = array()) {

    // Return a filtered object.
    return array_filter($this->getObject(), create_function('$x', 'return $x !== NULL;'));
  }

  /**
   * Return the params that will be sent to the server.  If a diff or
   * params are provided, then it will only send the values that exist within
   * the diff or params that also exist in the data model. Wheras if there is
   * no diff or params, then it will return only the parameters with a valid
   * value.
   */
  protected function getParams($params = array()) {

    // Check for an ID.
    if ($this->id) {

      // If they provide params, then we want to return the
      // intersection between those params and the filtered object.
      if ($params) {
        $obj = $this->getFilteredObject();
        return array_intersect_key($params, $obj);
      }

      // Return the diff.
      return $this->diff;
    }
    else {

      // Return the filtered object.
      return $this->getFilteredObject();
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
      if ($params = $this->getParams($params)) {

        // If the resource has an ID, then we PUT otherwise POST.
        $method = $this->id ? 'put' : 'post';

        // Set the server object.
        $this->server->{$method}($this->endpoint('set'), $params);

        // Update the data model based on the response.
        $this->update($this->getResponse());
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
   * Determine if the value of a parameter is different or not.
   */
  protected function setDiff($param, $value) {
    // If the current value is already set, and the value from this update
    // is different from the value that is already in this object, then
    // we need to keep track of this difference so that when we push an
    // update, we don't send everything.
    if (isset($this->{$param}) && ($this->{$param} != $value)) {

      // Store this value in the diff array.
      $this->diff[$param] = $this->{$param};
    }
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

          // Normalize the value.
          $value = is_object($value) ? (array)$value : $value;

          // Set the difference of this parameter.
          $this->setDiff($key, $value);

          // Update the data model with this value.
          $this->{$key} = $value;
        }
      }
    }

    return $this;
  }

  /**
   * This method allows you to set a property of your class which has its own
   * endpoint to set or delete the values of that property.  This is useful if
   * you have a property in this class which is an array of key/value pairs;
   * each of which can have their own endpoint to set or delete.
   *
   * @param string $param The name of the parameter in this object.
   * @param string $endpoint The endpoint for this parameter.
   * @param array $values An array of key/value pairs for this parameter.
   * @param array $options An array of the following options.
   *    - seteach: Make a service call to set each key|value pair.
   *    - deleteeach: Make a service call to delete each key|value pair.
   */
  public function setProperties($param, $endpoint, $values = array(), $options = array()) {
    if ($this->id) {

      $set = array();
      $delete = array();
      $values = isset($this->diff[$param]) ? $this->diff[$param] : $values;

      // If a property is set in the values, but not set or different in
      // the object, then we will want to set the value.
      if ($values) {
        foreach ($values as $key => $value) {
          if ($value) {
            if (is_string($key)) {
              if(!isset($this->{$param}[$key]) || ($this->{$param}[$key] != $value)) {
                $set[$key] = $value;
              }
            }
            else if (!in_array($value, $this->{$param})) {
              $set[$key] = $value;
            }
          }
        }

        // Now set the values.
        $each = isset($options['seteach']) ? $options['seteach'] : FALSE;
        $this->setProperty($param, $endpoint, $set, 'put', $each);
      }

      // If a property is set in the object, but is set but not valid in the values, then delete.
      if ($this->{$param}) {
        foreach ($this->{$param} as $key => $value) {
          if (isset($values[$key])) {
            if (is_string($key)) {
              if(!$values[$key]) {
                $delete[$key] = $value;
              }
            }
            else if (!in_array($value, $values)) {
              $delete[$key] = $value;
            }
          }
        }

        // Now delete the values.
        $each = isset($options['deleteeach']) ? $options['deleteeach'] : FALSE;
        $this->setProperty($param, $endpoint, $delete, 'delete', $each);
      }
    }
  }

  /**
   * Sets a property.
   *
   * @param string $param The name of the parameter in this object.
   * @param string $endpoint The endpoint for this parameter.
   * @param array $values An array of key/value pairs for this parameter.
   * @param string $type The type of operation to perform 'set' or 'delete'.
   * @param boolean $each Whether to set each key|value pair or not.
   */
  protected function setProperty($param, $endpoint, $values, $type, $each) {
    if ($values) {
      if (!$each) {
        $this->server->{$type}($endpoint, $values);
        $errors = $this->errors();
      }
      foreach ($values as $key => $value) {
        if ($each) {
          $property = is_string($key) ? $key : $value;
          $this->server->{$type}($endpoint . '/' . rawurlencode($property), array(), FALSE);
          $errors = $this->errors();
        }
        if (!$errors) {
          if ($type == 'put') {
            if (!isset($this->{$param})) {
              $this->{$param} = array();
            }
            $this->{$param}[$key] = $value;
          }
          else {
            unset($this->{$param}[$key]);
          }
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
