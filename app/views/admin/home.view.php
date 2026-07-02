<?php

/** @var array|null  $user */
/** @var array       $protocols */
/** @var array       $statuses */

$title = 'Staff Dashboard';

include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/scroll-top.php';

$user      = $user      ?? $_SESSION['user'] ?? [];
$csrf      = $csrf      ?? '';
$protocols = $protocols ?? [];

// ── Map internal status strings to human-readable display labels ──────────
$statusDisplayMap = [
    'under review'   => 'To Review',
    'needs revision' => 'Returned for Revision',
    'reviewed'       => 'Reviewed',
    'endorsed'       => 'Endorsed',
    'approved'       => 'Approved',
];

// CSS badge modifier keyed by internal status
$badgeClassMap = [
    'under review'   => 'badge-to-review',
    'needs revision' => 'badge-returned',
    'reviewed'       => 'badge-reviewed',
    'endorsed'       => 'badge-endorsed',
    'approved'       => 'badge-approved',
];

// data-status slug used by the filter pills
$filterSlugMap = [
    'under review'   => 'to-review',
    'needs revision' => 'returned-for-revision',
    'reviewed'       => 'reviewed',
    'endorsed'       => 'endorsed',
    'approved'       => 'approved',
];

foreach ($protocols as &$protocol) {
    $key = strtolower($protocol['status']);

    $protocol['status_display'] = $statusDisplayMap[$key] ?? $protocol['status'];
    $protocol['badge_class']    = $badgeClassMap[$key]    ?? 'badge-to-review';
    $protocol['filter_slug']    = $filterSlugMap[$key]    ?? 'other';
}
unset($protocol);

// ── Compute per-status counts for metric cards and filter pill badges ─────
$countsBySlug = [];
foreach ($protocols as $p) {
    $slug = $p['filter_slug'];
    $countsBySlug[$slug] = ($countsBySlug[$slug] ?? 0) + 1;
}

$totalCount     = count($protocols);
$toReviewCount  = $countsBySlug['to-review']             ?? 0;
$revisionCount  = $countsBySlug['returned-for-revision'] ?? 0;
$reviewedCount  = $countsBySlug['reviewed']              ?? 0;
$endorsedCount  = $countsBySlug['endorsed']              ?? 0;
$approvedCount  = $countsBySlug['approved']              ?? 0;

// "Approved this month" for the metric card
$approvedThisMonth = 0;
$currentMonth = date('Y-m');
foreach ($protocols as $p) {
    if ($p['filter_slug'] === 'approved' && str_starts_with($p['submitted_at'], $currentMonth)) {
        $approvedThisMonth++;
    }
}

?>

<link rel="stylesheet" href="<?= CSSPATH ?>/admin/admin-home.css">

<div class="body">
    <?php include dirname(__DIR__) . '/includes/navigation.php'; ?>

    <main class="main-content" id="main-content" tabindex="-1">

        <!-- ── Page header ─────────────────────────────────────── -->
        <div class="dashboard-page-header">
            <h1 class="dashboard-page-title">Protocol Inbox</h1>
        </div>

        <!-- ── Flash messages ──────────────────────────────────── -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert success-message" id="flashSuccess">
                <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <use href="#check-icon" />
                </svg>
                <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert error-messages" id="flashError">
                <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- ── Metric cards ────────────────────────────────────── -->
        <div class="metrics-row">
            <div class="metric-card">
                <div class="metric-card-label">To review</div>
                <div class="metric-card-value">
                    <?= $toReviewCount ?>
                    <span class="metric-unit">unread</span>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-card-label">Awaiting revision</div>
                <div class="metric-card-value"><?= $revisionCount ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-card-label">Reviewed</div>
                <div class="metric-card-value"><?= $reviewedCount ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-card-label">Approved this month</div>
                <div class="metric-card-value"><?= $approvedThisMonth ?></div>
            </div>
        </div>

        <?php if (empty($protocols)): ?>
            <!-- ── Empty state ─────────────────────────────────── -->
            <div class="empty-state">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <use href="#file-x-icon" />
                    </svg>
                    No protocols yet
                </h3>
                <p>No protocol submissions have been received.</p>
            </div>

        <?php else: ?>

            <!-- ── Search toolbar ──────────────────────────────── -->
            <div class="inbox-toolbar">
                <div class="inbox-search-wrap">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" id="inboxSearchInput" class="inbox-search-input"
                        placeholder="Search by title or researcher..." autocomplete="off">
                    <button class="inbox-search-clear" id="inboxSearchClear" aria-label="Clear search">
                        &#x2715;
                    </button>
                </div>
            </div>

            <!-- ── Filter pills ────────────────────────────────── -->
            <div class="filter-pills-row" id="filterPillsRow">
                <button class="filter-pill" data-filter="all">
                    All <span class="pill-count"><?= $totalCount ?></span>
                </button>
                <button class="filter-pill active" data-filter="to-review">
                    To review <span class="pill-count"><?= $toReviewCount ?></span>
                </button>
                <button class="filter-pill" data-filter="returned-for-revision">
                    Returned for revision <span class="pill-count"><?= $revisionCount ?></span>
                </button>
                <button class="filter-pill" data-filter="reviewed">
                    Reviewed <span class="pill-count"><?= $reviewedCount ?></span>
                </button>
                <button class="filter-pill" data-filter="endorsed">
                    Endorsed <span class="pill-count"><?= $endorsedCount ?></span>
                </button>
                <button class="filter-pill" data-filter="approved">
                    Approved <span class="pill-count"><?= $approvedCount ?></span>
                </button>
            </div>

            <!-- ── Protocol table ───────────────────────────────── -->
            <div class="protocol-table-wrap">
                <div class="protocol-table-scroll">
                    <table class="protocol-table" id="protocolTable">
                        <thead>
                            <tr>
                                <th class="col-title">Protocol title</th>
                                <th class="col-researcher">Researcher</th>
                                <th class="col-submitted">Submitted</th>
                                <th class="col-status">Status</th>
                                <th class="col-actions"></th>
                            </tr>
                        </thead>

                        <?php
                        $iconMap = [
                            'review'   => '#review-icon',
                            'history'  => '#history-icon',
                            'check'    => '#check-icon',
                            'upload'   => '#upload-icon',
                            'download' => '#download-icon',
                            'back'     => '#back-icon',
                        ];
                        ?>

                        <tbody id="protocolTableBody">
                            <?php foreach ($protocols as $protocol):
                                $submittedDate  = date('M j, Y', strtotime($protocol['submitted_at']));
                                $statusDisplay  = $protocol['status_display'];
                                $badgeClass     = $protocol['badge_class'];
                                $filterSlug     = $protocol['filter_slug'];
                                $statusLower    = strtolower($protocol['status']);
                                $protocolId     = (int) $protocol['protocol_id'];
                                $researcherName = htmlspecialchars(
                                    $protocol['first_name'] . ' ' . $protocol['last_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                $title = htmlspecialchars($protocol['research_title'], ENT_QUOTES, 'UTF-8');

                                // Determine primary action button per status and role
                                $userRole = $user['role'] ?? '';
                                $actions = [];

                                $userRole = strtolower($user['role'] ?? '');
                                $actions = [];

                                if ($userRole === 'reviewer') {

                                    switch ($statusLower) {

                                        case 'under review':
                                            $actions = [
                                                [
                                                    'label' => 'Review',
                                                    'action' => 'open',
                                                    'icon' => 'review',
                                                    'primary' => true
                                                ],
                                                [
                                                    'label' => 'Show History',
                                                    'action' => 'show-history',
                                                    'icon' => 'history'
                                                ]
                                            ];
                                            break;

                                        case 'approved':
                                            $actions = [
                                                [
                                                    'label' => 'Show Clearance',
                                                    'action' => 'view-clearance',
                                                    'icon' => 'download',
                                                    'primary' => true
                                                ],
                                                [
                                                    'label' => 'View',
                                                    'action' => 'view',
                                                    'icon' => 'review'
                                                ],
                                                [
                                                    'label' => 'Show History',
                                                    'action' => 'show-history',
                                                    'icon' => 'history'
                                                ]
                                            ];
                                            break;

                                        default:
                                            $actions = [
                                                [
                                                    'label' => 'View',
                                                    'action' => 'view',
                                                    'icon' => 'review',
                                                    'primary' => true
                                                ],
                                                [
                                                    'label' => 'Show History',
                                                    'action' => 'show-history',
                                                    'icon' => 'history'
                                                ]
                                            ];
                                    }
                                } else {

                                    switch ($statusLower) {

                                        case 'reviewed':
                                            $actions = [
                                                [
                                                    'label' => 'Mark as Endorsed',
                                                    'action' => 'mark-endorsed',
                                                    'icon' => 'check',
                                                    'primary' => true
                                                ],
                                                [
                                                    'label' => 'View',
                                                    'action' => 'view',
                                                    'icon' => 'review'
                                                ],
                                                [
                                                    'label' => 'Show History',
                                                    'action' => 'show-history',
                                                    'icon' => 'history'
                                                ]
                                            ];
                                            break;

                                        case 'endorsed':
                                            $actions = [
                                                [
                                                    'label' => 'Upload Clearance and Mark as Approved',
                                                    'action' => 'upload-clearance',
                                                    'icon' => 'upload',
                                                    'primary' => true
                                                ],
                                                [
                                                    'label' => 'View',
                                                    'action' => 'view',
                                                    'icon' => 'review'
                                                ],
                                                [
                                                    'label' => 'Show History',
                                                    'action' => 'show-history',
                                                    'icon' => 'history'
                                                ]
                                            ];
                                            break;

                                        case 'approved':
                                            $actions = [
                                                [
                                                    'label' => 'Show Clearance',
                                                    'action' => 'view-clearance',
                                                    'icon' => 'download',
                                                    'primary' => true
                                                ],
                                                [
                                                    'label' => 'View',
                                                    'action' => 'view',
                                                    'icon' => 'review'
                                                ],
                                                [
                                                    'label' => 'Show History',
                                                    'action' => 'show-history',
                                                    'icon' => 'history'
                                                ]
                                            ];
                                            break;

                                        default:
                                            $actions = [
                                                [
                                                    'label' => 'View',
                                                    'action' => 'view',
                                                    'icon' => 'review',
                                                    'primary' => true
                                                ],
                                                [
                                                    'label' => 'Show History',
                                                    'action' => 'show-history',
                                                    'icon' => 'history'
                                                ]
                                            ];
                                    }
                                }
                            ?>
                                <tr data-protocol-id="<?= $protocolId ?>"
                                    data-filter-slug="<?= $filterSlug ?>"
                                    data-researcher="<?= strtolower(htmlspecialchars($protocol['first_name'] . ' ' . $protocol['last_name'], ENT_QUOTES, 'UTF-8')) ?>">

                                    <td>
                                        <div class="protocol-title-cell">
                                            <?= $title ?>
                                        </div>
                                        <div class="protocol-title-meta">
                                            <span class="meta-researcher"><?= $researcherName ?></span>
                                            <span class="meta-sep">&middot;</span>
                                            <span class="meta-date"><?= $submittedDate ?></span>
                                        </div>
                                    </td>
                                    <td class="researcher-cell"><?= $researcherName ?></td>
                                    <td class="date-cell"><?= $submittedDate ?></td>
                                    <td>
                                        <span class="status-badge <?= $badgeClass ?>">
                                            <span class="status-badge-dot"></span>
                                            <?= htmlspecialchars($statusDisplay, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="row-actions">

                                            <?php foreach ($actions as $action): ?>

                                                <button
                                                    class="row-btn <?= !empty($action['primary']) ? 'row-btn-primary' : '' ?>"
                                                    data-action="<?= $action['action'] ?>">

                                                    <?php if (!empty($action['icon'])): ?>
                                                        <svg width="14" height="14" viewBox="0 0 24 24"
                                                            aria-hidden="true" focusable="false">
                                                            <use href="<?= $iconMap[$action['icon']] ?>"></use>
                                                        </svg>
                                                    <?php endif; ?>

                                                    <?= htmlspecialchars($action['label']) ?>

                                                </button>

                                            <?php endforeach; ?>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="inbox-no-results" id="noResultsRow" hidden>
                        No protocols match your search or filter.
                    </div>
                </div><!-- /.protocol-table-scroll -->
            </div><!-- /.protocol-table-wrap -->

            <!-- ── Pagination ───────────────────────────────────── -->
            <div class="pagination-bar" id="paginationBar">
                <span class="pagination-info" id="paginationInfo"></span>
                <div class="pagination-buttons" id="paginationButtons"></div>
                <div class="rows-per-page-wrap">
                    Rows per page:
                    <select id="rowsPerPageSelect">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

        <?php endif; ?>

    </main>
</div>

<!-- ────────────────────────────────────────────────────────── -->
<!-- JavaScript                                                 -->
<!-- ────────────────────────────────────────────────────────── -->
<script>
    const protocolsData = <?= json_encode($protocols) ?>;
    const ROOT_URL = <?= json_encode(ROOT) ?>;
    const USER_ROLE = <?= json_encode($user['role'] ?? '') ?>;
    const CSRF_TOKEN = <?= json_encode($csrf) ?>;
    const STATUS_API = ROOT_URL + '/apply/status';
    const CLEARANCE_UPLOAD_API = ROOT_URL + '/apply/clearance_upload';
    const CLEARANCE_VIEW_URL = ROOT_URL + '/apply/clearance/';

    // ── DOM refs ─────────────────────────────────────────────
    const tableBody = document.getElementById('protocolTableBody');
    const noResultsRow = document.getElementById('noResultsRow');
    const filterPills = document.querySelectorAll('.filter-pill');
    const searchInput = document.getElementById('inboxSearchInput');
    const searchClearBtn = document.getElementById('inboxSearchClear');
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationBtns = document.getElementById('paginationButtons');
    const rowsPerPageSel = document.getElementById('rowsPerPageSelect');

    const allRows = tableBody ? [...tableBody.querySelectorAll('tr')] : [];

    let activeFilter = 'to-review';
    let searchQuery = '';
    let currentPage = 1;
    let rowsPerPage = 10;

    // ── Filter pills ─────────────────────────────────────────
    filterPills.forEach(pill => {
        pill.addEventListener('click', () => {
            filterPills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            activeFilter = pill.dataset.filter;
            currentPage = 1;
            const url = new URL(window.location);
            url.searchParams.set('status', activeFilter);
            history.replaceState(null, '', url);
            renderTable();
        });
    });

    // ── Search ───────────────────────────────────────────────
    searchInput?.addEventListener('input', () => {
        searchQuery = searchInput.value.trim().toLowerCase();
        searchClearBtn.classList.toggle('visible', searchQuery.length > 0);
        currentPage = 1;
        renderTable();
    });

    searchClearBtn?.addEventListener('click', () => {
        searchInput.value = '';
        searchQuery = '';
        searchClearBtn.classList.remove('visible');
        currentPage = 1;
        renderTable();
        searchInput.focus();
    });

    // ── Rows-per-page selector ────────────────────────────────
    rowsPerPageSel?.addEventListener('change', () => {
        rowsPerPage = parseInt(rowsPerPageSel.value, 10);
        currentPage = 1;
        renderTable();
    });

    // ── Restore filter from URL param (?status=...) ───────────
    (function restoreFilterFromUrl() {
        const requestedStatus = new URLSearchParams(window.location.search).get('status');
        if (requestedStatus) {
            const matchingPill = [...filterPills].find(p => p.dataset.filter === requestedStatus);
            if (matchingPill) {
                filterPills.forEach(p => p.classList.remove('active'));
                matchingPill.classList.add('active');
                activeFilter = requestedStatus;
            }
        } else {
            const url = new URL(window.location);
            url.searchParams.set('status', activeFilter);
            history.replaceState(null, '', url);
        }
    })();

    // ── Main render ───────────────────────────────────────────
    function renderTable() {
        // 1. Determine which rows match the current filter + search
        const visibleRows = allRows.filter(row => {
            const slug = row.dataset.filterSlug;
            const title = row.querySelector('.protocol-title-cell')?.textContent.toLowerCase() ?? '';
            const researcher = row.dataset.researcher ?? '';

            const matchesFilter =
                activeFilter === 'all' ||
                slug === activeFilter;

            const matchesSearch = !searchQuery ||
                title.includes(searchQuery) ||
                researcher.includes(searchQuery);

            return matchesFilter && matchesSearch;
        });

        // 2. Hide all rows
        allRows.forEach(row => row.classList.add('protocol-row-hidden'));

        // 3. Pagination slice
        const totalRows = visibleRows.length;
        const totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));
        if (currentPage > totalPages) currentPage = totalPages;

        const startIndex = (currentPage - 1) * rowsPerPage;
        const pageRows = visibleRows.slice(startIndex, startIndex + rowsPerPage);

        pageRows.forEach(row => row.classList.remove('protocol-row-hidden'));

        // 4. No-results message
        if (noResultsRow) {
            noResultsRow.hidden = totalRows !== 0;
        }

        // 5. Pagination info
        if (paginationInfo) {
            if (totalRows === 0) {
                paginationInfo.textContent = 'No protocols found';
            } else {
                const from = startIndex + 1;
                const to = Math.min(startIndex + rowsPerPage, totalRows);
                paginationInfo.textContent = `Showing ${from}–${to} of ${totalRows} protocols`;
            }
        }

        // 6. Pagination buttons
        renderPaginationButtons(totalPages);
    }

    function renderPaginationButtons(totalPages) {
        if (!paginationBtns) return;
        paginationBtns.innerHTML = '';

        function makeBtn(label, page, isActive) {
            const btn = document.createElement('button');
            btn.className = 'pagination-btn' + (isActive ? ' active' : '');
            btn.textContent = label;
            btn.addEventListener('click', () => {
                currentPage = page;
                renderTable();
            });
            return btn;
        }

        function makeEllipsis() {
            const span = document.createElement('span');
            span.className = 'pagination-ellipsis';
            span.textContent = '...';
            return span;
        }

        // Prev arrow
        const prevBtn = document.createElement('button');
        prevBtn.className = 'pagination-btn';
        prevBtn.innerHTML = '&#8249;';
        prevBtn.setAttribute('aria-label', 'Previous page');
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        });
        paginationBtns.appendChild(prevBtn);

        // Page number buttons (show up to 5 around current)
        const pageSet = buildPageSet(currentPage, totalPages);
        let prevPageNum = null;
        pageSet.forEach(pageNum => {
            if (prevPageNum !== null && pageNum - prevPageNum > 1) {
                paginationBtns.appendChild(makeEllipsis());
            }
            paginationBtns.appendChild(makeBtn(pageNum, pageNum, pageNum === currentPage));
            prevPageNum = pageNum;
        });

        // Next arrow
        const nextBtn = document.createElement('button');
        nextBtn.className = 'pagination-btn';
        nextBtn.innerHTML = '&#8250;';
        nextBtn.setAttribute('aria-label', 'Next page');
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        });
        paginationBtns.appendChild(nextBtn);
    }

    function buildPageSet(current, total) {
        const pages = new Set();
        pages.add(1);
        if (total > 1) pages.add(total);
        for (let i = Math.max(1, current - 1); i <= Math.min(total, current + 1); i++) {
            pages.add(i);
        }
        return [...pages].sort((a, b) => a - b);
    }

    // ── Row action buttons ────────────────────────────────────
    tableBody?.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;

        const row = btn.closest('tr');
        const protocolId = parseInt(row?.dataset.protocolId, 10);
        const protocol = protocolsData.find(p => p.protocol_id == protocolId);
        const action = btn.dataset.action;

        if (!protocolId || !action) return;

        switch (action) {
            case 'open':
                window.location.href = ROOT_URL + '/apply/viewer/' + protocolId + '?from=' + encodeURIComponent(activeFilter);
                break;

            case 'view':
                window.location.href = ROOT_URL + '/apply/viewer/' + protocolId + '?from=' + encodeURIComponent(activeFilter);
                break;

            case 'show-history':
                openHistoryModal(protocolId, protocol?.research_title ?? '');
                break;

            case 'view-clearance':
                window.open(CLEARANCE_VIEW_URL + protocolId, '_blank', 'noopener');
                break;

            case 'mark-endorsed':
                confirmAction('Mark this protocol as endorsed? It will move forward for final clearance.', {
                    okText: 'Mark as Endorsed',
                    cancelText: 'Cancel'
                }).then(ok => ok && submitStatusChange(protocolId, 'Endorsed'));
                break;

            case 'upload-clearance':
                openClearanceModal(protocolId, protocol?.research_title ?? '');
                break;
        }
    });

    // ── Status change API call ────────────────────────────────
    async function submitStatusChange(protocolId, newStatus) {
        try {
            const res = await fetch(STATUS_API, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify({
                    protocol_id: protocolId,
                    status: newStatus
                }),
            });
            const data = await res.json();
            if (data.ok) {
                window.location.reload();
            } else if (data.queued) {
                // Queued while offline — the action-queue banner explains it.
            } else {
                alert('Error: ' + (data.error ?? 'Could not update status.'));
            }
        } catch (err) {
            if (!navigator.onLine) {
                // Should not normally reach here — action-queue.js intercepts
                // these requests. Surface a clear message just in case.
                alert('You are offline. The action could not be queued. Please try again when reconnected.');
            } else {
                alert('Network error. Please try again.');
            }
        }
    }

    // ── Flash message auto-dismiss ────────────────────────────
    (function() {
        function dismissFlash(id, delay) {
            const el = document.getElementById(id);
            if (!el) return;
            setTimeout(() => {
                el.style.transition = 'opacity 0.4s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 420);
            }, delay);
        }
        dismissFlash('flashSuccess', 4000);
        dismissFlash('flashError', 7000);
    })();

    // ── Initial render ────────────────────────────────────────
    renderTable();
</script>


<!-- ── History modal ──────────────────────────────────────── -->
<div class="modal-backdrop" id="historyModalBackdrop">
    <div class="modal-card history-modal-card">
        <div class="history-modal-header">
            <div>
                <p class="history-modal-label">Submission History</p>
                <p class="history-modal-title" id="historyModalTitle"></p>
            </div>
            <button class="button history-modal-close" onclick="closeHistoryModal()" aria-label="Close">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <use href="#close-icon" />
                </svg>
            </button>
        </div>
        <div id="historyModalBody" class="history-modal-body">
            <p class="helper history-loading">Loading&hellip;</p>
        </div>
    </div>
</div>

<script>
    const historyBackdrop = document.getElementById('historyModalBackdrop');

    function openHistoryModal(protocolId, title) {
        document.getElementById('historyModalTitle').textContent = title;
        document.getElementById('historyModalBody').innerHTML = '<p class="helper history-loading">Loading…</p>';
        historyBackdrop.classList.add('open');

        fetch(ROOT_URL + '/apply/allversions/' + protocolId)
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('historyModalBody').innerHTML =
                        '<p class="helper history-error">' + data.error + '</p>';
                    return;
                }
                renderHistory(data);
            })
            .catch(() => {
                document.getElementById('historyModalBody').innerHTML =
                    '<p class="helper history-offline">Submission history is not available offline. It will load once you reconnect.</p>';
            });
    }

    function closeHistoryModal() {
        historyBackdrop.classList.remove('open');
        closeFilePopup();
    }

    historyBackdrop.addEventListener('click', e => {
        if (e.target === historyBackdrop) closeHistoryModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeHistoryModal();
            closeFilePopup();
        }
    });

    function buildHistorySection(versions, sectionLabel) {
        if (!versions || versions.length === 0) return '';
        const rows = versions.map((v, i) => {
            const date = new Date(v.uploaded_at).toLocaleString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            const isLatest = i === 0;
            return `
                <div class="history-row${isLatest ? ' history-row--latest' : ''}">
                    <div class="history-row-meta">
                        <span class="history-ver">v${v.version_number}</span>
                        ${isLatest ? '<span class="history-latest-badge">Latest</span>' : ''}
                    </div>
                    <div class="history-row-detail">
                        <span class="history-filename">${v.original_name}</span>
                        <span class="helper">${date}</span>
                    </div>
                    <button class="button history-open-btn"
                        data-file-url="${ROOT_URL + '/apply/file/' + v.id}"
                        data-file-title="${v.original_name.replace(/"/g, '&quot;')}"
                        onclick="openFilePopup(this.dataset.fileUrl, this.dataset.fileTitle)">
                        <svg width="15" height="15" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <use href="#review-icon" />
                        </svg>
                        Open
                    </button>
                </div>`;
        }).join('');
        return `<div class="history-section-label">${sectionLabel}</div>${rows}`;
    }

    function renderHistory(data) {
        const body = document.getElementById('historyModalBody');

        const hasProtocolFiles = data.protocol_files && data.protocol_files.length > 0;
        const hasCertFiles = data.cert_files && data.cert_files.length > 0;
        const hasAuthFiles = data.auth_files && data.auth_files.length > 0;

        if (!hasProtocolFiles && !hasCertFiles && !hasAuthFiles) {
            body.innerHTML = '<p class="helper">No submission history found.</p>';
            return;
        }

        let html = '';
        html += buildHistorySection(data.protocol_files, 'Protocol Submissions');
        html += buildHistorySection(data.cert_files, 'IACUC Training Certificates');
        if (!data.is_pi) {
            html += buildHistorySection(data.auth_files, 'Authorization Letters');
        }
        body.innerHTML = html;
    }
</script>

<!-- ââ File popup modal (cert / auth letter / protocol versions) ââ -->
<div class="modal-backdrop" id="filePopupBackdrop">
    <div class="modal-card file-popup-card">
        <div class="file-popup-header">
            <span class="file-popup-title" id="filePopupTitle"></span>
            <button class="button file-popup-close" onclick="closeFilePopup()" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <use href="#close-icon" />
                </svg>
                Close
            </button>
        </div>
        <iframe class="file-popup-frame" id="filePopupFrame" title="Document preview" src="about:blank"></iframe>
    </div>
</div>

<script>
    const filePopupBackdrop = document.getElementById('filePopupBackdrop');

    function openFilePopup(fileUrl, title) {
        document.getElementById('filePopupTitle').textContent = title;
        document.getElementById('filePopupFrame').src = fileUrl;
        filePopupBackdrop.classList.add('open');
    }

    function closeFilePopup() {
        if (!filePopupBackdrop.classList.contains('open')) return;
        filePopupBackdrop.classList.remove('open');
        document.getElementById('filePopupFrame').src = 'about:blank';
    }

    filePopupBackdrop.addEventListener('click', e => {
        if (e.target === filePopupBackdrop) closeFilePopup();
    });
</script>

<!-- ── Upload Clearance modal (admin only) ────────────────── -->
<div class="modal-backdrop" id="clearanceModalBackdrop">
    <div class="modal-card clearance-modal-card">
        <h2>Upload Clearance</h2>
        <p id="clearanceSubtitle" class="helper clearance-modal-subtitle"></p>

        <div id="clearanceError" class="alert error-messages clearance-modal-error" hidden></div>

        <label for="clearance_file">Clearance document (PDF)</label>
        <input type="file" id="clearance_file" name="clearance_file"
            accept=".pdf,application/pdf" required>
        <p class="helper clearance-modal-hint">PDF only &middot; max 10 MB</p>

        <div class="modal-actions">
            <button class="button" type="button" onclick="closeClearanceModal()">Cancel</button>
            <button class="button btn-apply" type="button" id="clearanceSubmitBtn"
                onclick="submitClearanceUpload()">
                <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <use href="#upload-icon" />
                </svg>
                Upload &amp; Mark as Approved
            </button>
        </div>
    </div>
</div>

<script>
    const clearanceModal = document.getElementById('clearanceModalBackdrop');
    let currentClearanceProtocolId = null;

    function openClearanceModal(protocolId, title) {
        currentClearanceProtocolId = protocolId;
        document.getElementById('clearanceSubtitle').textContent = title;
        document.getElementById('clearance_file').value = '';
        document.getElementById('clearanceError').hidden = true;
        clearanceModal.classList.add('open');
    }

    function closeClearanceModal() {
        clearanceModal.classList.remove('open');
        currentClearanceProtocolId = null;
    }

    clearanceModal.addEventListener('click', e => {
        if (e.target === clearanceModal) closeClearanceModal();
    });

    async function submitClearanceUpload() {
        const fileInput = document.getElementById('clearance_file');
        const errBox = document.getElementById('clearanceError');
        const btn = document.getElementById('clearanceSubmitBtn');

        if (!fileInput.files.length) {
            errBox.textContent = 'Please select a file.';
            errBox.hidden = false;
            return;
        }

        btn.disabled = true;
        errBox.hidden = true;

        const formData = new FormData();
        formData.append('protocol_id', currentClearanceProtocolId);
        formData.append('clearance_file', fileInput.files[0]);
        formData.append('csrf_token', CSRF_TOKEN);

        try {
            const res = await fetch(CLEARANCE_UPLOAD_API, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                window.location.reload();
            } else {
                errBox.textContent = data.error ?? 'Upload failed. Please try again.';
                errBox.hidden = false;
                btn.disabled = false;
            }
        } catch (err) {
            errBox.textContent = 'Network error. Please try again.';
            errBox.hidden = false;
            btn.disabled = false;
        }
    }
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>