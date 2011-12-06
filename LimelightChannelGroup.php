<?php

// Require the LimelightEntity class.
require_once 'LimelightEntity.php';
require_once 'LimelightChannel.php';

class LimeLightChannelGroup extends LimelightEntity {

  /** The date the channel was last updated. */
  public $update_date = 0;

  /** The date the channel was created. */
  public $create_date = 0;

  /**
   * Returns the type for this entity.
   */
  public function getType() {
    return 'channelgroups';
  }

  /**
   * Returns a list of channel groups.
   *
   * @param array $filter
   * @return type
   */
  public function getList($filter = array()) {

    // You can only get ALL channel groups.
    $filter['published'] = FALSE;
    return parent::getList($filter);
  }

  /**
   * Returns all the channels within this channel group.
   */
  public function getChannels($filter = array()) {

    // Return a typelist of channels.
    return $this->getTypeList('channels', $filter, array(
      'page_id' => 0,
      'page_size' => 25
    ), 'LimelightChannel');
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
