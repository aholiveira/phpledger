<?php

namespace PHPLedger\Views;

use PHPLedger\Util\CSRF;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;

class ConfigView
{
    public function render(array $config, bool $hasPermission, bool $success, array $messages = []): void
    {
        $pagetitle = L10n::l("Configuration");
        $t = htmlspecialchars($config['title']);
        $st = htmlspecialchars($config['storage']['type'] ?? 'mysql');
        $s = $config['storage']['settings'] ?? [];
        $smtp_host = htmlspecialchars($config['smtp']['host'] ?? '');
        $smtp_port = htmlspecialchars($config['smtp']['port'] ?? 25);
        $smtp_from = htmlspecialchars($config['smtp']['from'] ?? '');
        $url = htmlspecialchars($config['url'] ?? '');
?>
        <!DOCTYPE html>
        <html lang="<?= L10n::html() ?>">

        <head>
            <?php Html::header($pagetitle); ?>
        </head>

        <body>
            <?php if (!empty($messages)): ?>
                <div id="notification" class="notification <?= $success ? "success" : "fail" ?>">
                    <?= htmlspecialchars(implode(", ", $messages)) ?>
                </div>
                <script>
                    const el = document.getElementById('notification');
                    setTimeout(() => {
                        el.classList.add('hide');
                        el.addEventListener('transitionend', () => el.remove(), {
                            once: true
                        });
                    }, 2500);
                </script>
            <?php endif ?>

            <div class="maingrid">
                <?php Html::menu(); ?>
                <div class="header" style="height: 0;"></div>
                <main>
                    <div class="main config" id="main">
                        <?php if ($hasPermission): ?>
                            <form method="post">
                                <?= CSRF::inputField() ?>
                                <p><label><?= L10n::l("application_name") ?></label><input name="title" value="<?= $t ?>"></p>
                                <p><label><?= L10n::l("smtp_host") ?></label><input name="smtp_host" value="<?= $smtp_host ?>"></p>
                                <p><label><?= L10n::l("smtp_port") ?></label><input name="smtp_port" type="number" min="1" max="65535" value="<?= $smtp_port ?>" required></p>
                                <p><label><?= L10n::l("from") ?></label><input name="smtp_from" value="<?= $smtp_from ?>"></p>
                                <p><label><?= L10n::l("url") ?></label><input name="url" value="<?= $url ?>"></p>
                                <p><label><?= L10n::l("storage_type") ?></label>
                                    <select name="storage_type">
                                        <option value="none" <?= $st === 'none' ? ' selected' : '' ?>>None</option>
                                        <option value="mysql" <?= $st === 'mysql' ? ' selected' : '' ?>>MySQL</option>
                                    </select>
                                </p>
                                <div class="settings mysql" id="mysql-settings" style="display: <?= $st === 'mysql' ? 'grid' : 'none' ?>;">
                                    <h3><?= L10n::l("mysql_settings") ?></h3>
                                    <p><label><?= L10n::l("host") ?></label><input name="storage_host" value="<?= htmlspecialchars($s['host'] ?? '') ?>"></p>
                                    <p><label><?= L10n::l("port") ?></label><input name="storage_port" type="number" min="1" max="65535" value="<?= htmlspecialchars($s['port'] ?? 3306) ?>" required></p>
                                    <p><label><?= L10n::l("database") ?></label><input name="storage_database" value="<?= htmlspecialchars($s['database'] ?? '') ?>"></p>
                                    <p><label><?= L10n::l("user") ?></label><input name="storage_user" value="<?= htmlspecialchars($s['user'] ?? '') ?>"></p>
                                    <p><label><?= L10n::l("password") ?></label>
                                        <!-- <div style="position:relative; display:inline-block;"> -->
                                        <input id="storage_password" name="storage_password" type="password" value="<?= htmlspecialchars($s['password'] ?? '') ?>" style="padding-right: 30px;">
                                        <!-- <span id="togglePassword" style="position:absolute; right:5px; top:50%; transform:translateY(-50%); cursor:pointer;">V</span> -->
                                        <!-- </div> -->
                                    </p>
                                </div>
                                <button style="width:fit-content;" type="submit"><?= L10n::l("save") ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </main>
                <?php Html::footer(); ?>
            </div>
        </body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const storageSelect = document.querySelector('select[name="storage_type"]');
                const mysqlSettings = document.getElementById('mysql-settings');

                storageSelect.addEventListener('change', function() {
                    if (this.value === 'mysql') {
                        mysqlSettings.style.display = 'block';
                    } else {
                        mysqlSettings.style.display = 'none';
                    }
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const storageSelect = document.querySelector('select[name="storage_type"]');
                const mysqlSettings = document.getElementById('mysql-settings');

                storageSelect.addEventListener('change', function() {
                    mysqlSettings.style.display = this.value === 'mysql' ? 'block' : 'none';
                });

                const form = document.querySelector('form');
                form.addEventListener('submit', function(e) {
                    let valid = true;
                    const requiredFields = [
                        'title', 'smtp_host', 'smtp_from', 'storage_type'
                    ];
                    if (storageSelect.value === 'mysql') {
                        requiredFields.push(
                            'storage_host', 'storage_user', 'storage_database'
                        );
                    }

                    requiredFields.forEach(name => {
                        const field = form.querySelector(`[name="${name}"]`);
                        if (!field.value.trim()) {
                            valid = false;
                            field.style.borderColor = 'red';
                        } else {
                            field.style.borderColor = '';
                        }
                    });

                    const smtpPort = form.querySelector('[name="smtp_port"]');
                    if (smtpPort && (smtpPort.value < 1 || smtpPort.value > 65535)) {
                        valid = false;
                        smtpPort.style.borderColor = 'red';
                    }

                    const storagePort = form.querySelector('[name="storage_port"]');
                    if (storagePort && (storagePort.value < 1 || storagePort.value > 65535)) {
                        valid = false;
                        storagePort.style.borderColor = 'red';
                    }

                    if (!valid) {
                        e.preventDefault();
                        alert('Please fill in all required fields correctly.');
                    }
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toggle = document.getElementById('togglePassword');
                const password = document.getElementById('storage_password');

                toggle.addEventListener('click', function() {
                    if (password.type === 'password') {
                        password.type = 'text';
                    } else {
                        password.type = 'password';
                    }
                });
            });
        </script>

        </html>

<?php
    }
}
