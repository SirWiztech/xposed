<?php
/**
 * XPOSED Admin — FAQ management (list, edit, delete)
 */

require __DIR__ . '/_guard.php';
require_admin();

$flash = '';
$flashType = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        Faq::delete((int)($_POST['id'] ?? 0));
        $flash = 'FAQ deleted.';
    } elseif ($action === 'add') {
        Faq::create([
            'question' => trim((string)($_POST['question'] ?? '')),
            'keywords' => trim((string)($_POST['keywords'] ?? '')),
            'answer'   => (string)($_POST['answer'] ?? ''),
            'category' => trim((string)($_POST['category'] ?? 'General')),
            'sort'     => (int)($_POST['sort'] ?? 0),
        ]);
        $flash = 'FAQ added.';
    } elseif ($action === 'update') {
        Faq::update((int)($_POST['id'] ?? 0), [
            'question' => trim((string)($_POST['question'] ?? '')),
            'keywords' => trim((string)($_POST['keywords'] ?? '')),
            'answer'   => (string)($_POST['answer'] ?? ''),
            'category' => trim((string)($_POST['category'] ?? 'General')),
            'sort'     => (int)($_POST['sort'] ?? 0),
        ]);
        $flash = 'FAQ updated.';
    }
}

$faqs = Faq::all();

$adminTitle  = 'FAQ';
$adminActive = 'faqs';
$adminFlash  = $flash;
$adminFlashType = $flashType;
include __DIR__ . '/_header.php';
?>

<div class="admin-toolbar">
  <div>
    <p class="page-eyebrow">Support</p>
    <h1>FAQ <span class="accent">Answers</span></h1>
  </div>
</div>

<div class="card">
  <h2>Add answer</h2>
  <form method="post" action="<?= e(url('admin/faqs.php')) ?>" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="field"><label>Question</label><input type="text" name="question" required></div>
    <div class="field"><label>Category</label><input type="text" name="category" value="General"></div>
    <div class="field full"><label>Keywords (comma-separated — what the matcher looks for)</label><input type="text" name="keywords" placeholder="schedule, when, time, live"></div>
    <div class="field full"><label>Answer</label><textarea name="answer" required></textarea></div>
    <div class="field"><label>Sort (lower first)</label><input type="number" name="sort" value="0"></div>
    <div class="field" style="display:flex; align-items:flex-end;"><button type="submit" class="btn red">Add</button></div>
  </form>
</div>

<div class="card">
  <table>
    <thead><tr><th>Question</th><th>Category</th><th>Keywords</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($faqs as $f): ?>
    <tr>
      <td>
        <form method="post" action="<?= e(url('admin/faqs.php')) ?>" style="display:flex; flex-direction:column; gap:6px; max-width:520px;">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
          <input type="text" name="question" value="<?= e($f['question']) ?>">
          <input type="text" name="keywords" value="<?= e($f['keywords']) ?>" placeholder="keywords">
          <textarea name="answer" style="min-height:70px;"><?= e($f['answer']) ?></textarea>
          <div style="display:flex; gap:8px; align-items:center;">
            <input type="text" name="category" value="<?= e($f['category']) ?>" style="max-width:140px;">
            <input type="number" name="sort" value="<?= (int)$f['sort'] ?>" style="max-width:70px;">
            <button type="submit" class="btn sm">Save</button>
        </form>
            <form method="post" action="<?= e(url('admin/faqs.php')) ?>" onsubmit="return confirm('Delete this FAQ?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
              <button type="submit" class="btn danger sm">Delete</button>
            </form>
          </div>
      </td>
      <td><?= e($f['category']) ?></td>
      <td style="font-size:.78rem; color:var(--muted);"><?= e($f['keywords']) ?></td>
      <td></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
