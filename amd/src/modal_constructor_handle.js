import $ from 'jquery';
import CustomEvents from 'core/custom_interaction_events';
import ModalConstructor from "./modal_constructor";

export const init = (base_url) => {
    let trigger = $('#lan_constructor_button');
    ModalConstructor.getAjaxCall('mod_lanebs_get_subscriber_token', [], function (data) {
        if (data['subscriber_token']) {
            $(trigger).attr('data-token', data['subscriber_token']);
        }
    });
    ModalConstructor.getAjaxCall('mod_lanebs_get_service_token', [], function (data) {
        if (data['service_token']) {
            $(trigger).attr('data-service', data['service_token']);
        }
    });
    $(trigger).on(CustomEvents.events.activate, function() {
        $('form.mform')[0].reset();
        const modal = ModalConstructor.create({});
        modal.then(function (resolve) {
            const modalRoot = resolve.getRoot();
            $(modalRoot).find('.modal-dialog').addClass('modal_dialog_lan_videos');
            ModalConstructor.getAjaxCall('mod_lanebs_get_script_names', [], function (response) {
                const appContainer = document.getElementById('app-container');
                const base = base_url+'/front/';
                ModalConstructor.appendHeadStylesheet(
                    "https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&amp;display=swap"
                );
                ModalConstructor.appendHeadStylesheet("https://fonts.googleapis.com/icon?family=Material+Icons");
                if (Array.isArray(response.scripts)) {
                    $.each(response.scripts, function (key, value) {
                        if (value.includes('.css')) {
                            ModalConstructor.appendHeadStylesheet(base + value);
                        } else {
                            ModalConstructor.appendScript(appContainer,base + value);
                        }
                    });
                } else {
                    ModalConstructor.appendHeadStylesheet(base + "styles.css");
                    ModalConstructor.appendScript(appContainer, base + "runtime.js");
                    ModalConstructor.appendScript(appContainer, base + "polyfills.js");
                    ModalConstructor.appendScript(appContainer, base + "vendor.js");
                    ModalConstructor.appendScript(appContainer, base + "main.js");
                }
            });
            resolve.show();
        });
    });
};