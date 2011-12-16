This is where the FileCache is stored.

You can alter this at runtime by passing in the configuration of cache_directory
when declaring the request like so.

$request = new HTTP_FileCachedRequest('http://mysite.com/rest', 'GET', array(
  'cache_directory' => '/tmp'
));

