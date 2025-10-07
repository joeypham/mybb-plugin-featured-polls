// featuredpolls_front.js
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.fp-form-container').forEach(pollContainer => {
        initFeaturedPoll(pollContainer);
    });
});

function initFeaturedPoll(root) {
    if (!root || root.dataset.initialized) return;
    root.dataset.initialized = 'true';

    const form = root.querySelector('.fp-form');
    const optsBox = root.querySelector('.fp-options-inner');
    const feedback = root.querySelector('.fp-feedback');
    const optsTemplate = root.querySelector('.fp-options-template');
    const actions = root.querySelector('.fp-actions');
    let busy = false;

    const getLang = (key, fallback) => root.dataset[key] || fallback;

    function setBusy(state) {
        busy = state;
        root.querySelectorAll('button, input[type="submit"]').forEach(b => { b.disabled = state; });
    }

    function showMsg(msg, isErr) {
        if (!feedback) return;

        clearTimeout(feedback._fadeTimer);
        feedback.style.transition = "opacity 0.4s ease";
        feedback.style.background = isErr ? "#ffe5e5" : "#e6ffe6";
        feedback.style.color = isErr ? "#b00" : "#080";
        feedback.textContent = msg || "";

        if (msg) {
            feedback.style.display = "block";
            feedback.style.opacity = "0";
            requestAnimationFrame(() => {
                feedback.style.opacity = "1";
            });

            feedback._fadeTimer = setTimeout(() => {
                feedback.style.opacity = "0";
                feedback._fadeTimer = setTimeout(() => {
                    feedback.style.display = "none";
                    feedback.textContent = "";
                }, 400);
            }, 3000);
        } else {
            feedback.style.opacity = "0";
            feedback._fadeTimer = setTimeout(() => {
                feedback.style.display = "none";
                feedback.textContent = "";
            }, 400);
        }
    }

    function setOptionsFromTemplate() {
        if (optsTemplate && optsBox) {
            optsBox.innerHTML = optsTemplate.innerHTML;
        }
    }

    function setActionsHtml(html) {
        if (actions) {
            actions.innerHTML = html;
            bindActionButtons();
        }
    }

    function renderResults(html, newActionsHtml) {
        if (optsBox) {
            optsBox.innerHTML = html;
        }
        if (newActionsHtml !== undefined) {
            setActionsHtml(newActionsHtml);
        }
    }

    function fetchJSON(url, opts) {
        setBusy(true);
        return fetch(url, opts || { credentials: 'same-origin' })
            .then(r => r.json())
            .finally(() => { setBusy(false); });
    }

    function bindActionButtons() {
        const btnVote = root.querySelector('.fp-vote');
        if (btnVote) {
            btnVote.addEventListener('click', e => {
                e.preventDefault();
                if (busy) return;
                if (form.requestSubmit) form.requestSubmit();
                else form.submit();
            });
        }

        const btnViewResults = root.querySelector('.fp-view-results');
        if (btnViewResults) {
            btnViewResults.addEventListener('click', e => {
                e.preventDefault();
                if (busy) return;
                const pid = form.querySelector('input[name="pid"]').value;
                const url = `xmlhttp.php?action=featuredpolls_results&pid=${encodeURIComponent(pid)}`;
                fetchJSON(url)
                    .then(data => {
                        if (data && data.ok) {
                            renderResults(data.results_html, data.actions_html || '');
                            showMsg('', false);
                        } else {
                            showMsg((data && data.error) ? data.error : getLang('errorUnableFetchResults', 'Could not fetch results.'), true);
                        }
                    })
                    .catch(() => { showMsg(getLang('errorNetwork', 'A network error occurred.'), true); });
            });
        }

        const btnViewOptions = root.querySelector('.fp-view-options');
        if (btnViewOptions) {
            btnViewOptions.addEventListener('click', e => {
                e.preventDefault();
                if (busy) return;
                setOptionsFromTemplate();
                setActionsHtml(btnViewOptions.getAttribute('data-default-actions') || '');
                showMsg('', false);
            });
        }

        const btnUndo = root.querySelector('.fp-undo-vote');
        if (btnUndo) {
            btnUndo.addEventListener('click', e => {
                e.preventDefault();
                if (busy) return;
                const pid = form.querySelector('input[name="pid"]').value;
                const url = 'xmlhttp.php?action=featuredpolls_undo';
                const fd = new FormData();
                fd.append('pid', pid);
                fd.append('my_post_key', form.querySelector('input[name="my_post_key"]').value);
                fetchJSON(url, { method: 'POST', body: fd })
                    .then(data => {
                        if (data && data.ok) {
                            if (optsBox) optsBox.innerHTML = data.options_html;
                            setActionsHtml(data.actions_html || '');
                            if (optsTemplate) optsTemplate.innerHTML = data.options_html;
                            showMsg(data.message || '', false);
                        } else {
                            showMsg((data && data.error) ? data.error : getLang('errorUnableUndo', 'Could not undo vote.'), true);
                        }
                    })
                    .catch(() => { showMsg(getLang('errorNetwork', 'A network error occurred.'), true); });
            });
        }
    }

    if (form) {
        form.addEventListener('submit', ev => {
            ev.preventDefault();
            if (busy) return;
            const formData = new FormData(form);
            fetchJSON(form.action, { method: 'POST', body: formData })
                .then(data => {
                    if (data && data.ok) {
                        renderResults(data.results_html, data.actions_html || '');
                        showMsg(data.message || '', false);
                        if (data.redirect_url) {
                            setTimeout(() => { window.location = data.redirect_url; }, 600);
                        }
                    } else {
                        showMsg((data && data.error) ? data.error : getLang('errorGeneric', 'An unknown error occurred.'), true);
                    }
                })
                .catch(() => { showMsg(getLang('errorNetwork', 'A network error occurred.'), true); });
        });
    }

    bindActionButtons();
}