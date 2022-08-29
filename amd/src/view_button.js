define(["exports", "jquery", "mod_lanebs/modal_book_handle"],
    function (exports, $, ModalBookHandle) {
        return {
            init: function() {
                let BOOK_BUTTON_SELECTOR = '[data-action="book_modal"]';
                $(BOOK_BUTTON_SELECTOR).on('click', function(e) {
                    ModalBookHandle.init(e);
                });
            }
        };
    });