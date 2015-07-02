@extends(Config::get('oxygen/core::layout'))

@section('content')

<?php

    use Oxygen\Core\Html\Header\Header;

    $header = Header::fromBlueprint(
        $blueprint,
        Lang::get('oxygen/mod-marketplace::ui.home.title')
    );

?>

<div class="Block">
    {{ $header->render()}}
</div>

<div class="Row--layout Row--alignTop">
    @include('oxygen/mod-marketplace::filters', ['fields' => $fields])
    <div class="Cell-twoThirds Cell--last Row--layout">
        @if(empty($results['results']))
            <h2 class="heading-gamma">No results</h2>
        @else
            <?php
                $i = 0;
            ?>
            @foreach($paginator as $package)
                <?php
                    $i++;
                ?>
                <a href="{{{ URL::route('marketplace.getDetails', $package->getSplitName()) }}}" class="Marketplace-item Header Header--block Block Cell-oneThird<?php if($i % 3 === 0) { echo ' Cell--last'; } ?> Link--smoothState">
                    <div class="Header-content flex-item">
                        @if($package->hasIcon())
                            <img src="{{{ $package->getIcon()}}}">
                        @else
                            <div class="Icon-container">
                                <span class="Icon Icon--gigantic Icon--light Icon-dropbox"></span>
                            </div>
                        @endif
                    </div>
                    <h2 class="Header-title heading-gamma flex-item">
                        {{{ $package->getPrettyName() }}}
                    </h2>
                    <h2 class="Header-title heading-delta flex-item">
                        {{{ $package->getDescription() }}}
                    </h2>
                    <ul class="Marketplace-item-icons">
                        <li><span class="Icon Icon--pushRight Icon-download"></span>{{{ $package->downloads['total'] }}}</li>
                        <li><span class="Icon Icon--pushRight Icon-star"></span>{{{ $package->favers }}}</li>
                    </ul>
                    <!--<a href="" class="Box Marketplace-item">
                    <div class="Marketplace-item-label">
                        <h1 class="Marketplace-item-title heading-gamma"></h1>
                        <h3 class="heading-delta"></h3>
                        <ul class="Marketplace-item-icons">
                            <li><span class="Icon Icon--pushRight Icon-download"></span>{{{ $package->downloads['total'] }}}</li>
                            <li><span class="Icon Icon--pushRight Icon-star"></span>{{{ $package->favers }}}</li>
                        </ul>
                    </div>
                </a>-->
                </a>
            @endforeach
        @endif
    </div>
</div>
@if($paginator !== null)
    <div class="Row">
            {{ $paginator->links() }}
    </div>
@endif

@stop
