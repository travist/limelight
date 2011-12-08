<?php

require_once 'lib/restPHP/Resource.php';
require_once 'LimelightConfig.php';
require_once 'LimelightServer.php';

class LimelightResource extends restPHP_Resource {


  /** The title of this resource. */
  public $title = '';

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
   * Gets the properties of this resource.
   */
  public function get() {
    if ($this->id && $this->type) {
      $endpoint = $this->type . '/' . $this->id . '/properties';
      $this->update($this->server->get($endpoint, NULL, FALSE));
    }
  }

  /**
   * Create a set function which updates the parameters.
   */
  public function set() {
    if ($this->type) {
      $endpoint = $this->type;
      $endpoint .= $this->id ? ('/' . $this->id . '/properties') : '';
      $this->server->set($endpoint, $this->getObject());
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

    if ($resource = parent::getObject()) {
      return array_merge($resource, array(
        'title' => $this->title
      ));
    }
    else {
      return FALSE;
    }
  }
}
?>
