define(["exports", "jquery", "core/modal_factory", "core/custom_interaction_events", "mod_lanebs/modal_constructor"],
    function (
        exports,
        $,
        ModalFactory,
        CustomEvents,
        ModalConstructor
    ) {
        return {
            init: function (base_url) {
                let trigger = $('#lan_constructor_button');
                ModalConstructor.prototype.getAjaxCall('mod_lanebs_get_subscriber_token', [], function (data) {
                    if (data['subscriber_token']) {
                        $(trigger).attr('data-token', data['subscriber_token']);
                    }
                });
                ModalConstructor.prototype.getAjaxCall('mod_lanebs_get_service_token', [], function (data) {
                    if (data['service_token']) {
                        $(trigger).attr('data-service', data['service_token']);
                    }
                });
                $(trigger).on('click', function(event) {
                    console.log('constructor click');
                    $('form.mform')[0].reset();
                    ModalFactory.create({type: ModalConstructor.TYPE}, trigger).then(function(modal) {
                        console.log('constructor modal create');
                        let modalRoot = modal.getRoot();
                        $(modalRoot).find('.modal-dialog').addClass('modal_dialog_lan_videos');
                        modal.show();
                        var appContainer = document.getElementById('app-container');
                        ModalConstructor.prototype.getAjaxCall('mod_lanebs_get_script_names', [], function (response) {
                            let base = base_url+'/front/';
                            appendHeadStylesheet("https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&amp;display=swap");
                            appendHeadStylesheet("https://fonts.googleapis.com/icon?family=Material+Icons");
                            if (Array.isArray(response.scripts)) {
                                $.each(response.scripts, function (key, value) {
                                    if (value.includes('.css')) {
                                        appendHeadStylesheet(base + value);
                                    } else {
                                        appendScript(appContainer,base + value);
                                    }
                                });
                            } else {
                                appendHeadStylesheet(base + "styles.css")
                                appendScript(appContainer, base + "runtime.js")
                                appendScript(appContainer, base + "polyfills.js")
                                appendScript(appContainer, base + "vendor.js")
                                appendScript(appContainer, base + "main.js")
                            }
                        });

                        function appendScript(container, src) {
                            var script = document.createElement('script');
                            script.src = src;
                            script.type = 'text/javascript';
                            script.defer = true;
                            script.async = false;

                            container.appendChild(script);
                        }

                        function appendHeadStylesheet(href) {
                            var script = document.createElement('link');
                            script.type = 'text/css';
                            script.rel = 'stylesheet';
                            script.href = href;

                            document.head.appendChild(script);
                        }
                    });
                });
            }
        };
    });