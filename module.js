M.block_tutorlink = {
    init: function(Y) {
        inputs = Y.Node.create('<div class="fileinputs" />');
        realinput = Y.Node.create('<input type="file" name="csvfile" class="file" />');
        fakefile = Y.Node.create('<div class="fakefile" />');
        this.fakeinput = Y.Node.create('<input />');
        fakebutton = Y.Node.create('<span class="selectfile">'+M.util.get_string('select', 'moodle')+'</span>');
        inputs.appendChild(realinput);
        fakefile.appendChild(this.fakeinput);
        fakefile.appendChild(fakebutton);
        inputs.appendChild(fakefile);
        Y.one('#tutorlink_file').replace(inputs);

        Y.on('change', this.get_path, realinput);
    },

    get_path: function(e) {
        path = e.target.get('value');
        M.block_tutorlink.show_filename(path);
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
    }
    
}