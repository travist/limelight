<?php
require_once '../LimelightChannel.php';
require_once '../LimelightMedia.php';

// Define our constants.
define('LIMELIGHT_TEST_CHANNEL', 'TESTCHANNEL_DELETEME!!!');
define('LIMELIGHT_UPDATE_CHANNEL', 'TESTCHANNEL_DELETEME_UPDATE!!!');
define('LIMELIGHT_TEST_MEDIA1', 'TESTMEDIA_DELETEME_1!!!');
define('LIMELIGHT_TEST_MEDIA2', 'TESTMEDIA_DELETEME_2!!!');

/**
 * Returns an uncached list of channels.
 *
 * @return type
 */
function limelight_get_channels() {
  $channel = new LimelightChannel(array('server'=>array('request'=>array('cache'=>FALSE))));
  return $channel->index(array('published'=>FALSE));
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

  function testChannelList() {

    // Get the list of channels.
    $channels = limelight_get_channels();

    // Make sure we found some...
    $this->assertTrue(!!$channels, 'List is found.');

    // Iterate through all the channels.
    foreach ($channels as $channel) {

      // Make sure that the ID is defined.
      $this->assertTrue(isset($channel->id) && $channel->id, 'ID is defined');

      // Make sure that the title is set.
      $this->assertTrue(isset($channel->title) && $channel->title, 'Title is defined.');
    }
  }

  function testUpdateChannel() {

    // Get the test channel.
    $channel = limelight_get_channel(LIMELIGHT_TEST_CHANNEL);

    if ($channel) {
      // Change the title to the updated name.
      $channel->set(array('title' => LIMELIGHT_UPDATE_CHANNEL));

      // Make sure that the title was changed.
      $this->assertEquals($channel->title, LIMELIGHT_UPDATE_CHANNEL);

      // Set the channel back to the test name.
      $channel->set(array('title' => LIMELIGHT_TEST_CHANNEL));

      // Make sure that the title was changed back.
      $this->assertEquals($channel->title, LIMELIGHT_TEST_CHANNEL);
    }
    else {
      $this->assertTrue(FALSE, "Test channel not found.");
    }
  }

  function testAddMediaToChannel() {

    // Get the test channel.
    $channel = limelight_get_channel(LIMELIGHT_TEST_CHANNEL);

    if ($channel) {

      // Create new media.
      $media1 = new LimelightMedia(array('title' => LIMELIGHT_TEST_MEDIA1));
      $media1->set();

      // Create new media.
      $media2 = new LimelightMedia(array('title' => LIMELIGHT_TEST_MEDIA2));
      $media2->set();

      // Now let's add some media to this channel.
      $channel->addMedia($media1);
      $channel->addMedia($media2);

      // Now get the media for this channel, and check out if everything is there.
      $channel_media = $channel->getMedia();

      // Make sure that there are two media in this channel.
      $this->assertTrue(count($channel_media) == 2, 'Media in channel is correct.');

      // Make sure that the media in the channel equals the media that we added.
      $this->assertEquals($channel_media[0].id, $media1.id);
      $this->assertEquals($channel_media[1].id, $media2.id);
    }
    else {
      $this->assertTrue(FALSE, "Test channel not found.");
    }
  }

  function testPublishChannel() {
    // Get the test channel.
    $channel = limelight_get_channel(LIMELIGHT_TEST_CHANNEL);

    if ($channel) {
      // Now publish this channel.
      $channel->publish();

      // Get the test channel.
      $channel = limelight_get_channel(LIMELIGHT_TEST_CHANNEL);

      // Check to make sure the state is Published.
      $this->assertEquals($channel->state, "Published");
    }
    else {
      $this->assertTrue(FALSE, "Test channel not found.");
    }
  }

  function testDeleteChannel() {

    // Get the test channel.
    $channel = limelight_get_channel(LIMELIGHT_TEST_CHANNEL);

    if ($channel) {
      // Now finally delete the test channel.
      $channel->delete();

      // Make sure we cannot find the channel.
      $this->assertTrue(!limelight_channel_found(LIMELIGHT_TEST_CHANNEL), "Delete Channel success.");
    }
    else {
      $this->assertTrue(FALSE, "Test channel not found.");
    }
  }
}
?>
