<?php
/*
 * This will simply be the Upload Widget to upload files directly to your server.
 */
include_once 'LimelightMedia.php';
$media = new LimelightMedia();
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Limelight Upload Widget</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <script type='text/javascript'>
      var media = null;
      function delveUploadWidgetCallback(data) {
        media = eval(unescape(data));
        console.log(media);
      }
    </script>
  </head>
  <body>
    <?php print $media->getUploadWidget(); ?>
  </body>
</html>
