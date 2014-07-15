<?php
date_default_timezone_set('America/New_York');
set_include_path(dirname(__DIR__) . "/app");
require_once("core/comet.php");
Comet::run();