<?php

require_once 'lib/CachedRequest/HTTP/FileCachedRequest.php';

class LimelightCachedRequest extends HTTP_FileCachedRequest {

  /**
   * Override the set_cache_name function to rip out the authentication
   * parameters from the request URL.  This is so that GET methods that require
   * authentication do not cause a cache miss since the URL will be different
   * for each request on authentication requests.
   *
   * @return HTTP_CachedResponse
   */
  public function set_cache_name($cache_name) {

    // Get the query variables from the URL.
    $query = $temp = $this->url->getQueryVariables();

    // Unset the signature and expires since those change per request.
    unset($temp['signature']);
    unset($temp['expires']);

    // Set the temporary query without the changing params.
    $this->url->setQueryVariables($temp);

    // Get the cache name based on the URL.
    $cache_name = md5($this->url->getURL());

    // Restore the original query variables.
    $this->url->setQueryVariables($query);

    // Call the parent.
    parent::set_cache_name($cache_name);
  }
}
?>
