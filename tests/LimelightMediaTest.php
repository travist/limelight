<?php
require_once dirname(__FILE__) . '/../LimelightMedia.php';
class LimelightMediaTest extends PHPUnit_Framework_TestCase {

  // Test viewing a list of media.
  public function testMediaList() {

    // Create a new media.
    $media = new LimelightMedia();

    // Get a list of all published media.
    $media_list = $media->index();

    // Assert that it returned 25 items.
    $this->assertTrue(!!$media_list, 'The media list is defined.');
    $this->assertEquals(25, count($media_list));

    foreach ($media_list as $item) {

      $this->assertTrue(isset($item->id) && $item->id, 'ID is defined');
      $this->assertTrue(isset($item->title) && $item->title, 'Title is defined.');
    }
  }

  // Test viewing unpublished media.
  public function testUnpublishedMediaList() {

    // Create a new media object.
    $media = new LimeLightMedia();
    $media_list = $media->index(array('published' => FALSE));
    $this->assertTrue(!!$media_list, 'The media list is defined.');
    $this->assertEquals(25, count($media_list));
    foreach ($media_list as $item) {
      $this->assertTrue(isset($item->id) && $item->id, 'ID is defined');
      $this->assertTrue(isset($item->title) && $item->title, 'Title is defined.');
    }
  }

  // Test loading a single media item.
  public function testMediaLoad() {

    // Create a new media.
    $media = new LimelightMedia();

    // Get a list of all published media.
    $media_list = $media->index();

    // Get the ID of the first item.
    $id = $media_list[0]->id;

    // Now load that media item.
    $media_node = new LimeLightMedia(array(
      'id' => $id
    ));

    // Now verify that they are the same...
    $this->assertEquals($media_node, $media_list[0]);
  }

}
?>
