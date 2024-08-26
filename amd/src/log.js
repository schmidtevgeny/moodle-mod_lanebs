define(["exports", "core/ajax", "jquery", "mod_lanebs/modal_book", "mod_lanebs/modal_player"],
    function (exports, ajax, $, ModalBook, ModalPlayer) {
        return {
            init: function (type, resourceid, coursename, email, fio, trigger, course_date) {
                $(ModalBook.prototype.MODAL_BOOK_BUTTON+', '+ModalPlayer.prototype.PLAYER_MODAL).on('click', function (e) {
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
            }
        };
    });