/**
 * Custom scroll behavior for Elementor AJAX Pagination
 * Override default scroll to widget behavior - scroll to top of page (header) instead
 * 
 * File: wp-content/themes/astra-child/js/elementor-pagination-scroll.js
 */
(function($) {
    'use strict';

    // Function to scroll to top of PAGE (not tab container)
    function scrollToTop() {
        const offset = 0; // Change to header height if you have fixed header (e.g., 100)
        
        // Force scroll to top of PAGE using window.scrollTo
        // This ensures we scroll the entire page, not just tab container
        if (window.scrollTo) {
            try {
                window.scrollTo({
                    top: offset,
                    behavior: 'smooth'
                });
            } catch(e) {
                // Fallback if smooth scroll not supported
                window.scrollTo(0, offset);
            }
        }
        
        // Also use jQuery animate as backup/ensure it works
        $('html, body').stop(true, false).animate({
            scrollTop: offset
        }, 500, 'swing');
        
        // Also try document.documentElement for maximum compatibility
        if (document.documentElement) {
            document.documentElement.scrollTop = offset;
        }
        if (document.body) {
            document.body.scrollTop = offset;
        }
    }

    // Method 1: Override scrollIntoView to prevent Elementor from scrolling to widget
    let originalScrollIntoView = null;
    let isPaginationClick = false;

    function setupScrollOverride() {
        if (!originalScrollIntoView && Element.prototype.scrollIntoView) {
            originalScrollIntoView = Element.prototype.scrollIntoView;
            
            Element.prototype.scrollIntoView = function(options) {
                // If this is called during pagination, scroll to top of PAGE instead of widget/tab
                if (isPaginationClick) {
                    console.log('[Elementor Pagination] scrollIntoView intercepted, scrolling to top of PAGE');
                    scrollToTop();
                    return;
                }
                // Otherwise use original behavior
                return originalScrollIntoView.call(this, options);
            };
        }
    }

    // Setup immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupScrollOverride);
    } else {
        setupScrollOverride();
    }

    // Method 2: Direct event interception - Use CAPTURE phase to intercept BEFORE Elementor
    $(document).ready(function() {
        let scrollTimeout = null;
        let scrollAfterLoad = false;

        // Use CAPTURE phase to catch event BEFORE Elementor's listener
        // This ensures our code runs first
        document.addEventListener('click', function(e) {
            const target = e.target;
            
            // Check if clicked element is a pagination link (including page 1 which may not have e-page- param)
            if (target && target.matches && target.matches('.elementor-pagination a.page-numbers:not(.current)')) {
                const href = target.getAttribute('href') || '';
                
                // Check if it's Elementor pagination:
                // 1. Contains e-page- parameter (for pages 2+)
                // 2. OR is inside .elementor-pagination (for page 1, which may not have param)
                const isElementorPagination = href.indexOf('e-page-') !== -1 || 
                                              (target.closest('.elementor-pagination') !== null);
                
                if (isElementorPagination) {
                    console.log('[Elementor Pagination] Click detected in CAPTURE phase, will scroll to top of PAGE', {
                        href: href,
                        isPage1: href.indexOf('e-page-') === -1
                    });
                    
                    // Set flag immediately
                    isPaginationClick = true;
                    window._elementorPaginationScrollTop = true;
                    scrollAfterLoad = true;
                    
                    // Scroll immediately (before Elementor processes)
                    scrollToTop();
                    
                    // Also set up scroll after Elementor finishes
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(function() {
                        scrollToTop();
                    }, 100);
                    
                    // Reset flag after delay
                    setTimeout(function() {
                        isPaginationClick = false;
                    }, 2000);
                }
            }
        }, true); // true = use capture phase

        // Also use jQuery delegation as backup
        $(document).on('click', '.elementor-pagination a.page-numbers:not(.current)', function(e) {
            const $link = $(this);
            const href = $link.attr('href') || '';
            
            // Check if it's Elementor pagination (including page 1)
            const isElementorPagination = href.indexOf('e-page-') !== -1 || 
                                          $link.closest('.elementor-pagination').length > 0;
            
            if (isElementorPagination) {
                console.log('[Elementor Pagination] Click detected in bubble phase (backup)', {
                    href: href,
                    isPage1: href.indexOf('e-page-') === -1
                });
                // Flag already set in capture phase, just ensure scroll
                setTimeout(function() {
                    scrollToTop();
                }, 150);
            }
        });

        // Monitor DOM changes to catch when pagination content is loaded
        let observerTimeout = null;
        let lastScrollTime = 0;
        const observer = new MutationObserver(function(mutations) {
            if (window._elementorPaginationScrollTop || scrollAfterLoad) {
                const now = Date.now();
                // Throttle: only scroll if last scroll was more than 300ms ago
                if (now - lastScrollTime > 300) {
                    // Clear existing timeout
                    if (observerTimeout) {
                        clearTimeout(observerTimeout);
                    }
                    
                    // Wait a bit for content to fully render, then scroll to top of PAGE
                    observerTimeout = setTimeout(function() {
                        console.log('[Elementor Pagination] Content loaded, scrolling to top of PAGE');
                        scrollToTop();
                        lastScrollTime = Date.now();
                        window._elementorPaginationScrollTop = false;
                        scrollAfterLoad = false;
                    }, 600);
                }
            }
        });

        // Start observing body for changes
        if (document.body) {
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }

        // Method 4: Listen for Elementor's afterInsertPosts event (if available)
        // This fires after pagination content is loaded
        $(window).on('elementor/posts/pagination/success', function() {
            console.log('[Elementor Pagination] Pagination success event fired, scrolling to top');
            setTimeout(function() {
                scrollToTop();
            }, 100);
        });

        // Method 5: Override handleSuccessFetch if possible (most reliable)
        // This is called after Elementor fetches and inserts new content
        if (elementorModules && elementorModules.frontend) {
            // Try to find and patch AjaxPagination class after it loads
            const checkAndOverride = setInterval(function() {
                // Look for AjaxPagination in webpack chunks or elementor modules
                if (window.elementorPro && window.elementorPro.modules) {
                    // Try to find the handler
                    const handlers = elementorFrontend.handlers || {};
                    for (let handlerName in handlers) {
                        const handler = handlers[handlerName];
                        if (handler && handler.prototype && 
                            typeof handler.prototype.handleSuccessFetch === 'function' &&
                            typeof handler.prototype.maybeScrollToTop === 'function') {
                            
                            console.log('[Elementor Pagination] Found AjaxPagination handler, patching...');
                            
                            // Override handleSuccessFetch to scroll after content loads
                            const originalHandleSuccessFetch = handler.prototype.handleSuccessFetch;
                            handler.prototype.handleSuccessFetch = function(result) {
                                const ret = originalHandleSuccessFetch.call(this, result);
                                // Scroll after content is inserted
                                setTimeout(function() {
                                    console.log('[Elementor Pagination] handleSuccessFetch completed, scrolling to top');
                                    scrollToTop();
                                }, 200);
                                return ret;
                            };
                            
                            clearInterval(checkAndOverride);
                            break;
                        }
                    }
                }
            }, 500);
            
            // Stop checking after 10 seconds
            setTimeout(function() {
                clearInterval(checkAndOverride);
            }, 10000);
        }

        // Method 3: Override Elementor's maybeScrollToTop method directly
        // This is the most reliable way - intercept the method Elementor calls
        $(window).on('elementor/frontend/init', function() {
            console.log('[Elementor Pagination] Elementor frontend initialized, trying to override maybeScrollToTop');
            
            // Wait a bit for handlers to be registered
            setTimeout(function() {
                // Try to find AjaxPagination handler in elementor modules
                if (elementorModules && elementorModules.frontend && elementorModules.frontend.handlers) {
                    // Try to override via hooks
                    if (elementorFrontend && elementorFrontend.hooks) {
                        elementorFrontend.hooks.addFilter(
                            'frontend/handler',
                            function(HandlerClass) {
                                if (HandlerClass && HandlerClass.prototype && 
                                    typeof HandlerClass.prototype.maybeScrollToTop === 'function') {
                                    
                                    console.log('[Elementor Pagination] Found handler with maybeScrollToTop, overriding...');
                                    const original = HandlerClass.prototype.maybeScrollToTop;
                                    HandlerClass.prototype.maybeScrollToTop = function() {
                                        console.log('[Elementor Pagination] maybeScrollToTop called, scrolling to top of PAGE');
                                        // Original check
                                        if ('yes' !== this.getElementSettings('auto_scroll')) {
                                            return;
                                        }
                                        // Scroll to top of PAGE instead of widget
                                        scrollToTop();
                                    };
                                }
                                return HandlerClass;
                            },
                            999
                        );
                    }
                }
            }, 1000);
        });
    });

    // Debug: Log when script loads
    if (window.console && console.log) {
        console.log('[Elementor Pagination Scroll] Custom script loaded');
        
        // Check if jQuery is available
        if (typeof $ === 'undefined') {
            console.error('[Elementor Pagination Scroll] jQuery is not available!');
        } else {
            console.log('[Elementor Pagination Scroll] jQuery is available');
        }
    }

})(jQuery);

