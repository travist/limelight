<?php

// Require the LimelightResource class.
require_once 'LimelightResource.php';
require_once 'LimelightChannel.php';

class LimelightChannelGroup extends LimelightResource {

  /** The date the channel was last updated. */
  public $update_date = NULL;

  /** The date the channel was created. */
  public $create_date = NULL;

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
  protected function __index($filter = array()) {
    // You can only get ALL channel groups.
    $filter['published'] = FALSE;
    return parent::__index($filter);
  }

  /**
   * Returns all the channels within this channel group.
   */
  public function getChannels($query = array()) {
    $query = $this->getQuery($query, array(
      'page_id' => 0,
      'page_size' => 25
    ));
    $endpoint = $this->type . '/' . $this->id . '/channels';
    return $this->getIndex($endpoint, $query, 'LimelightChannel');
  }

  /**
   * Delete a channel from this group.
   */
  public function deleteChannel($channel_id) {

    if ($this->type && $this->id && $channel_id) {
      $endpoint = $this->type . '/' . $this->id . '/channels/' . $channel_id;
      return !$this->server->delete($endpoint)->errors();
    }
    else {
      return FALSE;
    }
  }
}
?>
