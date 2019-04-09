var DDLayout = DDLayout || {};

DDLayout.WPML_Dialog_Controls = function($){
        var self = this;
};

jQuery(document).on('DLLayout.admin.ready', function($){
    DDLayout.text_cell = new DDLayout.WPML_Dialog_Controls($);
});