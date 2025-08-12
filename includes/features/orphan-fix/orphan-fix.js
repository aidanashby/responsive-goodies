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
                
                // Skip if element or any parent has exclude class
                if ($element.hasClass(excludeClass) || $element.closest('.' + excludeClass).length > 0) {
                    return;
                }
                
                // Skip if element contains child block elements
                if ($element.find('p, div, h1, h2, h3, h4, h5, h6').length > 0) {
                    return;
                }
                
                var textContent = $element.text();
                
                // Skip if text is empty
                if (!textContent || textContent.trim() === '' || !/\S/.test(textContent)) {
                    return;
                }
                
                // Only process elements that contain direct text nodes (not just child elements)
                var hasDirectText = false;
                $element.contents().each(function() {
                    if (this.nodeType === 3 && $(this).text().trim() !== '') { // Text node
                        hasDirectText = true;
                        return false;
                    }
                });
                
                if (!hasDirectText) {
                    return;
                }
                
                // Process each text node separately
                $element.contents().each(function() {
                    if (this.nodeType === 3) { // Text node
                        var text = $(this).text();
                        if (text.trim() !== '') {
                            // Find the last words in the text
                            var spacesToFix = Math.max(1, maxWords - 1);
                            var lastSpaceRegex = new RegExp('\\s+(?=\\S+(?:\\s+\\S+){0,' + (spacesToFix - 1) + '}\\s*$)', 'g');
                            var fixedText = text.replace(lastSpaceRegex, '\u00A0'); // Use Unicode non-breaking space
                            
                            if (fixedText !== text) {
                                $(this).replaceWith(document.createTextNode(fixedText));
                            }
                        }
                    }
                });

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
                    $element.contents().each(function() {
                        if (this.nodeType === 3) { // Text node
                            var text = $(this).text();
                            if (text.indexOf('\u00A0') !== -1) {
                                $(this).replaceWith(document.createTextNode(text.replace(/\u00A0/g, ' ')));
                            }
                        }
                    });
                });
                
                // Reapply fixes
                fixOrphans();
            }, 250);
        });
    });
    
})(jQuery);
