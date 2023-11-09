define(["exports", "jquery", "core/modal_factory", "core/custom_interaction_events", "mod_lanebs/modal_book"],
    function (exports, $, ModalFactory, CustomEvents, ModalBook) {
        return {
            init: function (e, id = null, page = null, type = 'book') {
                let trigger = $(e.currentTarget);
                if (!id) {
                    id = $(trigger).closest('.item-container').find('.item').attr('data-id');
                }
                let pageNumber = page ?? $(trigger).closest('.item-container').find('.item').attr('data-page');
                //$(trigger).on(CustomEvents.events.activate, function(event) {
                    ModalFactory.create({type: ModalBook.TYPE}, trigger, id, pageNumber, type).then(function (modal) {
                        console.log(modal);
                        modal.show();
                        let modalRoot = modal.getRoot();
                        $(modalRoot).find(ModalBook.CONTENT_BLOCK).attr('data-id', id);
                        $(modalRoot).find(ModalBook.CONTENT_BLOCK).attr('data-page', pageNumber);
                        $(modalRoot).find(ModalBook.CONTENT_BLOCK).attr('data-type', type);
                        $(modalRoot).find('.modal-dialog').addClass('modal_dialog_lan_reader');
                        $(modalRoot).find('.modal-content').addClass('modal_content_lan_reader');
                        $(modalRoot).find(ModalBook.CONTENT_BLOCK).trigger('cie:scrollBottom');
                        console.log(modal);
                    });
                //});
            }
        };
    });