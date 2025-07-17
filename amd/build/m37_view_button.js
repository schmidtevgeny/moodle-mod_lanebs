define(["exports", "jquery", "mod_lanebs/m37_modal_book_handle", "mod_lanebs/m37_modal_book"],
    function (exports, $, ModalBookHandle, ModalBook) {
        return {
            init: function(id = null, page = null, type = 'book') {
                $(ModalBook.prototype.MODAL_BOOK_BUTTON).on('click', function(e) {
                    ModalBookHandle.init(e, id, page, type);
                });
            }
        };
    });