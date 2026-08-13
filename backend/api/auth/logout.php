<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';

start_session_if_needed();
$_SESSION = [];
session_destroy();

send_success(['logged_out' => true]);
