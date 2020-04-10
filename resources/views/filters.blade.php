<?php

    use Oxygen\Core\Html\Form\EditableField;use Oxygen\Core\Html\Form\Form;use Oxygen\Core\Html\Form\Label;use Oxygen\Core\Html\Form\Row;

$exclude = isset($exclude) ? $exclude : [];

?>

<div class="Block Cell-oneThird">
    <div class="Row--visual">
        <h2 class="heading-gamma">Filters</h2>
    </div>
    <form method="GET" class="Form--singleColumn">
        <?php
            $fields = [
                new EditableField($fields->getField('q'), request()->input('q', '')),
                new EditableField($fields->getField('scope'), request()->input('scope', null)),
                new EditableField($fields->getField('tags'), request()->input('tags', [])),
                new EditableField($fields->getField('type'), request()->input('type', ''))
            ];

            foreach($fields as $field):
                if(!in_array($field->getMeta()->name, $exclude) && $field->getMeta()->editable) {
                    $label = new Label($field->getMeta());
                    $row = new Row([$label, $field]);
                    echo $row->render();
                }
            endforeach;
        ?>
        <div class="Row Form-footer">
            <button type="submit" class="Button Button-color--green">Filter</button>
        </div>
    </form>
</div>