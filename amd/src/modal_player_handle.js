import $ from 'jquery';
import ModalPlayer from './modal_player';
import CustomEvents from 'core/custom_interaction_events';

export const init = (e, linkId = null) => {
    const trigger = $(e.currentTarget);
    if (!linkId) {
        linkId = $(trigger).attr('data-id');
    }
    const modal = ModalPlayer.create({removeOnClose: true});
    modal.then((resolve) => {
        let modalRoot = resolve.getRoot();
        $(modalRoot).find(ModalPlayer.CONTENT_BLOCK).attr('data-id', linkId);
        $(modalRoot).find('.modal-dialog').addClass('mw-100');
        $(modalRoot).find('.modal-dialog').css('height', '94%');
        $(modalRoot).find('.modal-content').css('height', '100%');
        $(modalRoot).find(ModalPlayer.CONTENT_BLOCK).trigger('cie:scrollBottom');
    });
    $(trigger).on(CustomEvents.events.activate, () => {
        modal.then((resolve) => {
            resolve.show();
        });
    });
};