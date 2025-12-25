<?php
require_once '../config/db.php';
header('Content-Type: application/json');

// 1. WordPress REST API Endpoint (Latest 10 posts)
$api_url = "https://accrachic.com/wp-json/wp/v2/posts?per_page=10&_embed";

try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'AccraChic-Affiliate-Scanner');
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) throw new Exception("Could not connect to blog API.");

    $posts = json_decode($response, true);
    $new_count = 0;

    foreach ($posts as $post) {
        $title = $post['title']['rendered'];
        $slug  = $post['slug'];
        $image = $post['_embedded']['wp:featuredmedia'][0]['source_url'] ?? '';

        // Check if post already exists in our affiliate DB
        $stmt = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ?");
        $stmt->execute([$slug]);

        if ($stmt->rowCount() == 0) {
            // Add new post with default GHS 0.02 rate
            $ins = $pdo->prepare("INSERT INTO blog_posts (title, slug, image_url, rate_per_view, status) VALUES (?, ?, ?, 0.02, 'active')");
            $ins->execute([$title, $slug, $image]);
            $new_count++;
        }
    }

    echo json_encode(['success' => true, 'new_posts' => $new_count]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
