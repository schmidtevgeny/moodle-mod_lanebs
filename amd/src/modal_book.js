import Modal from 'core/modal';
import $ from 'jquery';
import ajax from 'core/ajax';
import CustomEvents from 'core/custom_interaction_events';

export default class ModalBook extends Modal {
    static TYPE = 'mod_lanebs/modal_book';
    static TEMPLATE = 'mod_lanebs/modal_book';

    static CONTENT_BLOCK;

    static SELECTORS = {
        CLOSE_BUTTON: ".modal_content_lan_reader [data-action='cancel']",
        CONTENT_BLOCK: "[data-action='book_content_block']",
        CLOSE_CROSS: "modal_content_lan_reader .close",
        ROOT_MODAL: "[data-region='modal-container']",
        CONTAINER: "[data-region='modal-container']",
        MODAL: "[data-region='modal']",
        MODAL_BOOK_BUTTON: '[data-action="book_modal"]',
    };

    configure(modalConfig) {
        super.configure(modalConfig);
        ModalBook.CONTENT_BLOCK = ModalBook.SELECTORS.CONTENT_BLOCK;
    }

    registerEventListeners() {
        // Call the registerEventListeners method on the parent class.
        super.registerEventListeners();
        const modal = this;
        this.getRoot().on(CustomEvents.events.activate, ModalBook.SELECTORS.CLOSE_BUTTON, () => {
            modal.destroy();
        });

        this.getModal().on(CustomEvents.events.scrollBottom, ModalBook.SELECTORS.CONTENT_BLOCK, function (e) {
            let agent = navigator.userAgent.toLowerCase();
            let preg = new RegExp("android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|" +
                       "mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos|ios", 'g');
            let mobile = !!agent.match(preg);
            mobile = mobile || (window.innerWidth < 1006); // kostil for ignore redirect window.location.origin + '/m'
            let id = $(e.currentTarget).attr('data-id');
            ajax.call([
                {
                    methodname: 'mod_lanebs_book_content',
                    args: {
                        id: id,
                        mobile: mobile
                    }
                }
            ])[0].then(function (response) {
                let pageNumber = $(ModalBook.SELECTORS.CONTENT_BLOCK).attr('data-page');
                let type = $(ModalBook.SELECTORS.CONTENT_BLOCK).attr('data-type');
                ModalBook.getBookResult(response['body'], pageNumber, id, type);
                ajax.call([
                    {
                        methodname: 'mod_lanebs_send_log',
                        args: {
                            id: id,
                            type: type
                        },
                    }
                ]);
            }).fail(function (response) {
                window.console.log(response);
                return response;
            });
        });
    }

     static getBookResult = (response, pageNumber, bookId, type = 'book') => {
        let iframeBook = document.getElementById('book_iframe');
        let readerBase = 'https://reader.lanbook.com/'+type+'/';
        let readerUrl = readerBase+bookId+'?jwtToken='+response+'&mode=moodlePlugin'+'#'+pageNumber;
        $(iframeBook).attr('src', readerUrl);
    };
}
