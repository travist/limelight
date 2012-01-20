<?php

require_once 'lib/restPHP/Resource.php';
require_once 'LimelightServer.php';

class LimelightResource extends restPHP_Resource {


  /** The title of this resource. */
  public $title = NULL;

  /** An array of custom data. */
  public $custom_property = NULL;

  /**
   * Create the server object.
   */
  public function createServer($config = array()) {
    $this->server = new LimelightServer($config);
  }

  /**
   * Returns the default filter for creating the list of resources.
   */
  protected function getIndexDefaults() {
    return array(
      'page_id' => 0,
      'page_size' => 25,
      'sort_by' => 'update_date',
      'sort_order' => 'asc'
    );
  }

  /**
   * Parse function to parse out resources returned by list functions.
   *
   * @param type $resources
   */
  protected function parse($resources, $className) {
    // If media_list exists, use it instead.
    if (isset($resources->media_list)) {
      $resources = $resources->media_list;
    }

    // If channel_list exists, use it instead.
    if (isset($resources->channel_list)) {
      $resources = $resources->channel_list;
    }

    // Parse the resources.
    return parent::parse($resources, $className);
  }

  /**
   * Override the getParams to unset the custom_property.
   *
   * @param type $params
   */
  protected function getParams($params = array()) {
    $params = parent::getParams($params);
    unset($params['custom_property']);
    return $params;
  }

  /**
   * Returns the list of resources.
   */
  protected function getIndex($endpoint, $filter, $className) {
    if (isset($filter['published'])) {
      if (!$filter['published']) {
        $endpoint .= '/all';
        $this->server->setConfig('authenticate', TRUE);
      }
      unset($filter['published']);
    }
    $index = parent::getIndex($endpoint, $filter, $className);
    $this->server->setConfig('authenticate', FALSE);
    return $index;
  }

  /**
   * Returns the endpoint for this resource for both get and set operations.
   */
  protected function endpoint($type) {

    // Get the endpoint from the parent.
    $endpoint = parent::endpoint($type);

    // For get and set calls, we append "properties" to the endpoint.
    if ($this->id && in_array($type, array('get', 'set'))) {
      $endpoint .= '/properties';
    }

    // Return the endpoint.
    return $endpoint;
  }

  /**
   * Perform a set along with custom data.
   */
  public function set($params = array()) {

    /**
     * HACK: Limelight API has a bug where it expects a string 'true' or 'false'
     * instead of a boolean value.  Because of this, we need to iterate through
     * each of the parameters and turn it into the string representation of a
     * boolean.
     */
    foreach ($params as &$value) {
      if (is_bool($value)) {
        $value = $value ? 'true' : 'false';
      }
    }

    // Set the parent first.
    parent::set($params);

    // Now set the custom data.
    $endpoint = $this->endpoint('set') . '/custom';
    $custom = isset($params['custom']) ? $params['custom'] : array();
    $this->setProperties('custom_property', $endpoint, $custom, array(
      'deleteeach' => TRUE
    ));

    // Return the this pointer.
    return $this;
  }

  /**
   * Adds a custom property to this resource.
   */
  public function addCustom($name, $type = 'text', $default_values = array()) {
    $endpoint = $this->endpoint('set') . '/custom/' . rawurlencode($name);
    $params = array();
    $params['type'] = $type;
    if ($default_values) {
      $params['default_values'] = $default_values;
    }
    $this->server->post($endpoint, $params, FALSE);
    return $this;
  }

  /**
   * Removes a custom property of this resource.
   */
  public function deleteCustom($name) {
    $endpoint = $this->endpoint('set') . '/custom/' . rawurlencode($name);
    $this->server->delete($endpoint);
    return $this;
  }

  /**
   * Updates a custom property.
   */
  public function updateCustom($name, $new_name = '', $type = 'text', $default_values = array()) {
    $endpoint = $this->endpoint('set') . '/custom/' . rawurlencode($name);
    $params = array();
    $params['new_property_name'] = $new_name ? $new_name : $name;
    $params['type'] = $type;
    if ($default_values) {
      $params['default_values'] = $default_values;
    }
    $this->server->put($endpoint, $params, FALSE);
    return $this;
  }

  /**
   * Returns the object sent to the server.
   */
  public function getObject() {

    // If there isn't any title, then return null to skip the call.
    if (!$this->title) {
      return false;
    }

    // Return the object.
    return array(
      'title' => $this->title
    );
  }
}
?>
