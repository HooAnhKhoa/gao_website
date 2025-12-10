<?php
// includes/init.php

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load constants
require_once dirname(__DIR__) . '/config/constants.php';

// Load database class - TRƯỚC functions.php
require_once __DIR__ . '/db.php';

// Load functions - SAU db.php
require_once __DIR__ . '/functions.php';