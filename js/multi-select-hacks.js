(function($){
  $(function(){
    // Read http://loudev.com/#usage
    // for better understanding
    
    // For changing template in post/page
    
    // For editing posts and pages
    $("#post-formats-select-plugins").multiSelect({
        selectableHeader: "<h3 class='selectHeader'>Disabled Plugins</h3>",
        selectedHeader: "<h3 class='selectHeader'>Enabled Plugins</h3>"
    });
    
  });
})(jQuery);