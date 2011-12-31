<?php

// Require the LimelightResource class.
require_once 'LimelightResource.php';
require_once 'LimelightChannel.php';

// Define the media qualities.
define('LIMELIGHT_QUALITY_LOW', 'low');
define('LIMELIGHT_QUALITY_MEDIUM', 'medium');
define('LIMELIGHT_QUALITY_HIGH', 'high');
define('LIMELIGHT_QUALITY_HD', 'hd');

class LimelightMedia extends LimelightResource {

  /** The media file. */
  public $media_file = NULL;

  /** The description of the media. */
  public $description = NULL;

  /** The media type of this media. */
  public $media_type = NULL;

  /** The original filename. */
  public $original_filename = NULL;

  /** The state of this media. */
  public $state = NULL;

  /** The duration in milliseconds. */
  public $duration_in_milliseconds = NULL;

  /** The total storage in bytes. */
  public $total_storage_in_bytes = NULL;

  /** The category of this media. */
  public $category = NULL;

  /** The reference id. */
  public $ref_id = NULL;

  /** The restriction rule ID. */
  public $restrictionrule_id = NULL;

  /** The thumbnails for thie media. */
  public $thumbnails = NULL;

  /** The tags associated with this media. */
  public $tags = NULL;

  /** The scheduled start date. */
  public $sched_start_date = NULL;

  /** The scheduled end date. */
  public $sched_end_date = NULL;

  /** The date the media was published. */
  public $publish_date = NULL;

  /** The date the media was upated. */
  public $update_date = NULL;

  /** The date the media was created. */
  public $create_date = NULL;

  /**
   * Override the update method to provide custom ID's.
   *
   * @param type $params
   * @return LimelightMedia
   */
  public function update($params = array()) {
    $params = (array)$params;
    parent::update($params);
    if (isset($params['media_id'])) {
      $this->id = $this->id ? $this->id : $params['media_id'];
    }
    return $this;
  }

  /**
   * Returns the endpoint for this resource.
   */
  public function getType() {
    return 'media';
  }

  /**
   * Create a search index.
   *
   * This function allows you to search media on the Limelight network.
   */
  public static function search($query = array(), $operator = 'AND', $params = array()) {

    // Build the query string for the search.
    $query_string = array();
    foreach ($query as $key => $value) {
      $query_string[] = strtolower($key) . ':' . strtolower($value);
    }

    // We need to rewrite the query based on the operator.
    $search_query = array(strtolower($operator) => implode(';', $query_string));

    // Now get the index with this query.
    $class = get_called_class();
    return $class::index($search_query, $params);
  }

  /**
   * All index queries for media require authentication.
   *
   * @param type $query
   * @return type
   */
  protected function __index($query = array()) {
    $this->server->setConfig('authenticate', TRUE);
    $ret = parent::__index($query);
    $this->server->setConfig('authenticate', FALSE);
    return $ret;
  }

  /**
   * Returns the endpoint for this resource for both get and set operations.
   */
  protected function endpoint($type) {
    $endpoint = $this->type;
    if ($type == 'index') {
      $endpoint .= ($type == 'index') ? ('/search') : '';
    }
    else {
      $endpoint .= $this->id ? ('/' . $this->id . '/properties') : '';
    }
    return $endpoint;
  }

  /**
   * Returns the download URL for this media.
   */
  public function url($quality = LIMELIGHT_QUALITY_LOW) {
    $url = '';
    if ($this->type && $this->id) {

      // Set the server configurations to cache the request, as well
      // as authenticate the request.
      $this->server->setConfig('authenticate', TRUE);
      $this->server->setConfig('request', array(
        'force_cache' => TRUE,
        'cache_seed' => $quality,
        'cache_timeout' => 900, /* 15 minute invalidation */
      ));

      // Get the endpoint, and make the request.
      $endpoint = $this->type . '/' . $this->id . '/download_url';
      $params = array('quality' => $quality);
      if (!$this->server->call($endpoint, HTTP_Request2::METHOD_POST, $params, NULL, TRUE)->errors()) {
        $url = $this->server->response();
      }

      // Reset the server configurations.
      $this->server->setConfig('authenticate', FALSE);
      $this->server->setConfig('request', array(
        'force_cache' => FALSE,
        'cache_seed' => '',
        'cache_timeout' => 3600,
      ));
    }
    return $url;
  }

  /**
   * Returns all the channels that this media belongs too.
   */
  public function getChannels($query = array()) {
    // Return a typelist of channels.
    $this->server->setConfig('authenticate', TRUE);
    $query = $this->getQuery($query, array(
      'page_id' => 0,
      'page_size' => 25,
      'sort_by' => 'update_date',
      'sort_order' => 'asc'
    ));
    $endpoint = $this->type . '/' . $this->id . '/channels';
    $list = $this->getIndex($endpoint, $query, 'LimelightChannel');
    $this->server->setConfig('authenticate', FALSE);
    return $list;
  }

  /**
   * Create / Delete a tag on a media item.
   *
   * @param type $tag
   * @param type $method
   * @return type
   */
  private function tag($tag, $method) {
    $ret = FALSE;
    $this->server->setConfig('authenticate', TRUE);
    $endpoint = $this->type . '/' . $this->id . '/properties/tags/' . $tag;
    $ret = !$this->server->call($endpoint, $method, NULL, NULL, FALSE)->errors();
    $this->server->setConfig('authenticate', FALSE);
    return $ret;
  }

  /**
   * Create a tag for this media.
   *
   * @param type $tag
   */
  public function addTag($tag) {
    $ret = FALSE;

    // This media must have an ID.
    if ($this->id) {

      // Create the tags if they are not set.
      if (!$this->tags) {
        $this->tags = array();
      }

      // Add the tag to the tags array.
      $this->tags[] = $tag;

      // Now make the call.
      $ret = $this->tag($tag, HTTP_Request2::METHOD_PUT);
    }

    return $ret;
  }

  /**
   * Delete a tag for this media.
   */
  public function deleteTag($tag) {
    $ret = FALSE;
    if ($this->id) {

      if ($this->tags) {
        foreach ($this->tags as $i => $value) {
          if ($tag == $value) {
            unset($this->tags[$i]);
            break;
          }
        }
      }

      // Now make the call.
      $ret = $this->tag($tag, HTTP_Request2::METHOD_DELETE);
    }
    return $ret;
  }

  /**
   * Returns the object to send to the server when creating/updating.
   * @return type
   */
  public function getObject() {
    if ($resource = parent::getObject()) {
      return array_merge($resource, array(
        'media_file' => $this->media_file,
        'description' => $this->description,
        'category' => $this->category,
        'ref_id' => $this->ref_id,
        'restrictionrule_id' => $this->restrictionrule_id,
        'tags' => $this->tags,
        'sched_start_date' => $this->sched_start_date,
        'sched_end_date' => $this->sched_end_date,
      ));
    }
    else {

      // Return FALSE to not perform the update.
      return FALSE;
    }
  }
}
?>
