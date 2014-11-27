<?php

    use Oxygen\Core\Html\Form\EditableField;

    $exclude = isset($exclude) ? $exclude : [];

?>

<div class="Block Cell-oneThird">
    <div class="Row--visual">
        <h2 class="heading-gamma">Filters</h2>
    </div>
    {{ Form::open(['method' => 'GET', 'class' => 'Form--singleColumn']) }}
        <?php
            $fields = [
                new EditableField($blueprint->getField('q'), Input::get('q', '')),
                new EditableField($blueprint->getField('scope'), Input::get('scope', null)),
                new EditableField($blueprint->getField('tags'), Input::get('tags', [])),
                new EditableField($blueprint->getField('type'), Input::get('type', ''))
            ];

            foreach($fields as $field):
                if(!in_array($field->getMeta()->name, $exclude))
               echo $field->render(['entireRow' => true]);
            endforeach;
        ?>
        <div class="Row Form-footer">
            <button type="submit" class="Button Button-color--green">Filter</button>
        </div>
    {{ Form::close() }}
</div>