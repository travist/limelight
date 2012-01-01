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
   * Get the filtered object, which for limelight, always requires the title.
   *
   * @param type $params
   */
  protected function getFilteredObject($params = array()) {
    $params = parent::getFilteredObject($params);
    return $params;
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

    // See if there is custom data to be set.
    if (isset($this->diff['custom_property'])) {

      // Set the custom data.
      $endpoint = $this->endpoint('set');
      $endpoint .= '/custom';
      $this->server->setConfig('authenticate', TRUE);
      $this->server->put($endpoint, $this->diff['custom_property']);
      $this->server->setConfig('authenticate', FALSE);

      /**
       * As long as no errors occured, we can assume the call was a success.
       * Update the custom properties since there isn't any return.
       */
      if (!$this->errors()) {
        foreach ($this->diff['custom_property'] as $key => $value) {
          if (!isset($this->custom_property)) {
            $this->custom_property = new stdClass();
          }
          $this->custom_property->{$key} = $value;
        }
      }
    }

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
