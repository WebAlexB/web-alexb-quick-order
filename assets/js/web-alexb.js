jQuery(document).ready(function ($) {
    jQuery(document).on('click', '.quick_order', function (e) {
        e.preventDefault();
        let modalId = jQuery(this).data('modal');
        jQuery(modalId).css({
            right: 0
        });
        jQuery(document).on('click', '.quick-order-close', function () {
            jQuery(modalId).css({
                right: '-100%'
            });
        });
    });
});
