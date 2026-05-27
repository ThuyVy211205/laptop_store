<?php
$conn = new mysqli('localhost', 'root', '', 'laptop_store');
$result = $conn->query('SELECT id, name, slug FROM products');
$files = scandir('assets/images');
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $slug = $row['slug'];
    // Find best match in files
    $bestMatch = '';
    foreach ($files as $file) {
        if (strpos($file, '.webp') !== false && strpos($file, '.1.') === false && strpos($file, '.2.') === false && strpos($file, '.3.') === false) {
            // simple matching
            if (strpos($slug, str_replace('.webp', '', $file)) !== false || strpos(str_replace('.webp', '', $file), explode('-', $slug)[0]) !== false) {
                // very simple heuristics
            }
        }
    }
}
