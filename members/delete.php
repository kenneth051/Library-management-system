<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit(); }
if (empty($_SESSION['is_admin'])) { header('Location: ../books/index.php'); exit(); }

require_once __DIR__ . '/../config/database.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    $stmt = $conn->prepare('SELECT full_name FROM users WHERE id = ? AND is_admin = 0');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();

    if (!$member) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Member not found.'];
    } else {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM book_loans WHERE user_id = ? AND return_date IS NULL');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $unreturned = (int)$stmt->get_result()->fetch_row()[0];

        if ($unreturned > 0) {
            $_SESSION['flash'] = ['type' => 'error', 'text' => $member['full_name'] . ' cannot be deleted because '
                . $unreturned . ($unreturned === 1 ? ' book has' : ' books have') . ' not been returned yet.'];
        } else {
            try {
                $stmt = $conn->prepare('DELETE FROM users WHERE id = ? AND is_admin = 0');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $_SESSION['flash'] = ['type' => 'success', 'text' => 'Member deleted successfully.'];
            } catch (mysqli_sql_exception $e) {
                $_SESSION['flash'] = ['type' => 'error', 'text' => $member['full_name'] . ' cannot be deleted because they have loan history.'];
            }
        }
    }
}

header('Location: index.php');
exit();
