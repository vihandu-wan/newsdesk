<?php
session_start();
require_once __DIR__ . '/../db.php';
const NEW_COMMENT_STATUS='approved';
const MAX_COMMENT_LENGTH = 1000;
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: ../index.php');
    exist;
}

