<?php
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'card_html' => ''];

// Validate required fields
if (empty($_POST['name']) || empty($_POST['request_about']) || empty($_POST['rating']) || empty($_POST['review_text'])) {
    $response['message'] = 'All fields except image are required.';
    echo json_encode($response);
    exit;
}

$name = trim($_POST['name']);
$request_about = trim($_POST['request_about']);
$rating = (int)$_POST['rating'];
$review_text = trim($_POST['review_text']);
$image_path = null;

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/uploads/reviews/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('review_') . '.' . $ext;
    $destination = $upload_dir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
        $image_path = 'uploads/reviews/' . $filename;
    }
}

// Insert into DB
$stmt = $conn->prepare("INSERT INTO reviews (name, request_about, rating, review_text, image_path) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiss", $name, $request_about, $rating, $review_text, $image_path);
if ($stmt->execute()) {
    $new_id = $stmt->insert_id;
    $created_at = date('Y-m-d H:i:s');
    $date_formatted = date('M j, Y', strtotime($created_at));
    $short_text = strlen($review_text) > 280 ? substr($review_text,0,277).'…' : $review_text;
    $img = $image_path ?: 'top1.webp';

    // Generate the HTML for the new card
    ob_start();
    ?>
    <article class="review-card"
        data-id="<?= $new_id ?>"
        data-name="<?= htmlspecialchars($name) ?>"
        data-role="<?= htmlspecialchars($request_about) ?>"
        data-rating="<?= $rating ?>"
        data-date="<?= $created_at ?>"
        data-img="<?= htmlspecialchars($img) ?>"
        data-text="<?= htmlspecialchars($review_text) ?>">
        <div class="review-avatar"><img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>"></div>
        <div class="review-body">
            <div class="review-name"><?= htmlspecialchars($name) ?></div>
            <div class="review-meta">
                <span class="stars" aria-hidden="true">
                    <?php for($i=1;$i<=5;$i++): ?>
                        <span class="star"><?= $i <= $rating ? '★' : '☆' ?></span>
                    <?php endfor; ?>
                </span>
                <span> • </span>
                <small><?= htmlspecialchars($request_about) ?></small>
                <span> • </span>
                <small><?= $date_formatted ?></small>
            </div>
            <div class="review-text"><?= htmlspecialchars($short_text) ?></div>
            <div class="review-actions">
                <button class="btn-inline read-more" data-bs-toggle="modal" data-bs-target="#reviewModal">Read more</button>
                <button class="btn-inline helpful" data-id="<?= $new_id ?>">Helpful</button>
            </div>
        </div>
    </article>
    <?php
    $response['card_html'] = ob_get_clean();
    $response['success'] = true;
    $response['message'] = 'Review added successfully.';
} else {
    $response['message'] = 'Database error: ' . $stmt->error;
}
$stmt->close();
$conn->close();
echo json_encode($response);