document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('configForm');
    if (!form) return;

    const storageSelect = form.querySelector('select[name="storage_type"]');
    const mysqlSettings = document.getElementById('mysql-settings');
    const configRequired = document.getElementById('config-required');
    const ajaxField = form.querySelector('#ajaxField');
    const createBtn = form.querySelector('button[name="itemaction"][value="create_db"]');
    const saveBtn = form.querySelector('button[name="itemaction"][value="save"]');

    const sections = {
        config: document.getElementById('config-required'),
        'create-step': document.getElementById('create-step'),
        migration: document.getElementById('migration-step'),
        admin: document.getElementById('admin-create'),
        complete: document.getElementById('setup-complete'),
    };

    let testExecuted = false;
    let testFailed = false;
    let pendingMigrations = [];

    const showSection = (sectionKey) => {
        Object.keys(sections).forEach(key => {
            if (sections[key]) {
                sections[key].style.display = (key === sectionKey) ? 'block' : 'none';
            }
        });
    };

    storageSelect?.addEventListener('change', () => {
        mysqlSettings.style.display = storageSelect.value === 'mysql' ? 'grid' : 'none';
    });

    const showNotification = (msg, success = true) => {
        let el = document.getElementById('notification');
        if (!el) {
            el = document.createElement('div');
            el.id = 'notification';
            form.prepend(el);
        }
        el.className = 'notification ' + (success ? 'success' : 'fail');
        el.innerText = msg;

        setTimeout(() => {
            el.classList.add('hide');
            el.addEventListener('transitionend', () => el.remove(), { once: true });
        }, 2500);
    };

    const updateSaveState = () => {
        if (saveBtn) {
            saveBtn.disabled = testExecuted && testFailed;
            if (pendingMigrations.length > 0) {
                saveBtn.disabled = true;
            }
        }
    };

    const handleAjax = (action) => {
        if (!window.fetch) return;

        ajaxField.value = 1;
        const formData = new FormData(form);
        formData.set('itemaction', action);

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(r => r.json())
            .then(data => {
                showNotification(data.message, data.success);
                const csrfInput = form.querySelector('input[name="_csrf_token"]');
                if (csrfInput) {
                    csrfInput.value = data.csrf;
                }
                if (data.state) {
                    switch (data.state) {
                        case 'config_required':
                            showSection('config');
                            break;
                        case 'storage_missing':
                        case 'migrations_pending':
                            showSection('migration');
                            break;
                        case 'admin_missing':
                            showSection('admin');
                            break;
                        case 'complete':
                            showSection('complete');
                            break;
                    }
                }

                if (action === 'test_storage') {
                    testExecuted = true;
                    testFailed = !data.success;
                }
                if (action === 'create_storage' && data.success) {
                    testExecuted = false;
                    testFailed = false;
                }
                updateSaveState();
                if (data.save_label) {
                    saveBtn.innerText = data.save_label;
                }
                if (createBtn) {
                    createBtn.style.display = data.db_exists === false ? 'inline-block' : 'none';
                }
                if (Array.isArray(data.pending_migrations)) {
                    pendingMigrations = data.pending_migrations;
                    if (pendingMigrations.length > 0) {
                        showNotification('Storage migrations are required before continuing.', true);
                        if (configRequired) {
                            configRequired.remove();
                        }
                    }
                    if (pendingMigrations.length === 0) {
                        const el = document.getElementById('migration-confirm');
                        if (el) el.remove();
                    }
                }
            })
            .finally(() => {
                ajaxField.value = 0;
            });
    };

    form.addEventListener('submit', function (e) {
        const btn = e.submitter;
        if (!btn || !window.fetch) { return; }
        e.preventDefault();
        handleAjax(btn.value);
    });

    // Show initial state
    const initialState = form.dataset.state;
    if (initialState) {
        switch (initialState) {
            case 'config_required': showSection('config'); break;
            case 'storage_missing': showSection('create-step'); break;
            case 'migrations_pending': showSection('migration'); break;
            case 'admin_missing': showSection('admin'); break;
            case 'complete': showSection('complete'); break;
        }
    }
});
