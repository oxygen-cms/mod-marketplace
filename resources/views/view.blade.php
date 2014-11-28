@extends(Config::get('oxygen/core::layout'))

@section('content')

<?php

    use Carbon\Carbon;
    use Oxygen\Core\Html\Header\Header;
    use Oxygen\Core\Html\Toolbar\ButtonToolbarItem;
    use Oxygen\Core\Html\Toolbar\DisabledToolbarItem;

    $header = Header::fromBlueprint(
        $blueprint,
        $package->getPrettyName()
    );

    $previous = URL::previous();
    $url = $previous === URL::to('/') || $previous === URL::current() ? URL::route($blueprint->getRouteName('getHome')) : $previous;

    $header->setBackLink($url);

?>

<div class="Block">
    {{ $header->render()}}
</div>

<div class="Block">
    <div class="Row Row--border Row--highlight Row--singleLine">
        <div class="Cell-oneThird Text--alignCenter">
            @if($package->hasIcon())
                <img src="{{{ $package->getIcon()}}}">
            @else
                <div class="Icon-container">
                    <span class="Icon Icon--gigantic Icon--light Icon-dropbox"></span>
                </div>
            @endif
        </div>
        <div class="Cell-twoThirds Cell--last">
            <h1 class="heading-alpha">{{{ $package->getPrettyName() }}}</h1>
            @if($package->getPrettyName() !== $package->getName())
                <h2 class="heading-gamma"><code>{{{ $package->getName() }}}</code></h2>
            @endif
            <div class="Header-toolbar Header-toolbar--alignLeft Header-toolbar--padding">
                <?php
                    $item = $blueprint->getToolbarItem('postRequire');
                    $arguments = ['vendor' => $package->getSplitName()[0], 'package' => $package->getSplitName()[1]];
                    if($item->shouldRender($arguments)) {
                        echo $item->render($arguments);
                    }

                    $item = new DisabledToolbarItem('Status: ' . Marketplace::getInstaller()->getStatus($package->getName()));
                    echo $item->render();
                ?>
            </div>
        </div>
    </div>
    <div class="Row Row--alignTop Row--border Row--singleLine">
        <div class="Cell-oneThird">
            <ul class="List--bordered">
                <li><span class="Icon Icon--pushRight Icon-fw Icon-star"></span>{{{ $package->favers }}} stars</li>
                <li><span class="Icon Icon--pushRight Icon-fw Icon-download"></span>{{{ $package->downloads['total'] }}} downloads</li>
                <li><span class="Icon Icon--pushRight Icon-fw Icon-user"></span>Authored by {{{ $package->getAuthorsAsSentence() }}}</li>
                <li><span class="Icon Icon--pushRight Icon-fw Icon-clock-o"></span>Added on {{{ $package->time->toFormattedDateString() }}}</li>
                <li><span class="Icon Icon--pushRight Icon-fw Icon-info-circle"></span><code>{{{ $package->getName() }}}</code></li>
                <li><span class="Icon Icon--pushRight Icon-fw Icon-code-fork"></span>Latest Version: <code>{{{ $package->getLatestVersion()['version'] }}}</code></li>
                @if(isset($package->homepage) && $package->homepage !== '')
                    <li><span class="Icon Icon--pushRight Icon-fw Icon-home"></span><a href="{{{ $package->homepage }}}" target="_blank"><?php
                        $url = parse_url($package->homepage);
                        if(isset($url['host'])) { echo e($url['host']); }
                        else { echo 'Homepage'; }
                    ?></a></li>
                @endif
                <li><span class="Icon Icon--pushRight Icon-fw Icon-code"></span><a href="{{{ $package->repository }}}" target="_blank">Source on {{{ $package->getRepositoryType() }}}</a></li>
            </ul>
        </div>
        <div class="Cell-twoThirds Cell--last Content">
            {{ $package->getReadme('<em>No readme found</em>') }}
        </div>
    </div>
    <div class="Row--visual Row--border">
        <h2 class="heading-beta">Photos &amp; Screenshots</h2>
        <?php
            $images = $package->getImages();
        ?>
        @if(empty($images))
            <p><em>This package provided no photos or screenshots.</em></p>
        @else
            <div class="Slider margin-vertical" data-autoplay="true">
                <button type="button" class="Slider-back"><span class="Icon Icon-angle-left"></span></button>
                <ul class="Slider-list">
                    @foreach($images as $image)
                        <li class="Slider-item"><img src="{{{ $image }}}"></li>
                    @endforeach
                </ul>
                <button type="button" class="Slider-forward"><span class="Icon Icon-angle-right"></span></button>
            </div>
        @endif
    </div>
    <div class="Row--visual Row--border">
            <h2 class="heading-beta">Service Providers</h2>
            <br>
            <?php
                $providers = $package->getProviders();
            ?>
            @if(empty($providers))
                <p><em>This package contains no service providers.</em></p>
            @else
                @foreach($providers as $provider)
                    <h3 class="heading-delta">{{{ $provider['name'] }}} <span class="subtext">{{{ $provider['class'] }}}</span></h3>
                    <p>{{{ $provider['description'] }}}</p>
                @endforeach
            @endif
        </div>
    <div class="Row--visual Row--border">
        <h2 class="heading-beta">More Information</h2>
        <?php
            $version = $package->getLatestVersion()
        ?>
        <ul class="List--reset">
            <li>Latest Version: {{{ $version['version'] }}}</li>
            <li>Description: {{{ $version['description'] }}}</li>
            <li>Keywords: <code>{{{ implode(', ', $version['keywords']) }}}</code></li>
            <li>Homepage: {{{ $version['homepage'] ?: 'None' }}}</li>
            <li>License: {{{ implode(', ', $version['license']) }}}</li>
            <li>Author(s): {{{ $package->getAuthorsAsSentence($version) }}} </li>
            <li>Type: <code>{{{ $version['type'] }}}</code></li>
            <li>Timestamp: {{{ with(new Carbon($version['time']))->diffForHumans() }}}</li>
        </ul>
    </div>
</div>

@stop
