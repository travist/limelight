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
    $this->assertEquals($media_node->title, $media_list[0]->title);
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

  // Test an upload of a media file.
  public function testMediaUpload() {
    $this->media = new LimelightMedia(array(
      'title' => 'TestMedia__DELETEME',
      'media_file' => 'jellies.mp4'
    ));
    $this->media->set();

    // If the upload passed, then it would set the values of the media.
    $this->assertTrue($this->media->media_type == 'Video', 'Type is video');
    $this->assertTrue($this->media->state == 'New', 'State is New');
    $this->assertTrue(isset($this->media->id) && $this->media->id, 'ID is defined');
  }
/** TO-DO: GET THIS WORKING!!!!
 * 
  // Test adding a tag.
  public function testAddDeleteTag() {
    $media_list = LimelightMedia::index(array('published' => FALSE, 'server' => array('request' => array('cache' => FALSE))));
    $media = NULL;
    foreach ($media_list as $item) {
      if ($item->title == 'TestMedia__DELETEME') {
        $media = $item;
        break;
      }
    }

    if ($media && $media->id) {
      // Get a list of all published media.
      $tag = 'testing_one_two_three';
      $media->addTag($tag);

      // Now get the media separately, with server caching turned off...
      $check = new LimelightMedia(array('id' => $media->id, 'server' => array('request' => array('cache' => FALSE))));
      $this->assertTrue(in_array($tag, $check->tags), "Tag was set.");
      $check->deleteTag($tag);
      $check->get();
      $this->assertTrue(!in_array($tag, $check->tags), 'Tag was deleted.');
    }
  }

  public function testMediaDelete() {
    $media_list = LimelightMedia::index(array('published' => FALSE, 'server' => array('request' => array('cache' => FALSE))));
    $media = NULL;
    foreach ($media_list as $item) {
      if ($item->title == 'TestMedia__DELETEME') {
        $media = $item;
        break;
      }
    }

    if ($media && $media->id) {

      // Delete the media...
      $media->delete();

      // Check to make sure it is gone...
      $check = new LimelightMedia(array('id' => $media->id, 'server' => array('request' => array('cache' => FALSE))));
      $this->assertTrue(!$check->title, 'Media was deleted...');
    }
  }
 *
 */
}
?>
