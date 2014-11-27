@extends(Config::get('oxygen/core::layout'))

@section('content')

<?php

    use Carbon\Carbon;
    use Oxygen\Core\Html\Header\Header;
    use Oxygen\Core\Html\Toolbar\ButtonToolbarItem;
    use Oxygen\Core\Html\Toolbar\DisabledToolbarItem;

    $header = Header::fromBlueprint(
        $blueprint,
        Lang::get('oxygen/marketplace::ui.installProgress.title')
    );

    $header->setBackLink(URL::route($blueprint->getRouteName('getHome')));

?>

<div class="Block">
    {{ $header->render()}}
</div>

<div class="Block">
    {{ Form::token() }}
    <div class="Row--visual">
        <div class="ProgressBar" id="install-progress"><span class="ProgressBar-fill" style="width: 100%;"></span></div>
        <div class="ProgressBar-message">
            <span class="ProgressBar-message-item"></span>
            <span class="ProgressBar-message-section">
                Step
                <span class="ProgressBar-message-section-count">0</span>
                -
                <span class="ProgressBar-message-section-message">Contacting Server</span>
            </span>
        </div>
    </div>
    <div class="TabSwitcher-tabs TabSwitcher-content">
        <button
          type="button"
          class="Accordion-section" data-switch-to-tab="simple" data-default-tab>
            <span class="Icon Icon-chevron-right Accordion-section-icon"></span>
            <span class="Accordion-section-message">Help</span>
        </button>
        <div data-tab="simple">
            <p>Oxygen uses <a href="https://getcomposer.org/">Composer</a> to manage dependencies and install extensions.</p>
            <p>If you see the message 'Install Log Not Found. Has Installation Started Yet?', then you may need to wait a few moments until the queue worker recieves the install command. If nothing happens for long time, then you may not have a queue worker running. For information about how to set up queues, check the <a href="http://laravel.com/docs/4.2/queues">Laravel</a> docs.</p>
        </div>
        <button
          type="button"
          class="Accordion-section" data-switch-to-tab="advanced">
            <span class="Icon Icon-chevron-right Accordion-section-icon"></span>
            <span class="Accordion-section-message">Installation Log</span>
        </button>
        <div data-tab="advanced">
            <textarea id="install-log" rows="15" readonly>Contacting server...</textarea>
        </div>
    </div>
</div>

<script>

window.onload = function() {
    var progressBar = new Oxygen.ProgressBar($("#install-progress"));
    progressBar.transitionTo(1, 1);
    var times = 0;
    var currentSection = null;
    var currentNotification = null;
    var data = {'_token': $('input[name="_token"]').val()};
    var pollInterval = 2000;
    var poll = function() {
        times++;
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/admin/marketplace/install/progress",
            data: data,
            success: function(response) {
                if(response.log) {
                    $("#install-log").html(response.log);
                }

                if(response.progress === false) {
                    progressBar.reset();
                    $("#install-log").html("");
                } else {
                    if(response.progress) {
                        if(response.progress.section.count !== currentSection) {
                            progressBar.setSectionCount(response.progress.section.count);
                            progressBar.setSectionMessage(response.progress.section.message);
                            progressBar.reset(function() {
                                progressBar.transitionTo(response.progress.item.count, response.progress.item.total);
                            });
                            currentSection = response.progress.section.count;
                        } else {
                            progressBar.transitionTo(response.progress.item.count, response.progress.item.total);
                        }

                        progressBar.setMessage(response.progress.item.message);

                    }
                }

                if(response.notification) {
                    if(!response.notification.unique || currentNotification !== response.notification.unique) {
                        new Oxygen.Notification(response.notification);
                        currentNotification = response.notification.unique;
                    }
                }

                if(response.stopPolling !== true) {
                    setTimeout(poll, pollInterval);
                }
            },
            error: function(response, textStatus, errorThrown) {
                Oxygen.Ajax.handleError(response, textStatus, errorThrown);
            }
        });
    };

    poll();
};

</script>

@stop
