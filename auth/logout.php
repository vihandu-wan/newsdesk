<?php
require_once __DIR__ . '/../includes/init.php';

$_SESSION = [];
session_regenerate_id(true);
flash('success', 'You have been logged out.');
redirect('index.php');
