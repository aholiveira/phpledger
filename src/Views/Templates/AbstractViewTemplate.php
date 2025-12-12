<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Contracts\ViewTemplateInterface;

abstract class AbstractViewTemplate implements ViewTemplateInterface
{
    abstract public function render(array $data): void;
    protected function renderSelectOptions(array $optionList): void
    {
        $byParent = [];

        foreach ($optionList as $row) {
            $parent = $row['parentId'] ?? 0;
            $byParent[$parent][] = $row;
        }

        if (!isset($byParent[0])) {
            return;
        }
        foreach ($byParent[0] as $parent) {
            $id = $parent['value'];
            $children = $byParent[$id] ?? [];

            if (count($children) > 0 && $id > 0) {
?>
                <optgroup label="<?= $parent['text'] ?>">
                    <?php
                    $this->renderOptionRow($parent);
                    foreach ($children as $child) {
                        $this->renderOptionRow($child);
                    }
                    ?>
                </optgroup>
        <?php
            } else {
                $this->renderOptionRow($parent);
            }
        }
    }

    protected function renderOptionRow(array $row): void
    {
        ?>
        <option value="<?= $row['value'] ?>" <?= $row['selected'] === true ? "selected" : "" ?>><?= $row['text'] ?></option>
<?php
    }
}
