<?php

  # Shared-Secrets v0.20b2
  #
  # Copyright (c) 2016-2019, SysEleven GmbH
  # All rights reserved.
  #
  # This page allows you to share a secret through a secret sharing link.
  # The secret is stored in the secret sharing link and not on the server.
  # A secret sharing link can only be used once.
  #
  # The actions can be found in ./actions/<name>.php
  #
  # The configuration can be found in ./config.php
  #
  # The pages can be found in ./pages/<name>/get.php and ./pages/<name>/post.php
  #
  # The template can be found in ./template/header.php and ./template/footer.php

  # prevent direct access
  define("SYS11_SECRETS", true);

  # store the __DIR__ constant in an additional constant
  # so that is does not change between script files
  define("ROOT_DIR", __DIR__);

  # include required defines
  require_once(ROOT_DIR."/lib/shared-secrets.def.php");

  # include required execution functions
  require_once(ROOT_DIR."/lib/shared-secrets.exec.php");

  # include required configuration
  require_once(ROOT_DIR."/config/config.php");

  # prepare debug mode
  if (!defined("DEBUG_MODE")) {
    define("DEBUG_MODE", false);
  }
  if (DEBUG_MODE) {
    error_reporting(E_ALL | E_STRICT | E_NOTICE);
  } else {
    error_reporting(0);
  }
  ini_set("display_errors",         (DEBUG_MODE) ? 1 : 0);
  ini_set("display_startup_errors", (DEBUG_MODE) ? 1 : 0);
  ini_set("html_errors",            (DEBUG_MODE) ? 1 : 0);
  ini_set("track_errors",           (DEBUG_MODE) ? 1 : 0);

  # set default timezone because PHP dislikes to use system defaults
  date_default_timezone_set(DEFAULT_TIMEZONE);

  # prepare read-only mode
  if (!defined("READ_ONLY")) {
    define("READ_ONLY", false);
  }

  # prepare request method
  define("REQUEST_METHOD", strtolower($_SERVER["REQUEST_METHOD"]));

  # prepare secret param
  $param = null;
  if (isset($_POST[SECRET_PARAM_NAME])) {
    if (!empty($_POST[SECRET_PARAM_NAME])) {
      $param = $_POST[SECRET_PARAM_NAME];
    }
  }
  define("SECRET_PARAM", $param);

  # prepare plain param
  $param = null;
  if (isset($_POST[PLAIN_PARAM_NAME])) {
    $param = true;
  }
  define("PLAIN_PARAM", $param);

  # prepare URI
  $uri = $_SERVER["REQUEST_URI"];
  # handle URL encoded URIs
  if (false !== strpos($uri, URL_ENCODE_MARKER)) {
    $uri = urldecode($uri);
  }
  # kill request parameters
  $uri = parse_url($uri, PHP_URL_PATH);
  # remove leading slash
  if (0 === stripos($uri, "/")) {
    $uri = substr($uri, 1);
  }
  define("SECRET_URI", $uri);

  # prepare action name, show read page by default
  $action = READ_PAGE_NAME;
  # show share page if no URI is given
  if (empty(SECRET_URI)) {
    $action = SHARE_PAGE_NAME;
  } else {
    # show pages based on page URI
    if (in_array(SECRET_URI, array(HOW_PAGE_NAME, IMPRINT_PAGE_NAME, PUB_PAGE_NAME))) {
      $action = SECRET_URI;
    }
  }
  define("SECRET_ACTION", $action);

  # only proceed when a GET or POST request is encountered
  if (in_array(REQUEST_METHOD, array("get", "post"))) {
    # import actions based on action name
    require_once(ROOT_DIR."/actions/".SECRET_ACTION.".php");

    # import pages based on action name and request method
    require_once(ROOT_DIR."/pages/".SECRET_ACTION."/".REQUEST_METHOD.".php");
  } else {
    # return a corresponding result code
    http_response_code(405);
    header("Allow: GET, POST");
  }

