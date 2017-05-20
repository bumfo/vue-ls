<?php

require 'common.php';

function ls($cd, &$err = 0) {
  if (!file_exists($cd)) {
    $err = 404;
    $ls = ['(not found)'];
  } else if (!is_readable($cd)) {
    $err = 403;
    $ls = ['(no access)'];
  } else if (!is_executable($cd)) {
    $err = 403;
    $ls = ['(no list)'];
  } else if (strpos(basename($cd), '.') === 0) {
    $err = 403;
    $ls = ['(no visibility)'];
  } else {
    $err = 0;

    $ocd = getcwd();
    chdir($cd);

    $ls = scandir($cd, SCANDIR_SORT_ASCENDING);

    foreach ($ls as $key => $value) {
      if ($value === 'index.php' || $value === 'index.html') {
        $err = 302;
        $ls = ['(has default)'];
        break;
      } else {
        continue;
      }
    }
  }

  if ($err === 0) {
    $ls = array_filter($ls, function ($x) {
      return strpos($x, '.') !== 0 && is_readable($x);
    });

    $dirs = array_filter($ls, function ($x) {
      return is_dir($x) && is_executable($x);
    });
    $files = array_filter($ls, function ($x) {
      return is_file($x);
    });
    $ls = array_merge(array_map(function ($x) {
      return $x . '/';
    }, $dirs), $files);

    $is_root = $cd === __ROOT__;
    if (!$is_root) {
      array_unshift($ls, '../');
    }

    chdir($ocd);

    return $ls;
  } else {
    $is_root = $cd === __ROOT__;
    if (!$is_root) {
      array_unshift($ls, '..');
    }

    return $ls;
  }
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

function check_dir($cd) {
  if (!is_dir($cd)) {
    return false;
  } else if (!is_readable($cd)) {
    return false;
  } else if (!is_executable($cd)) {
    return false;
  } else if (strpos(basename($cd), '.') === 0) {
    return false;
  } else {
    return true;
  }
}

function die_auto_cache($filename, $cache_filename) {
  die_if_not_modified($filename);

  if (file_exists($cache_filename)) {
    echo file_get_contents($cache_filename);
    die;
  } else {
    ob_start();
    require $filename;
    $content = ob_get_contents();
    ob_end_clean();
    if (is_writable_or_creatable($cache_filename)) {
      file_put_contents($cache_filename, $content);
      echo $content;
      die;
    } else {
      return false;
    }
  }
}

if (!isset($_GET['d'])) {
  $cd = realpath(__ROOT__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

  if (check_dir($cd)) {
    die_auto_cache('ls_front.php', './cache/ls.html');
    die_code(403);
  } else {
    die_code(404);
  }
}

$cd = realpath(__ROOT__ . $_GET['d']);

if (!check_dir($cd)) {
  die_code(404);
}

$err = 0;
$ls = ls($cd, $err);

if ($err !== 0) {
  die_code($err);
}

$dir = substr($cd, strlen(__ROOT__)) . '/';

$obj = array_map(function ($u) use ($dir) {
  $href = normalize_path($dir . $u);
  $query = '';

  if (!$href) {
    $href = $dir;
  }

  // if (pathinfo($href, PATHINFO_EXTENSION) === 'js') {
  //   $href = '/test.php' . $href;
  //   $query = 'w=1';
  // }

  return [
    'href' => build_href($href, $query),
    'title' => $u,
  ];
}, $ls);

header('Content-Type: text/plain; charset=UTF-8');
echo json_encode($obj);

die;
