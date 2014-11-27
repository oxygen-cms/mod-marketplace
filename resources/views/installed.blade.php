@extends(Config::get('oxygen/core::layout'))

@section('content')

<?php

    use Oxygen\Core\Html\Header\Header;

    $header = Header::fromBlueprint(
        $blueprint,
        Lang::get('oxygen/marketplace::ui.installed.title')
    );

    $header->setBackLink(URL::route($blueprint->getRouteName('getHome')));

    if(Input::get('onlyLaravel') == true) {
        $header->setSubtitle('(Only Laravel)');
    }

    if(Input::get('onlyOxygen') == true) {
        $header->setSubtitle('(Only Oxygen)');
    }

?>

<div class="Block">
    {{ $header->render()}}
</div>

<div class="Row--layout Row--alignTop">
    @include('oxygen/marketplace::filters', ['exclude' => ['q']])
    <div class="Cell-twoThirds Cell--last Block">
        @if(empty($installed))
            <div class="Row--visual">
                <h2 class="heading-gamma">No results</h2>
            </div>
        @endif
        @foreach($paginator as $package)
           <?php
               $header = Header::fromBlueprint($blueprint, $package->getPrettyName(), ['vendor' => $package->getSplitname()[0], 'package' => $package->getSplitname()[1]], Header::TYPE_SMALL, 'item');
               if($package->getPrettyName() !== $package->getName()) {
                   $header->setSubtitle($package->getName());
               }
               echo $header->render();

               $providers = $package->getProviders()
           ?>
           @if(!empty($providers))
               <div class="Row--visual">
                   <div class="Text--indent">
                       @foreach($providers as $provider)
                           <?php
                               $header = Header::fromBlueprint($blueprint, $provider['name'], ['provider' => $provider['class']], Header::TYPE_TINY, 'provider');
                               $header->setSubtitle($provider['class']);
                               echo $header->render();
                           ?>
                           <p>{{{ $provider['description'] }}}</p>
                       @endforeach
                   </div>
               </div>
           @endif
        @endforeach
    </div>
</div>
@if(!empty($installed))
    <div class="Row">
        {{ $paginator->links() }}
    </div>
@endif

@stop
