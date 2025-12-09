<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Contracts\ViewTemplateInterface;

abstract class AbstractViewTemplate implements ViewTemplateInterface
{
    abstract public function render(array $data): void;
    protected function renderSelectOptions(array $optionList): void
    {
        extract($optionList, EXTR_SKIP);
        foreach ($rows as $row):
            if (is_array($row['text'])):
?>
                <optgroup label="<?= $row['label'] ?>">
                    <?php
                    foreach ($row['text'] as $subrow) {
                        $this->renderOptionRow($subrow);
                    }
                    ?>
                </optgroup>
            <?php
            elseif (is_string($row['text'])):
                $this->renderOptionRow($row);
            ?>
        <?php
            endif;
        endforeach;
    }
    protected function renderOptionRow(array $row): void
    {
        ?>
        <option value="<?= $row['value'] ?>" <?= $row['selected'] === true ? "selected" : "" ?>><?= $row['text'] ?></option>
<?php
    }
}
