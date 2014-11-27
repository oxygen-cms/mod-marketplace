@extends(Config::get('oxygen/core::layout'))

@section('content')

<?php

    use Oxygen\Core\Html\Header\Header;

    $header = Header::fromBlueprint(
        $blueprint,
        Lang::get('oxygen/marketplace::ui.error.title')
    );

?>

<div class="Block">
    {{ $header->render()}}
</div>

<div class="Block">
    <div class="Row--visual">
         <h2 class="heading-gamma">The Marketplace could not be loaded.</h2>
         <br>
        <strong>Reason:</strong>
        <pre style="display: inline;">{{ $exception->getMessage() }}</pre>
    </div>
</div>

@stop
