import $ from 'jquery';
import {init as ModalBookHandle} from './modal_book_handle';
import ModalBook from './modal_book';

export const init = (id = null, page = null, type = 'book') => {
    $(ModalBook.SELECTORS.MODAL_BOOK_BUTTON).on('click', function(e) {
        ModalBookHandle(e, id, page, type);
    });
};