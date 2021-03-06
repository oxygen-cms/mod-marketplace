@extends(app('oxygen.layout'))

@section('content')

    <?php

    use Carbon\Carbon;
    use Oxygen\Core\Html\Header\Header;
    use Oxygen\Core\Html\Form\Form;

    $header = Header::fromBlueprint(
            $blueprint,
            Lang::get('oxygen/mod-marketplace::ui.installProgress.title')
    );

    $header->setBackLink(URL::route($blueprint->getRouteName('getHome')));

    ?>

    <div class="Block">
        {!! $header->render() !!}
    </div>

    <div class="Block">
        <?php
        $form = new Form($blueprint->getAction('postInstallProgress'));
        $form->setId('progressForm');

        echo $form->render();
        ?>
        <div class="Row--visual">
            <div class="ProgressBar" id="install-progress"><span class="ProgressBar-fill" style="width: 100%;"></span></div>
            <div class="ProgressBar-message">
                <span class="ProgressBar-message-section">Contacting Server</span>:
                <span class="ProgressBar-message-item">Sending Request</span>
            </div>
        </div>
        <div class="Row--visual TabSwitcher TabSwitcher-tabs TabSwitcher-content">
            <button
                    type="button"
                    class="Accordion-section" data-switch-to-tab="simple" data-default-tab>
                <span class="Icon Icon-chevron-right Accordion-section-icon"></span>
                <span class="Accordion-section-message">Help</span>
            </button>
            <div data-tab="simple">
                <p>Oxygen uses <a href="https://getcomposer.org/">Composer</a> to manage dependencies and install extensions.</p>
                <p>If you see the message 'Install Log Not Found. Has Installation Started Yet?', then you may need to wait a few moments until the queue worker recieves the install command. If nothing happens for long time, then you may not have a queue worker running. For information about how to set up queues, check the <a href="http://laravel.com/docs/4.2/queues">Laravel</a> docs.</p>
                <?php
                $toolbarItem = $blueprint->getToolbarItem('deleteInstallProgress');
                echo $toolbarItem->render(['margin' => 'vertical']);
                ?>
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

    <?php Event::listen('oxygen.layout.page.after', function() { ?>

    <script>
        var Oxygen = Oxygen || {};
        Oxygen.load = Oxygen.load || [];
        Oxygen.load.push(function() {
            var progressBar = document.getElementById("install-progress");
            function pbFill(amount) {
                progressBar.querySelector(".ProgressBar-fill").style.width = (amount * 100).toString() + "%";
            }
            pbFill(1);
            var times = 0;
            var currentSection = null;
            var currentNotification = null;
            var form = document.getElementById("progressForm");
            var data = {'_token': $('input[name="_token"]').val()};
            var pollInterval = 2000;
            var poll = function() {
                times++;
                window.fetch(
                    form.getAttribute("action"),
                    FetchOptions.default()
                        .body(getFormDataObject(getFormData(form)))
                        .wantJson()
                        .method("POST")
                )
                    .then(Oxygen.respond.checkStatus)
                    .then(Oxygen.respond.json)
                    .then(response => {
                        console.log(response);

                        if(response.log) {
                            $("#install-log").html(response.log);
                        }

                        /*if(response.finished) {
                         progressBar.reset();
                         $("#install-log").html("");
                         } else {*/
                        if(response.progress) {
                            /*if(response.progress.section.count !== currentSection) {
                             progressBar.setSectionCount(response.progress.section.count);
                             progressBar.setSectionMessage(response.progress.section.message);
                             progressBar.reset(function() {
                             progressBar.transitionTo(response.progress.item.count, response.progress.item.total);
                             });
                             currentSection = response.progress.section.count;
                             } else {*/
                            pbFill(response.progress);
                            //}

                            document.querySelector(".ProgressBar-message-section").innerHTML = response.sectionMessage;
                            document.querySelector(".ProgressBar-message-item").innerHTML = response.message;
                        }

                        if(response.notification) {
                            if(!response.notification.unique || currentNotification !== response.notification.unique) {
                                NotificationCenter.present(new Notification(response.notification));
                                currentNotification = response.notification.unique;
                            }
                        }

                        if(typeof Pace !== "undefined") {
                            console.log('ignoring');
                            setTimeout(function() {
                                Pace.ignore(poll);
                            }, pollInterval);
                        } else {
                            setTimeout(poll, pollInterval);
                        }
                    });
                    /*.catch(Oxygen.respond.handleAPIError)
                    type: form.getAttribute("method"),
                    data: data,
                    success: function(response) {

                    },
                    error: function(response, textStatus, errorThrown) {
                        //Oxygen.Ajax.handleError(response, textStatus, errorThrown);

                        if(typeof Pace !== "undefined") {
                            console.log('ignoring');
                            setTimeout(function() {
                                Pace.ignore(poll);
                            }, pollInterval);
                        } else {
                            setTimeout(poll, pollInterval);
                        }
                    }*/
            };

            if(typeof Pace !== 'undefined') {
                Pace.ignore(poll);
            } else {
                poll();
            }
        });
    </script>

    <?php }); ?>

@stop
