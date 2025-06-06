<?php
class Auth {
  public static function check() {
    $headers = apache_request_headers();
    return isset($headers['Authorization']) && $headers['Authorization'] === 'Bearer Raul12345';
  }
}
