@extends(Config::get('oxygen/core::layout'))

@section('content')

<?php

    use Oxygen\Core\Html\Header\Header;

    $header = Header::fromBlueprint(
        $blueprint,
        Lang::get('oxygen/marketplace::ui.home.title')
    );

?>

<div class="Block">
    {{ $header->render()}}
</div>

<div class="Block">
    <div class="Row Row--alignTop">
        @include('oxygen/marketplace::filters')
        <div class="Cell-flex">
            @if(empty($results['results']))
                <h2 class="heading-gamma">No results</h2>
            @else
                @foreach($paginator as $package)
                    <a href="{{{ URL::route('marketplace.getDetails', $package->getSplitName()) }}}" class="Box Marketplace-item">
                        @if($package->hasIcon())
                            <img src="{{{ $package->getIcon()}}}">
                        @else
                            <span class="Marketplace-item-icon Icon Icon-dropbox"></span>
                        @endif
                        <div class="Marketplace-item-label">
                            <h1 class="Marketplace-item-title heading-gamma">{{{ $package->getPrettyName() }}}</h1>
                            <h3 class="heading-delta">{{{ $package->getDescription() }}}</h3>
                            <ul class="Marketplace-item-icons">
                                <li><span class="Icon Icon--pushRight Icon-download"></span>{{{ $package->downloads['total'] }}}</li>
                                <li><span class="Icon Icon--pushRight Icon-star"></span>{{{ $package->favers }}}</li>
                            </ul>
                        </div>
                    </a>
                @endforeach
            @endif
        </div>
    </div>
    <div class="Row">
        {{ $paginator->links() }}
    </div>
</div>

@stop
