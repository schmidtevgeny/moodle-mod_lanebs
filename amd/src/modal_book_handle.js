import $ from 'jquery';
import CustomEvents from 'core/custom_interaction_events';
import ModalBook from "./modal_book";

export const init = (e, id = null, page = null, type = 'book') => {
    let trigger = $(e.currentTarget);
    if (!id) {
        id = $(trigger).closest('.item-container').find('.item').attr('data-id');
    }
    let pageNumber = page ?? $(trigger).closest('.item-container').find('.item').attr('data-page');
    const modal = ModalBook.create({removeOnClose: true});
    modal.then((resolve) => {
        const modalRoot = resolve.getRoot();
        $(modalRoot).find(ModalBook.CONTENT_BLOCK).attr('data-id', id);
        $(modalRoot).find(ModalBook.CONTENT_BLOCK).attr('data-page', pageNumber);
        $(modalRoot).find(ModalBook.CONTENT_BLOCK).attr('data-type', type);
        $(modalRoot).find('.modal-dialog').addClass('modal_dialog_lan_reader');
        $(modalRoot).find('.modal-content').addClass('modal_content_lan_reader');
        $(modalRoot).find(ModalBook.CONTENT_BLOCK).trigger('cie:scrollBottom');
    });
    $(trigger).on(CustomEvents.events.activate, () => {
        modal.then((resolve) => {
            resolve.show();
        });
    });
};
