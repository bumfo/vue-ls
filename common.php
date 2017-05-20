<?php
define('__ROOT__', realpath($_SERVER['DOCUMENT_ROOT']));
define('__APP__', __DIR__);
define('__PUB__', public_path(__DIR__));

function is_writable_or_creatable($filename) {
  if (file_exists($filename)) {
    return is_writable($filename);
  } else {
    return is_writable(dirname($filename));
  }
}

function die_if_not_modified($filename) {
  $last_modified_time = filemtime($filename);

  if (!isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $client_time = 0;
  } else {
    $client_time = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
  }

  if ($client_time >= $last_modified_time) {
    header('HTTP/1.1 304 Not Modified');
    exit;
  } else {
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified_time) . ' GMT');
    return;
  }
}

function public_path($realpath) {
  if (strpos($realpath, __ROOT__) !== 0) {
    return false;
  } else {
    $path = substr($realpath, strlen(__ROOT__));
    return $path;
  }
}

function die_code($code = 404, $message = '') {
  function mylowerstr($s) {
    $l = strlen($s);
    for ($i = 1; $i < $l - 1; $i++) {
      $c = $s{$i - 1} . $s{$i} . $s{$i + 1};

      if (preg_match('/[^a-zA-Z][A-Z][a-z]/', $c)) {
        $s{$i} = strtolower($s{$i});
      }

    }
    return $s;
  }

  $codes = [
    100 => 'Continue',
    101 => 'Switching Protocols',

    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',

    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    306 => '(Unused)',
    307 => 'Temporary Redirect',

    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Requested Range Not Satisfiable',
    417 => 'Expectation Failed',

    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
  ];

  if (isset($codes[$code])) {
    $msg = $codes[$code];
  } else {
    $msg = 'Internal Server Error (' . $code . ')';
  }
  $s = mylowerstr($msg);
  $title = $code . ' ' . $msg;

  header('HTTP/1.1 ' . $title);
  die('<!DOCTYPE html><title>' . $title . '</title><meta name=viewport content="width=100">' . $s . '. ' . $message);
}
