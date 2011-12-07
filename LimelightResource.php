<?php

require_once 'lib/restPHP/Resource.php';
require_once 'LimelightConfig.php';
require_once 'LimelightServer.php';

class LimelightResource extends restPHP_Resource {


  /** The title of this entity. */
  public $title = '';

  /**
   * Create the server object.
   */
  public function createServer() {
    $this->server = new LimelightServer();
  }

  /**
   * Returns the default filter for creating the list of entities.
   */
  protected function getDefaultListFilter() {
    return array(
      'page_id' => 0,
      'page_size' => 25,
      'sort_by' => 'update_date',
      'sort_order' => 'asc'
    );
  }

  /**
   * Returns a list of self() objects.
   */
  public function getList($filter = array(), $param = '') {

    // Make sure we authenticate the "all" list.
    if (isset($filter['published'])) {
      if (!$filter['published']) {
        $param = 'all';
        $this->server->setConfig('authenticate', TRUE);
      }
      unset($filter['published']);
    }
    $list = parent::getList($filter, $param);
    $this->server->setConfig('authenticate', FALSE);
    return $list;
  }

  /**
   * Gets the properties of this entity.
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

    if ($entity = parent::getObject()) {
      return array_merge($entity, array(
        'title' => $this->title
      ));
    }
    else {
      return FALSE;
    }
  }
}
?>
