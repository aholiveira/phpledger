<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\SetupState;
use PHPLedger\Views\Templates\AbstractViewTemplate;

final class SetupViewFormTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <p>
            <label for="title"><?= $label['application_name'] ?></label>
            <input id="title" name="title" value="<?= htmlspecialchars($config['title'] ?? '') ?>">
        </p>
        <p>
            <label for="smtp_host"><?= $label['smtp_host'] ?></label>
            <input id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($config['smtp']['host'] ?? '') ?>">
        </p>
        <p>
            <label for="smtp_port"><?= $label['smtp_port'] ?></label>
            <input id="smtp_port" type="number" min="1" max="65535" name="smtp_port" value="<?= htmlspecialchars($config['smtp']['port'] ?? 25) ?>" required>
        </p>
        <p>
            <label for="smtp_from"><?= $label['from'] ?></label>
            <input id="smtp_from" name="smtp_from" value="<?= htmlspecialchars($config['smtp']['from'] ?? '') ?>">
        </p>
        <p>
            <label for="url"><?= $label['url'] ?></label>
            <input id="url" name="url" value="<?= htmlspecialchars($config['url'] ?? '') ?>">
        </p>
        <p>
            <label for="storage_type"><?= $label['storage_type'] ?></label>
            <select id="storage_type" name="storage_type">
                <option value="mysql" <?= ($config['storage']['type'] ?? 'mysql') === 'mysql' ? 'selected' : '' ?>>MySQL</option>
            </select>
        </p>
        <p>
            <label for="admin_username"><?= $label['admin_username'] ?? 'Admin Username' ?></label>
            <input id="admin_username" name="admin_username" value="<?= htmlspecialchars($config['admin']['username'] ?? 'admin') ?>" required>
        </p>
        <p>
            <label for="admin_password"><?= $label['admin_password'] ?? 'Admin Password' ?></label>
            <input id="admin_password" type="password" name="admin_password" value="<?= htmlspecialchars($config['admin']['password'] ?? 'admin') ?>" required>
        </p>
        <div class="settings mysql" id="mysql-settings" style="display: <?= ($config['storage']['type'] ?? 'mysql') === 'mysql' ? 'grid' : 'none' ?>">
            <h3><?= $label['mysql_settings'] ?></h3>
            <?php foreach (['host', 'port', 'database', 'user', 'password'] as $key): ?>
                <p>
                    <label for="storage_<?= $key ?>"><?= $label[$key] ?? ucfirst($key) ?></label>
                    <input id="storage_<?= $key ?>" name="storage_<?= $key ?>" <?= $key === 'port' ? 'type="number" min="1" max="65535"' : '' ?> value="<?= htmlspecialchars($config['storage']['settings'][$key] ?? ($key === 'port' ? 3306 : '')) ?>">
                </p>
            <?php endforeach; ?>
            <p>
                <button style="grid-column: 2;" type="submit" name="itemaction" value="test_storage"><?= $label['test_db'] ?? 'Test Database Connection' ?></button>
                <button style="grid-column: 2; display: <?= $state === SetupState::STORAGE_MISSING ? 'inline-block' : 'none' ?>;" type="submit" name="itemaction" value="create_storage"><?= $label['create_storage'] ?? 'Create storage' ?></button>
            </p>
        </div>
        <button type="submit" name="itemaction" value="save"><?= $state === SetupState::CONFIG_REQUIRED ? $label['save'] : $label['save_anyway'] ?></button>
<?php
    }
}
