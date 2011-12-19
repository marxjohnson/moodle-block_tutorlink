// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Defines tutorlink block's Javascript methods
 *
 * @package    block_tutorlink
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.block_tutorlink = {

    overlay: '',

    /**
     * Initialisation function. Sets up the block
     *
     * Creates an overlay for displaying output, and defines Hijax-style handler
     * for the form
     *
     * @param Y the YUI object
     */
    init: function(Y) {

        this.Y = Y;
        // Create an overlay (like the ones that Help buttons display) for
        // showing output from asynchronous call.
        html = '<a id="block_tutorlink_output_header" href="#">'
            +'<img src="'+M.util.image_url('t/delete', 'moodle')+'" /></a>';
        this.overlay_close = Y.Node.create(html);
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

        // Hijack form submission - pull all the data out and send it through
        // an asynchronous POST request
        var form = Y.one('.block_tutorlink form');
        form.on('submit', function(e) {

            Y = M.block_tutorlink.Y;
            e.preventDefault(); // Stops form submitting normally

            // Display a throbber while we're processing and requesting
            M.block_tutorlink.show_response('<img src="'+M.cfg.loadingicon+'" class="spinner" />');

            var postdata = ''; // Turn the field values into a query string for the POST request
            form.get('elements').each(function(field) {
                if (postdata.length > 0) {
                    postdata += '&';
                }
                postdata += field.get('name')+'='+field.get('value')
            });
            var uri = M.cfg.wwwroot+'/blocks/tutorlink/process.php';
            // Send the post request, and display the output in the overlay
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

    /**
     * Displays the specifies response in the overlay
     *
     * @param data The text to display
     */
    show_response: function(data) {
        Y = this.Y;
        header = '<h1 class="heading">'+M.util.get_string('pluginname', 'block_tutorlink')+'</h1>';
        // Create a node from the data
        this.overlay.set('bodyContent', Y.Node.create(header+'<p>'+data+'</p>'));
        this.overlay.set("align", {
            node: Y.one('#id_tutorlink_submit'),
            points: [
                Y.WidgetPositionAlign.TL,
                Y.WidgetPositionAlign.RC
            ]
        }); // Align the overlay with the submit button
        this.overlay.show(); // Show the overlay
        this.overlay_close.focus();
    },

    /**
     * Hides the overlay and any response being displayed
     */
    hide_response: function() {
        this.overlay.hide();
    }


}
