<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../models/Pet.php';

$ownerId = require_auth();

send_success(['pets' => Pet::listByOwner($ownerId)]);
