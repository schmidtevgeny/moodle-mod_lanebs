import ajax from 'core/ajax';
import Modal from 'core/modal';
import CustomEvents from 'core/custom_interaction_events';

export default class ModalConstructor extends Modal {
    static TYPE = 'mod_lanebs/modal_constructor';
    static TEMPLATE = 'mod_lanebs/modal_constructor';
    static SELECTORS = {
        'SCRIPT_BUTTON': '#script_button',
        'APP_CONTAINER': 'div#app_container',
        'CLOSE_CROSS': '.close',
    };
    static breadcrumbs = {};

    configure(modalConfig) {
        super.configure(modalConfig);
    }

    registerEventListeners() {
        const modal = this;
        this.getRoot().on(CustomEvents.events.hidden, function () {
            modal.destroy();
        });
        this.getRoot().on(CustomEvents.events.activate, ModalConstructor.SELECTORS.CLOSE_CROSS,
            function () {
                modal.destroy();
            });
    }

    static getAjaxCall(methodname, args, callback) {
        return ajax.call([
            {
                methodname: methodname,
                args,
            }
        ])[0].then(function(response) {
            callback(JSON.parse(response['body']));
            return true;
        }).fail(function (response) {
            window.console.log(response);
            callback({'error': true, 'code': 500, 'message': 'error'});
            return false;
        });
    }

    static appendScript(container, src) {
        let script = document.createElement('script');
        script.src = src;
        script.type = 'text/javascript';
        script.defer = true;

        container.appendChild(script);
    }

    static appendHeadStylesheet(href) {
        let script = document.createElement('link');
        script.type = 'text/css';
        script.rel = 'stylesheet';
        script.href = href;

        document.head.appendChild(script);
    }
}