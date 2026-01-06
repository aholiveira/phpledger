<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\SetupState;
use PHPLedger\Views\Templates\AbstractViewTemplate;

final class SetupViewFormTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <h2><?= $label['basic_configuration'] ?></h2>
        <p class="help"><?= $label['basic_configuration_help'] ?></p>
        <label for="title"><?= $label['application_name'] ?><span class="required">*</span></label>
        <input id="title" name="title" value="<?= htmlspecialchars($config['title'] ?? '') ?>" required>
        <label for="url"><?= $label['url'] ?><span class="required">*</span></label>
        <input id="url" name="url" value="<?= htmlspecialchars($config['url'] ?? '') ?>" required>
        <label for="admin_username"><?= $label['admin_username'] ?><span class="required">*</span></label>
        <input id="admin_username" type="text" name="admin_username" autocomplete="username" placeholder="admin" value="<?= htmlspecialchars($config['admin']['username'] ?? '') ?>" required>
        <label for="admin_password"><?= $label['admin_password'] ?><span class="required">*</span></label>
        <input id="admin_password" type="password" name="admin_password" autocomplete="new-password" placeholder="*****" value="<?= htmlspecialchars($config['admin']['password'] ?? '') ?>" required>
        <h2><?= $label['email_settings'] ?></h2>
        <p class="help"><?= $label['email_settings_help'] ?></p>
        <label for="smtp_host"><?= $label['smtp_host'] ?><span class="required">*</span></label>
        <input id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($config['smtp']['host'] ?? '') ?>" placeholder="smtp.example.com" required>
        <label for="smtp_port"><?= $label['smtp_port'] ?><span class="required">*</span></label>
        <input id="smtp_port" type="number" min="1" max="65535" name="smtp_port" value="<?= htmlspecialchars($config['smtp']['port'] ?? 25) ?>" required>
        <label for="smtp_from"><?= $label['from'] ?><span class="required">*</span></label>
        <input id="smtp_from" name="smtp_from" value="<?= htmlspecialchars($config['smtp']['from'] ?? '') ?>" required>
        <h2><?= $label['storage_settings'] ?></h2>
        <p class="help"><?= $label['storage_settings_help'] ?></p>
        <label for="storage_type"><?= $label['storage_type'] ?><span class="required">*</span></label>
        <select id="storage_type" name="storage_type" required>
            <option value="mysql" <?= ($config['storage']['type'] ?? 'mysql') === 'mysql' ? 'selected' : '' ?>>MySQL</option>
        </select>
        <div class="settings mysql" id="mysql-settings" style="display: <?= ($config['storage']['type'] ?? 'mysql') === 'mysql' ? 'grid' : 'none' ?>">
            <h3><?= $label['mysql_settings'] ?></h3>
            <?php foreach (['host', 'port', 'database', 'user', 'password'] as $key): ?>
                <label for="storage_<?= $key ?>"><?= $label[$key] ?></label>
                <input id="storage_<?= $key ?>" name="storage_<?= $key ?>" <?= $key === 'port' ? 'type="number" min="1" max="65535"' : '' ?> value="<?= htmlspecialchars($config['storage']['settings'][$key] ?? ($key === 'port' ? 3306 : '')) ?>">
            <?php endforeach; ?>
            <button style="grid-column: 2;" type="submit" name="itemaction" value="test_storage"><?= $label['test_db'] ?></button>
            <button style="grid-column: 2; display: <?= $state === SetupState::STORAGE_MISSING ? 'inline-block' : 'none' ?>;" type="submit" name="itemaction" value="create_storage"><?= $label['create_storage'] ?></button>
        </div>
        <button type="submit" name="itemaction" value="save"><?= $state === SetupState::CONFIG_REQUIRED ? $label['save'] : $label['save_anyway'] ?></button>
<?php
    }
}
