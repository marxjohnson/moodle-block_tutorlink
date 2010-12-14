M.block_tutorlink = {

    overlay: '',

    init: function(Y) {

        this.Y = Y;
        this.overlay_close = Y.Node.create('<a id="block_tutorlink_output_header" href="#"><img  src="'+M.util.image_url('t/delete', 'moodle')+'" /></a>');
        // Create an overlay from markup
        this.overlay = new Y.Overlay({
            headerContent: this.overlay_close,
            bodyContent: '',
            id: 'block_tutorlink_output',
            width:'400px',
            visible : false,
            constrain : true
        });
        this.overlay.render(Y.one(document.body));

        this.overlay_close.on('click', this.overlay.hide, this.overlay);

        Y.on("key", this.hide_response, this.overlay_close, "down:13", this);
        this.overlay_close.on('click', this.hide_response, this);
        var form = Y.one('.block_tutorlink form');

        form.on('submit', function(e) {
            Y = M.block_tutorlink.Y;
            e.preventDefault();

            M.block_tutorlink.show_response('<img src="'+M.cfg.loadingicon+'" class="spinner" />');
            var postdata = '';
            form.get('elements').each(function(field) {
                if (postdata.length > 0) {
                    postdata += '&';
                }
                postdata += field.get('name')+'='+field.get('value')
            });
            var uri = M.cfg.wwwroot+'/blocks/tutorlink/process.php';
            this.xhr = Y.io(uri, {
                method: 'POST',
                data: postdata,
                on: {
                    success: function(id, o) {
                        M.block_tutorlink.show_response(o.responseText);
                    },
                    failure: function(id, o) {
                        if (o.statusText != 'abort') {
                            M.block_tutorlink.show_response(o.responseText);
                        } else {
                            M.block_tutorlink.hide_response();
                        }
                    }
                }
            });
        });
    },    
    
    show_response: function(data) {
        Y = this.Y;
        header = '<h1 class="heading">'+M.util.get_string('pluginname', 'block_tutorlink')+'</h1>';
        this.overlay.set('bodyContent', Y.Node.create(header+'<p>'+data+'</p>'));
        this.overlay.set("align", {node:Y.one('#id_tutorlink_submit'), points:[Y.WidgetPositionAlign.TL, Y.WidgetPositionAlign.RC]});
        this.overlay.show();
        this.overlay_close.focus();
    },

    hide_response: function() {
        this.overlay.hide();
    }

    
}