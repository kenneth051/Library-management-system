<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$page_title  = 'Loans';
$active_page = 'loans';

$search = get_string('q');
$status = get_string('status');
if (!in_array($status, ['on_loan', 'overdue', 'returned'], true)) {
    $status = '';
}

$today = date('Y-m-d');
$loans = [];
$total = 0;
$error = null;
$page  = page_number();

try {
    $where  = [];
    $params = [];
    if ($search !== '') {
        $like     = '%' . $search . '%';
        $where[]  = '(m.full_name LIKE ? OR b.title LIKE ?)';
        $params[] = $like;
        $params[] = $like;
    }
    if ($status === 'returned') {
        $where[] = 'l.date_returned IS NOT NULL';
    } elseif ($status === 'on_loan') {
        $where[] = 'l.date_returned IS NULL';
    } elseif ($status === 'overdue') {
        $where[]  = 'l.date_returned IS NULL AND l.due_date < ?';
        $params[] = $today;
    }
    $sql_where = $where ? ' WHERE ' . implode(' AND ', $where) : '';

    $from = ' FROM loans l
              JOIN members m ON m.member_id = l.member_id
              JOIN books b   ON b.book_id   = l.book_id';

    $stmt = $pdo->prepare('SELECT COUNT(*)' . $from . $sql_where);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    $pages  = max(1, (int) ceil($total / PER_PAGE));
    $page   = min($page, $pages);
    $offset = ($page - 1) * PER_PAGE;

    // PER_PAGE and $offset are whole numbers made by the code above, never user text.
    $stmt = $pdo->prepare(
        'SELECT l.loan_id, m.full_name AS member_name, b.title, l.date_issued, l.due_date, l.date_returned'
        . $from . $sql_where
        . ' ORDER BY l.date_issued DESC, l.loan_id DESC LIMIT ' . PER_PAGE . ' OFFSET ' . $offset
    );
    $stmt->execute($params);
    $loans = $stmt->fetchAll();
} catch (PDOException $ex) {
    error_log('Loans list error: ' . $ex->getMessage());
    $error = 'Sorry, we could not load the loans. Please try again later.';
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="head">
    <div>
        <h1>Loans management</h1>
        <p class="sub">Issue and track book loans</p>
    </div>
    <a class="btn" href="<?= BASE_URL ?>/loans/issue.php"><svg class="ic"><use href="#i-plus"/></svg>Issue book</a>
</div>

<?php show_flash(); ?>
<?php if ($error): ?>
    <div class="alert err" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<section class="card">
    <form class="filters" method="get" action="">
        <div class="sbox">
            <svg class="ic"><use href="#i-search"/></svg>
            <label for="q" style="position:absolute;left:-9999px">Search</label>
            <input id="q" name="q" type="search" placeholder="Search by member or book" value="<?= e($search) ?>">
        </div>
        <label for="status" style="flex:1 1 180px">
            <span style="position:absolute;left:-9999px">Loan status</span>
            <select id="status" name="status">
                <option value="">All loans</option>
                <option value="on_loan"<?= $status === 'on_loan' ? ' selected' : '' ?>>On loan</option>
                <option value="overdue"<?= $status === 'overdue' ? ' selected' : '' ?>>Overdue</option>
                <option value="returned"<?= $status === 'returned' ? ' selected' : '' ?>>Returned</option>
            </select>
        </label>
        <button class="btn" type="submit">Search</button>
        <?php if ($search !== '' || $status !== ''): ?>
            <a class="btn alt" href="<?= BASE_URL ?>/loans/index.php">Reset</a>
        <?php endif; ?>
    </form>

    <div class="tw">
        <table>
            <thead>
                <tr><th>#</th><th>Member</th><th>Book</th><th>Issued</th><th>Due</th><th>Returned</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php if (!$loans): ?>
                <tr><td colspan="8" class="empty">No loans found.</td></tr>
            <?php endif; ?>
            <?php foreach ($loans as $loan): ?>
                <?php [$status_text, $status_class] = loan_status($loan, $today); ?>
                <tr>
                    <td><?= (int) $loan['loan_id'] ?></td>
                    <td><b><?= e($loan['member_name']) ?></b></td>
                    <td><?= e($loan['title']) ?></td>
                    <td><?= e(format_date($loan['date_issued'])) ?></td>
                    <td><?= e(format_date($loan['due_date'])) ?></td>
                    <td><?= $loan['date_returned'] ? e(format_date($loan['date_returned'])) : 'Not yet' ?></td>
                    <td><span class="badge <?= $status_class ?>"><?= e($status_text) ?></span></td>
                    <td class="act">
                        <?php if (!$loan['date_returned']): ?>
                            <form class="inline" method="post" action="<?= BASE_URL ?>/loans/return.php" data-confirm="Mark &quot;<?= e($loan['title']) ?>&quot; as returned by <?= e($loan['member_name']) ?>?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $loan['loan_id'] ?>">
                                <button class="ib e" type="submit" aria-label="Mark <?= e($loan['title']) ?> as returned"><svg class="ic"><use href="#i-ret"/></svg></button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= render_pager($total, $page, 'loans', ['q' => $search, 'status' => $status]) ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
