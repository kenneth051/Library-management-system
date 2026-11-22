<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Only a POST with a valid CSRF token can mark a loan as returned.
require_post('/loans/index.php');

$id = int_param($_POST, 'id');

try {
    // Business rule 2: the loan and the copy count change together, or not at all.
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT book_id FROM loans WHERE loan_id = ?');
    $stmt->execute([$id]);
    $loan = $stmt->fetch();

    if (!$loan) {
        throw new RuntimeException('That loan could not be found.');
    }

    // "date_returned IS NULL" in the WHERE makes sure a loan can only be returned once,
    // even if the button is clicked twice.
    $stmt = $pdo->prepare('UPDATE loans SET date_returned = ? WHERE loan_id = ? AND date_returned IS NULL');
    $stmt->execute([date('Y-m-d'), $id]);
    if ($stmt->rowCount() === 0) {
        throw new RuntimeException('This book has already been returned.');
    }

    $stmt = $pdo->prepare('UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?');
    $stmt->execute([$loan['book_id']]);

    $pdo->commit();
    set_flash('success', 'Book returned successfully. Available copies updated.');
} catch (RuntimeException $rule) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', $rule->getMessage());
} catch (PDOException $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Return book error: ' . $ex->getMessage());
    set_flash('error', 'Sorry, the book could not be marked as returned. Please try again.');
}

redirect('/loans/index.php');
