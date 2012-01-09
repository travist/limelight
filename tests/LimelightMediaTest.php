<?php
include_once 'LimelightChannelTest.php';
require_once dirname(__FILE__) . '/../LimelightMedia.php';

// Define our constants.
define('LIMELIGHT_TEST_MEDIA', 'TESTMEDIA_DELETEME!!!');
define('LIMELIGHT_UPDATE_MEDIA', 'TESTMEDIA_DELETEME_UPDATE!!!');
define('LIMELIGHT_MEDIA_TAG', 'testing, one, two, three!');

/**
 * Returns an uncached list of media.
 *
 * @return type
 */
function limelight_get_media_index() {
  $media = new LimelightMedia(array('server'=>array('request'=>array('cache'=>FALSE))));
  return $media->search(array('state' => 'new'), 'AND');
}

/**
 * Returns a media provided a title.
 *
 * @param type $title
 * @return type
 */
function limelight_get_media($title) {
  foreach (limelight_get_media_index() as $media) {
    if ($media->title == $title) {
      return $media;
    }
  }

  return NULL;
}

/**
 * Checks to see if a media is found.
 *
 * @param type $title
 * @return boolean
 */
function limelight_media_found($title) {
  $found = FALSE;
  foreach (limelight_get_media_index() as $media) {
    if ($media->title == $title) {
      $found = TRUE;
      break;
    }
  }
  return $found;
}


class LimelightMediaTest extends PHPUnit_Framework_TestCase {

  public function testCreateMedia() {

    // Create a new media.
    $media = new LimelightMedia(array('title' => LIMELIGHT_TEST_MEDIA));

    // Now create it.
    $media->set();

    // Check to see if the id is now set.
    $this->assertTrue(isset($media->id) && $media->id, 'ID is set.');

    // Check to see if the title is correct.
    $this->assertTrue(isset($media->title) && $media->title == LIMELIGHT_TEST_MEDIA, 'Title is set.');

    // Now check to make sure we can find the media with this title.
    $this->assertTrue(limelight_media_found($media->title), "Media Found.");
  }

  // Test viewing a list of media.
  public function testMediaList() {

    // Get the list of media.
    $media = limelight_get_media_index();

    // Make sure we found some...
    $this->assertTrue(!!$media, 'List is found.');

    // Iterate through all the media.
    foreach ($media as $item) {

      // Make sure that the ID is defined.
      $this->assertTrue(isset($item->id) && $item->id, 'ID is defined');

      // Make sure that the title is set.
      $this->assertTrue(isset($item->title) && $item->title, 'Title is defined.');
    }
  }

  // Test loading a single media item.
  public function testMediaLoad() {

    // Get a list of all media.
    $media_list = limelight_get_media_index();

    // Get the ID of the first item.
    $id = $media_list[0]->id;

    // Now load that media item.
    $media_node = new LimelightMedia(array('id' => $id));

    // Now verify that they are the same...
    $this->assertEquals($media_node->title, $media_list[0]->title);
  }

  // Test updating media.
  public function testMediaUpdate() {

    // Get the test media.
    $media = limelight_get_media(LIMELIGHT_TEST_MEDIA);

    if ($media) {
      // Change the title.
      $media->set(array('title' => LIMELIGHT_UPDATE_MEDIA));

      // Now make sure we can't find the old name.
      $this->assertTrue(!limelight_media_found(LIMELIGHT_TEST_MEDIA), "Create update success.");

      // Now make sure we CAN find the new name.
      $this->assertTrue(limelight_media_found(LIMELIGHT_UPDATE_MEDIA), "Create update success.");

      // Set the media name back to the test name.
      $media->set(array('title' => LIMELIGHT_TEST_MEDIA));
    }
    else {
      $this->assertTrue(FALSE, "Test media not found.");
    }
  }

  // Test getting the channels associated with a media.
  public function testGetChannels() {

    // First add a channel and add this media to the the channel.
    $channel = new LimelightChannel(array('title' => LIMELIGHT_TEST_CHANNEL));

    // Now create it.
    $channel->set();

    // Get the test media.
    $media = limelight_get_media(LIMELIGHT_TEST_MEDIA);

    // If the media exists.
    if ($media) {

      // Add the media to the channel.
      $channel->addMedia($media);

      // Now get the channels for this media.
      $channels = $media->getChannels();

      // Now iterate through the channels, and make sure we find the test channel.
      foreach ($channels as $item) {
        $this->assertTrue(isset($item->id) && $item->id, "ID is defined");
        $this->assertTrue(isset($item->title) && $item->title, "Title is defined");
        $this->assertEquals($item->title, LIMELIGHT_TEST_CHANNEL);
      }
    }
    else {
      $this->assertTrue(FALSE, "Test media not found.");
    }

    // Now delete the test channel.
    $channel->delete();
  }

  // Test an upload of a media file.
  public function testMediaUpload() {

    // Get the test media.
    $media = limelight_get_media(LIMELIGHT_TEST_MEDIA);

    if ($media) {
      // Now upload the media to that media.
      $media->set(array('media_file' => 'jellies.mp4'));

      // If the upload passed, then it would set the values of the media.
      $this->assertTrue($media->media_type == 'Video', 'Type is video');
    }
    else {
      $this->assertTrue(FALSE, "Test media not found.");
    }
  }

  // Test adding a tag.
  public function testAddTag() {

    // Get the test media.
    $media = limelight_get_media(LIMELIGHT_TEST_MEDIA);

    if ($media) {
      // Add the tag.
      $media->addTag(LIMELIGHT_MEDIA_TAG);

      // Now reload it and check it...
      $check = limelight_get_media(LIMELIGHT_TEST_MEDIA);

      // Test to see if this tag was added.
      $this->assertTrue(in_array(LIMELIGHT_MEDIA_TAG, $check->tags), "Tag was set.");
    }
    else {
      $this->assertTrue(FALSE, "Test media not found.");
    }
  }

  // Test deleting a tag.
  public function testDeleteTag() {

    // Get the test media.
    $media = limelight_get_media(LIMELIGHT_TEST_MEDIA);

    if ($media) {
      // Delete the tag.
      $media->deleteTag(LIMELIGHT_MEDIA_TAG);

      // Now reload it and check it...
      $check = limelight_get_media(LIMELIGHT_TEST_MEDIA);

      // Test to see if this tag was added.
      $this->assertTrue(!in_array(LIMELIGHT_MEDIA_TAG, $check->tags), "Tag was deleted.");
    }
    else {
      $this->assertTrue(FALSE, "Test media not found.");
    }
  }

  // Delete the media.
  public function testMediaDelete() {

    // Get the test media.
    $media = limelight_get_media(LIMELIGHT_TEST_MEDIA);

    if ($media) {
      // Delete the media...
      $media->delete();

      // Make sure we cannot find the media.
      $this->assertTrue(!limelight_media_found(LIMELIGHT_TEST_MEDIA), "Delete Media success.");
    }
    else {
      $this->assertTrue(FALSE, "Test media not found.");
    }
  }
}
?>
