<?php
require_once '../LimelightChannel.php';
require_once '../LimelightMedia.php';
class LimelightChannelTest extends PHPUnit_Framework_TestCase {

  private $channel = NULL;

  function testGetChannel() {
    $channels = LimelightChannel::index();
    $this->assertTrue(!!$channels, 'Channel list is defined.');
    foreach ($channels as $channel) {
      $class = get_class($channel);
      $this->assertTrue(get_class($channel) == 'LimelightChannel', 'Type is LimelightChannel');
      $this->assertTrue(isset($channel->id) && $channel->id, 'ID is defined');
      $this->assertTrue(isset($channel->title) && $channel->title, 'Title is defined.');
    }
  }

  private function channel_found($title) {
    $found = FALSE;
    $channels = LimelightChannel::index(array('published'=>FALSE), array('server'=>array('request'=>array('cache'=>FALSE))));
    foreach ($channels as $channel) {
      if ($channel->title == $title) {
        $found = TRUE;
        break;
      }
    }
    return $found;
  }

  function testCreateUpdateChannel() {

    // Create a new channel.
    $rand_title = 'Test' . rand();
    $this->channel = new LimelightChannel(array(
      'title' => $rand_title
    ));

    // Now create it.
    $this->channel->set();

    // Check to see if the id is now set.
    $this->assertTrue(isset($this->channel->id) && $this->channel->id, 'Channel ID is set.');
    $this->assertTrue(isset($this->channel->title) && $this->channel->title == $rand_title, 'Channel Title is set.');
    $this->assertTrue($this->channel_found($this->channel->title), "Create Channel success.");
  }

  function testAddMediaToChannel() {

    if ($this->channel) {

      // Now let's add some media to this channel.
      $media = LimelightMedia::index();
      $this->channel->addMedia($media[0]);
      $this->channel->addMedia($media[1]);

      // Now get the media for this channel, and check out if everything is there.
      $channel_media = $this->channel->getMedia();
      $this->assertTrue(count($channel_media) == 2, 'Media in channel is correct.');
      $this->assertEquals($channel_media[0], $media[0]);
      $this->assertEquals($channel_media[1], $media[1]);
    }
  }

  function testDeleteChannel() {

    if ($this->channel) {

      // Now finally delete this channel.
      $this->channel->delete();
      $this->assertTrue(!$this->channel_found($this->channel->title), "Delete Channel success.");
    }
  }
}
?>
