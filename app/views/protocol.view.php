<?php

/**
 * Protocol viewer
 *
 * Variables provided by Apply::view():
 *   $protocol  array  — protocol row (protocol_id, title, status, user_id, …)
 *   $version   array  — latest protocol_versions row
 *   $csrf      string — CSRF token
 *   $isStaff   bool   — admin or reviewer
 *   $isAdmin   bool   — admin only
 */

/** @var array  $protocol */
/** @var array  $version  */
/** @var string $csrf     */
/** @var bool   $isStaff    */
/** @var bool   $isAdmin    */
/** @var bool   $isReviewer */

$title = htmlspecialchars($protocol['research_title'] ?? 'Protocol', ENT_QUOTES, 'UTF-8');

$statusLabels = [
    'under review'   => 'Under Review',
    'needs revision' => 'Needs Revision',
    'reviewed'       => 'Reviewed',
    'endorsed'       => 'Endorsed',
    'approved'       => 'Approved',
];
$statusKey   = strtolower($protocol['status'] ?? '');
$statusLabel = $statusLabels[$statusKey] ?? ($protocol['status'] ?? '');
$isCompleted = in_array($statusKey, ['approved'], true);

$fileUrl    = ROOT . '/apply/file/' . (int) $version['id'];
$annotApi   = ROOT . '/apply/annotate';
$statusApi  = ROOT . '/apply/status';
$protocolId = (int) $protocol['protocol_id'];
$versionId  = (int) $version['id'];
$versionNum = (int) $version['version_number'];

// $backUrl is injected by the controller (includes ?status= for filter restore).
// Fall back only if somehow not provided.
$backUrl = $backUrl ?? ($isStaff ? ROOT . '/admin/home' : ROOT . '/submissions');

$submitterName     = trim(($protocol['submitter_first_name'] ?? '') . ' ' . ($protocol['submitter_last_name'] ?? ''));
$isPi              = ! empty($protocol['is_pi']);
$certUrl           = ROOT . '/apply/cert/' . (int) $protocol['user_id'];
$latestCertFileUrl = ! empty($latestCertVersion['id']) ? ROOT . '/apply/file/' . (int) $latestCertVersion['id'] : null;
$latestAuthFileUrl = ! empty($latestAuthVersion['id']) ? ROOT . '/apply/file/' . (int) $latestAuthVersion['id'] : null;

include 'includes/header.php';
?>

<!-- PDF.js from CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<link rel="stylesheet" href="<?= CSSPATH ?>/viewer.css">

<div class="viewer-body">

    <?php if ($isStaff): ?>
        <div class="notice-bar">
            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <use href="#account-icon" />
            </svg>
            <span>
                Submitted by <strong><?= htmlspecialchars($submitterName, ENT_QUOTES, 'UTF-8') ?></strong>
                <?= $isPi ? '(Principal Investigator)' : '(submitted with an authorization letter from the Principal Investigator)' ?>
            </span>
            <div class="notice-btns">
                <?php if ($latestCertFileUrl): ?>
                    <button class="tool-btn"
                        data-file-url="<?= htmlspecialchars($latestCertFileUrl, ENT_QUOTES, 'UTF-8') ?>"
                        onclick="openFilePopup(this.dataset.fileUrl, 'IACUC Training Certificate')">
                        View IACUC Training Certificate
                    </button>
                <?php else: ?>
                    <button class="tool-btn"
                        data-file-url="<?= htmlspecialchars($certUrl, ENT_QUOTES, 'UTF-8') ?>"
                        onclick="openFilePopup(this.dataset.fileUrl, 'IACUC Training Certificate')">
                        View IACUC Training Certificate
                    </button>
                <?php endif; ?>
                <?php if (! $isPi && $latestAuthFileUrl): ?>
                    <button class="tool-btn"
                        data-file-url="<?= htmlspecialchars($latestAuthFileUrl, ENT_QUOTES, 'UTF-8') ?>"
                        onclick="openFilePopup(this.dataset.fileUrl, 'Authorization Letter')">
                        View Authorization Letter
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isStaff && !empty($returnReason)): ?>
        <?php
        $rrItems = [];
        if (!empty($returnReason['wrong_cert']))   $rrItems[] = 'update IACUC training certificate';
        if (!empty($returnReason['wrong_auth']))   $rrItems[] = 'update authorization letter';
        if (!empty($returnReason['other_reason'])) $rrItems[] = 'revise protocol';
        $rrLabel = empty($rrItems) ? 'revise protocol' : implode('; ', $rrItems);
        ?>
        <div class="return-reason-bar">
            <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <use href="#info-icon" />
            </svg>
            Previously returned to: <?= htmlspecialchars($rrLabel, ENT_QUOTES, 'UTF-8') ?>
            <?php if (!empty($returnReason['comment'])): ?>
                <span class="return-reason-bar-comment">"<?= htmlspecialchars($returnReason['comment'], ENT_QUOTES, 'UTF-8') ?>"</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ── Top bar ──────────────────────────────────────────── -->
    <div class="viewer-topbar">
        <div class="viewer-topbar-left">
            <a href="<?= $backUrl ?>"
                class="tool-btn"
                <?php if ($isReviewer && $statusKey === 'under review'): ?>
                onclick="event.preventDefault();
   confirmAction(
       'Leave this review and return to the dashboard? Your comments have been saved.',
       {
           okText: 'Go Back',
           cancelText: 'Stay Here'
       }
   ).then(ok => {
       if (ok) window.location.href = this.href;
   });"
                <?php endif; ?>>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <use href="#back-icon" />
                </svg>
                Back
            </a>

            <span class="viewer-title"><?= $title ?></span>
            <span class="ver-badge">v<?= $versionNum ?></span>
            <span class="status-badge status-badge--<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <div class="viewer-topbar-right">
            <span class="page-indicator" id="pageIndicator">Loading…</span>

            <a class="tool-btn" href="<?= $fileUrl ?>" download="<?= htmlspecialchars($version['original_name'] ?? 'protocol', ENT_QUOTES, 'UTF-8') ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <use href="#download-icon" />
                </svg>
                Download
            </a>

            <?php if ($isReviewer && $statusKey === 'under review'): ?>
                <button class="tool-btn tool-btn--warn" id="btnNeedsRevision"
                    onclick="openReturnModal()">
                    Return for Revision
                </button>
                <button class="tool-btn tool-btn--success" id="btnApprove"
                    onclick="confirmAction('Finish your review? This will send the protocol to the IACUC admin for endorsement.', { okText: 'Finish Review', cancelText: 'Cancel' }).then(ok => ok && updateStatus('Reviewed'))">
                    Finish Review
                </button>
            <?php elseif ($isCompleted): ?>
                <span class="ver-badge ver-badge--green">✓ Approved</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isReviewer && $statusKey === 'under review'): ?>
        <!-- ── Annotation hint (reviewer only, while under review) ──────── -->
        <div class="annot-toolbar">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.414.586H9v-1.414A2 2 0 019 13z" />
            </svg>
            <span class="toolbar-hint">Click and drag on the document to add a comment.</span>
        </div>
    <?php endif; ?>

    <!-- ── Layout: PDF + sidebar ────────────────────────────── -->
    <div class="viewer-layout">

        <div class="pdf-column" id="pdfColumn"></div>

        <div class="annot-sidebar" id="annotSidebar">
            <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()" aria-expanded="true">
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="vertical-align:middle;margin-right:4px">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Comments
                </span>
                <svg class="sidebar-toggle-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="annot-sidebar-inner">
                <h3>Comments</h3>
                <div id="annotList">
                    <p class="annot-empty">Loading…</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Comment dialog (reviewer only, while under review) ──── -->
    <?php if ($isReviewer && $statusKey === 'under review'): ?>
        <div class="modal-backdrop" id="commentDialog">
            <div class="modal-card">
                <h2>Add Comment</h2>
                <textarea id="commentText" rows="4" placeholder="Type your comment…"></textarea>
                <div class="modal-actions">
                    <button class="tool-btn" onclick="cancelComment()">Cancel</button>
                    <button class="tool-btn active" onclick="saveComment()">Save</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div><!-- .viewer-body -->

<?php if ($isReviewer && $statusKey === 'under review'): ?>
    <!-- ── Return for Revision modal ─────────────────────────────────────────── -->
    <div class="modal-backdrop" id="returnRevisionBackdrop">
        <div class="modal-card return-modal-card">
            <div class="return-modal-header">
                <div>
                    <p class="return-modal-label">Return for Revision</p>
                    <p class="return-modal-title"><?= htmlspecialchars($protocol['research_title'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <button class="tool-btn" onclick="closeReturnModal()" aria-label="Close">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <use href="#close-icon" />
                    </svg>
                    Close
                </button>
            </div>
            <div class="return-modal-body">
                <p class="return-modal-intro">Optionally select the issue(s) with this protocol. The researcher will see this feedback.</p>

                <fieldset class="return-reasons-fieldset">
                    <legend class="return-reasons-legend">Issues found <span class="return-comment-optional">(optional)</span></legend>

                    <label class="return-reason-option">
                        <input type="checkbox" name="return_reason" value="wrong_cert" id="returnReasonWrongCert">
                        <span class="return-reason-label">Wrong / invalid IACUC training certificate</span>
                    </label>

                    <label class="return-reason-option">
                        <input type="checkbox" name="return_reason" value="wrong_auth" id="returnReasonWrongAuth">
                        <span class="return-reason-label">Wrong / invalid authorization letter</span>
                    </label>

                    <label class="return-reason-option">
                        <input type="checkbox" name="return_reason" value="other" id="returnReasonOther">
                        <span class="return-reason-label">Other</span>
                    </label>
                </fieldset>

                <label class="return-comment-label" for="returnComment">
                    Additional details <span class="return-comment-optional">(optional)</span>
                </label>
                <textarea id="returnComment" class="return-comment-textarea"
                    placeholder="Add any notes for the researcher..."
                    rows="4" maxlength="1000"></textarea>
                <p class="return-char-count"><span id="returnCharCount">0</span> / 1000</p>

                <div id="returnRevisionError" class="error-messages" hidden></div>

                <div class="return-modal-actions">
                    <button class="tool-btn" type="button" onclick="closeReturnModal()">Cancel</button>
                    <button class="tool-btn tool-btn--warn" type="button" id="returnRevisionSubmitBtn"
                        onclick="submitReturnRevision()">
                        <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <use href="#back-icon" />
                        </svg>
                        Return for Revision
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- File popup lives outside .viewer-body so it isn't scoped out of
     the .viewer-body .modal-backdrop selector in viewer.css -->
<div class="modal-backdrop" id="filePopupBackdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:900;align-items:center;justify-content:center;">
    <div class="modal-card file-popup-card">
        <div class="file-popup-header">
            <span class="file-popup-title" id="filePopupTitle"></span>
            <button class="tool-btn" onclick="closeFilePopup()" aria-label="Close">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <use href="#close-icon" />
                </svg>
                Close
            </button>
        </div>
        <iframe class="file-popup-frame" id="filePopupFrame" title="Document preview" src="about:blank"></iframe>
    </div>
</div>
<style>
    #filePopupBackdrop.open {
        display: flex !important;
    }

    .docx-fallback {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 3rem 2rem;
        text-align: center;
        color: var(--text-muted, #6b7280);
    }

    .docx-fallback svg {
        opacity: .45;
    }

    .docx-fallback p {
        max-width: 28rem;
        line-height: 1.6;
    }
</style>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const PDF_URL = <?= json_encode($fileUrl) ?>;
    const FILE_EXT = <?= json_encode(strtolower(pathinfo($version['original_name'] ?? '', PATHINFO_EXTENSION))) ?>;
    const VERSION_ID = <?= $versionId              ?>;
    const PROTOCOL_ID = <?= $protocolId             ?>;
    const IS_STAFF = <?= $isStaff    ? 'true' : 'false' ?>;
    const IS_ADMIN = <?= $isAdmin    ? 'true' : 'false' ?>;
    const IS_REVIEWER = <?= $isReviewer ? 'true' : 'false' ?>;
    const IS_COMPLETED = <?= $isCompleted ? 'true' : 'false' ?>;
    const STATUS_KEY = <?= json_encode($statusKey) ?>;
    const CAN_REVIEW = IS_REVIEWER && STATUS_KEY === 'under review';
    const CSRF_TOKEN = <?= json_encode($csrf)      ?>;
    const ANNOT_API = <?= json_encode($annotApi)  ?>;
    const STATUS_API = <?= json_encode($statusApi) ?>;

    // ── State ───────────────────────────────────────────────────────────────────
    let pdfDoc = null;
    let annotations = [];
    let pendingBox = null;
    let dragState = null;

    // ── Load PDF ─────────────────────────────────────────────────────────────────
    async function loadPdf() {
        // DOCX files cannot be rendered by PDF.js — show a download prompt instead.
        if (FILE_EXT === 'docx') {
            const col = document.getElementById('pdfColumn');
            const name = <?= json_encode(htmlspecialchars($version['original_name'] ?? 'protocol.docx', ENT_QUOTES, 'UTF-8')) ?>;
            col.innerHTML =
                '<div class="docx-fallback">' +
                '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">' +
                '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>' +
                '<polyline points="14 2 14 8 20 8"/>' +
                '</svg>' +
                '<p>This protocol was submitted as a Word document (.docx) and cannot be previewed here.</p>' +
                '<a class="button" href="' + escHtml(PDF_URL) + '" download="' + escHtml(name) + '">Download to view</a>' +
                '</div>';
            await loadAnnotations();
            return;
        }
        try {
            pdfDoc = await pdfjsLib.getDocument(PDF_URL).promise;
            const col = document.getElementById('pdfColumn');
            col.innerHTML = '';

            for (let p = 1; p <= pdfDoc.numPages; p++) {
                const page = await pdfDoc.getPage(p);
                const scale = Math.min(1.5, (col.clientWidth - 48) / page.getViewport({
                    scale: 1
                }).width);
                const vp = page.getViewport({
                    scale
                });

                const wrapper = document.createElement('div');
                wrapper.className = 'page-wrapper';
                wrapper.dataset.page = p;
                wrapper.style.width = vp.width + 'px';

                const canvas = document.createElement('canvas');
                canvas.className = 'pdf-canvas';
                canvas.width = vp.width;
                canvas.height = vp.height;
                wrapper.appendChild(canvas);

                const overlay = document.createElement('div');
                overlay.className = 'annot-overlay';
                overlay.dataset.origW = vp.width;
                overlay.dataset.origH = vp.height;
                overlay.dataset.page = p;

                if (CAN_REVIEW) {
                    overlay.style.cursor = 'crosshair';
                    attachDrawListeners(overlay, p, canvas);
                }
                wrapper.appendChild(overlay);
                col.appendChild(wrapper);

                await page.render({
                    canvasContext: canvas.getContext('2d'),
                    viewport: vp
                }).promise;
            }

            document.getElementById('pageIndicator').textContent =
                pdfDoc.numPages + (pdfDoc.numPages === 1 ? ' page' : ' pages');

        } catch (err) {
            document.getElementById('pdfColumn').innerHTML =
                '<p class="error-msg">Could not load document: ' + escHtml(err.message) + '</p>';
            // Still attempt to load any cached annotations even if the PDF failed.
        }
        // Load annotations separately so a network failure there does not
        // report as a document error.
        await loadAnnotations();
    }

    // ── Draw listeners ───────────────────────────────────────────────────────────
    // canvas is passed so we always read its *current* rendered size,
    // which may differ from the original viewport dims after CSS scaling.
    function attachDrawListeners(overlay, pageNum, canvas) {
        overlay.addEventListener('mousedown', e => {
            e.preventDefault();
            const rect = overlay.getBoundingClientRect();
            const ghost = document.createElement('div');
            ghost.className = 'annot-ghost';
            overlay.appendChild(ghost);
            dragState = {
                pageNum,
                startX: e.clientX - rect.left,
                startY: e.clientY - rect.top,
                canvas,
                ghost
            };
        });

        overlay.addEventListener('mousemove', e => {
            if (!dragState || dragState.pageNum !== pageNum) return;
            const rect = overlay.getBoundingClientRect();
            const curX = e.clientX - rect.left;
            const curY = e.clientY - rect.top;
            const g = dragState.ghost;
            g.style.left = Math.min(dragState.startX, curX) + 'px';
            g.style.top = Math.min(dragState.startY, curY) + 'px';
            g.style.width = Math.abs(curX - dragState.startX) + 'px';
            g.style.height = Math.abs(curY - dragState.startY) + 'px';
        });

        overlay.addEventListener('mouseup', e => {
            if (!dragState || dragState.pageNum !== pageNum) return;
            const rect = overlay.getBoundingClientRect();
            const curX = e.clientX - rect.left;
            const curY = e.clientY - rect.top;
            const rawW = Math.abs(curX - dragState.startX);
            const rawH = Math.abs(curY - dragState.startY);
            dragState.ghost?.remove();

            if (rawW < 10 || rawH < 10) {
                dragState = null;
                return;
            }

            // Normalise against the canvas's *current* rendered size
            const cRect = dragState.canvas.getBoundingClientRect();
            pendingBox = {
                pageNum: dragState.pageNum,
                x: Math.min(dragState.startX, curX) / cRect.width,
                y: Math.min(dragState.startY, curY) / cRect.height,
                w: rawW / cRect.width,
                h: rawH / cRect.height,
            };
            dragState = null;
            openCommentDialog();
        });
    }

    // ── Comment dialog ───────────────────────────────────────────────────────────
    function openCommentDialog() {
        document.getElementById('commentText').value = '';
        document.getElementById('commentDialog').classList.add('open');
        document.getElementById('commentText').focus();
    }

    function cancelComment() {
        pendingBox = null;
        document.getElementById('commentDialog').classList.remove('open');
    }
    async function saveComment() {
        const text = document.getElementById('commentText').value.trim();
        if (!text || !pendingBox) return;
        document.getElementById('commentDialog').classList.remove('open');

        const res = await fetch(ANNOT_API, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                action: 'save',
                version_id: VERSION_ID,
                page_number: pendingBox.pageNum,
                x: pendingBox.x,
                y: pendingBox.y,
                width: pendingBox.w,
                height: pendingBox.h,
                comment: text,
            }),
        });
        const data = await res.json();
        if (data.ok) {
            annotations.push({
                id: data.id,
                page_number: pendingBox.pageNum,
                x: pendingBox.x,
                y: pendingBox.y,
                width: pendingBox.w,
                height: pendingBox.h,
                comment: text,
                created_at: new Date().toISOString(),
            });
            pendingBox = null;
            renderAnnotations();
        } else if (data.queued) {
            // Queued while offline — show a temporary local preview so the
            // reviewer can see what they typed; it will be saved on reconnect.
            annotations.push({
                id: 'queued-' + Date.now(),
                page_number: pendingBox.pageNum,
                x: pendingBox.x,
                y: pendingBox.y,
                width: pendingBox.w,
                height: pendingBox.h,
                comment: text,
                created_at: new Date().toISOString(),
                _queued: true,
            });
            pendingBox = null;
            renderAnnotations();
        } else {
            alert('Could not save comment: ' + (data.error ?? 'unknown error'));
        }
    }

    // ── Load + render annotations ─────────────────────────────────────────────────
    async function loadAnnotations() {
        try {
            const res = await fetch(ANNOT_API + '?version_id=' + VERSION_ID);
            const data = await res.json();
            annotations = Array.isArray(data) ? data : [];
        } catch (err) {
            // Offline and no cached copy — start with an empty list.
            // Any queued annotations added this session are already in memory.
            annotations = [];
        }
        renderAnnotations();
    }

    function renderAnnotations() {
        document.querySelectorAll('.annot-box').forEach(el => el.remove());

        annotations.forEach((ann, idx) => {
            const overlay = document.querySelector('.annot-overlay[data-page="' + ann.page_number + '"]');
            if (!overlay) return;

            // Use the canvas's current rendered size so boxes stay in the right
            // place regardless of CSS scaling or viewport width.
            const canvas = overlay.closest('.page-wrapper')?.querySelector('canvas');
            const pw = canvas ? canvas.getBoundingClientRect().width : parseFloat(overlay.dataset.origW);
            const ph = canvas ? canvas.getBoundingClientRect().height : parseFloat(overlay.dataset.origH);
            const box = document.createElement('div');
            box.className = 'annot-box' + (ann._queued ? ' annot-box--queued' : '');
            box.dataset.annotId = ann.id;
            box.style.left = (ann.x * pw) + 'px';
            box.style.top = (ann.y * ph) + 'px';
            box.style.width = (ann.width * pw) + 'px';
            box.style.height = (ann.height * ph) + 'px';
            box.title = ann._queued ? '[Queued - pending sync] ' + ann.comment : ann.comment;

            const label = document.createElement('span');
            label.className = 'annot-label';
            label.textContent = idx + 1;
            box.appendChild(label);
            box.addEventListener('click', () => highlightSidebarItem(ann.id));
            overlay.appendChild(box);
        });

        renderSidebar();
    }

    function renderSidebar() {
        const list = document.getElementById('annotList');
        if (!annotations.length) {
            list.innerHTML = '<p class="annot-empty">No comments yet.</p>';
            return;
        }
        list.innerHTML = annotations.map((ann, idx) => `
        <div class="annot-item${ann._queued ? ' annot-item--queued' : ''}" id="sidebar-${ann.id}" onclick="scrollToAnnotation(${ann.id})">
            <div class="annot-item-header">
                <span class="annot-num">${idx + 1}</span>
                <span class="annot-page">Page ${ann.page_number}</span>
                ${ann._queued ? '<span class="annot-queued-badge">Pending sync</span>' : ''}
                ${CAN_REVIEW && !ann._queued ? `
                <button class="annot-delete" title="Delete comment"
                    onclick="confirmAction('Delete this comment? This cannot be undone.', { okText: 'Delete', danger: true }).then(ok => ok && deleteAnnotation(${ann.id}))">✕</button>` : ''}
            </div>
            <p class="annot-comment">${escHtml(ann.comment)}</p>
            <p class="annot-date">${formatAnnotDate(ann.created_at)}</p>
        </div>
    `).join('');
    }

    function formatAnnotDate(value) {
        if (!value) return '';
        const d = new Date(value);
        if (isNaN(d.getTime())) return '';
        return d.toLocaleString('en-PH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function highlightSidebarItem(annotId) {
        document.querySelectorAll('.annot-item').forEach(el => el.classList.remove('highlight'));
        const el = document.getElementById('sidebar-' + annotId);
        if (el) {
            el.classList.add('highlight');
            el.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }

    function scrollToAnnotation(annotId) {
        const box = document.querySelector('.annot-box[data-annot-id="' + annotId + '"]');
        if (box) box.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
        highlightSidebarItem(annotId);
    }

    // ── Delete annotation ─────────────────────────────────────────────────────────
    async function deleteAnnotation(annotId) {
        const res = await fetch(ANNOT_API, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                action: 'delete',
                id: annotId
            }),
        });
        const data = await res.json();
        if (data.ok) {
            annotations = annotations.filter(a => a.id !== annotId);
            renderAnnotations();
        }
    }

    // ── Status update ─────────────────────────────────────────────────────────────
    async function updateStatus(newStatus) {
        try {
            const res = await fetch(STATUS_API, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify({
                    protocol_id: PROTOCOL_ID,
                    status: newStatus
                }),
            });
            const data = await res.json();
            if (data.ok) {
                window.location.href = <?= json_encode($backUrl) ?>;
            } else if (data.queued) {
                // Queued while offline — the action-queue banner tells the reviewer.
            } else {
                alert('Error: ' + (data.error ?? 'unknown error'));
            }
        } catch (err) {
            // Should not reach here — action-queue.js intercepts failed POSTs.
            // If it does, the action is lost, so surface a clear message.
            alert('Could not reach the server and the action could not be queued. Please check your connection.');
        }
    }

    // ── Mobile sidebar toggle ─────────────────────────────────────────────────────
    function toggleSidebar() {
        const sidebar = document.getElementById('annotSidebar');
        const btn = document.getElementById('sidebarToggle');
        const isCollapsed = sidebar.classList.toggle('collapsed');
        btn.classList.toggle('collapsed', isCollapsed);
        btn.setAttribute('aria-expanded', String(!isCollapsed));
    }

    // ── Util ──────────────────────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    loadPdf();

    // ── File popup (cert / auth letter) ──────────────────────────────────────────
    const filePopupBackdrop = document.getElementById('filePopupBackdrop');

    function openFilePopup(fileUrl, title) {
        document.getElementById('filePopupTitle').textContent = title;
        document.getElementById('filePopupFrame').src = fileUrl;
        filePopupBackdrop.classList.add('open');
    }

    function closeFilePopup() {
        filePopupBackdrop.classList.remove('open');
        document.getElementById('filePopupFrame').src = 'about:blank';
    }

    filePopupBackdrop.addEventListener('click', e => {
        if (e.target === filePopupBackdrop) closeFilePopup();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeFilePopup();
    });

    // Re-render annotation boxes whenever the viewport is resized
    // so they stay aligned with the (CSS-scaled) canvas.
    let _resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(_resizeTimer);
        _resizeTimer = setTimeout(renderAnnotations, 100);
    });

    <?php if ($isReviewer && $statusKey === 'under review'): ?>
        // ── Return for Revision modal ─────────────────────────────────────────────
        const RETURN_REVISION_API = <?= json_encode(ROOT . '/apply/return_revision') ?>;
        const returnBackdrop = document.getElementById('returnRevisionBackdrop');
        const returnComment = document.getElementById('returnComment');
        const returnCharCount = document.getElementById('returnCharCount');

        function openReturnModal() {
            document.querySelectorAll('input[name="return_reason"]').forEach(cb => cb.checked = false);
            returnComment.value = '';
            returnCharCount.textContent = '0';
            document.getElementById('returnRevisionError').hidden = true;
            returnBackdrop.classList.add('open');
        }

        function closeReturnModal() {
            returnBackdrop.classList.remove('open');
        }

        returnBackdrop.addEventListener('click', e => {
            if (e.target === returnBackdrop) closeReturnModal();
        });

        returnComment.addEventListener('input', () => {
            returnCharCount.textContent = returnComment.value.length;
        });

        async function submitReturnRevision() {
            const selectedReasons = [...document.querySelectorAll('input[name="return_reason"]:checked')].map(cb => cb.value);
            const commentText = returnComment.value.trim();
            const errBox = document.getElementById('returnRevisionError');
            const submitBtn = document.getElementById('returnRevisionSubmitBtn');

            errBox.hidden = true;
            submitBtn.disabled = true;

            try {
                const res = await fetch(RETURN_REVISION_API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        protocol_id: PROTOCOL_ID,
                        reasons: selectedReasons,
                        comment: commentText
                    })
                });
                const data = await res.json();

                if (data.ok) {
                    window.location.href = <?= json_encode($backUrl) ?>;
                } else {
                    errBox.textContent = data.error ?? 'Could not return protocol. Please try again.';
                    errBox.hidden = false;
                    submitBtn.disabled = false;
                }
            } catch {
                errBox.textContent = 'Network error. Please try again.';
                errBox.hidden = false;
                submitBtn.disabled = false;
            }
        }
    <?php endif; ?>
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>