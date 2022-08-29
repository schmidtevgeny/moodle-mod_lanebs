define(["exports", "jquery", "mod_lanebs/modal_player_handle"],
    function (exports, $, ModalPlayerHandle) {
        return {
            init: function(e) {
                let PLAYER_BUTTON_SELECTOR = '[data-action="player_modal"]';
                $(PLAYER_BUTTON_SELECTOR).on('click', function(e) {
                    let linkId = $(e.currentTarget).attr('data-id');
                    ModalPlayerHandle.init(e, linkId);
                });
            }
        };
    });