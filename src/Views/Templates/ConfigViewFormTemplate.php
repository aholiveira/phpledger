<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Views\Templates\AbstractViewTemplate;
use PHPLedger\Util\Html;
use PHPLedger\Util\CSRF;

final class ConfigViewFormTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>

        <form method="POST">
            <?= CSRF::inputField() ?>
            <p><label for="title"><?= $label['application_name'] ?></label>
                <input id="title" name="title" value="<?= htmlspecialchars($config['title']) ?>">
            </p>
            <p><label for="smtp_host"><?= $label['smtp_host'] ?></label>
                <input id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($config['smtp']['host'] ?? '') ?>">
            </p>
            <p><label for="smtp_port"><?= $label['smtp_port'] ?></label>
                <input id="smtp_port" type="number" min="1" max="65535" name="smtp_port" value="<?= htmlspecialchars($config['smtp']['port'] ?? 25) ?>" required>
            </p>
            <p><label for="smtp_from"><?= $label['from'] ?></label>
                <input id="smtp_from" name="smtp_from" value="<?= htmlspecialchars($config['smtp']['from'] ?? '') ?>">
            </p>
            <p><label for="url"><?= $label['url'] ?></label>
                <input id="url" name="url" value="<?= htmlspecialchars($config['url'] ?? '') ?>">
            </p>
            <p><label for="storage_type"><?= $label['storage_type'] ?></label>
                <select id="storage_type" name="storage_type">
                    <option value="none" <?= ($config['storage']['type'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                    <option value="mysql" <?= ($config['storage']['type'] ?? '') === 'mysql' ? 'selected' : '' ?>>MySQL</option>
                </select>
            </p>
            <div class="settings mysql" id="mysql-settings" style="display: <?= ($config['storage']['type'] ?? '') === 'mysql' ? 'grid' : 'none' ?>">
                <h3><?= $label['mysql_settings'] ?></h3>
                <?php foreach (['host', 'port', 'database', 'user', 'password'] as $key): ?>
                    <p>
                        <label for="storage_<?= $key ?>"><?= $label[$key] ?? ucfirst($key) ?></label>
                        <input id="storage_<?= $key ?>" name="storage_<?= $key ?>" <?= $key === 'port' ? 'type="number" min="1" max="65535"' : '' ?> value="<?= htmlspecialchars($config['storage']['settings'][$key] ?? ($key === 'port' ? 3306 : '')) ?>">
                    </p>
                <?php endforeach; ?>
            </div>
            <button type="submit"><?= $label['save'] ?></button>
        </form>
<?php
    }
}
