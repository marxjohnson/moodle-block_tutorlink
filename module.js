M.block_tutorlink = {

    overlay: '',

    init: function(Y, courseid, userid, sesskey, sname, sid) {
        this.Y = Y;
        this.courseid = courseid;
        this.userid =   userid;
        this.sname = sname;
        this.sid = sid;
        this.sesskey = sesskey;
        this.fakeinput = Y.Node.create('<input class="fakefile" />');        
        this.fileid = null;
        inputs = Y.Node.create('<div class="fileinputs" />');
        this.selectbutton = Y.Node.create('<div class="flashbutton" />');
        this.uploadbutton = Y.Node.create('<input type="button" class="button" value="'+M.util.get_string('upload', 'moodle')+'" />');
        uploaddiv = Y.Node.create('<div class="Yuploader" />');        
        uploaddiv.appendChild(this.selectbutton);
        uploaddiv.appendChild(this.fakeinput)
        uploaddiv.appendChild(this.uploadbutton);
        Y.one('#tutorlink_form').replace(uploaddiv);

        this.uploader = new Y.Uploader({boundingBox: this.selectbutton,
                                        buttonSkin:M.cfg.wwwroot+'/blocks/tutorlink/pix/select.png',
					transparent: false});

        this.uploader.on("uploaderReady", function(e) {
            var file_filters = new Array({description:'Comma-Separated Values (CSV)', extensions:"*.csv;*.txt"});
            e.target.set("fileFilters", file_filters);
        });


        this.uploader.on("fileselect", function(e) {
            var file_data = e.fileList;
            for (var key in file_data) {
                    M.block_tutorlink.show_filename(file_data[key].name);
                    M.block_tutorlink.fileid = file_data[key].id;
            }
        });

        this.uploader.on('uploadcompletedata', function(e) {
            M.block_tutorlink.show_response(e.data);
        });

        this.uploader.on('uploaderror', function(e) {
            M.block_tutorlink.show_response(e.status);
        });

        this.uploadbutton.on("click", function() {
            M.block_tutorlink.upload_file();
            M.block_tutorlink.show_response('<img src="'+M.cfg.loadingicon+'" class="spinner" />');
        });

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

    },

    show_filename: function(path) {
        if (path.search('/') >= 0) {
            filename = path.substring(path.lastIndexOf('/'));
        } else if (path.search('\\\\') >= 0) { // Yes, 4 backslashes.
            filename = path.substring(path.lastIndexOf('\\')+1);
        } else {
            filename = path;
        }
        this.fakeinput.set('value', filename);
    },

    upload_file: function() {
        this.uploader.upload(this.fileid, M.cfg.wwwroot+'/blocks/tutorlink/process_async.php', 'post', {userid: this.userid, sesskey: this.sesskey, sname: this.sname, sid: this.sid});
    },
    
    show_response: function(data) {
        Y = this.Y;
        header = '<h1 class="heading">'+M.util.get_string('pluginname', 'block_tutorlink')+'</h1>';
        this.overlay.set('bodyContent', Y.Node.create(header+'<p>'+data+'</p>'));
        this.overlay.set("align", {node:this.uploadbutton, points:[Y.WidgetPositionAlign.TL, Y.WidgetPositionAlign.RC]});
        this.overlay.show();
        this.overlay_close.focus();
    },

    hide_response: function() {
        this.overlay.hide();
    }

    
}