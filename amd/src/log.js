import $ from 'jquery';
import ModalBook from "./modal_book";
import ajax from 'core/ajax';
import ModalPlayer from "./modal_player";

export const init = (type, resourceid, coursename, email, fio, trigger, course_date) => {
    $(ModalBook.SELECTORS.MODAL_BOOK_BUTTON+', '+ModalPlayer.SELECTORS.PLAYER_MODAL).on('click', function (e) {
        let data = {};
        data.type = type;
        if (resourceid !== '') {
            data.resourceid = resourceid;
        } else {
            data.resourceid = $(e.currentTarget).attr('data-id');
            data.type = 'video';
        }
        if (type === undefined || type === '' || type === null) {
            data.type = 'book';
        }
        data.coursename = coursename;
        data.email = email;
        data.fio = fio;
        data.trigger = trigger;
        data.course_date = course_date;
        ajax.call([
            {
                methodname: 'mod_lanebs_send_log',
                args: {
                    data: data
                }
            }
        ]);
    });
};