define(["exports", "jquery", "core/modal_factory", "mod_lanebs/modal_player"],
    function (exports, $, ModalFactory, ModalPlayer) {
        return {
            init: function (e, linkId = null) {
                let trigger = $(e.currentTarget);
                if (!linkId) {
                    linkId = $(trigger).attr('data-id');
                }
                ModalFactory.create({type: ModalPlayer.TYPE}, trigger, linkId).then(function (modal) {
                    let modalRoot = modal.getRoot();
                    $(modalRoot).find(ModalPlayer.CONTENT_BLOCK).attr('data-id', linkId);
                    $(modalRoot).find('.modal-dialog').addClass('mw-100');
                    $(modalRoot).find('.modal-dialog').css('height', '94%');
                    $(modalRoot).find('.modal-content').css('height', '100%');
                    $(modalRoot).find(ModalPlayer.CONTENT_BLOCK).trigger('cie:scrollBottom');
                });
            }
        };
    });