<?php

/** @var array  $user            */
/** @var string $csrf            */
/** @var array  $records         */
/** @var int    $total           */
/** @var int    $page            */
/** @var int    $totalPages      */
/** @var int    $perPage         */
/** @var string $search          */
/** @var string $school          */
/** @var string $animalType      */
/** @var string $gender          */
/** @var string $researcherType  */
/** @var array  $schools         */
/** @var array  $animalTypes     */
/** @var array  $genders         */
/** @var array  $researcherTypes */
/** @var string $flash_success   */
/** @var string $flash_error     */

$title = 'Records';
include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/scroll-top.php';

$user            = $user            ?? $_SESSION['user'] ?? [];
$role            = $user['role']    ?? '';
$csrf            = $csrf            ?? '';
$records         = $records         ?? [];
$total           = $total           ?? 0;
$page            = $page            ?? 1;
$totalPages      = $totalPages      ?? 1;
$perPage         = $perPage         ?? 25;
$search          = $search          ?? '';
$school          = $school          ?? '';
$animalType      = $animalType      ?? '';
$gender          = $gender          ?? '';
$researcherType  = $researcherType  ?? '';
$schools         = $schools         ?? [];
$animalTypes     = $animalTypes     ?? [];
$genders         = $genders         ?? [];
$researcherTypes = $researcherTypes ?? [];
$flash_success   = $flash_success   ?? '';
$flash_error     = $flash_error     ?? '';

$offset      = ($page - 1) * $perPage;
$hasFilters  = $search !== '' || $school !== '' || $animalType !== '' || $gender !== '' || $researcherType !== '';

function pageUrl(int $p, string $search, string $school, string $animalType, string $gender, string $researcherType): string
{
    return '?' . http_build_query(array_filter([
        'page'   => $p,
        'search' => $search,
        'school' => $school,
        'animal' => $animalType,
        'gender' => $gender,
        'rtype'  => $researcherType,
    ], fn($v) => $v !== '' && $v !== 1 || is_string($v)));
}
?>

<link rel="stylesheet" href="<?= asset_css('admin/admin-home.css') ?>">
<link rel="stylesheet" href="<?= asset_css('admin/records.css') ?>">

<div class="body">
    <?php include dirname(__DIR__) . '/includes/navigation.php'; ?>

    <main class="main-content" id="main-content" tabindex="-1">

        <!-- ===== Flash messages ===== -->
        <?php if ($flash_success): ?>
            <div class="alert success-message" id="flashSuccess">
                <?= htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert error-messages" id="flashError">
                <?= htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- ===== Page header ===== -->
        <div class="dashboard-page-header records-page-header">
            <div>
                <h1 class="dashboard-page-title">Records</h1>
                <!-- <p>Protocol entries are automatically added to the records table once the reviewer finishes review.</p> -->
            </div>

            <div class="inbox-search-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <use href="#search-icon">
                </svg>
                <input type="text"
                    name="search"
                    id="recordSearch"
                    class="inbox-search-input"
                    placeholder="Search records…"
                    value="<?= htmlspecialchars($search, ENT_QUOTES) ?>"
                    autocomplete="off"
                    form="recordsFilterForm">
                <button type="button" class="inbox-search-clear <?= $search ? 'visible' : '' ?>" id="clearSearch" aria-label="Clear search">✕</button>
            </div>

            <?php if ($role === 'admin'): ?>
                <button class="row-btn row-btn-primary" id="addRecordBtn" type="button">
                    <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <use href="#add-icon">
                    </svg>
                    Add Record
                </button>
            <?php endif; ?>
        </div>

        <!-- ===== Metric cards ===== -->
        <div class="metrics-row records-metrics">
            <div class="metric-card">
                <div class="metric-card-label">Total Records</div>
                <div class="metric-card-value"><?= $total ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-card-label">Showing</div>
                <div class="metric-card-value">
                    <?= min($perPage, max(0, $total - $offset)) ?>
                    <span class="metric-unit">of <?= $total ?></span>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-card-label">Page</div>
                <div class="metric-card-value">
                    <?= $page ?>
                    <span class="metric-unit">/ <?= max(1, $totalPages) ?></span>
                </div>
            </div>
        </div>

        <!-- ===== Filters ===== -->
        <form method="GET" action="" id="recordsFilterForm">

            <?php if ($hasFilters): ?>
                <div class="inbox-toolbar">
                    <a href="<?= ROOT ?>/admin/records" class="row-btn records-clear-btn">Clear all</a>
                </div>
            <?php endif; ?>

            <!-- Filter selects -->
            <div class="records-filters-row">
                <p>Filter by: </p>
                <select name="school" class="records-filter-select" aria-label="Filter by school" onchange="this.form.submit()">
                    <option value="">All Schools</option>
                    <?php foreach ($schools as $s): ?>
                        <option value="<?= htmlspecialchars($s, ENT_QUOTES) ?>" <?= $school === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="animal" class="records-filter-select" aria-label="Filter by animal type" onchange="this.form.submit()">
                    <option value="">All Animal Types</option>
                    <?php foreach ($animalTypes as $a): ?>
                        <option value="<?= htmlspecialchars($a, ENT_QUOTES) ?>" <?= $animalType === $a ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="gender" class="records-filter-select" aria-label="Filter by gender" onchange="this.form.submit()">
                    <option value="">All Genders</option>
                    <?php foreach ($genders as $g): ?>
                        <option value="<?= htmlspecialchars($g, ENT_QUOTES) ?>" <?= $gender === $g ? 'selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="rtype" class="records-filter-select" aria-label="Filter by researcher type" onchange="this.form.submit()">
                    <option value="">All Researcher Types</option>
                    <?php foreach ($researcherTypes as $r): ?>
                        <option value="<?= htmlspecialchars($r, ENT_QUOTES) ?>" <?= $researcherType === $r ? 'selected' : '' ?>><?= htmlspecialchars($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" name="page" value="1">
        </form>

        <!-- ===== Table ===== -->
        <div class="protocol-table-wrap records-table-wrap">
            <div class="protocol-table-scroll">
                <table class="protocol-table records-table">
                    <thead>
                        <tr>
                            <!-- ACTION BUTTONS column -->
                            <?php if ($role === 'admin'): ?>
                                <th class="col-actions">Actions</th>
                            <?php elseif ($role === 'reviewer'): ?>
                                <th class="col-actions"></th>
                            <?php endif; ?>
                            <th class="col-ref">IPN</th>
                            <th class="col-title">Title of Research</th>
                            <th class="col-school">School</th>
                            <th class="col-animal">Animal Type</th>
                            <th class="col-count">Count</th>
                            <th class="col-pi">Researcher</th>
                            <th class="col-gender">Gender</th>
                            <th class="col-rtype">Researcher Type</th>
                            <th class="col-adviser">Research Adviser</th>
                            <th class="col-vet">Veterinarian</th>
                            <th class="col-duration">Duration</th>
                            <th class="col-date">Date Released</th>
                            <th class="col-recv">Received By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($records) === 0): ?>
                            <tr>
                                <td colspan="14">
                                    <div class="inbox-no-results">
                                        <?php if ($hasFilters): ?>
                                            No records match your search or filters.
                                        <?php else: ?>
                                            No records yet. Records are added automatically when a protocol is marked <strong>Reviewed</strong>, or you can add one manually.
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $i => $r): ?>
                                <tr>
                                    <!-- ACTION BUTTONS -->
                                    <td class="actions-cell">
                                        <?php if ($role === 'admin'): ?>
                                            <div class="row-actions">
                                                <button type="button" class="row-btn edit-record-btn"
                                                    data-id="<?= (int)$r['id'] ?>"
                                                    aria-label="Edit record">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                        <use href="#edit-icon">
                                                    </svg>
                                                    Update
                                                </button>
                                                <button type="button" class="row-btn delete-record-btn"
                                                    data-id="<?= (int)$r['id'] ?>"
                                                    data-title="<?= htmlspecialchars(mb_substr($r['title_of_research'], 0, 60), ENT_QUOTES) ?>"
                                                    aria-label="Delete record">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                        <use href="#trash-icon">
                                                    </svg>
                                                    Delete
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="date-cell records-ref"><?= htmlspecialchars($r['reference_no']) ?></td>
                                    <td>
                                        <div class="protocol-title-cell">
                                            <?= htmlspecialchars($r['title_of_research']) ?>
                                        </div>
                                    </td>
                                    <td class="researcher-cell"><?= htmlspecialchars($r['school'] ?? '') ?></td>
                                    <td class="date-cell"><?= htmlspecialchars($r['animal_type'] ?? '') ?></td>
                                    <td class="date-cell"><?= htmlspecialchars($r['animal_count'] ?? '') ?></td>
                                    <td class="researcher-cell"><?= htmlspecialchars($r['principal_investigator'] ?? '') ?></td>
                                    <td class="date-cell"><?= htmlspecialchars($r['gender'] ?? '') ?></td>
                                    <td class="date-cell"><?= htmlspecialchars($r['researcher_type'] ?? '') ?></td>
                                    <td class="researcher-cell"><?= htmlspecialchars($r['research_adviser'] ?? '') ?></td>
                                    <td class="researcher-cell"><?= htmlspecialchars($r['veterinarian'] ?? '') ?></td>
                                    <td class="date-cell"><?= htmlspecialchars($r['research_duration'] ?? '') ?></td>
                                    <td class="date-cell"><?= $r['date_released'] ? date('M j, Y', strtotime($r['date_released'])) : '' ?></td>
                                    <td class="researcher-cell"><?= htmlspecialchars($r['received_by'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== Pagination ===== -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-bar">
                <div class="pagination-info">
                    Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $total) ?> of <?= $total ?> records
                </div>
                <div class="pagination-buttons">
                    <?php if ($page > 1): ?>
                        <a href="<?= pageUrl(1, $search, $school, $animalType, $gender, $researcherType) ?>" class="pagination-btn" title="First">«</a>
                        <a href="<?= pageUrl($page - 1, $search, $school, $animalType, $gender, $researcherType) ?>" class="pagination-btn" title="Previous">‹</a>
                    <?php else: ?>
                        <span class="pagination-btn" style="opacity:.35;cursor:default">«</span>
                        <span class="pagination-btn" style="opacity:.35;cursor:default">‹</span>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end   = min($totalPages, $page + 2);
                    if ($start > 1) echo '<span class="pagination-ellipsis">…</span>';
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <a href="<?= pageUrl($i, $search, $school, $animalType, $gender, $researcherType) ?>"
                            class="pagination-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor;
                    if ($end < $totalPages) echo '<span class="pagination-ellipsis">…</span>';
                    ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= pageUrl($page + 1, $search, $school, $animalType, $gender, $researcherType) ?>" class="pagination-btn" title="Next">›</a>
                        <a href="<?= pageUrl($totalPages, $search, $school, $animalType, $gender, $researcherType) ?>" class="pagination-btn" title="Last">»</a>
                    <?php else: ?>
                        <span class="pagination-btn" style="opacity:.35;cursor:default">›</span>
                        <span class="pagination-btn" style="opacity:.35;cursor:default">»</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>

<!-- ===== ADD RECORD MODAL ===== -->
<div class="modal-backdrop" id="addModal" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
    <div class="modal-card records-modal-card">
        <div class="records-modal-header">
            <h2 id="addModalTitle">Add Record</h2>
            <button type="button" class="records-modal-close" data-close="addModal" aria-label="Close">✕</button>
        </div>
        <div class="records-modal-body">
            <div class="alert error-messages" id="addError" hidden></div>
            <div class="records-form-grid">
                <div class="records-form-group records-form-full">
                    <label for="add_reference_no">IPN <span class="records-required">*</span></label>
                    <input type="text" id="add_reference_no" name="reference_no" placeholder="e.g. BSU-IACUC-2025-001">
                </div>
                <div class="records-form-group records-form-full">
                    <label for="add_title">Title of Research <span class="records-required">*</span></label>
                    <textarea id="add_title" name="title_of_research" rows="3" placeholder="Full research title"></textarea>
                </div>
                <div class="records-form-group">
                    <label for="add_pi">Principal Investigator</label>
                    <input type="text" id="add_pi" name="principal_investigator" placeholder="Full name">
                </div>
                <div class="records-form-group">
                    <label for="add_school">School / Department</label>
                    <input type="text" id="add_school" name="school" placeholder="e.g. College of Agriculture">
                </div>
                <div class="records-form-group">
                    <label for="add_animal_type">Animal Type</label>
                    <input type="text" id="add_animal_type" name="animal_type" placeholder="e.g. Mice, Rats">
                </div>
                <div class="records-form-group">
                    <label for="add_animal_count">Animal Count</label>
                    <input type="number" id="add_animal_count" name="animal_count" min="0" placeholder="0">
                </div>
                <div class="records-form-group">
                    <label for="add_gender">Gender</label>
                    <select id="add_gender" name="gender">
                        <option value="">— select —</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Mixed</option>
                    </select>
                </div>
                <div class="records-form-group">
                    <label for="add_researcher_type">Researcher Type</label>
                    <select id="add_researcher_type" name="researcher_type">
                        <option value="">— select —</option>
                        <option>Student</option>
                        <option>Faculty</option>
                        <option>Staff</option>
                        <option>External</option>
                    </select>
                </div>
                <div class="records-form-group">
                    <label for="add_research_adviser">Research Adviser</label>
                    <input type="text" id="add_research_adviser" name="research_adviser" placeholder="Full name">
                </div>
                <div class="records-form-group">
                    <label for="add_veterinarian">Veterinarian</label>
                    <input type="text" id="add_veterinarian" name="veterinarian" placeholder="Full name">
                </div>
                <div class="records-form-group">
                    <label for="add_research_duration">Research Duration</label>
                    <input type="text" id="add_research_duration" name="research_duration" placeholder="e.g. 6 months">
                </div>
                <div class="records-form-group">
                    <label for="add_date_released">Date Released</label>
                    <input type="date" id="add_date_released" name="date_released">
                </div>
                <div class="records-form-group records-form-full">
                    <label for="add_received_by">Received By</label>
                    <input type="text" id="add_received_by" name="received_by" placeholder="Name of receiving officer">
                </div>
            </div>
        </div>
        <div class="records-modal-footer">
            <button type="button" class="row-btn" data-close="addModal">Cancel</button>
            <button type="button" class="row-btn row-btn-primary" id="addRecordSave">Save Record</button>
        </div>
    </div>
</div>

<!-- ===== EDIT RECORD MODAL ===== -->
<div class="modal-backdrop" id="editModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="modal-card records-modal-card">
        <div class="records-modal-header">
            <h2 id="editModalTitle">Edit Record</h2>
            <button type="button" class="records-modal-close" data-close="editModal" aria-label="Close">✕</button>
        </div>
        <div class="records-modal-body">
            <div class="alert error-messages" id="editError" hidden></div>
            <div class="records-form-grid">
                <input type="hidden" id="edit_id">
                <div class="records-form-group records-form-full">
                    <label for="edit_reference_no">IPN</label>
                    <input type="text" id="edit_reference_no" name="reference_no" placeholder="e.g. BSU-IACUC-2025-001">
                </div>
                <div class="records-form-group records-form-full">
                    <label for="edit_title">Title of Research</label>
                    <textarea id="edit_title" name="title_of_research" rows="3"></textarea>
                </div>
                <div class="records-form-group">
                    <label for="edit_pi">Principal Investigator</label>
                    <input type="text" id="edit_pi" name="principal_investigator">
                </div>
                <div class="records-form-group">
                    <label for="edit_school">School / Department</label>
                    <input type="text" id="edit_school" name="school">
                </div>
                <div class="records-form-group">
                    <label for="edit_animal_type">Animal Type</label>
                    <input type="text" id="edit_animal_type" name="animal_type">
                </div>
                <div class="records-form-group">
                    <label for="edit_animal_count">Animal Count</label>
                    <input type="number" id="edit_animal_count" name="animal_count" min="0">
                </div>
                <div class="records-form-group">
                    <label for="edit_gender">Gender</label>
                    <select id="edit_gender" name="gender">
                        <option value="">— select —</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Mixed</option>
                    </select>
                </div>
                <div class="records-form-group">
                    <label for="edit_researcher_type">Researcher Type</label>
                    <select id="edit_researcher_type" name="researcher_type">
                        <option value="">— select —</option>
                        <option>Student</option>
                        <option>Faculty</option>
                        <option>Staff</option>
                        <option>External</option>
                    </select>
                </div>
                <div class="records-form-group">
                    <label for="edit_research_adviser">Research Adviser</label>
                    <input type="text" id="edit_research_adviser" name="research_adviser">
                </div>
                <div class="records-form-group">
                    <label for="edit_veterinarian">Veterinarian</label>
                    <input type="text" id="edit_veterinarian" name="veterinarian">
                </div>
                <div class="records-form-group">
                    <label for="edit_research_duration">Research Duration</label>
                    <input type="text" id="edit_research_duration" name="research_duration">
                </div>
                <div class="records-form-group">
                    <label for="edit_date_released">Date Released</label>
                    <input type="date" id="edit_date_released" name="date_released">
                </div>
                <div class="records-form-group records-form-full">
                    <label for="edit_received_by">Received By</label>
                    <input type="text" id="edit_received_by" name="received_by">
                </div>
            </div>
        </div>
        <div class="records-modal-footer">
            <button type="button" class="row-btn" data-close="editModal">Cancel</button>
            <button type="button" class="row-btn row-btn-primary" id="editRecordSave">Save Changes</button>
        </div>
    </div>
</div>

<script>
    (function() {
        const ROOT = '<?= ROOT ?>';
        const CSRF = '<?= htmlspecialchars($csrf, ENT_QUOTES) ?>';

        // ===== Modal helpers =====
        function openModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('open');
            const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (focusable) focusable.focus();
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        document.querySelectorAll('[data-close]').forEach(btn => {
            btn.addEventListener('click', () => closeModal(btn.dataset.close));
        });
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
            backdrop.addEventListener('click', e => {
                if (e.target === backdrop) closeModal(backdrop.id);
            });
        });

        // ===== Search clear =====
        const searchInput = document.getElementById('recordSearch');
        const clearSearch = document.getElementById('clearSearch');
        if (searchInput && clearSearch) {
            clearSearch.addEventListener('click', () => {
                searchInput.value = '';
                document.getElementById('recordsFilterForm').submit();
            });
            searchInput.addEventListener('input', () => {
                clearSearch.classList.toggle('visible', searchInput.value.length > 0);
            });
        }

        // ===== AJAX helper =====
        function post(url, body) {
            body.csrf_token = CSRF;
            const fd = new FormData();
            Object.entries(body).forEach(([k, v]) => fd.append(k, v ?? ''));
            return fetch(ROOT + url, {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json());
        }

        function showErr(id, msg) {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = msg;
            el.hidden = false;
        }

        function hideErr(id) {
            const el = document.getElementById(id);
            if (el) {
                el.hidden = true;
                el.textContent = '';
            }
        }

        // ===== ADD =====
        const addRecordBtn = document.getElementById('addRecordBtn');
        if (addRecordBtn) {
            addRecordBtn.addEventListener('click', () => {
                hideErr('addError');
                document.getElementById('addModal').querySelectorAll('input,textarea,select').forEach(el => el.value = '');
                openModal('addModal');
            });
        }

        const addRecordSave = document.getElementById('addRecordSave');
        if (addRecordSave) {
            addRecordSave.addEventListener('click', () => {
                hideErr('addError');
                const ref = document.getElementById('add_reference_no').value.trim();
                const title = document.getElementById('add_title').value.trim();
                if (!ref) {
                    showErr('addError', 'IPN is required.');
                    return;
                }
                if (!title) {
                    showErr('addError', 'Title of research is required.');
                    return;
                }

                post('/admin/records_add', {
                    reference_no: ref,
                    title_of_research: title,
                    school: document.getElementById('add_school').value,
                    animal_type: document.getElementById('add_animal_type').value,
                    animal_count: document.getElementById('add_animal_count').value,
                    principal_investigator: document.getElementById('add_pi').value,
                    gender: document.getElementById('add_gender').value,
                    researcher_type: document.getElementById('add_researcher_type').value,
                    research_adviser: document.getElementById('add_research_adviser').value,
                    veterinarian: document.getElementById('add_veterinarian').value,
                    research_duration: document.getElementById('add_research_duration').value,
                    date_released: document.getElementById('add_date_released').value,
                    received_by: document.getElementById('add_received_by').value,
                }).then(data => {
                    if (data.ok) {
                        closeModal('addModal');
                        sessionStorage.setItem('records_flash', 'Record added successfully.');
                        location.reload();
                    } else {
                        showErr('addError', data.message || 'Add failed.');
                    }
                }).catch(() => showErr('addError', 'Network error. Please try again.'));
            });
        }

        // ===== EDIT =====
        document.querySelectorAll('.edit-record-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                hideErr('editError');
                const id = btn.dataset.id;
                fetch(ROOT + '/admin/records_get?id=' + encodeURIComponent(id))
                    .then(r => r.json())
                    .then(data => {
                        if (!data.ok) {
                            alert(data.message || 'Could not load record.');
                            return;
                        }
                        const d = data.data;
                        document.getElementById('edit_id').value = d.id;
                        document.getElementById('edit_reference_no').value = d.reference_no ?? '';
                        document.getElementById('edit_title').value = d.title_of_research ?? '';
                        document.getElementById('edit_pi').value = d.principal_investigator ?? '';
                        document.getElementById('edit_school').value = d.school ?? '';
                        document.getElementById('edit_animal_type').value = d.animal_type ?? '';
                        document.getElementById('edit_animal_count').value = d.animal_count ?? '';
                        document.getElementById('edit_gender').value = d.gender ?? '';
                        document.getElementById('edit_researcher_type').value = d.researcher_type ?? '';
                        document.getElementById('edit_research_adviser').value = d.research_adviser ?? '';
                        document.getElementById('edit_veterinarian').value = d.veterinarian ?? '';
                        document.getElementById('edit_research_duration').value = d.research_duration ?? '';
                        document.getElementById('edit_date_released').value = d.date_released ?? '';
                        document.getElementById('edit_received_by').value = d.received_by ?? '';
                        openModal('editModal');
                    })
                    .catch(() => alert('Network error. Please try again.'));
            });
        });

        document.getElementById('editRecordSave').addEventListener('click', () => {
            hideErr('editError');
            post('/admin/records_edit', {
                id: document.getElementById('edit_id').value,
                reference_no: document.getElementById('edit_reference_no').value,
                title_of_research: document.getElementById('edit_title').value,
                school: document.getElementById('edit_school').value,
                animal_type: document.getElementById('edit_animal_type').value,
                animal_count: document.getElementById('edit_animal_count').value,
                principal_investigator: document.getElementById('edit_pi').value,
                gender: document.getElementById('edit_gender').value,
                researcher_type: document.getElementById('edit_researcher_type').value,
                research_adviser: document.getElementById('edit_research_adviser').value,
                veterinarian: document.getElementById('edit_veterinarian').value,
                research_duration: document.getElementById('edit_research_duration').value,
                date_released: document.getElementById('edit_date_released').value,
                received_by: document.getElementById('edit_received_by').value,
            }).then(data => {
                if (data.ok) {
                    closeModal('editModal');
                    sessionStorage.setItem('records_flash', 'Record updated successfully.');
                    location.reload();
                } else {
                    showErr('editError', data.message || 'Update failed.');
                }
            }).catch(() => showErr('editError', 'Network error. Please try again.'));
        });

        // ===== DELETE =====
        document.querySelectorAll('.delete-record-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const title = btn.dataset.title || '#' + btn.dataset.id;
                const confirmed = await confirmAction(
                    'Delete "' + title + '"? This cannot be undone.', {
                        okText: 'Delete',
                        cancelText: 'Cancel',
                        danger: true
                    }
                );
                if (!confirmed) return;

                post('/admin/records_delete', {
                        id: btn.dataset.id
                    })
                    .then(data => {
                        if (data.ok) {
                            sessionStorage.setItem('records_flash', 'Record deleted.');
                            location.reload();
                        } else {
                            alert(data.message || 'Delete failed.');
                        }
                    }).catch(() => alert('Network error. Please try again.'));
            });
        });

        // ===== Drag-to-scroll table =====
        (function() {
            const scrollEl = document.querySelector('.records-table-wrap .protocol-table-scroll');
            if (!scrollEl) return;

            let isDragging = false;
            let startX = 0;
            let startScrollLeft = 0;

            scrollEl.addEventListener('mousedown', e => {
                if (e.target.closest('button, a, input, select, textarea')) return;
                isDragging = true;
                scrollEl.classList.add('dragging');
                startX = e.pageX;
                startScrollLeft = scrollEl.scrollLeft;
            });

            window.addEventListener('mouseup', () => {
                isDragging = false;
                scrollEl.classList.remove('dragging');
            });

            scrollEl.addEventListener('mouseleave', () => {
                isDragging = false;
                scrollEl.classList.remove('dragging');
            });

            scrollEl.addEventListener('mousemove', e => {
                if (!isDragging) return;
                e.preventDefault();
                scrollEl.scrollLeft = startScrollLeft - (e.pageX - startX);
            });
        })();

        // ===== sessionStorage flash (after reload) =====
        const pendingFlash = sessionStorage.getItem('records_flash');
        if (pendingFlash) {
            sessionStorage.removeItem('records_flash');
            const flash = document.createElement('div');
            flash.className = 'alert success-message';
            flash.id = 'flashSuccess';
            flash.textContent = pendingFlash;
            const main = document.getElementById('main-content');
            main.insertBefore(flash, main.firstChild);
            setTimeout(() => flash.remove(), 4000);
        }

        // ===== Auto-dismiss PHP flash messages =====
        function dismissFlash(id, delay) {
            const el = document.getElementById(id);
            if (!el) return;
            setTimeout(() => el.remove(), delay);
        }
        dismissFlash('flashSuccess', 4000);
        dismissFlash('flashError', 7000);

    })();
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
