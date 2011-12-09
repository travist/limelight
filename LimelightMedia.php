<?php

// Require the LimelightResource class.
require_once 'LimelightResource.php';
require_once 'LimelightChannel.php';

class LimelightMedia extends LimelightResource {

  /** The media file. */
  public $media_file = '';

  /** The description of the media. */
  public $description = '';

  /** The media type of this media. */
  public $media_type = '';

  /** The original filename. */
  public $original_filename = '';

  /** The state of this media. */
  public $state = '';

  /** The duration in milliseconds. */
  public $duration_in_milliseconds = 0;

  /** The total storage in bytes. */
  public $total_storage_in_bytes = 0;

  /** The category of this media. */
  public $category = '';

  /** The reference id. */
  public $ref_id = '';

  /** The restriction rule ID. */
  public $restrictionrule_id = '';

  /** The thumbnails for thie media. */
  public $thumbnails = array();

  /** The tags associated with this media. */
  public $tags = array();

  /** The scheduled start date. */
  public $sched_start_date = 0;

  /** The scheduled end date. */
  public $sched_end_date = 0;

  /** The date the media was published. */
  public $publish_date = 0;

  /** The date the media was upated. */
  public $update_date = 0;

  /** The date the media was created. */
  public $create_date = 0;

  /**
   * Override the update method to provide custom ID's.
   *
   * @param type $params
   * @return LimelightMedia
   */
  public function update($params = array()) {
    $params = (array)$params;
    parent::update($params);
    $this->id = $this->id ? $this->id : $params['media_id'];
    return $this;
  }

  /**
   * Returns the endpoint for this resource.
   */
  public function getType() {
    return 'media';
  }

  /**
   * Returns all the channels that this media belongs too.
   */
  public function getChannels($filter = array()) {
    // Return a typelist of channels.
    $this->server->setConfig('authenticate', TRUE);
    $filter = $this->getFilter($filter, array(
      'page_id' => 0,
      'page_size' => 25,
      'sort_by' => 'update_date',
      'sort_order' => 'asc'
    ));
    $endpoint = $this->type . '/' . $this->id . '/channels';
    $list = $this->getIndex($endpoint, $filter, 'LimelightChannel');
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
    $ret = $this->server->call($endpoint, $method, NULL, NULL, FALSE);
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
