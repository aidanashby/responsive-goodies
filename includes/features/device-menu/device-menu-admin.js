(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Ensure device visibility controls remain visible when menu items are expanded/collapsed
        $(document).on('click', '.menu-item-handle', function() {
            setTimeout(function() {
                $('.rg-device-visibility').show();
            }, 100);
        });
    });
    
})(jQuery);
