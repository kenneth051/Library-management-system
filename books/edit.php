<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$page_title   = 'Edit book';
$active_page  = 'books';
$submit_label = 'Update book';

$id   = int_param($_GET, 'id');
$book = false;

try {
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT isbn, title, author_id, category_id, publisher, year_published, total_copies, available_copies FROM books WHERE book_id = ?');
        $stmt->execute([$id]);
        $book = $stmt->fetch();
    }
} catch (PDOException $ex) {
    error_log('Edit book load error: ' . $ex->getMessage());
}

if (!$book) {
    set_flash('error', 'That book could not be found.');
    redirect('/books/index.php');
}

$on_loan = (int) $book['total_copies'] - (int) $book['available_copies'];
$data    = $book;
$data['publisher']      = (string) $data['publisher'];
$data['year_published'] = (string) $data['year_published'];
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$data, $errors] = validate_book($pdo, $_POST, $id);

    if (!verify_csrf()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }

    // Total copies can never be lower than the copies currently out on loan.
    if (!isset($errors['total_copies']) && (int) $data['total_copies'] < $on_loan) {
        $errors['total_copies'] = $on_loan . ($on_loan === 1 ? ' copy is' : ' copies are') . ' on loan, so the total cannot be lower than ' . $on_loan . '.';
    }

    if (!$errors) {
        try {
            // Available copies move by the same amount as the total, so copies on loan stay correct.
            $stmt = $pdo->prepare(
                'UPDATE books
                 SET isbn = ?, title = ?, author_id = ?, category_id = ?, publisher = ?, year_published = ?,
                     available_copies = ? , total_copies = ?
                 WHERE book_id = ?'
            );
            $stmt->execute([
                $data['isbn'], $data['title'], (int) $data['author_id'], (int) $data['category_id'],
                $data['publisher'] !== '' ? $data['publisher'] : null,
                $data['year_published'] !== '' ? (int) $data['year_published'] : null,
                (int) $data['total_copies'] - $on_loan, (int) $data['total_copies'], $id,
            ]);
            set_flash('success', 'Book updated successfully.');
            redirect('/books/index.php');
        } catch (PDOException $ex) {
            error_log('Edit book error: ' . $ex->getMessage());
            $errors['form'] = $ex->getCode() === '23000'
                ? 'A book with this ISBN already exists.'
                : 'Sorry, the book could not be updated. Please try again.';
        }
    }
}

$authors = $categories = [];
try {
    $authors    = $pdo->query('SELECT author_id, name FROM authors ORDER BY name')->fetchAll();
    $categories = $pdo->query('SELECT category_id, name FROM categories ORDER BY name')->fetchAll();
} catch (PDOException $ex) {
    error_log('Edit book lists error: ' . $ex->getMessage());
    $errors['form'] = $errors['form'] ?? 'Sorry, we could not load the authors and categories.';
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="head">
    <div>
        <h1>Edit book</h1>
        <p class="sub">Change the details of <?= e($book['title']) ?> (<?= $on_loan ?> on loan)</p>
    </div>
    <a class="btn alt" href="<?= BASE_URL ?>/books/index.php">Back to books</a>
</div>

<?php if (isset($errors['form'])): ?>
    <div class="alert err" role="alert"><?= e($errors['form']) ?></div>
<?php elseif ($errors): ?>
    <div class="alert err" role="alert">Please fix the problems below and try again.</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/book_form.php'; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
