(function($){
    
    var id                  = null;
    var previous            = null;
    var type                = null;
    var plugins             = [];
    var normal_top          = '#eee';
    var normal_bottom       = '#fff';
    var activated_top       = '#99f';
    var activated_bottom    = '#ddf';
    
    function reset_plugin_list() {
        $('.plugin_listed').each(function(){
            $(this).css('background', '-webkit-gradient(linear, left top, left bottom, from(' + normal_top + '), to(' + normal_bottom + '))');
            $(this).css('background', '-moz-linear-gradient(top,  ' + normal_top + ',  ' + normal_bottom + ')');
            $(this).css('filter', 'progid:DXImageTransform.Microsoft.gradient(startColorstr="' + normal_top + '", endColorstr="' + normal_bottom + '")');
        });
        
       plugins = [];
    }
    
    function reset_previous() {
        if(previous != null) {
            $('#' + previous).css('background', '-webkit-gradient(linear, left top, left bottom, from(' + normal_top + '), to(' + normal_bottom + '))');
            $('#' + previous).css('background', '-moz-linear-gradient(top,  ' + normal_top + ',  ' + normal_bottom + ')');
            $('#' + previous).css('filter', 'progid:DXImageTransform.Microsoft.gradient(startColorstr="' + normal_top + '", endColorstr="' + normal_bottom + '")');
        }
    }
    
    function save() {
        if(id == null) {
            alert('Please choose a category or template first')
        }
        
        else {
            var data = {};
            var files= new Array();;

            // Get the plugins data
            for(var i = 0; i < plugins.length; i++) {
                if( $('#' + plugins[i]).attr('slp-value') != undefined) {
                    files.push( $('#' + plugins[i]).attr('slp-value') );
                }
            }

            if(type == 'template') {
                data = {
                    action: "save_ajax",
                    index: id,
                    arr: files
                };

                $.post(ajaxurl, data, function(response){
                    console.log("Template: " + id);
                    console.log("Plugins: " + files);
                    console.log("Server: " + response);
                });
            }

            else {
                data = {
                    action: "save_ajax_category",
                    index: id,
                    arr: files
                };      

                $.post(ajaxurl, data, function(response){
                    console.log("Category: " + id);
                    console.log("Plugins: " + files);
                    console.log("Server: " + response);
                });
            }
        }

    }
    
    function get_plugins() {
        var data        = {};
        var activate    = [];
        
        if(type != null) {
                
            if(type == 'template') {
                data = {
                    action: "get_plugins",
                    template: id
                };
            }

            else {
                data = {
                    action: "get_plugins_category",
                    template: id
                };
            }

            $.post(ajaxurl, data, function(response){

                activate = response.split(",");
                reset_plugin_list();

                for( var i = 0; i < activate.length; i++) {
                    plugins.push(activate[i]);
                    $("#" + activate[i]).css('background', '-webkit-gradient(linear, left top, left bottom, from('+ activated_top +'), to('+ activated_bottom +'))');
                    $("#" + activate[i]).css('background', '-moz-linear-gradient(top,  '+ activated_top +',  '+ activated_bottom +')');
                    $("#" + activate[i]).css('filter', 'progid:DXImageTransform.Microsoft.gradient(startColorstr="'+ activated_top +'", endColorstr="'+ activated_bottom +'")');
                }
            });
        }
    }
    
    $('.plugin_listed').each(function(){
        $(this).click(function(){
            var plugin_id = $(this).attr('id');
            
            if($.inArray( plugin_id, plugins ) == -1) {
                
                $(this).css('background', '-webkit-gradient(linear, left top, left bottom, from('+ activated_top +'), to('+ activated_bottom +'))');
                $(this).css('background', '-moz-linear-gradient(top,  '+ activated_top +',  '+ activated_bottom +')');
                $(this).css('filter', 'progid:DXImageTransform.Microsoft.gradient(startColorstr="'+ activated_top +'", endColorstr="'+ activated_bottom +'")');
                plugins.push( plugin_id );
            }
            
            else{
                var index = plugins.indexOf( plugin_id );
                $(this).css('background', '-webkit-gradient(linear, left top, left bottom, from(' + normal_top + '), to(' + normal_bottom + '))');
                $(this).css('background', '-moz-linear-gradient(top,  ' + normal_top + ',  ' + normal_bottom + ')');
                $(this).css('filter', 'progid:DXImageTransform.Microsoft.gradient(startColorstr="' + normal_top + 'ccc", endColorstr="' + normal_bottom + '")');
                plugins.splice( index, 1 );
            }
        });
    });
    
    $('.template').each(function(){
        $(this).click(function(){
            type = 'template';
            previous = id;
            var clicked_id = $(this).attr('id');
            
            if( id == clicked_id ) {
                id = null;
                $(this).css('background', '-webkit-gradient(linear, left top, left bottom, from(' + normal_top + '), to(' + normal_bottom + '))');
                $(this).css('background', '-moz-linear-gradient(top,  ' + normal_top + ',  ' + normal_bottom + ')');
                $(this).css('filter', 'progid:DXImageTransform.Microsoft.gradient(startColorstr="' + normal_top + 'ccc", endColorstr="' + normal_bottom + '")');
                reset_plugin_list();
            }
            
            else {
                reset_previous();
                id = $(this).attr('id');
                $(this).css('background', '-webkit-gradient(linear, left top, left bottom, from('+ activated_top +'), to('+ activated_bottom +'))');
                $(this).css('background', '-moz-linear-gradient(top,  '+ activated_top +',  '+ activated_bottom +')');
                $(this).css('filter', 'progid:DXImageTransform.Microsoft.gradient(startColorstr="'+ activated_top +'", endColorstr="'+ activated_bottom +'")');
                get_plugins('template');
            }
        });
    });
    
    $('.category-template').each(function(){
        $(this).click(function(){
            type = 'category';
            previous = id;
            var clicked_id = $(this).attr('id');
            
            if( id == clicked_id ) {
                id = null;
                $(this).css('background', '-webkit-gradient(linear, left top, left bottom, from(' + normal_top + '), to(' + normal_bottom + '))');
                $(this).css('background', '-moz-linear-gradient(top,  ' + normal_top + ',  ' + normal_bottom + ')');
                $(this).css('filter', 'progid:DXImageTransform.Microsoft.gradient(startColorstr="' + normal_top + 'ccc", endColorstr="' + normal_bottom + '")');
                reset_plugin_list();
            }
            
            else {
                reset_previous();
                id = $(this).attr('id');
                $(this).css('background', '-webkit-gradient(linear, left top, left bottom, from('+ activated_top +'), to('+ activated_bottom +'))');
                $(this).css('background', '-moz-linear-gradient(top,  '+ activated_top +',  '+ activated_bottom +')');
                $(this).css('filter', 'progid:DXImageTransform.Microsoft.gradient(startColorstr="'+ activated_top +'", endColorstr="'+ activated_bottom +'")');
                get_plugins('category');
            }
        });
    });
    
    $('#save_btn').click(function(){ save(); });
    
})(jQuery);