<?php
require_once 'includes/init.php';

$db = Database::getInstance();

// Get categories with images
$categories = $db->select("SELECT id, name, image FROM categories WHERE image IS NOT NULL AND image != ''");

echo "<h3>Categories with images:</h3>";
foreach ($categories as $category) {
    $imagePath = 'assets/images/categories/' . $category['image'];
    $exists = file_exists($imagePath) ? 'EXISTS' : 'NOT FOUND';
    $url = SITE_URL . '/assets/images/categories/' . $category['image'];
    
    echo "<p>";
    echo "<strong>{$category['name']}</strong><br>";
    echo "Image: {$category['image']} - {$exists}<br>";
    echo "Path: {$imagePath}<br>";
    echo "URL: <a href='{$url}' target='_blank'>{$url}</a>";
    echo "</p>";
}

if (empty($categories)) {
    echo "<p>No categories with images found.</p>";
}
?>