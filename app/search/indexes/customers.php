<?php
# Set up to run in Comet
date_default_timezone_set('America/New_York');
set_include_path(dirname(dirname(__DIR__)) . "/");
require_once("core/comet.php");
Comet::init();

$res = Db::query("SELECT * FROM customers");
if ($res->success && isset($res->results[0])) {
    echo json_encode($res->results);
}


