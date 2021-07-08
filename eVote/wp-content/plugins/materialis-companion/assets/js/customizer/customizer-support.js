(function ($, CP_Customizer) {
    CP_Customizer.addModule(function () {
        var used = false;
        var usedWoo = false;
        CP_Customizer.bind(CP_Customizer.events.PREVIEW_LOADED, function () {

            // if front page was opened in customizer, but woocommerce modified the content in the preview (without changing the link)
            // set used to false, so that the function will continue and hide the page sections
            if (
                used &&
                (
                    (CP_Customizer.preview.data('queryVars:post_type', 'default') === 'product') ||
                    (CP_Customizer.preview.data('queryVars:pagename', 'default') === 'checkout')
                ) &&
                (usedWoo === false)) {
                used = false;
            }

            if (used) {
                return;
            }
            used = true;

            var $activatePageCard = $('.reiki-needed-container[data-type="activate"]');
            var $openPageCard = $('.reiki-needed-container[data-type="select"]');
            var $makeEditable = $('.reiki-needed-container[data-type="edit-this-page"]');
            var $makeProductEditable = $('.reiki-needed-container[data-type="edit-this-product"]');

            var data = CP_Customizer.preview.data();
            var toAppend;

            var canMaintainThis = CP_Customizer.preview.data('canEditInCustomizer') //CP_Customizer.options('isMultipage', false) && (data.pageID !== false);

            if (data.maintainable) {

            } else {
                if (canMaintainThis) {

                    if (CP_Customizer.preview.data('queryVars:post_type', 'page') === 'page') {
                        toAppend = $makeEditable.clone().show();
                    } else {
                        toAppend = $makeProductEditable.clone().show();
                    }

                    wp.customize.panel('page_content_panel').container.eq(0).find('.sections-list-reorder').empty().append(toAppend);

                } else {
                    wp.customize.panel('page_content_panel').container.eq(0).find('.accordion-section-title > .add-section-plus').remove();
                    if (!data.hasFrontPage) {
                        toAppend = $activatePageCard.eq(0).clone().show();
                        wp.customize.panel('page_content_panel').container.eq(0).find('.sections-list-reorder').empty().append(toAppend);
                    } else {
                        if (!data.isFrontPage) {
                            toAppend = $openPageCard.eq(0).clone().show();
                            wp.customize.panel('page_content_panel').container.eq(0).find('.sections-list-reorder').empty().append(toAppend);
                        }
                    }

                    if (CP_Customizer.preview.data('queryVars:post_type', 'product') === 'product') {
                        usedWoo = true;
                    }
                }
            }

            if (toAppend) {
                toAppend.show();
            }

        });
    });
})(jQuery, CP_Customizer);
