<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit(); }
if ((int)($_SESSION['is_admin'] ?? 0) !== 1) { header('Location: ../books/index.php'); exit(); }

if (empty($_SESSION['is_admin'])) { header('Location: index.php'); exit(); }

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare('DELETE FROM books WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

header('Location: index.php?deleted=1');
exit();
