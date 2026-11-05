<?php
function timeAgo($date) {
    $seconds = time() - strtotime($date);
    if ($seconds < 3600)  return floor($seconds / 60) . " min ago";
    if ($seconds < 86400) return floor($seconds / 3600) . " hours ago";
    return floor($seconds / 86400) . " days ago";
}

function summary($text) {
    return mb_strimwidth(strip_tags($text), 0, 110, "...");
}