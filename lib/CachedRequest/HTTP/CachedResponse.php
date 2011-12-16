<?php

/**
 * This is a clever wrapper class that is used to act like a Reponse class
 * with the getBody method, but instead calls into the CachedRequest class
 * to return cache if it exists.
 */
class HTTP_CachedResponse {

  protected $request = null;
  protected $response = null;

  // Construct a new CachedResponse object.
  function __construct($request, $response) {
    $this->request = $request;
    $this->response = $response;
  }

  /** Implement other functions in the Response.php class. */
  public function parseHeaderLine($headerLine) {$this->response->parseHeaderLine($headerLine);}
  public function appendBody($bodyChunk) {$this->response->appendBody($bodyChunk);}
  public function getEffectiveUrl() {return $this->response->getEffectiveUrl();}
  public function getStatus() {return $this->response->getStatus();}
  public function getReasonPhrase() {return $this->response->getReasonPhrase();}
  public function isRedirect() {return $this->response->isRedirect();}
  public function getHeader($headerName = null) {return $this->response->getHeader($headerName);}
  public function getCookies() {return $this->response->getCookies();}
  public function getVersion() {return $this->response->getVersion();}
  public static function decodeGzip($data) {return HTTP_Request2_Response::decodeGzip($data);}
  public static function decodeDeflate($data) {return HTTP_Request2_Response::decodeDeflate($data);}
  public static function getDefaultReasonPhrase($code = null) {return HTTP_Request2_Response::getDefaultReasonPhrase($code);}


  /**
   * Creating the getBody method for caching.
   *
   * @return type
   */
  public function getBody() {

    // Default the response to nothing...
    $response = '';

    /**
     * If we should cache and the cache is valid, then go ahead
     * and return the cache response.
     */
    if ($this->request->should_cache() && $this->request->cache_valid()) {
      $response = $this->request->get_cache();
    }

    // If there isn't a response for the cache, then just get the
    // body and cache it.
    if (!$response && $this->response) {
      $response = $this->response->getBody();
      if ($response && $this->request->should_cache()) {
        $this->request->cache_response($response);
      }
    }

    // Return the response.
    return $response;
  }
}
?>
