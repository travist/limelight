Limelight PHP API
====================================
This is a PHP-API library that is used to work with the Limelight CDN in an
object oriented way.

Installation:
------------------------------------
This library requires the PEAR HTTP_Request2 library to work.  Do do this, you
must first install PEAR and then install the HTML_Request2 component like the
following.

  - Install PEAR by following http://pear.php.net/manual/en/installation.getting.php

  - Now install the HTTP_Request2 library by typing...

    sudo pear upgrade PEAR
    sudo pear config-set auto_discover 1
    sudo pear install HTTP_Request2

If you wish to run the PHPUnit tests, you will need to have PHPUnit installed.

    sudo pear install pear.phpunit.de/PHPUnit

Configuration:
------------------------------------
Within the LimelightConfig.php file, provide the following...

 - organization_id
 - access_key
 - secret

Examples:
------------------------------------

To add new media on the Limelight CDN.

require_once 'LimelightMedia.php';
$media = new LimelightMedia();

