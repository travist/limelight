<?php

// Require the LimelightResource class.
require_once 'LimelightMedia.php';
require_once 'LimelightResource.php';

class LimeLightChannel extends LimelightResource {

  /** The description of the channel. */
  public $description = '';

  /** The URL of the thumbnail image associated with the channel. */
  public $thumbnail_url = '';

  /** Current state of the channel. */
  public $state = '';

  /** An indicator that enables share with a friend functionality. */
  public $email_enabled = FALSE;

  /** An indicator that enables get embed code functionality. */
  public $embed_enabled = FALSE;

  /** An indicator that enables search inside functionality. */
  public $search_inside_enabled = FALSE;

  /** An indicator that enables autoplay functionality. */
  public $autoplay_enabled = FALSE;

  /** An indicator that enables RSS functionality. */
  public $rss_enabled = FALSE;

  /** An indicator that enables iTunes functionality. */
  public $itunes_rss_enabled = FALSE;

  /** The date the channel was last set to 'Published'. */
  public $publish_date = 0;

  /** The date the channel was last updated. */
  public $update_date = 0;

  /** The date the channel was created. */
  public $create_date = 0;

  function __construct($params = null) {
    parent::__construct(&$params);
    $this->id = $this->id ? $this->id : $params['channel_id'];
  }

  /**
   * Returns the endpoint for this resource.
   */
  public function getType() {
    return 'channels';
  }

  /**
   * Returns all the media associated with this channel.
   */
  public function getMedia($filter = array()) {
    $filter = $this->getFilter($filter, array(
      'page_id' => 0,
      'page_size' => 25
    ));
    $endpoint = $this->type . '/' . $this->id . '/media';
    return $this->getIndex($endpoint, $filter, 'LimelightMedia');
  }

  /**
   * Returns the object to send to the server when creating/updating.
   * @return type
   */
  public function getObject() {
    if ($resource = parent::getObject()) {
      return array_merge($resource, array(
        'description' => $this->description,
        'state' => $this->state,
        'email_enabled' => $this->email_enabled,
        'embed_enabled' => $this->embed_enabled,
        'search_inside_enabled' => $this->search_inside_enabled,
        'autoplay_enabled' => $this->autoplay_enabled,
        'rss_enabled' => $this->rss_enabled,
        'itunes_rss_enabled' => $this->itunes_rss_enabled
      ));
    }
    else {

      // Return FALSE to not perform the update.
      return FALSE;
    }
  }
}
?>
