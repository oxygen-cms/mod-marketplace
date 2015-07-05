@extends(app('oxygen.layout'))

@section('content')

<?php

    use Oxygen\Core\Html\Header\Header;

    $header = Header::fromBlueprint(
        $blueprint,
        Lang::get('oxygen/mod-marketplace::ui.error.title')
    );

?>

<div class="Block">
    {!! $header->render() !!}
</div>

<div class="Block">
    <div class="Row--visual">
        <h2 class="heading-gamma">The Marketplace could not be loaded.</h2>
    </div>
    <div class="Row--visual">
        <h3 class="heading-delta">Reason:</h3>
        <pre>{{ $exception->getMessage() }}</pre>
    </div>
</div>

@stop
