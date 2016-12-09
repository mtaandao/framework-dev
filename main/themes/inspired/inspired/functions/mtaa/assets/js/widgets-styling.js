jQuery(document).ready(function($) {
    var mtaa_widget_regexp = /mtaa/;
    $('.widget').each(function(i, el) {
        var el = $(el);
        var id = el.prop('id');

        if (mtaa_widget_regexp.test(id)) {
            $(el).addClass('mnz_widget_style');
        }
    });
});
