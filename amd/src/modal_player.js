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
    "core/str",
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
    Str,
) {
    let SELECTORS = {
        CONTENT_BLOCK: '[data-action="player_content_block"]',
        CLOSE_BUTTON: '[data-action="close_button"]',
        CLOSE_CROSS: ".close",
        ROOT_MODAL: "[data-region='modal-container']",
    };

    /**
     * Constructor for the Modal
     *
     */
    let ModalPlayer = function(root) {
        Modal.call(this, root);
        ModalPlayer.prototype.modal = this;

        if (!this.getFooter().find(SELECTORS.CLOSE_BUTTON).length) {
            Notification.exception({message: Str.get_string('lanebs_error_close', 'mod_lanebs')});
        }
    };

    ModalPlayer.TYPE = 'mod_lanebs-player';
    ModalPlayer.CONTENT_BLOCK = SELECTORS.CONTENT_BLOCK;
    ModalPlayer.prototype = Object.create(Modal.prototype);
    ModalPlayer.prototype.constructor = ModalPlayer;

    ModalPlayer.prototype.registerEventListeners = function () {
        Modal.prototype.registerEventListeners.call(this);

        this.getModal().on(CustomEvents.events.activate, SELECTORS.CLOSE_BUTTON, function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.getRoot().trigger('click');
            this.getRoot().remove()
            this.getModal().remove();
        }.bind(this));

        this.getModal().on(CustomEvents.events.activate, SELECTORS.CLOSE_CROSS, function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.getRoot().trigger('click');
            this.getRoot().remove()
            this.getModal().remove();
        }.bind(this));

        this.getModal().on(CustomEvents.events.scrollBottom, SELECTORS.CONTENT_BLOCK, function (e) {
            let agent = navigator.userAgent.toLowerCase();
            let mobile = !!agent.match(/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos|ios)/g);
            mobile = mobile || (window.innerWidth < 1006); // kostil for ignore redirect window.location.origin + '/m'
            let linkId = $(e.currentTarget).attr('data-id');
            let iframeBlock =
                '<iframe id="player_iframe" src="https://www.youtube.com/embed/'+linkId+'" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width:100%;height:100%;">' +
                '</iframe>';
            $(SELECTORS.CONTENT_BLOCK).append(iframeBlock);
            ModalPlayer.prototype.modal.getBody().find(SELECTORS.CONTENT_BLOCK).append(iframeBlock);
        });
    };

    ModalRegistry.register(ModalPlayer.TYPE, ModalPlayer, 'mod_lanebs/modal_player');

    return ModalPlayer;

});