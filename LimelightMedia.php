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
   * Override the getParams to unset the tags.
   *
   * @param type $params
   */
  protected function getParams($params = array()) {
    $params = parent::getParams($params);
    unset($params['tags']);
    return $params;
  }

  /**
   * Perform a set along with custom data.
   */
  public function set($params = array()) {

    // Set the parent params.
    parent::set($params);

    // Now set the tags
    $endpoint = $this->endpoint('set') . '/tags';
    $tags = isset($params['tags']) ? $params['tags'] : array();
    $this->setProperties('tags', $endpoint, $tags, array(
      'seteach' => TRUE,
      'deleteeach' => TRUE
    ));

    // Return the this pointer.
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
  public function search($query = array(), $operator = 'AND') {

    // Build the query string for the search.
    $query_string = array();
    foreach ($query as $key => $value) {
      $query_string[] = strtolower($key) . ':' . strtolower($value);
    }

    // We need to rewrite the query based on the operator.
    $search_query = array(strtolower($operator) => implode(';', $query_string));

    // Now get the index with this query.
    return $this->index($search_query);
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
    $endpoint = parent::endpoint($type);
    if ($type == 'index') {
      $endpoint .= '/search';
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
      $this->server->call($endpoint, HTTP_Request2::METHOD_POST, $params, NULL, TRUE);
      $url = $this->getResponse();

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
   * Returns an upload URL for media.
   */
  public function getUploadURL($redirect = '') {
    // Get the POST request.
    $this->server->setConfig('authenticate', TRUE);
    $request = $this->server->setRequest($this->endpoint('set'),HTTP_Request2::METHOD_POST);
    $this->server->setConfig('authenticate', FALSE);

    // Get the URL for this request.
    return $request->request()->getUrl()->getUrl();
  }

  /**
   * Returns an Upload Widget for your page.
   */
  public function getUploadWidget($redirect = '', $width = 475, $height = 325) {

    // Get the flashvars for this request.
    $flashVars = "presigned_url=" . urlencode($this->getUploadURL());
    if ($redirect) {
      $redirect = urlencode($redirect);
      $flashVars .= "&redirect_to={$redirect}";
    }

    $widget = "<object id='obj1'
      width='{$width}' height='{$height}'
      classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000'
      codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,5,0,0' height='400' width='500'>
      <param name='src' value='http://assets.delvenetworks.com/upload-widget/current.swf'/>
      <param name='AllowScriptAccess' value='always'/>
      <param name='flashvars' value='{$flashVars}'/>
      <embed name='obj2'
        pluginspage='http://www.macromedia.com/go/getflashplayer'
        AllowScriptAccess='always'
        src='http://assets.delvenetworks.com/upload-widget/current.swf'
        height='{$height}' width='{$width}' flashvars='{$flashVars}'/>
      </object><br/>";

    // Return the upload widget.
    return $widget;
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
