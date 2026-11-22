<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$page_title  = 'Issue book';
$active_page = 'loans';

$today  = date('Y-m-d');
$data   = ['member_id' => 0, 'book_id' => 0, 'date_issued' => $today];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'member_id'   => int_param($_POST, 'member_id'),
        'book_id'     => int_param($_POST, 'book_id'),
        'date_issued' => trim((string) ($_POST['date_issued'] ?? '')),
    ];

    if (!verify_csrf()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }
    if ($data['member_id'] === 0) {
        $errors['member_id'] = 'Choose a member.';
    }
    if ($data['book_id'] === 0) {
        $errors['book_id'] = 'Choose a book.';
    }
    if (!is_valid_date($data['date_issued'])) {
        $errors['date_issued'] = 'Enter a valid date.';
    } elseif ($data['date_issued'] > $today) {
        $errors['date_issued'] = 'The issue date cannot be in the future.';
    }

    if (!$errors) {
        // Business rule 5: the due date is set automatically.
        $due_date = (new DateTime($data['date_issued']))->modify('+' . LOAN_DAYS . ' days')->format('Y-m-d');

        try {
            // Business rule 2: the loan and the copy count change together, or not at all.
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('SELECT full_name, status FROM members WHERE member_id = ?');
            $stmt->execute([$data['member_id']]);
            $member = $stmt->fetch();

            $stmt = $pdo->prepare('SELECT title FROM books WHERE book_id = ?');
            $stmt->execute([$data['book_id']]);
            $book = $stmt->fetch();

            if (!$member || !$book) {
                throw new RuntimeException('The member or the book could not be found.');
            }

            // Rule 4: a suspended member cannot borrow.
            if ($member['status'] !== 'active') {
                throw new RuntimeException($member['full_name'] . ' is suspended and cannot borrow books.');
            }

            // Rule 3: the same member cannot have the same book twice at the same time.
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM loans WHERE member_id = ? AND book_id = ? AND date_returned IS NULL');
            $stmt->execute([$data['member_id'], $data['book_id']]);
            if ($stmt->fetchColumn() > 0) {
                throw new RuntimeException($member['full_name'] . ' already has "' . $book['title'] . '" on loan and cannot borrow it twice at the same time.');
            }

            // Rule 1: no copies left, no loan. The "available_copies > 0" part is checked by the
            // database itself, so two librarians clicking at once cannot take the last copy twice.
            $stmt = $pdo->prepare('UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ? AND available_copies > 0');
            $stmt->execute([$data['book_id']]);
            if ($stmt->rowCount() === 0) {
                throw new RuntimeException('This book cannot be issued: "' . $book['title'] . '" has 0 copies available.');
            }

            $stmt = $pdo->prepare('INSERT INTO loans (member_id, book_id, date_issued, due_date) VALUES (?, ?, ?, ?)');
            $stmt->execute([$data['member_id'], $data['book_id'], $data['date_issued'], $due_date]);

            $pdo->commit();
            set_flash('success', '"' . $book['title'] . '" was issued to ' . $member['full_name'] . '. Due on ' . format_date($due_date) . '.');
            redirect('/loans/index.php');
        } catch (RuntimeException $rule) {
            // A business rule said no: undo everything and show the reason.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors['form'] = $rule->getMessage();
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Issue book error: ' . $ex->getMessage());
            $errors['form'] = 'Sorry, the book could not be issued. Please try again.';
        }
    }
}

// Lists for the two drop-downs
$members = [];
$books   = [];
try {
    $members = $pdo->query('SELECT member_id, member_number, full_name, status FROM members ORDER BY full_name')->fetchAll();
    $books   = $pdo->query('SELECT book_id, title, available_copies FROM books ORDER BY title')->fetchAll();
} catch (PDOException $ex) {
    error_log('Issue book lists error: ' . $ex->getMessage());
    $errors['form'] = $errors['form'] ?? 'Sorry, we could not load the members and books. Please try again later.';
}

$due_preview = (new DateTime(is_valid_date($data['date_issued']) ? $data['date_issued'] : $today))
    ->modify('+' . LOAN_DAYS . ' days')->format('Y-m-d');

require_once __DIR__ . '/../includes/header.php';
?>
<div class="head">
    <div>
        <h1>Issue book</h1>
        <p class="sub">Lend a book to a member</p>
    </div>
    <a class="btn alt" href="<?= BASE_URL ?>/loans/index.php">Back to loans</a>
</div>

<?php if (isset($errors['form'])): ?>
    <div class="alert err" role="alert"><?= e($errors['form']) ?></div>
<?php elseif ($errors): ?>
    <div class="alert err" role="alert">Please fix the problems below and try again.</div>
<?php endif; ?>

<form class="card form-card" method="post" action="">
    <?= csrf_field() ?>
    <div class="grid">
        <label for="member_id">Member
            <select id="member_id" name="member_id" required<?= err_class($errors, 'member_id') ?>>
                <option value="">Select member</option>
                <?php foreach ($members as $m): ?>
                    <option value="<?= (int) $m['member_id'] ?>"<?= $data['member_id'] === (int) $m['member_id'] ? ' selected' : '' ?>>
                        <?= e($m['member_number'] . ' · ' . $m['full_name'] . ($m['status'] !== 'active' ? ' (suspended)' : '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'member_id') ?: '<span class="hint">Only active members can borrow.</span>' ?>
        </label>
        <label for="book_id">Book
            <select id="book_id" name="book_id" required<?= err_class($errors, 'book_id') ?>>
                <option value="">Select book</option>
                <?php foreach ($books as $b): ?>
                    <option value="<?= (int) $b['book_id'] ?>"<?= $data['book_id'] === (int) $b['book_id'] ? ' selected' : '' ?>>
                        <?= e($b['title'] . ' · ' . (int) $b['available_copies'] . ' available') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'book_id') ?>
        </label>
        <label for="date_issued">Date issued
            <input id="date_issued" name="date_issued" type="date" max="<?= e($today) ?>" required value="<?= e($data['date_issued']) ?>"<?= err_class($errors, 'date_issued') ?>>
            <?= field_error($errors, 'date_issued') ?>
        </label>
        <label for="due_date">Due date
            <input id="due_date" type="date" value="<?= e($due_preview) ?>" data-days="<?= (int) LOAN_DAYS ?>" disabled>
            <span class="hint">Set automatically: <?= (int) LOAN_DAYS ?> days after the issue date.</span>
        </label>
    </div>
    <div class="form-actions">
        <button class="btn" type="submit">Issue book</button>
        <a class="btn alt" href="<?= BASE_URL ?>/loans/index.php">Cancel</a>
    </div>
</form>

<section class="card form-card" style="margin-top:20px;padding:20px 24px">
    <h2 style="margin-bottom:10px">Rules the system checks before issuing</h2>
    <ol class="rules">
        <li>The book must have at least one available copy.</li>
        <li>The member must be active, not suspended.</li>
        <li>The member must not already have this book on loan.</li>
        <li>The loan and the copy count update together, or not at all.</li>
    </ol>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
