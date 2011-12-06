<?php

// Require the LimelightEntity class.
require_once 'LimelightEntity.php';
require_once 'LimelightChannel.php';

class LimeLightMedia extends LimelightEntity {

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

  /** The API for this entity. */

  function __construct($params = null) {
    parent::__construct(&$params);
    $this->id = $this->id ? $this->id : $params['media_id'];
  }

  /**
   * Returns the type for this entity.
   */
  public function getType() {
    return 'media';
  }

  /**
   * Parse function to parse out entities returned by list functions.
   *
   * @param type $entities
   */
  protected function parseEntities($entities, $className) {

    // If media_list exists, use it instead.
    if (isset($entities['media_list'])) {
      $entities = $entities['media_list'];
    }

    // Parse the entities.
    return parent::parseEntities($entities, $className);
  }

  /**
   * Returns all the channels that this media belongs too.
   */
  public function getChannels($filter = array()) {

    // Return a typelist of channels.
    $this->server->setConfig('authenticate', TRUE);
    $list = $this->getTypeList('channels', $filter, array(
      'page_id' => 0,
      'page_size' => 25,
      'sort_by' => 'update_date',
      'sort_order' => 'asc'
    ), 'LimelightChannel');
    $this->server->setConfig('authenticate', FALSE);
    return $list;
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

      // Add the tag to the tags array.
      $this->tags->push($tag);

      // Now make the call.
      $this->server->setConfig('authenticate', TRUE);
      $endpoint = $this->type . '/' . $this->id . '/properties/tags/' . $tag;
      $ret = $this->server->call(HTTP_Request2::METHOD_POST, $endpoint, NULL, NULL);
      $this->server->setConfig('authenticate', FALSE);
    }

    return $ret;
  }

  /**
   * Returns the object to send to the server when creating/updating.
   * @return type
   */
  public function getObject() {
    if ($entity = parent::getObject()) {
      return array_merge($entity, array(
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
