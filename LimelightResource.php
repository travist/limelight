<?php

require_once 'lib/restPHP/Resource.php';
require_once 'LimelightConfig.php';
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

    // Set the parent first.
    parent::set($params);

    // Now set the custom data.
    $custom = isset($params['custom']) ? $params['custom'] : array();
    $this->setCustom($custom);

    return $this;
  }

  /**
   * Sets custom data within the Limelight Resource.
   *
   * @param type $params
   */
  public function setCustom($params = array()) {

    if ($this->id) {

      // See if there is custom data to be set.
      $set = array();
      $delete = array();
      $custom_diff = isset($this->diff['custom_property']) ? $this->diff['custom_property'] : $params;

      // If a property is set in the diff, but not set or different in the object, then set the value.
      if ($custom_diff) {
        foreach ($custom_diff as $key => $value) {
          if ($value && (!isset($this->custom_property[$key]) || ($this->custom_property[$key] != $value))) {
            $set[$key] = $value;
          }
        }
      }

      // If a property is set in the object, but not set in the diff, then delete.
      if ($this->custom_property) {
        foreach ($this->custom_property as $key => $value) {
          if (!isset($custom_diff[$key]) || !$custom_diff[$key]) {
            $delete[] = $key;
          }
        }
      }

      // Set the custom endpoint.
      $endpoint = $this->endpoint('set');
      $endpoint .= '/custom';

      if ($set) {
        // Set the custom data.
        $this->server->put($endpoint, $set);
        if (!$this->errors()) {
          foreach ($set as $key => $value) {
            if (!isset($this->custom_property)) {
              $this->custom_property = array();
            }
            $this->custom_property[$key] = $value;
          }
        }
      }

      if ($delete) {
        // To delete a value, we have to do them one-by-one.
        foreach ($delete as $property) {
          $this->server->delete($endpoint . '/' . rawurlencode($property));
          if (!$this->errors()) {
            unset($this->custom_property[$property]);
          }
        }
      }
    }
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
