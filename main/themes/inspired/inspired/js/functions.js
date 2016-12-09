/**
 * Theme functions file
 */
(function ($) {
    var $document = $(document);
    var $window = $(window);


    /**
    * Document ready (jQuery)
    */
    $(function () {
        /**
         * Header style.
         */
        if ($('.slides > li, .single .has-post-cover, .page .has-post-cover').length) {
            $('.navbar').addClass('page-with-cover');
        } else {
            $('.navbar').removeClass('page-with-cover');
        }

        /**
         * Activate superfish menu.
         */
        $('.sf-menu').superfish({
            'speed': 'fast',

            'animation': {
                'height': 'show'
            },
            'animationOut': {
                'height': 'hide'
            }
        });

        /**
         * Activate main slider.
         */
        $('#slider').sllider();

        /**
         *
         */
        $.fn.fullWidthContent();
        $.fn.responsiveSliderImages();

        $.fn.paralised();

        /**
         * Portfolio items popover.
         */
        $('.portfolio-archive .portfolio_item').thumbnailPopover();
        $('.portfolio-showcase .portfolio_item').thumbnailPopover();
        $('.portfolio-scroller .portfolio_item').thumbnailPopover();

        /**
         * Isotope filter for Portfolio Isotope template.
         */
        $('.portfolio-taxonomies-filter-by').portfolioIsotopeFilter();

        /**
         * Clickable divs.
         */
        $('.clickable').on('click', function() {
            window.location.href = $(this).data('href');
        });

        /**
         * Portflio infinite loading support.
         */
        var $folioitems = $('.portfolio-grid');
        if ( typeof mnz_currPage != 'undefined' && mnz_currPage < mnz_maxPages ) {
            $('.navigation').empty().append('<a class="btn btn-primary" id="load-more" href="#">Load More&hellip;</a>');

            $('#load-more').on('click', function() {
                if ( mnz_currPage < mnz_maxPages ) {
                    $(this).text('Loading...');
                    mnz_currPage++;

                    $.get(mnz_pagingURL + mnz_currPage + '/', function(data) {
                        $newItems = $('.portfolio-grid article', data).addClass('hidden').hide();

                        $folioitems.append($newItems);
                        $folioitems.find('article.hidden').fadeIn().removeClass('hidden');

                        if ( (mnz_currPage+1) <= mnz_maxPages ) {
                            $('#load-more').text('Load More\u2026');
                        } else {
                            $('#load-more').animate({height:'hide', opacity:'hide'}, 'slow', function(){$(this).remove();});
                        }
                    });
                }
                return false;
            });
        }
    });

    /**
     * Mobile menu support. (on document ready)
     */
    $(function() {
        var pageOffsetY;
        var $toggleBtn = $('.navbar-toggle');
        var $menu = $($toggleBtn.data('target'));
        var $header = $('.site-header');

        $toggleBtn.on('click', function() {
            if ($toggleBtn.hasClass('active')) {
                disablePopover();
            } else {
                enablePopover();
            }

            return false;
        });

        $window.on('resize orientationchange', update);

        function update() {
            disablePopover();
            $menu.attr('style', '');
        }

        function enablePopover() {
            $toggleBtn.addClass('active');
            pageOffsetY = window.pageYOffset;

            $header.addClass('has-menu-overlay');
            $menu.addClass('in').css('top', $('.site-header').outerHeight());

            $('body')
                .addClass('noscroll')
                .css('top', -pageOffsetY + 'px');
        }

        function disablePopover() {
            $toggleBtn.removeClass('active');
            $header.removeClass('has-menu-overlay');
            $menu.removeClass('in');
            $('body').removeClass('noscroll');
            $(window).scrollTop(pageOffsetY);
        }
    });

    $.fn.thumbnailPopover = function () {
        return this.on('mousemove', function (event) {
            var $this = $(this);
            var $popoverContent = $this.find('.entry-thumbnail-popover-content');

            var itemHeight = $this.outerHeight();
            var contentHeight = $popoverContent.outerHeight();
            var y = event.pageY - $this.offset().top;

            if (contentHeight <= itemHeight) {
                $popoverContent.addClass('popover-content--animated');
                $popoverContent.css('bottom', '');
                return;
            }

            $popoverContent.removeClass('popover-content--animated');

            $popoverContent.css({
                'bottom': (1 - y / itemHeight) * (itemHeight - contentHeight)
            });
        });
    };

    $.fn.sllider = function() {
        return this.each(function () {
            var $this = $(this);

            $this.flexslider({
                controlNav: false,
                directionNav: true,
                animationLoop: true,
                useCSS: true,
                smoothHeight: false,
                touch: true,
                pauseOnAction: true,
                slideshow: mtaaOptions.slideshow_auto,
                animation: mtaaOptions.slideshow_effect.toLowerCase(),
                slideshowSpeed: parseInt(mtaaOptions.slideshow_speed, 10)
            });

            $(window).on('resize focus', function () {
                $this.find('.slides, .slides > li')
                    .height($(window).height() - $('#slider').offset().top - parseInt($('#slider').css('padding-top'), 10));
            }).resize();

            $('#scroll-to-content').on('click', function () {
                $('html, body').animate({
                    scrollTop: $('#slider').offset().top + $('#slider').outerHeight()
                }, 600);
            });
        });
    };

    /**
     * Simple Parallax plugin.
     */
    $.fn.paralised = function() {
        var features = {
            bind : !!(function(){}.bind),
            rAF : !!(window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame)
        };

        if (typeof features === 'undefined' || !features.rAF || !features.bind) return;

        /**
         * Handles debouncing of events via requestAnimationFrame
         * @see http://www.html5rocks.com/en/tutorials/speed/animations/
         * @param {Function} callback The callback to handle whichever event
         */
        function Debouncer(e){this.callback=e;this.ticking=false}Debouncer.prototype={constructor:Debouncer,update:function(){this.callback&&this.callback();this.ticking=false},requestTick:function(){if(!this.ticking){requestAnimationFrame(this.rafCallback||(this.rafCallback=this.update.bind(this)));this.ticking=true}},handleEvent:function(){this.requestTick()}}

        var debouncer = new Debouncer(update.bind(this));

        $(window).on('scroll', debouncer.handleEvent.bind(debouncer));
        debouncer.handleEvent();

        function update() {
            var $postCover = $('.has-post-cover .entry-cover');

            if ($postCover.length) {
                var scrollPos = $(document).scrollTop();

                var $postCover = $('.entry-cover');
                var postCoverBottom = $postCover.position().top + $postCover.outerHeight();

                if (scrollPos < postCoverBottom) {
                    var x = easeOutSine(scrollPos, 0, 1, postCoverBottom);

                    $postCover.find('.entry-header').css({
                        'bottom' : 70 * (1 - x),
                        'opacity' : 1 - easeInQuad(scrollPos, 0, 1, postCoverBottom)
                    });
                }
            }
        }

        function easeOutSine(t, b, c, d) {
            return c * Math.sin(t/d * (Math.PI/2)) + b;
        }

        function easeInQuad(t, b, c, d) {
            return c*(t/=d)*t + b;
        }
    };

    $.fn.portfolioIsotopeFilter = function () {
        return this.each(function() {
            var $this = $(this);
            var $taxs = $this.find('li');
            var $portfolio = $('.portfolio-grid');

            $(window).load(function () {
                $portfolio.fadeIn().isotope({
                    layoutMode : 'fitRows',
                    itemSelector: 'article'
                });
            });

            var tax_filter_regex = /cat-item-([0-9]+)/gi;

            var preferences = {
                duration: 400,
                adjustHeight: 'dynamic',
                useScaling: true
            };

            $taxs.on('click', function(event) {
                event.preventDefault();

                $this = $(this);

                $taxs.removeClass('current-cat');
                $this.addClass('current-cat');

                var catID = tax_filter_regex.exec($this.attr('class'));
                tax_filter_regex.lastIndex = 0;

                if (catID === null) {
                    filter = '.type-portfolio_item';
                } else {
                    filter = '.portfolio_' + catID[1] +'_item';
                }

                $portfolio.isotope({
                    'filter': filter
                });
            });
        });
    };

    $.fn.fullWidthContent = function () {
        $(window).on('resize', update);

        function update() {
            var windowWidth = $(window).width();
            var containerWidth = $('.entry-content').width();
            var marginLeft = (windowWidth - containerWidth) / 2;

            $('.fullimg').css({
                'width'       : windowWidth,
                'margin-left' : -marginLeft
            });
        }

        update();
    };

    $.fn.responsiveSliderImages = function () {
        $(window).on('resize orientationchange', update);

        function update() {
            var windowWidth = $(window).width();

            if (windowWidth < 680) {
                $('#slider .slides li').each(function () {
                    var bgurl = $(this).css('background-image').match(/^url\(['"]?(.+)["']?\)$/)[1],
                        smallimg = $(this).data('smallimg');

                    if ( !bgurl || typeof smallimg === 'undefined' || bgurl == smallimg ) return;

                    $(this).css('background-image', 'url("' + smallimg + '")');
                });
            }

            if (windowWidth > 680) {
                $('#slider .slides li').each(function () {
                    var bgurl = $(this).css('background-image').match(/^url\(['"]?(.+)["']?\)$/)[1],
                        bigimg = $(this).data('bigimg');

                    if ( !bgurl || typeof bigimg === 'undefined' || bgurl == bigimg ) return;

                    if ( !$(this).data('smallimg') ) $(this).data('smallimg', bgurl);

                    $(this).css('background-image', 'url("' + bigimg + '")');
                });
            }
        }

        update();
    };

})(jQuery);
