document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('configForm');
    if (!form) return;

    const storageSelect = form.querySelector('select[name="storage_type"]');
    const mysqlSettings = document.getElementById('mysql-settings');
    const ajaxField = form.querySelector('#ajaxField');
    const createBtn = form.querySelector('button[name="itemaction"][value="create_db"]');
    const saveBtn = form.querySelector('button[name="itemaction"][value="save"]');

    const sections = Object.fromEntries(
        ['config-required', 'create-step', 'migration-step', 'admin-create', 'setup-complete']
            .map(id => [id.split('-')[0], document.getElementById(id)])
    );

    let testExecuted = false;
    let testFailed = false;
    let pendingMigrations = [];

    const showSection = (key) => {
        Object.values(sections).forEach(sec => {
            if (sec) sec.style.display = 'none';
        });
        if (sections[key]) sections[key].style.display = 'block';
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
        if (!saveBtn) return;
        saveBtn.disabled = (testExecuted && testFailed) || pendingMigrations.length > 0;
    };

    const handleAjax = async (action) => {
        if (!globalThis.fetch) return;
        ajaxField.value = 1;
        const formData = new FormData(form);
        formData.set('itemaction', action);

        try {
            const response = await fetch(globalThis.location.href, { method: 'POST', body: formData, credentials: 'same-origin' });
            const data = await response.json();

            showNotification(data.message, data.success);

            const csrfInput = form.querySelector('input[name="_csrf_token"]');
            if (csrfInput) csrfInput.value = data.csrf;

            if (data.state) {
                const mapping = {
                    'config_required': 'config',
                    'storage_missing': 'migration',
                    'migrations_pending': 'migration',
                    'admin_missing': 'admin',
                    'complete': 'complete'
                };
                showSection(mapping[data.state] || 'config');
            }

            if (action === 'test_storage') {
                testExecuted = true;
                testFailed = !data.success;
            }
            if (action === 'create_storage' && data.success) {
                testExecuted = false;
                testFailed = false;
            }

            if (data.save_label) saveBtn.innerText = data.save_label;
            if (createBtn) createBtn.style.display = data.db_exists === false ? 'inline-block' : 'none';

            pendingMigrations = Array.isArray(data.pending_migrations) ? data.pending_migrations : [];
            if (pendingMigrations.length > 0) {
                showNotification('Storage migrations are required before continuing.', true);
                document.getElementById('config-required')?.remove();
            } else {
                document.getElementById('migration-confirm')?.remove();
            }

            updateSaveState();
        } finally {
            ajaxField.value = 0;
        }
    };

    form.addEventListener('submit', (e) => {
        const btn = e.submitter;
        if (!btn || !window.fetch) return;
        e.preventDefault();
        handleAjax(btn.value);
    });

    const initialStateMapping = {
        'config_required': 'config',
        'storage_missing': 'create-step',
        'migrations_pending': 'migration',
        'admin_missing': 'admin',
        'complete': 'complete'
    };
    showSection(initialStateMapping[form.dataset.state] || 'config');
});
