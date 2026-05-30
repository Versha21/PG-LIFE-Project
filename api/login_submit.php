<?php
session_start();

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

// Delegate to existing login handler in login_project
require_once __DIR__ . '/../login_project/login_form.php';

// The included file emits JSON and handles the session.
exit;
