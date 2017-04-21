<?php
define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);

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

function ls($cd = null) {
  $ocd = getcwd();
  if ($cd === null) $cd = $ocd;
  if (!file_exists($cd)) {
    return $ls = [0 => '..', 1 => '(not found)'];
  }

  chdir($cd);

  $ls = scandir($cd, SCANDIR_SORT_ASCENDING);
  if (!$ls) {
    return $ls = [0 => '..', 1 => '(no access)'];
  }

  $is_root = $cd === __ROOT__;
  $dirs = array_filter($ls, function ($x) use ($is_root) {
    return $x !== '.' && !($is_root && $x === '..') && is_dir($x);
  });
  $files = array_filter($ls, function ($x) {
    return strpos($x, '.') !== 0 && is_file($x) & $x !== '-i';
  });
  $ls = array_merge(array_map(function ($x) {
    return $x . '/';
  }, $dirs), $files);

  chdir($ocd);

  return $ls;
}

function normalize_path($path) {
  $realpath = realpath(__ROOT__ . $path);

  if (!$realpath) {
    return false;
  }

  if (strpos($realpath, __ROOT__) !== 0) {
    return false;
  } else {
    $path = substr($realpath, strlen(__ROOT__));
    if (is_dir($realpath)) {
      return $path . '/';
    }
    return $path;
  }
}

function build_href($href, $query = '') {
  if ($query !== '') {
    $query = '?' . $query;
  }

  return join(array_map(function ($u) {return rawurlencode($u);}, explode('/', $href)), '/') . $query;
}

if (isset($_GET['d'])) {
  $cd = realpath(__ROOT__ . $_GET['d']);
} else {
  die_code(404);
  // $cd = realpath(__ROOT__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
}

if (!is_dir($cd)) {
  $cd = getcwd();
}

$ls = ls($cd);
$dir = substr($cd, strlen(__ROOT__)) . '/';

$obj = array_map(function ($u) use ($dir) {
  $href = normalize_path($dir . $u);
  $query = '';

  if (!$href) {
    $href = '#';
  }

  if (pathinfo($href, PATHINFO_EXTENSION) === 'js') {
    $href = '/test.php' . $href;
    $query = 'w=1';
  }

  return [
    'href' => build_href($href, $query),
    'title' => $u,
  ];
}, $ls);

header('Content-Type: text/plain; charset=UTF-8');
echo json_encode($obj);

die;
