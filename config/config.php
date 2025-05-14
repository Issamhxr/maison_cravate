<?php
// Base URL configuration
// Using relative path for better compatibility
$base_url = './';

// Function to generate URLs
function url($path = '') {
    global $base_url;
    return $base_url . ltrim($path, '/');
}
?> 