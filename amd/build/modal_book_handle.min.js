define(["exports", "jquery", "core/modal_factory", "mod_lanebs/modal_book"],
    function (exports, $, ModalFactory, ModalBook) {
        return {
            init: function (e, id = null) {
                let trigger = $(e.currentTarget);
                if (!id) {
                    id = $(trigger).closest('.item-container').find('.item').attr('data-id');
                }
                let pageNumber = $(trigger).closest('.item-container').find('.item').attr('data-page');
                ModalFactory.create({type: ModalBook.TYPE}, trigger, id, pageNumber).done(function (modal) {
                    let modalRoot = modal.getRoot();
                    $(modalRoot).find(ModalBook.CONTENT_BLOCK).attr('data-id', id);
                    $(modalRoot).find(ModalBook.CONTENT_BLOCK).attr('data-page', pageNumber);
                    $(modalRoot).find('.modal-dialog').addClass('modal_dialog_lan_reader');
                    $(modalRoot).find('.modal-content').addClass('modal_content_lan_reader');
                    $(modalRoot).find(ModalBook.CONTENT_BLOCK).trigger('cie:scrollBottom');
                });
            }
        };
    });