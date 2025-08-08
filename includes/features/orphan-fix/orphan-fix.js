(function($) {
    'use strict';
    
    $(document).ready(function() {
        if (typeof responsiveGoodiesOrphanFix === 'undefined') {
            return;
        }
        
        var settings = responsiveGoodiesOrphanFix;
        var maxWords = parseInt(settings.maxWords) || 2;
        var excludeClass = settings.excludeClass || 'no-orphan-fix';
        var applyHeadings = settings.applyHeadings === '1' || settings.applyHeadings === true;
        
        // Define selectors for text elements
        var selectors = ['p', 'li', 'div', 'span', 'td', 'th'];
        if (applyHeadings) {
            selectors = selectors.concat(['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
        }
        
        var selectorString = selectors.join(', ');
        
        function fixOrphans() {
            $(selectorString).each(function() {
                var $element = $(this);
                
                // Skip if element has exclude class
                if ($element.hasClass(excludeClass)) {
                    return;
                }
                
                // Skip if element contains child block elements
                if ($element.find('p, div, h1, h2, h3, h4, h5, h6').length > 0) {
                    return;
                }
                
                var text = $element.html();
                
                // Skip if text is empty or contains only HTML tags
                if (!text || text.trim() === '' || !/\S/.test($element.text())) {
                    return;
                }
                
                // Find the last words in the text (fix maxWords - 1 spaces to keep maxWords together)
                var spacesToFix = Math.max(1, maxWords - 1);
                var lastSpaceRegex = new RegExp('\\s+(?=\\S+(?:\\s+\\S+){0,' + (spacesToFix - 1) + '}\\s*$)', 'g');
                var fixedText = text.replace(lastSpaceRegex, '&nbsp;');
                
                if (fixedText !== text) {
                    $element.html(fixedText);
                }
            });
        }
        
        // Apply orphan fix
        fixOrphans();
        
        // Reapply on window resize (in case text reflows)
        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Reset any existing fixes first
                $(selectorString).each(function() {
                    var $element = $(this);
                    var html = $element.html();
                    if (html && html.indexOf('&nbsp;') !== -1) {
                        $element.html(html.replace(/&nbsp;/g, ' '));
                    }
                });
                
                // Reapply fixes
                fixOrphans();
            }, 250);
        });
    });
    
})(jQuery);
