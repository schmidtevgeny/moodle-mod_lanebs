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
                    $(modalRoot).find('.modal-dialog').addClass('mw-100');
                    $(modalRoot).find('.modal-dialog').css('height', '94%');
                    $(modalRoot).find('.modal-content').css('height', '100%');
                    $(modalRoot).find(ModalBook.CONTENT_BLOCK).trigger('cie:scrollBottom');
                });
            }
        };
    });