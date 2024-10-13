<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT * FROM posts WHERE user_id = ?");
$query->bind_param('i', $user_id);
$query->execute();
$posts = $query->get_result();
?>

<ul>
<?php while ($post = $posts->fetch_assoc()): ?>
    <li>
        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
        <p><?php echo htmlspecialchars($post['content']); ?></p>
        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Изображение">
    </li>
<?php endwhile; ?>
</ul>

