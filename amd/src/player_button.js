define(["exports", "jquery", "mod_lanebs/modal_player_handle", "core/ajax", "mod_lanebs/modal_player"],
    function (exports, $, ModalPlayerHandle, ajax, ModalPlayer) {
        return {
            init: function(e) {
                $(ModalPlayer.prototype.PLAYER_MODAL).on('click', function(e) {
                    let linkId = $(e.currentTarget).attr('data-id');
                    ModalPlayerHandle.init(e, linkId);

                    // отправка статистики
                    let bookId = $(e.currentTarget).attr('data-book');
                    let videoId = $(e.currentTarget).attr('data-unique');
                    let args = {
                        bookId: bookId,
                        videoId: videoId
                    };
                    ajax.call([
                        {
                            methodname: 'mod_lanebs_video_stat',
                            args,
                        }
                    ])[0].done(function (response) {
                        console.log(JSON.parse(response['body']));
                    }).fail(function (response) {
                        console.log(response);
                    });
                });
            }
        };
    });