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
        CLOSE_BUTTON: "[data-action='close_button']",
        CONTENT_BLOCK: "[data-action='book_content_block']",
        CLOSE_CROSS: ".close",
        ROOT_MODAL: "[data-region='modal-container']",
        CONTAINER: "[data-region='modal-container']",
        MODAL: "[data-region='modal']",
    };

    /**
     * Constructor for the Modal
     *
     */
    let ModalBook = function(root) {
        Modal.call(this, root);

        if (!this.getFooter().find(SELECTORS.CLOSE_BUTTON).length) {
            Notification.exception({message: Str.get_string('lanebs_error_close', 'mod_lanebs')});
        }
    };

    ModalBook.TYPE = 'mod_lanebs-book';
    ModalBook.CONTENT_BLOCK = SELECTORS.CONTENT_BLOCK;
    ModalBook.prototype = Object.create(Modal.prototype);
    ModalBook.prototype.constructor = ModalBook;

    ModalBook.prototype.registerEventListeners = function () {
        Modal.prototype.registerEventListeners.call(this);
        this.getModal().on(CustomEvents.events.activate, SELECTORS.CLOSE_BUTTON+', '+SELECTORS.CLOSE_CROSS, function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.getRoot().trigger('click');
            this.getModal().remove();
            this.getRoot().remove();
            return true;
        }.bind(this));

        this.getRoot().click(function(e) {
            if (!$(e.target).closest(SELECTORS.MODAL).length) {
                if ($(e.target).closest(SELECTORS.CONTAINER).length) {
                    this.getModal().remove();
                    this.getRoot().remove();
                }
            }
            return true;
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
                ModalBook.prototype.getBookResult(response['body'], pageNumber, id);
            }).fail(function (response) {
                console.log(response);
            });
        });
    };

    ModalBook.prototype.getBookResult = function (response, pageNumber, bookId) {
        let iframeBook = document.getElementById('book_iframe');
        let readerBase = 'https://reader.lanbook.com/book/';//'https://reader.landev.ru/book/';
        let readerUrl = readerBase+bookId+'?jwtToken='+response+'&mode=moodlePlugin'+'#'+pageNumber;
        $(iframeBook).attr('src', readerUrl);
        //iframeBook.contentWindow.document.open();
        //iframeBook.contentWindow.location.hash = '#' + pageNumber;
        //iframeBook.contentWindow.document.write(response);
        //iframeBook.contentWindow.document.close();
        // for change reader scale after loading
        // some kostil...
        /*let timer = setInterval(function() {
            if (iframeBook.contentWindow.Reader.Viewer.UIInputZoom !== undefined && iframeBook.contentWindow.Reader.Viewer.pageScale !== undefined) {
                iframeBook.contentWindow.Reader.Viewer.pageScale(50 * 0.01);
                clearInterval(timer);
            }
        }, 1000);*/
    };

    ModalRegistry.register(ModalBook.TYPE, ModalBook, 'mod_lanebs/modal_book');

    return ModalBook;

});