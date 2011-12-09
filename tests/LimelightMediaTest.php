<?php
require_once dirname(__FILE__) . '/../LimelightMedia.php';
class LimelightMediaTest extends PHPUnit_Framework_TestCase {

  // Test viewing a list of media.
  public function testMediaList() {

    // Get a list of all published media.
    $media_list = LimelightMedia::index();

    // Assert that it returned 25 items.
    $this->assertTrue(!!$media_list, 'The media list is defined.');
    foreach ($media_list as $item) {
      $this->assertTrue(get_class($item) == 'LimelightMedia', 'Class is LimelightMedia');
      $this->assertTrue(isset($item->id) && $item->id, 'ID is defined');
      $this->assertTrue(isset($item->title) && $item->title, 'Title is defined.');
    }
  }

  // Test viewing unpublished media.
  public function testUnpublishedMediaList() {

    // Create a new media object.
    $media_list = LimelightMedia::index(array('published' => FALSE));
    $this->assertTrue(!!$media_list, 'The media list is defined.');
    $this->assertEquals(25, count($media_list));
    foreach ($media_list as $item) {
      $this->assertTrue(isset($item->id) && $item->id, 'ID is defined');
      $this->assertTrue(isset($item->title) && $item->title, 'Title is defined.');
    }
  }

  // Test loading a single media item.
  public function testMediaLoad() {

    // Get a list of all published media.
    $media_list = LimelightMedia::index();

    // Get the ID of the first item.
    $id = $media_list[0]->id;

    // Now load that media item.
    $media_node = new LimelightMedia(array(
      'id' => $id
    ));

    // Now verify that they are the same...
    $this->assertEquals($media_node, $media_list[0]);
  }

  // Test getting the channels associated with a media.
  public function testGetChannels() {

    // Get a list of all published media.
    $media_list = LimelightMedia::index();
    $media = $media_list[0];

    // Now get the channels for this media.
    $channels = $media->getChannels();

    // Now iterate through the channels.
    foreach ($channels as $item) {

      $this->assertTrue(isset($item->id) && $item->id, "ID is defined");
      $this->assertTrue(isset($item->title) && $item->title, "Title is defined");
    }
  }

  // Test adding a tag.
  public function testAddDeleteTag() {

    // Get a list of all published media.
    $tag = 'testing_one_two_three';
    $media_list = LimelightMedia::index();
    $media = $media_list[0];
    $media->addTag($tag);

    // Now get the media separately, with server caching turned off...
    $check = new LimelightMedia(array('id' => $media->id, 'server' => array('request' => array('cache' => FALSE))));
    $this->assertTrue(in_array($tag, $check->tags), "Tag was set.");
    $check->deleteTag($tag);
    $check->get();
    $this->assertTrue(!in_array($tag, $check->tags), 'Tag was deleted.');
  }
}
?>
