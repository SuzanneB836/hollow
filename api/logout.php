<?php
// api/logout.php
require_once __DIR__ . '/helper.php';
session_unset();
session_destroy();
json_response(['success'=>true]);
