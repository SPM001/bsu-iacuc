<?php

/** @var array|null $user */
$title = "Submit Protocol";

include "includes/header.php";
include "includes/scroll-top.php";
?>

<link rel="stylesheet" href="<?= CSSPATH ?>/application.css">

<div class="body">
    <main class="main-content" id="main-content" tabindex="-1">

        <a class="button btn-back" id="btn-home" href="<?= ROOT ?>/submissions" onclick="askLeave(event, '<?= ROOT ?>/submissions')">
            <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <use href="#back-icon">
            </svg>
            Home
        </a>

        <div class="portal">
            <div class="portal-header">
                <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <use href="#beaker-icon" />
                </svg>
                <h1>IACUC Application Portal</h1>
            </div>

            <div class="stepper" id="stepper"></div>

            <div class="storage-notice">
                <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <use href="#info-icon" />
                </svg>
                <span>
                    <span class="bold">Progress is saved in this browser only.</span>
                    Clearing your browser data, switching browsers, or using a different device will reset your progress.
                </span>
            </div>

            <div class="card" id="page-content"></div>
        </div>

    </main>
</div>

<script>
    /* ═══════════════════════════════════════════════════════════════
   CONSTANTS
═══════════════════════════════════════════════════════════════ */
    const ROOT = '<?= ROOT ?>';
    const STEPS = ['Requirements', 'Terms', 'Documents', 'Download Form', 'Upload & Submit', 'Done'];
    const SAVE_KEY = 'bsu_iacuc_apply_v2_u<?= (int) ($_SESSION['user']['user_id'] ?? 0) ?>';

    /* ═══════════════════════════════════════════════════════════════
       STATE  (hydrated from localStorage on load)
    ═══════════════════════════════════════════════════════════════ */
    let state = {
        step: 0, // 0-4
        agreedTerms: false,
        agreedPrivacy: false,
        isPi: null, // null = not answered yet; true = yes PI; false = not PI
        certAlready: false, // server told us they have a cert on file
        /* uploaded file metadata (actual File objects can't be stored) */
        certName: null,
        authName: null,
        protocolName: null,
        title: '',
        submittedId: null,
    };

    /* Actual File references — not persisted */
    let files = {
        cert: null,
        auth: null,
        protocol: null
    };

    /* ═══════════════════════════════════════════════════════════════
       PERSISTENCE
    ═══════════════════════════════════════════════════════════════ */
    function saveState() {
        try {
            localStorage.setItem(SAVE_KEY, JSON.stringify(state));
        } catch (e) {}
    }

    function loadState() {
        try {
            const raw = localStorage.getItem(SAVE_KEY);
            if (raw) Object.assign(state, JSON.parse(raw));
        } catch (e) {}
    }

    function clearState() {
        try {
            localStorage.removeItem(SAVE_KEY);
        } catch (e) {}
    }

    /* ═══════════════════════════════════════════════════════════════
       NAVIGATION
    ═══════════════════════════════════════════════════════════════ */
    function goTo(n) {
        state.step = Math.max(0, Math.min(STEPS.length - 1, n));
        saveState();
        render();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    /* ═══════════════════════════════════════════════════════════════
       LEAVE GUARD
       — Custom modal intercepts all in-page navigation clicks.
       — A minimal beforeunload is kept ONLY for tab-close / hard
         reload (browsers block custom UI there; we cannot avoid it,
         but we make it fire as rarely as possible).
    ═══════════════════════════════════════════════════════════════ */
    let _guardArmed = false;

    /* Minimal native fallback – only reached on tab-close / reload */
    function _beforeUnloadHandler(e) {
        e.preventDefault();
        return (e.returnValue = '');
    }

    function armGuard() {
        if (_guardArmed) return;
        _guardArmed = true;
        window.addEventListener('beforeunload', _beforeUnloadHandler);
        document.addEventListener('click', _interceptClicks, true);
    }

    function disarmGuard() {
        if (!_guardArmed) return;
        _guardArmed = false;
        window.removeEventListener('beforeunload', _beforeUnloadHandler);
        document.removeEventListener('click', _interceptClicks, true);
    }

    /* Intercept any <a> click that would navigate away from this page */
    function _interceptClicks(e) {
        const anchor = e.target.closest('a[href]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        /* Ignore anchors, javascript: links, or the explicit askLeave handler */
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
        /* Ignore download links */
        if (anchor.hasAttribute('download')) return;
        /* Ignore links that already call askLeave */
        if (anchor.id === 'btn-home') return;

        e.preventDefault();
        e.stopImmediatePropagation();
        askLeave(e, href);
    }

    /* ═══════════════════════════════════════════════════════════════
       LEAVE DIALOG
    ═══════════════════════════════════════════════════════════════ */
    let _leaveHref = ROOT + '/submissions';

    function askLeave(e, overrideHref) {
        if (e && typeof e.preventDefault === 'function') e.preventDefault();
        const destination = overrideHref || _leaveHref;
        saveState();
        confirmAction(
            'Leave the form? Your progress has been saved. You can return and continue where you left off.', {
                okText: 'Leave',
                cancelText: 'Stay'
            }
        ).then((ok) => {
            if (ok) {
                disarmGuard();
                window.location.href = destination;
            }
        });
    }

    /* ═══════════════════════════════════════════════════════════════
       STEPPER
    ═══════════════════════════════════════════════════════════════ */
    function renderStepper() {
        const el = document.getElementById('stepper');
        const checkSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>`;
        let html = '';
        STEPS.forEach((label, i) => {
            const cls = i < state.step ? 'done' : i === state.step ? 'active' : '';
            const inner = i < state.step ? checkSvg : (i + 1);
            html += `<div class="step-dot ${cls}" title="${label}">${inner}</div>`;
            if (i < STEPS.length - 1)
                html += `<div class="step-line ${i < state.step ? 'done' : ''}"></div>`;
        });
        el.innerHTML = html;
    }

    /* ═══════════════════════════════════════════════════════════════
       HELPERS
    ═══════════════════════════════════════════════════════════════ */
    const esc = s => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    function uploadBox(key, label, subtitle, required = false) {
        const name = state[key + 'Name'];
        if (name) {
            return `<div class="info-bar">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="file-badge-name">${esc(name)}</span>
            <button class="file-badge-remove" onclick="removeFile('${key}')" title="Remove">✕</button>
        </div>`;
        }
        // PDF only for protocol, more formats for others
        const accept = key === 'protocol' ? 'application/pdf,.pdf' : '.pdf,.jpg,.jpeg,.png';
        return `<label class="upload-box" style="cursor:pointer;">
        <input type="file" accept="${accept}" onchange="handleUpload(event,'${key}')" style="display:none;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
        </svg>
        <div class="upload-label">${label}${required ? ' <span style="color:#dc2626;font-size:.75rem;">*</span>' : ''}</div>
        <div class="upload-sub">${subtitle}</div>
    </label>`;
    }

    /* ═══════════════════════════════════════════════════════════════
       STEP 0 — Requirements & Process
    ═══════════════════════════════════════════════════════════════ */
    function step0() {
        const requirements = [{
                label: 'IACUC Training Certificate',
                note: state.certAlready ?
                    '<span style="color:#065f46;font-size:.75rem;">✓ You have already submitted your certificate.</span>' : '<span style="color:#dc2626;font-size:.75rem;">Required for first-time submitters.</span>',
            },
            {
                label: 'Authorization Letter by the Principal Investigator',
                note: '<span style="color:#6b7280;font-size:.75rem;">Required only if you are not the PI of the study.</span>',
            },
        ];

        const process = [
            `Attach requirements (if applicable). <br>
         <span style="font-size:.78rem;color:#6b7280;">
           Your training certificate is required unless you have already submitted one previously.
           If you are not the Principal Investigator, attach an authorization letter.
         </span>`,
            `Download the official IACUC protocol form, fill it in, then upload it in the next step.`,
            `Submit your completed form. You will be notified via email on updates on your protocol.`,
        ];

        return `
    <div class="page-tag">Step 1 of 5</div>
    <div class="page-title">Requirements &amp; Process</div>

    <div class="section-label">Requirements</div>
    ${requirements.map(r => `
    <div class="req-item" style="flex-direction:column;align-items:flex-start;gap:2px;">
        <div style="display:flex;align-items:center;gap:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px;flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <strong style="font-size:.85rem;">${r.label}</strong>
        </div>
        <div style="margin-left:24px;">${r.note}</div>
    </div>`).join('')}

    <div class="section-label" style="margin-top:1.5rem;">Process</div>
    ${process.map((s, i) => `
    <div class="process-step">
        <div class="process-num">${i + 1}</div>
        <div class="process-text" style="font-size:.85rem;line-height:1.6;">${s}</div>
    </div>`).join('')}

    <div class="btn-row" style="margin-top:1.5rem;">
        <button class="btn-primary" onclick="goTo(1)">Proceed →</button>
    </div>`;
    }

    /* ═══════════════════════════════════════════════════════════════
       STEP 1 — Terms & Conditions
    ═══════════════════════════════════════════════════════════════ */
    function step1() {
        return `
    <div class="page-tag">Step 2 of 5</div>
    <div class="page-title">Terms &amp; Conditions</div>

    <div class="section-label">Terms of use</div>
    <div class="tc-scroll">
        By submitting this application, you agree to comply with all applicable laws, regulations, and
        institutional policies governing the use of animals in research, teaching, and testing.
        Information provided must be accurate and complete. Any misrepresentation may result in denial
        or revocation of approval.<br><br>
        All animal use activities must be conducted exactly as described and approved. Changes to the
        approved protocol must be submitted and approved before implementation. You are responsible for
        ensuring all personnel are appropriately trained and listed on this protocol.<br><br>
        The IACUC reserves the right to inspect and audit animal use activities at any time.
        Non-compliance may result in suspension or termination of the protocol and reporting to
        relevant regulatory authorities.
    </div>

    <div class="section-label" style="margin-top:1.25rem;">Privacy policy</div>
    <div class="tc-scroll">
        Personal information collected through this application will be used solely for processing your
        IACUC protocol submission. Data may be shared with relevant institutional offices, the Bureau
        of Animal Industry, and accrediting bodies as required by law.<br><br>
        Submitted protocols are confidential institutional documents and will not be disclosed to
        unauthorized parties. You have the right to request access to your personal data and correct
        any inaccuracies by contacting the IACUC office.
    </div>

    <label class="check-label" style="margin-top:1rem;">
        <input type="checkbox" id="chk-terms" ${state.agreedTerms ? 'checked' : ''}>
        I have read and agree to the Terms &amp; Conditions
    </label>
    <label class="check-label" style="margin-top:.5rem;">
        <input type="checkbox" id="chk-privacy" ${state.agreedPrivacy ? 'checked' : ''}>
        I have read and agree to the Privacy Policy
    </label>

    <div class="btn-row" style="margin-top:1.25rem;">
        <button class="btn-secondary" onclick="goTo(0)" style="border:1px solid #d1d5db;">← Previous</button>
        <button class="btn-primary" onclick="proceedFromTerms()">Next →</button>
    </div>`;
    }

    /* ═══════════════════════════════════════════════════════════════
       STEP 2 — Attach Documents
    ═══════════════════════════════════════════════════════════════ */
    function step2() {
        const certRequired = !state.certAlready;

        return `
    <div class="page-tag">Step 3 of 5</div>
    <div class="page-title">Attach Documents</div>

    <div class="section-label">
        IACUC Training Certificate
        ${certRequired
            ? '<span style="color:#dc2626;font-size:.75rem;margin-left:4px;text-transform:none;">Required</span>'
            : ''}
    </div>
    ${state.certAlready
        ? `<div class="notice-bar">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                You have a stored IACUC training certificate.
           </div>`
        : uploadBox('cert', 'Click to upload certificate', 'PDF, JPG, PNG · max 10 MB', certRequired)
    }

    <div class="section-label" style="margin-top:1.25rem;">Are you the Principal Investigator?</div>
    <div class="btn-row" style="margin:0 0 .75rem;justify-content:flex-start;gap:10px;">
        <button type="button" class="pi-choice ${state.isPi === true ? 'pi-choice--active' : ''}"
                style="border:1px solid #d1d5db;" onclick="setIsPi(true)">Yes</button>
        <button type="button" class="pi-choice ${state.isPi === false ? 'pi-choice--active' : ''}"
                style="border:1px solid #d1d5db;" onclick="setIsPi(false)">No</button>
    </div>

    ${state.isPi === false ? `
    <div id="auth-section">
        <div class="section-label">
            Authorization Letter by PI
            <span style="color:#dc2626;font-size:.75rem;margin-left:4px;text-transform:none;">Required</span>
        </div>
        ${uploadBox('auth', 'Click to upload authorization letter', 'PDF, JPG, PNG · max 10 MB', true)}
    </div>` : ''}

    <div id="doc-error" class="error-messages" style="display:none;margin-top:.75rem;"></div>

    <div class="btn-row" style="margin-top:1.25rem;">
        <button class="btn-secondary" onclick="goTo(1)" style="border:1px solid #d1d5db;">← Previous</button>
        <button class="btn-primary" onclick="proceedFromDocs()">Next →</button>
    </div>`;
    }

    /* ═══════════════════════════════════════════════════════════════
       STEP 3 — Download Protocol Form
    ═══════════════════════════════════════════════════════════════ */
    function step3() {
        const formPdfUrl = ROOT + '/assets/forms/BSU-IACUC_Application_for_Protocol_Review_Form.pdf';
        const formDocxUrl = ROOT + '/assets/forms/BSU-IACUC_Application_for_Protocol_Review_Form.docx';

        return `
    <div class="page-tag">Step 4 of 5</div>
    <div class="page-title">Download Protocol Form</div>

    <p style="font-size:.85rem;color:#6b7280;line-height:1.6;margin-bottom:1.25rem;">
        Choose to fill in either the fillable PDF or the DOCX format of the official BSU-IACUC protocol form below. Incomplete forms will be returned for revision. Once done, proceed to
        the next step to upload your completed form.
    </p>

    <div class="section-label">Official Protocol Form</div>
    <a href="${formPdfUrl}" download class="btn-download">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M12 3v13.5m0 0l-4.5-4.5m4.5 4.5l4.5-4.5"/>
        </svg>
        Download BSU-IACUC Protocol Form (Fillable PDF)
    </a>
    <a href="${formDocxUrl}" download class="btn-download">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M12 3v13.5m0 0l-4.5-4.5m4.5 4.5l4.5-4.5"/>
        </svg>
        Download BSU-IACUC Protocol Form (.DOCX)
    </a>

    <div class="btn-row" style="margin-top:1.25rem;">
        <button class="btn-secondary" onclick="goTo(2)" style="border:1px solid #d1d5db;">← Previous</button>
        <button class="btn-primary" onclick="goTo(4)">Next →</button>
    </div>`;
    }

    /* ═══════════════════════════════════════════════════════════════
       STEP 4 — Upload Protocol Form & Enter Title
    ═══════════════════════════════════════════════════════════════ */
    function step4() {
        return `
    <div class="page-tag">Step 5 of 5</div>
    <div class="page-title">Upload Completed Form</div>

    <div class="section-label">Protocol title <span class="req">*</span></div>
    <div class="field" style="margin-bottom:.5rem;">
        <input type="text" id="inp-title" value="${esc(state.title)}"
               placeholder="e.g. Effects of X on Y in Z model" maxlength="255">
    </div>
    <div class="info-bar orange" style="margin-bottom:1.25rem;font-size:.78rem;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;flex-shrink:0;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
        Make sure this title matches the Protocol Title written on your downloaded form exactly.
    </div>

    <div class="section-label">Completed protocol form <span class="req">*</span></div>
    ${uploadBox('protocol', 'Click to upload completed form', 'PDF only · max 10 MB', true)}

    <div id="upload-error" class="error-messages" style="display:none;margin-top:.75rem;"></div>

    <div class="btn-row" style="margin-top:1.25rem;">
        <button class="btn-secondary" onclick="goTo(3)" style="border:1px solid #d1d5db;">← Previous</button>
        <button class="btn-success" id="btn-submit" onclick="submitProtocol()">
            <span id="submit-label">Submit Protocol</span>
            <span id="submit-spinner" style="display:none;">Submitting…</span>
        </button>
    </div>`;
    }

    /* ═══════════════════════════════════════════════════════════════
       STEP 5 — Done
    ═══════════════════════════════════════════════════════════════ */
    function step5() {
        return `
    <div class="success-center">
        <div class="success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="page-title" style="text-align:center;">Protocol Submitted!</div>
        <p class="success-desc">
            Your protocol has been received and will be assigned to a reviewer at BSU-CCARD.<br>
            Expect feedback within <strong>5–7 business days</strong>. You will be notified via email.
        </p>
        <div class="success-btn-row">
            <a href="${ROOT}/submissions?submitted=1" class="btn-primary" style="padding:10px 22px;border-radius:8px;text-decoration:none;font-size:.82rem;font-weight:600;background:#2d7a3a;color:#fff;" onclick="clearState(); disarmGuard();">
                Go to My Protocols
            </a>
        </div>
    </div>`;
    }

    /* ═══════════════════════════════════════════════════════════════
       RENDER
    ═══════════════════════════════════════════════════════════════ */
    function render() {
        // Clear stale file name state when File objects are gone (navigating back/forward loses them).
        if (!files.protocol && state.protocolName) {
            state.protocolName = null;
            saveState();
        }
        if (!files.auth && state.authName) {
            state.authName = null;
            saveState();
        }
        if (!files.cert && state.certName) {
            state.certName = null;
            saveState();
        }

        renderStepper();
        const pages = [step0, step1, step2, step3, step4, step5];
        document.getElementById('page-content').innerHTML = pages[state.step]();
        attachStepListeners();
    }

    function attachStepListeners() {
        /* terms checkboxes */
        const ct = document.getElementById('chk-terms');
        const cp = document.getElementById('chk-privacy');
        if (ct) ct.addEventListener('change', e => {
            state.agreedTerms = e.target.checked;
            saveState();
        });
        if (cp) cp.addEventListener('change', e => {
            state.agreedPrivacy = e.target.checked;
            saveState();
        });

        /* protocol title — save on every keystroke so Back preserves it */
        const ti = document.getElementById('inp-title');
        if (ti) ti.addEventListener('input', e => {
            state.title = e.target.value;
            saveState();
        });
    }

    /* ═══════════════════════════════════════════════════════════════
       STEP LOGIC
    ═══════════════════════════════════════════════════════════════ */
    function proceedFromTerms() {
        const t = document.getElementById('chk-terms');
        const p = document.getElementById('chk-privacy');
        state.agreedTerms = t && t.checked;
        state.agreedPrivacy = p && p.checked;
        saveState();
        if (!state.agreedTerms || !state.agreedPrivacy) {
            alert('Please accept both the Terms & Conditions and the Privacy Policy to continue.');
            return;
        }
        goTo(2);
    }

    function setIsPi(val) {
        state.isPi = val;
        saveState();
        /* clear auth file if user switches to "Yes / I am the PI" */
        if (val === true) {
            files.auth = null;
            state.authName = null;
            saveState();
        }
        render(); // re-render so auth section appears/disappears cleanly
    }

    function proceedFromDocs() {
        const errBox = document.getElementById('doc-error');
        const showErr = msg => {
            errBox.textContent = msg;
            errBox.style.display = 'flex';
        };
        errBox.style.display = 'none';

        if (!state.certAlready && !state.certName) {
            showErr('Please upload your IACUC Training Certificate.');
            return;
        }
        if (state.isPi === null) {
            showErr('Please indicate if you are the Principal Investigator.');
            return;
        }
        if (state.isPi === false && !state.authName) {
            showErr('Please upload the Authorization Letter of the Principal Investigator.');
            return;
        }

        goTo(3);
    }

    function handleUpload(event, key) {
        const file = event.target.files[0];
        if (!file) return;

        // Only allow PDF for protocol
        if (key === 'protocol') {
            // Check MIME type
            if (file.type !== 'application/pdf') {
                alert('Only PDF files are accepted for the protocol form.');
                event.target.value = ''; // Clear the file input
                return;
            }
            // Also check extension as additional validation
            const ext = file.name.split('.').pop().toLowerCase();
            if (ext !== 'pdf') {
                alert('Only PDF files are accepted for the protocol form.');
                event.target.value = '';
                return;
            }
        } else {
            // For cert and auth files
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only PDF, JPG, or PNG files are accepted.');
                event.target.value = '';
                return;
            }
            // Check extensions
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['pdf', 'jpg', 'jpeg', 'png'].includes(ext)) {
                alert('Only PDF, JPG, or PNG files are accepted.');
                event.target.value = '';
                return;
            }
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('File is too large. Maximum size is 10 MB.');
            event.target.value = '';
            return;
        }

        files[key] = file;
        state[key + 'Name'] = file.name;

        // Capture the title field before touching the DOM (step 4 only)
        const titleInp = document.getElementById('inp-title');
        if (titleInp) {
            state.title = titleInp.value;
        }

        saveState();

        // Surgically replace only the upload widget so the File object in
        // `files[key]` is never lost by a full re-render wiping the DOM.
        const wrapper = event.target.closest('.upload-box, .info-bar');
        if (wrapper) {
            const tmp = document.createElement('div');
            tmp.innerHTML = uploadBox(key, '', '');
            const replacement = tmp.firstElementChild;
            wrapper.replaceWith(replacement);
        } else {
            render();
        }
    }

    function removeFile(key) {
        files[key] = null;
        state[key + 'Name'] = null;
        saveState();
        render();
    }

    /* ═══════════════════════════════════════════════════════════════
       SUBMIT
    ═══════════════════════════════════════════════════════════════ */
    async function submitProtocol() {
        const errBox = document.getElementById('upload-error');
        const btnLabel = document.getElementById('submit-label');
        const spinner = document.getElementById('submit-spinner');
        const btn = document.getElementById('btn-submit');

        errBox.style.display = 'none';

        /* save title (field now lives on this step) */
        const titleInp = document.getElementById('inp-title');
        if (titleInp) {
            state.title = titleInp.value.trim();
            saveState();
        }

        if (!state.title) {
            errBox.textContent = 'Please enter a protocol title.';
            errBox.style.display = 'flex';
            return;
        }

        if (!state.protocolName || !files.protocol) {
            errBox.textContent = 'Please upload your completed protocol form.';
            errBox.style.display = 'flex';
            return;
        }

        btn.disabled = true;
        btnLabel.style.display = 'none';
        spinner.style.display = 'inline';

        const fd = new FormData();
        fd.append('title', state.title);
        fd.append('is_pi', state.isPi === true ? '1' : '0');
        fd.append('protocol_file', files.protocol);
        if (files.cert) fd.append('cert', files.cert);
        if (files.auth) fd.append('auth', files.auth);

        try {
            const res = await fetch(ROOT + '/apply/submit', {
                method: 'POST',
                body: fd
            });
            const json = await res.json();

            if (!res.ok || json.error) {
                errBox.textContent = json.error ?? 'Submission failed. Please try again.';
                errBox.style.display = 'flex';
                btn.disabled = false;
                btnLabel.style.display = 'inline';
                spinner.style.display = 'none';
                return;
            }

            state.submittedId = json.protocolId ?? null;
            clearState();
            disarmGuard();
            goTo(5);

        } catch (err) {
            errBox.textContent = 'Network error. Please check your connection and try again.';
            errBox.style.display = 'flex';
            btn.disabled = false;
            btnLabel.style.display = 'inline';
            spinner.style.display = 'none';
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       INIT
    ═══════════════════════════════════════════════════════════════ */
    loadState();

    /*
     * Check with the server whether this user already has a cert on file.
     */
    fetch(ROOT + '/apply/hascert')
        .then(r => r.json())
        .then(d => {
            state.certAlready = !!d.has_cert;
            // Don't saveState() here — certAlready is always re-fetched from the
            // server on load, so persisting it would create a stale {step:0} entry
            // in localStorage that falsely triggers "Continue Application" elsewhere.
            render();
        })
        .catch(() => {
            state.certAlready = false;
            render();
        });

    render();

    /* Arm the leave guard — disarmed on confirmed in-page navigation, submit success, and the Done-step link. */
    armGuard();
</script>

<?php include "includes/footer.php"; ?>