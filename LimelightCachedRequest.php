<?php

require_once 'lib/CachedRequest/HTTP/FileCachedRequest.php';

class LimelightCachedRequest extends HTTP_FileCachedRequest {

  /**
   * Override the get_cache_name function to rip out the authentication
   * parameters from the request URL.  This is so that GET methods that require
   * authentication do not cause a cache miss since the URL will be different
   * for each request on authentication requests.
   *
   * @return HTTP_CachedResponse
   */
  public function get_cache_name() {

    // Get the query variables from the URL.
    $query = $temp = $this->url->getQueryVariables();

    // Is this a signed request?
    if (isset($temp['signature'])) {

      // Unset the authentication parameters from teh query.
      unset($temp['access_key']);
      unset($temp['signature']);
      unset($temp['expires']);

      // Set the temporary query without the changing params.
      $this->url->setQueryVariables($temp);
    }

    // Get the cache name from the parent.
    $cache_name = parent::get_cache_name();

    // Restore the original query variables.
    $this->url->setQueryVariables($query);

    // Return the cache_name.
    return $cache_name;
  }
}
?>
