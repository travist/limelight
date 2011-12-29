<?php

// Require the LimelightResource class.
require_once 'LimelightMedia.php';
require_once 'LimelightResource.php';

class LimelightChannel extends LimelightResource {

  /** The description of the channel. */
  public $description = NULL;

  /** The URL of the thumbnail image associated with the channel. */
  public $thumbnail_url = NULL;

  /** Current state of the channel. */
  public $state = NULL;

  /** An indicator that enables share with a friend functionality. */
  public $email_enabled = FALSE;

  /** An indicator that enables get embed code functionality. */
  public $embed_enabled = FALSE;

  /** An indicator that enables search inside functionality. */
  public $search_inside_enabled = FALSE;

  /** An indicator that enables autoplay functionality. */
  public $autoplay_enabled = FALSE;

  /** An indicator that enables RSS functionality. */
  public $rss_enabled = TRUE;

  /** An indicator that enables iTunes functionality. */
  public $itunes_rss_enabled = TRUE;

  /** The date the channel was last set to 'Published'. */
  public $publish_date = NULL;

  /** The date the channel was last updated. */
  public $update_date = NULL;

  /** The date the channel was created. */
  public $create_date = NULL;

  /**
   * Override the update method to provide custom ID's.
   *
   * @param type $params
   * @return LimelightChannel
   */
  public function update($params = array()) {
    $params = (array)$params;
    parent::update($params);
    if (isset($params['channel_id'])) {
      $this->id = $this->id ? $this->id : $params['channel_id'];
    }
    return $this;
  }

  /**
   * Returns the type for this resource.
   */
  public function getType() {
    return 'channels';
  }

  /**
   * Returns all the media associated with this channel.
   */
  public function getMedia($query = array()) {
    $query = $this->getQuery($query, array(
      'page_id' => 0,
      'page_size' => 25
    ));
    $endpoint = $this->type . '/' . $this->id . '/media';
    return $this->getIndex($endpoint, $query, 'LimelightMedia');
  }

  /**
   * Create or Remove a media from a channel.
   *
   * @param type $media
   * @param type $method
   * @return type
   */
  private function setMedia($media, $method) {
    $ret = FALSE;
    if ($this->id && $media && $media->id) {
      $this->server->setConfig('authenticate', TRUE);
      $endpoint = $this->type . '/' . $this->id . '/media/' . $media->id;
      $this->response = $this->server->call($endpoint, $method, NULL, NULL, FALSE);
      $ret = !$this->getLastErrors();
      $this->server->setConfig('authenticate', FALSE);
    }
    return $ret;
  }

  /**
   * Adds existing media to this channel.
   */
  public function addMedia($media, $allow_duplicates = FALSE) {

    // Default find to false.
    $found = FALSE;

    // If they don't want duplicates, then we need to search existing
    // media in this channel.
    if (!$allow_duplicates) {

      // Get all the media in this channel.
      $media_list = $this->getMedia();

      // Iterate through the media and find this media.
      foreach ($media_list as $list_media) {
        if ($media->id == $list_media->id) {
          $found = TRUE;
          break;
        }
      }
    }

    // Only add if the media doesn't already exist in the channel.
    if (!$found) {
      return $this->setMedia($media, HTTP_Request2::METHOD_PUT);
    }

    // Return FALSE that no media was added.
    return FALSE;
  }

  /**
   * Remove media from an existing channel.
   *
   * @param type $media
   */
  public function removeMedia($media) {
    return $this->setMedia($media, HTTP_Request2::METHOD_DELETE);
  }

  /**
   * Sets the publish state of this channel.
   */
  public function publish($state = TRUE) {
    $this->response = $this->set(array('state' => ($state ? 'Published' : 'NotPublished')));
    return !$this->getLastErrors();
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
