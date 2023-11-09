define([
    "exports",
    "jquery",
    "core/ajax",
    "core/modal_factory",
    "core/modal_events",
    "core/notification",
    "core/modal",
    "core/custom_interaction_events",
    "core/modal_registry",
], function (
    exports,
    $,
    ajax,
    ModalFactory,
    ModalEvents,
    Notification,
    Modal,
    CustomEvents,
    ModalRegistry,
) {

    /**
     * Constructor for the Modal
     *
     */
    let ModalConstructor = function(root) {
        Modal.call(this, root);
    };

    ModalConstructor.TYPE = 'mod_lanebs_constructor';
    ModalConstructor.prototype = Object.create(Modal.prototype);
    ModalConstructor.prototype.constructor = ModalConstructor;
    ModalConstructor.prototype.breadcrumbs = {};
    ModalConstructor.SCRIPT_BUTTON = '#script_button';

    let SELECTORS = {
        'SCRIPT_BUTTON': '#script_button',
        'APP_CONTAINER': 'div#app_container',
    };

    ModalConstructor.prototype.registerEventListeners = function () {
        Modal.prototype.registerEventListeners.call(this);

        this.getRoot().on(ModalEvents.hidden, function () {
            this.destroy();
        }.bind(this));

    };

    ModalConstructor.prototype.getAjaxCall = function (methodname, args, callback) {
        return ajax.call([
            {
                methodname: methodname,
                args,
            }
        ])[0].then(function(response) {
            return response;
        }).done(function(response) {
            callback(JSON.parse(response['body']));
            return true;
        }).fail(function (response) {
            console.log(response);
            callback({'error': true, 'code': 500, 'message': 'error'});
            return false;
        });
    };

    ModalRegistry.register(ModalConstructor.TYPE, ModalConstructor, 'mod_lanebs/modal_constructor');

    return ModalConstructor;
});