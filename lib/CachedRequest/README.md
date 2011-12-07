CachedRequest
============================

This is a library that extends the PEAR HTTP_Request2 library and implements caching
for the GET requests.  It is written in a way where other caching mechanisms can
be implemented and utilized by simply declaring that class as the request instead
of HTTP_Request2 class.

This class uses the exact same interface as a typical HTTP_Request2 request, except
that it uses caching for repeat GET requests.

Example
-----------------------------

If you wish to implement File Caching into your HTTP GET requests, you could simply
use...

```
<?php
  $request = new HTTP_FileCachedRequest('http://mysite.com/rest/resource/1234');
  $result = $request->send()->getBody();
?>
```
