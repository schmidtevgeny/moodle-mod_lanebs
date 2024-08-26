import Modal from 'core/modal';
import $ from 'jquery';
import CustomEvents from 'core/custom_interaction_events';
import ajax from 'core/ajax';

export default class ModalPlayer extends Modal {
    static TYPE = 'mod_lanebs/modal_player';
    static TEMPLATE = 'mod_lanebs/modal_player';
    static SELECTORS = {
        CONTENT_BLOCK: '[data-action="player_content_block"]',
        CLOSE_BUTTON: '[data-action="close_button"]',
        CLOSE_CROSS: ".close",
        ROOT_MODAL: "[data-region='modal-container']",
        PLAYER_MODAL: 'p[data-action="player_modal"]',
    };

    configure(modalConfig) {
        super.configure(modalConfig);
        ModalPlayer.CONTENT_BLOCK = ModalPlayer.SELECTORS.CONTENT_BLOCK;
        ModalPlayer.PLAYER_MODAL = ModalPlayer.SELECTORS.PLAYER_MODAL;
    }

    registerEventListeners() {
        const modal = this;
        // Call the registerEventListeners method on the parent class.
        super.registerEventListeners();

        this.getModal().on(CustomEvents.events.activate, ModalPlayer.SELECTORS.CLOSE_BUTTON, function () {
            modal.destroy();
        });

        this.getModal().on(CustomEvents.events.scrollBottom, ModalPlayer.SELECTORS.CONTENT_BLOCK, function (e) {
            let linkId = $(e.currentTarget).attr('data-id');
            let iframeBlock =
                '<iframe id="player_iframe" src="https://www.youtube.com/embed/'+linkId+'" title="YouTube video player"' +
                'frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"' +
                ' allowfullscreen style="width:100%;height:100%;">' +
                '</iframe>';
            $(ModalPlayer.SELECTORS.CONTENT_BLOCK).append(iframeBlock);
            modal.getBody().find(ModalPlayer.SELECTORS.CONTENT_BLOCK).append(iframeBlock);
            ajax.call([
                {
                    methodname: 'mod_lanebs_send_log',
                    args: {
                        id: linkId,
                        type: 'video'
                    },
                }
            ]);
        });
    }
}
