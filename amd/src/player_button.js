import $ from 'jquery';
import ajax from 'core/ajax';
import {init as ModalPlayerHandle} from './modal_player_handle';
import ModalPlayer from "./modal_player";

export const init = () => {
    console.log(ModalPlayer.SELECTORS.PLAYER_MODAL);
    console.log(ModalPlayer.PLAYER_MODAL);
    $(ModalPlayer.SELECTORS.PLAYER_MODAL).on('click', function(e) {
        let linkId = $(e.currentTarget).attr('data-id');
        ModalPlayerHandle(e, linkId);
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
        ])[0].then(function (response) {
            window.console.log(JSON.parse(response['body']));
        }).fail(function (response) {
            window.console.log(response);
        });
    });
};