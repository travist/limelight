<?php

// Require the LimelightResource class.
require_once 'LimelightResource.php';
require_once 'LimelightChannel.php';

class LimeLightChannelGroup extends LimelightResource {

  /** The date the channel was last updated. */
  public $update_date = 0;

  /** The date the channel was created. */
  public $create_date = 0;

  /**
   * Returns the endpoint for this resource.
   */
  public function getType() {
    return 'channelgroups';
  }

  /**
   * Returns an index of channel groups.
   *
   * @param array $filter
   * @return type
   */
  public function index($filter = array()) {

    // You can only get ALL channel groups.
    $filter['published'] = FALSE;
    return parent::index($filter);
  }

  /**
   * Returns all the channels within this channel group.
   */
  public function getChannels($filter = array()) {
    $filter = $this->getFilter($filter, array(
      'page_id' => 0,
      'page_size' => 25
    ));
    $endpoint = $this->type . '/' . $this->id . '/channels';
    return $this->getIndex($endpoint, $filter, 'LimelightChannel');
  }

  /**
   * Delete a channel from this group.
   */
  public function deleteChannel($channel_id) {

    if ($this->type && $this->id && $channel_id) {
      $endpoint = $this->type . '/' . $this->id . '/channels/' . $channel_id;
      return $this->server->delete($endpoint);
    }
    else {
      return FALSE;
    }
  }
}
?>
