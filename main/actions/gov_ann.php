<?php
require_once($sr_root . "/db/db.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $port = Database::getConnection('port');

    // Cache file path
    $cacheFile = 'cache/legal.json'; // The path to store the cache file
    $cacheTime = 60 * 60; // Cache expiry time in seconds (1 hour)

    // Check if the cache file exists and is still valid
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        // Load data from cache
        $data = json_decode(file_get_contents($cacheFile), true);
    } else {
        // Fetch data from the database
        $currentYear = date('Y');
        $stmt = $port->prepare("SELECT * FROM tbl_announcement a
            LEFT JOIN tbl201_basicinfo b ON a.ann_postby = b.bi_empno
            WHERE a.ann_type = 'GOVERNMENT'
            AND DATE_FORMAT(a.ann_timestatmp, '%Y') = ?
            ORDER BY a.ann_id DESC");
        $stmt->execute([$currentYear]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Save the fetched data into the cache file as JSON
        file_put_contents($cacheFile, json_encode($data));
    }

    // Start of the carousel HTML
    echo '
    <style>
        .carousel-inner {
          height: 300px;
          transition: height 0.4s ease;
        }

        .carousel-inner:hover {
          height: 100%;
        }

        .carousel-inner img {
          height: 100%;
          width: 100%;
          object-fit: cover;
        }
    </style>
    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
        <ol class="carousel-indicators">';

    // Generate carousel indicators
    if ($data) {
        $active_class = 'active';
        foreach ($data as $index => $image) {
            echo '<li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="' . $index . '" class="' . $active_class . '"></li>';
            $active_class = ''; // Only the first indicator should be active
        }
    }

    echo '</ol>
        <div class="carousel-inner">';

    // Loop through the data and generate carousel items
    $active_class = 'active';
    foreach ($data as $image) {
        echo '<div class="carousel-item ' . $active_class . '">';
        echo '<img src="' . htmlspecialchars($image["ann_content"]) . '" class="d-block w-100" alt="Slide ' . htmlspecialchars($image["ann_id"]) . '">';
        echo '<div class="carousel-caption">';
        echo '<h5>Slide ' . htmlspecialchars($image["ann_id"]) . '</h5>';
        echo '</div>';
        echo '</div>';
        $active_class = ''; // Only the first item should be active
    }

    echo '</div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </a>
    </div>
    ';

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
