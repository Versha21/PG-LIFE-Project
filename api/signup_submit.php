<?php
session_start();

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

// Delegate to existing signup handler in login_project
require_once __DIR__ . '/../login_project/login_submit.php';

// The included file emits JSON and handles DB operations.
exit;
