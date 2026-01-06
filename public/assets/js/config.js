/*!
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('configForm');
    if (!form) {
        return;
    }
    const storageSelect = form.querySelector('select[name="storage_type"]');
    const mysqlSettings = document.getElementById('mysql-settings');
    const ajaxField = form.querySelector('#ajaxField');
    const createBtn = form.querySelector('button[name="itemaction"][value="create_db"]');
    const saveBtn = form.querySelector('button[name="itemaction"][value="save"]');
    let testExecuted = false;
    let testFailed = false;

    const showSection = (id) => {
        document.querySelectorAll('.setup-section').forEach(el => {
            el.classList.toggle('active', el.id === id);
        });
    };

    storageSelect?.addEventListener('change', () => {
        if (mysqlSettings) mysqlSettings.style.display = storageSelect.value === 'mysql' ? 'grid' : 'none';
    });

    const showNotification = (msg, success = true) => {
        let el = document.getElementById('notification');
        if (!el) {
            el = document.createElement('div');
            el.id = 'notification';
            form.prepend(el);
        }
        el.className = `notification ${success ? 'success' : 'fail'}`;
        el.innerText = msg;

        setTimeout(() => {
            el.classList.add('hide');
            el.addEventListener('transitionend', () => el.remove(), { once: true });
        }, 2500);
    };

    const updateSaveState = () => {
        if (!saveBtn) {
            return;
        }
        saveBtn.disabled = (testExecuted && testFailed);
    };

    const handleAjax = async (action) => {
        ajaxField.value = 1;
        const formData = new FormData(form);
        formData.set('itemaction', action);

        try {
            const response = await fetch(globalThis.location.href, { method: 'POST', body: formData, credentials: 'same-origin' });
            const data = await response.json();
            showNotification(data.message, data.success);
            const csrfInput = form.querySelector('input[name="_csrf_token"]');
            if (csrfInput) {
                csrfInput.value = data.csrf;
            }
            if (data.state) {
                showSection(data.state);
            }
            if (action === 'test_storage') {
                testExecuted = true;
                testFailed = !data.success;
            }
            if (action === 'create_storage' && data.success) {
                testExecuted = false;
                testFailed = false;
            }
            if (createBtn) {
                createBtn.style.display = data.db_exists === false ? 'inline-block' : 'none';
            }
            const pending = Array.isArray(data.pending_migrations) ? data.pending_migrations : [];
            if (pending.length > 0) {
                document.getElementById('config_required')?.remove();
            } else {
                document.getElementById('migrations_pending')?.remove();
            }
            updateSaveState();
        } finally {
            ajaxField.value = 0;
        }
    };
    form.addEventListener('submit', (e) => {
        const btn = e.submitter;
        if (!btn || !globalThis.fetch) return;
        e.preventDefault();
        handleAjax(btn.value);
    });
    showSection(form.dataset.state || 'config_required');
});
