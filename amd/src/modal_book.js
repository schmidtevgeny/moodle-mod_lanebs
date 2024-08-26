define([
    "exports",
    "jquery",
    "core/ajax",
    "core/modal_factory",
    "core/modal_events",
    "core/notification",
    "core/modal",
    "core/custom_interaction_events",
    "core/modal_registry",
    "core/str",
], function (
    exports,
    $,
    ajax,
    ModalFactory,
    ModalEvents,
    Notification,
    Modal,
    CustomEvents,
    ModalRegistry,
    Str,
) {
    let SELECTORS = {
        CLOSE_BUTTON: "[data-action='cancel']",
        CONTENT_BLOCK: "[data-action='book_content_block']",
        CLOSE_CROSS: ".close",
        ROOT_MODAL: "[data-region='modal-container']",
        CONTAINER: "[data-region='modal-container']",
        MODAL: "[data-region='modal']",
        MODAL_BOOK_BUTTON: '[data-action="book_modal"]'
    };

    /**
     * Constructor for the Modal
     *
     */
    let ModalBook = function(root) {
        Modal.call(this, root);
        ModalBook.prototype.modal = this;

        if (!this.getFooter().find(SELECTORS.CLOSE_BUTTON).length) {
            Notification.exception({message: Str.get_string('lanebs_error_close', 'mod_lanebs')});
        }
    };

    ModalBook.TYPE = 'mod_lanebs-book';
    ModalBook.CONTENT_BLOCK = SELECTORS.CONTENT_BLOCK;

    ModalBook.prototype = Object.create(Modal.prototype);
    ModalBook.prototype.constructor = ModalBook;
    ModalBook.prototype.MODAL_BOOK_BUTTON = SELECTORS.MODAL_BOOK_BUTTON;

    ModalBook.prototype.registerEventListeners = function () {
        Modal.prototype.registerEventListeners.call(this);

        this.getRoot().on(CustomEvents.events.activate, SELECTORS.CLOSE_BUTTON+', '+SELECTORS.CLOSE_CROSS, function () {
            this.destroy();
            this.getBackdrop().then(function (backdrop) {
                backdrop.hide();
            });
        }.bind(this));

        this.getModal().on(CustomEvents.events.scrollBottom, SELECTORS.CONTENT_BLOCK, function (e) {
            let agent = navigator.userAgent.toLowerCase();
            let mobile = !!agent.match(/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos|ios)/g);
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
                return response;
            }).
            done(function (response) {
                let pageNumber = $(SELECTORS.CONTENT_BLOCK).attr('data-page');
                let type = $(SELECTORS.CONTENT_BLOCK).attr('data-type');
                ModalBook.prototype.getBookResult(response['body'], pageNumber, id, type);
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
                console.log(response);
            });
        });
    };

    ModalBook.prototype.getBookResult = function (response, pageNumber, bookId, type = 'book') {
        let iframeBook = document.getElementById('book_iframe');
        let readerBase = 'https://reader.lanbook.com/'+type+'/';
        let readerUrl = readerBase+bookId+'?jwtToken='+response+'&mode=moodlePlugin'+'#'+pageNumber;
        $(iframeBook).attr('src', readerUrl);
    };

    ModalRegistry.register(ModalBook.TYPE, ModalBook, 'mod_lanebs/modal_book');

    return ModalBook;

});
