<?php
require_once '../LimelightChannel.php';
require_once '../LimelightMedia.php';

// Define our constants.
define('LIMELIGHT_TEST_CHANNEL', 'TESTCHANNEL_DELETEME!!!');
define('LIMELIGHT_UPDATE_CHANNEL', 'TESTCHANNEL_DELETEME_UPDATE!!!');

/**
 * Returns an uncached list of channels.
 *
 * @return type
 */
function limelight_get_channels() {
  return LimelightChannel::index(array('published'=>FALSE), array('server'=>array('request'=>array('cache'=>FALSE))));
}

/**
 * Returns a channel provided a title.
 *
 * @param type $title
 * @return type
 */
function limelight_get_channel($title) {
  foreach (limelight_get_channels() as $channel) {
    if ($channel->title == $title) {
      return $channel;
    }
  }

  return NULL;
}

/**
 * Checks to see if a channel is found.
 *
 * @param type $title
 * @return boolean
 */
function limelight_channel_found($title) {
  $found = FALSE;
  foreach (limelight_get_channels() as $channel) {
    if ($channel->title == $title) {
      $found = TRUE;
      break;
    }
  }
  return $found;
}

class LimelightChannelTest extends PHPUnit_Framework_TestCase {

  function testGetChannel() {

    // Get the list of channels.
    $channels = LimelightChannel::index();

    // Make sure we found some...
    $this->assertTrue(!!$channels, 'Channel list is defined.');

    // Iterate through all the channels.
    foreach ($channels as $channel) {

      // Make sure that the ID is defined.
      $this->assertTrue(isset($channel->id) && $channel->id, 'ID is defined');

      // Make sure that the title is set.
      $this->assertTrue(isset($channel->title) && $channel->title, 'Title is defined.');
    }
  }

  function testCreateChannel() {

    // Create a new channel.
    $channel = new LimelightChannel(array('title' => LIMELIGHT_TEST_CHANNEL));

    // Now create it.
    $channel->set();

    // Check to see if the id is now set.
    $this->assertTrue(isset($channel->id) && $channel->id, 'Channel ID is set.');

    // Check to see if the title is correct.
    $this->assertTrue(isset($channel->title) && $channel->title == LIMELIGHT_TEST_CHANNEL, 'Channel Title is set.');

    // Now check to make sure we can find the channel with this title.
    $this->assertTrue(limelight_channel_found($channel->title), "Create Channel success.");
  }

  function testUpdateChannel() {

    // Get the test channel.
    $channel = limelight_get_channel(LIMELIGHT_TEST_CHANNEL);

    // Change the title to the updated name.
    $channel->set(array('title' => LIMELIGHT_UPDATE_CHANNEL));

    // Now make sure we can't find the old name.
    $this->assertTrue(!limelight_channel_found(LIMELIGHT_TEST_CHANNEL), "Create update success.");

    // Now make sure we CAN find the new name.
    $this->assertTrue(limelight_channel_found(LIMELIGHT_UPDATE_CHANNEL), "Create update success.");

    // Set the channel back to the test name.
    $channel->set(array('title' => LIMELIGHT_TEST_CHANNEL));
  }

  function testAddMediaToChannel() {

    // Get the test channel.
    $channel = limelight_get_channel(LIMELIGHT_TEST_CHANNEL);

    // Now let's add some media to this channel.
    $media = LimelightMedia::index();
    $channel->addMedia($media[0]);
    $channel->addMedia($media[1]);

    // Now get the media for this channel, and check out if everything is there.
    $channel_media = $channel->getMedia();

    // Make sure that there are two media in this channel.
    $this->assertTrue(count($channel_media) == 2, 'Media in channel is correct.');

    // Make sure that the media in the channel equals the media that we added.
    $this->assertEquals($channel_media[0].id, $media[0].id);
    $this->assertEquals($channel_media[1].id, $media[1].id);
  }

  function testPublishChannel() {
    // Get the test channel.
    $channel = limelight_get_channel(LIMELIGHT_TEST_CHANNEL);

    // Now publish this channel.
    $channel->publish();

    // Get the test channel.
    $channel = limelight_get_channel(LIMELIGHT_TEST_CHANNEL);

    // Check to make sure the state is Published.
    $this->assertEquals($channel->state, "Published");
  }

  function testDeleteChannel() {

    // Get the test channel.
    $channel = limelight_get_channel(LIMELIGHT_TEST_CHANNEL);

    // Now finally delete the test channel.
    $channel->delete();

    // Make sure we cannot find the channel.
    $this->assertTrue(!limelight_channel_found(LIMELIGHT_TEST_CHANNEL), "Delete Channel success.");
  }
}
?>
