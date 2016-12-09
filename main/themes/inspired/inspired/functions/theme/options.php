<?php return array(


/* Theme Admin Menu */
"menu" => array(
    array("id"    => "1",
          "name"  => "General"),

    array("id"    => "2",
          "name"  => "Homepage"),

    array("id"    => "5",
          "name"  => "Styling"),
),

/* Theme Admin Options */
"id1" => array(
    array("type"  => "preheader",
          "name"  => "Theme Settings"),

	array("name"  => "Logo Image",
          "desc"  => "Upload a custom logo image for your site, or you can specify an image URL directly.",
          "id"    => "misc_logo_path",
          "std"   => "",
          "type"  => "upload"),

    array("name"  => "Favicon URL",
          "desc"  => "Upload a favicon image (16&times;16px).",
          "id"    => "misc_favicon",
          "std"   => "",
          "type"  => "upload"),

    array("name"  => "Custom Feed URL",
          "desc"  => "Example: <strong>http://feeds.feedburner.com/mtaa</strong>",
          "id"    => "misc_feedburner",
          "std"   => "",
          "type"  => "text"),

	array("name"  => "Enable comments for static pages",
          "id"    => "comments_page",
          "std"   => "off",
          "type"  => "checkbox"),

    array("name"  => "Display WooCommerce Cart Button in the Header?",
          "id"    => "cart_icon",
          "std"   => "on",
          "type"  => "checkbox"),


      array(
            "type" => "preheader",
            "name" => "Global Posts Options"
        ),

        array(
            "name" => "Content",
            "desc" => "Number of posts displayed on homepage can be changed <a href=\"options-reading.php\" target=\"_blank\">here</a>.",
            "id" => "display_content",
            "options" => array(
                'Excerpt',
                'Full Content',
                'None'
            ),
            "std" => "Excerpt",
            "type" => "select"
        ),

        array(
            "name" => "Excerpt length",
            "desc" => "Default: <strong>50</strong> (words)",
            "id" => "excerpt_length",
            "std" => "50",
            "type" => "text"
        ),

        array(
            "type" => "startsub",
            "name" => "Featured Image"
        ),

        array(
            "name" => "Display Featured Image at the Top",
            "id" => "index_thumb",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Featured Image Width (in pixels)",
            "desc" => "Default: <strong>950</strong> (pixels).<br/><br/>You'll have to run the <a href=\"http://mtaandao.org/extend/plugins/regenerate-thumbnails/\" target=\"_blank\">Regenerate Thumbnails</a> plugin each time you modify width or height (<a href=\"http://www.mtaandao.co.ke/tutorial/fixing-stretched-images/\" target=\"_blank\">view how</a>).",
            "id" => "thumb_width",
            "std" => "950",
            "type" => "text"
        ),

        array(
            "name" => "Featured Image Height (in pixels)",
            "desc" => "Default: <strong>320</strong> (pixels)",
            "id" => "thumb_height",
            "std" => "320",
            "type" => "text"
        ),

        array(
            "type" => "endsub"
        ),


        array(
            "name" => "Display Category",
            "id" => "display_category",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Author",
            "id" => "display_author",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Date/Time",
            "desc" => "<strong>Date/Time format</strong> can be changed <a href='options-general.php' target='_blank'>here</a>.",
            "id" => "display_date",
            "std" => "on",
            "type" => "checkbox"
        ),


        array(
            "name" => "Display Comments Count",
            "id" => "display_comments",
            "std" => "on",
            "type" => "checkbox"
        ),


        array(
            "type" => "preheader",
            "name" => "Single Post Options"
        ),


        array(
            "name" => "Display Category",
            "id" => "post_category",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Date/Time",
            "desc" => "<strong>Date/Time format</strong> can be changed <a href='options-general.php' target='_blank'>here</a>.",
            "id" => "post_date",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Author",
            "desc" => "You can edit your profile on this <a href='profile.php' target='_blank'>page</a>.",
            "id" => "post_author",
            "std" => "on",
            "type" => "checkbox"
        ),

         array(
            "name" => "Display Tags",
            "id" => "post_tags",
            "std" => "on",
            "type" => "checkbox"
        ),


            array("type" => "startsub",
                   "name" => "Share Buttons"),

              array("name"  => "Display Share Buttons",
                    "id"    => "post_share",
                    "std"   => "on",
                    "type"  => "checkbox"),

              array("name"  => "Twitter Button Label",
                    "desc"  => "Default: <strong>Share on Twitter</strong>",
                    "id"    => "post_share_label_twitter",
                    "std"   => "Share on Twitter",
                    "type"  => "text"),

              array("name"  => "Facebook Button Label",
                    "desc"  => "Default: <strong>Share on Facebook</strong>",
                    "id"    => "post_share_label_facebook",
                    "std"   => "Share on Facebook",
                    "type"  => "text"),

              array("name"  => "Google+ Button Label",
                    "desc"  => "Default: <strong>Share on Google+</strong>",
                    "id"    => "post_share_label_gplus",
                    "std"   => "Share on Google+",
                    "type"  => "text"),

            array("type"  => "endsub"),



        array(
            "name" => "Display Comments",
            "id" => "post_comments",
            "std" => "on",
            "type" => "checkbox"
        ),


        array(
            "type" => "preheader",
            "name" => "Portfolio Post Options"
        ),


        array(
            "name" => "Display Category",
            "id" => "portfolio_category",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Date/Time",
            "desc" => "<strong>Date/Time format</strong> can be changed <a href='options-general.php' target='_blank'>here</a>.",
            "id" => "portfolio_date",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Author",
            "desc" => "You can edit your profile on this <a href='profile.php' target='_blank'>page</a>.",
            "id" => "portfolio_author",
            "std" => "on",
            "type" => "checkbox"
        ),


        array("type" => "startsub",
               "name" => "Share Buttons"),

          array("name"  => "Display Share Buttons",
                "id"    => "portfolio_share",
                "std"   => "on",
                "type"  => "checkbox"),

          array("name"  => "Twitter Button Label",
                "desc"  => "Default: <strong>Share on Twitter</strong>",
                "id"    => "portfolio_share_label_twitter",
                "std"   => "Share on Twitter",
                "type"  => "text"),

          array("name"  => "Facebook Button Label",
                "desc"  => "Default: <strong>Share on Facebook</strong>",
                "id"    => "portfolio_share_label_facebook",
                "std"   => "Share on Facebook",
                "type"  => "text"),

          array("name"  => "Google+ Button Label",
                "desc"  => "Default: <strong>Share on Google+</strong>",
                "id"    => "portfolio_share_label_gplus",
                "std"   => "Share on Google+",
                "type"  => "text"),

        array("type"  => "endsub"),



        array(
            "name" => "Display Comments",
            "id" => "portfolio_comments",
            "std" => "on",
            "type" => "checkbox"
        )

    ),


"id2" => array(

    array("type"  => "preheader",
          "name"  => "Homepage Slideshow"),

    array("name"  => "Display Slideshow on homepage?",
          "desc"  => "Do you want to show a featured slider on the homepage? To add posts in slider, go to <a href='edit.php?post_type=slider' target='_blank'>Slideshow section</a>",
          "id"    => "featured_posts_show",
          "std"   => "on",
          "type"  => "checkbox"),

    array("name"  => "Autoplay Slideshow?",
          "desc"  => "Do you want to auto-scroll the slides?",
          "id"    => "slideshow_auto",
          "std"   => "on",
          "type"  => "checkbox",
          "js"    => true),

    array("name"  => "Slider Autoplay Interval",
          "desc"  => "Select the interval (in miliseconds) at which the Slider should change posts (<strong>if autoplay is enabled</strong>). Default: 3000 (3 seconds).",
          "id"    => "slideshow_speed",
          "std"   => "3000",
          "type"  => "text",
          "js"    => true),

    array("name"  => "Slider Effect",
          "desc"  => "Select the effect for slides transition.",
          "id"    => "slideshow_effect",
          "options" => array('Slide', 'Fade'),
          "std"   => "Slide",
          "type"  => "select",
          "js"    => true),

    array("name"  => "Number of Posts in Slider",
          "desc"  => "How many posts should appear in  Slider on the homepage? Default: 5.",
          "id"    => "featured_posts_posts",
          "std"   => "5",
          "type"  => "text"),

),


"id5" => array(
    array("type"  => "preheader",
          "name"  => "Colors"),

    array("name"  => "Logo Color",
           "id"   => "logo_color",
           "type" => "color",
           "selector" => ".navbar-brand h1 a",
           "attr" => "color"),

    array("name"  => "Menu Link Color",
           "id"   => "menu_color",
           "type" => "color",
           "selector" => ".navbar a",
           "attr" => "color"),

    array("name"  => "Link Color",
           "id"   => "a_css_color",
           "type" => "color",
           "selector" => "a",
           "attr" => "color"),

    array("name"  => "Link Hover Color",
           "id"   => "ahover_css_color",
           "type" => "color",
           "selector" => "a:hover",
           "attr" => "color"),

    array("name"  => "Homepage Widget Title Color",
           "id"   => "widget_color",
           "type" => "color",
           "selector" => ".section-title",
           "attr" => "color"),


    array("type" => "startsub",
           "name" => "Buttons"),


    array("name"  => "Buttons Background Color",
           "id"   => "button_color",
           "type" => "color",
           "selector" => ".button, .btn, .more-link, .more_link, .site-footer .search-form .search-submit",
           "attr" => "background-color"),

    array("name"  => "Buttons Border Color",
           "id"   => "button_boreder_color",
           "type" => "color",
           "selector" => ".button, .btn, .more-link, .more_link, .site-footer .search-form .search-submit",
           "attr" => "border-color"),

    array("name"  => "Buttons Text Color",
           "id"   => "button_text_color",
           "type" => "color",
           "selector" => ".button, .btn, .more-link, .more_link, .site-footer .search-form .search-submit",
           "attr" => "color"),


    array("type"  => "endsub"),


    array("type" => "startsub",
           "name" => "Footer"),

    array("name"  => "Footer Background",
           "id"   => "headerbg_color",
           "type" => "color",
           "selector" => ".site-footer",
           "attr" => "background-color"),

    array("name"  => "Footer Widget Title Color",
           "id"   => "footer_widget_color",
           "type" => "color",
           "selector" => ".footer-widgets .title",
           "attr" => "color"),

    array("type"  => "endsub"),



    array("type"  => "preheader",
          "name"  => "Fonts"),

    array("name" => "General Text Font Style",
          "id" => "typo_body",
          "type" => "typography",
          "selector" => "body" ),

    array("name" => "Logo Text Style",
          "id" => "typo_logo",
          "type" => "typography",
          "selector" => ".navbar-brand h1 a" ),

    array("name" => "Homepage Slider Title",
          "id" => "slide_title",
          "type" => "typography",
          "selector" => ".slides > li h3" ),

    array("name" => "Homepage Slider Description",
          "id" => "slide_desc",
          "type" => "typography",
          "selector" => ".slides > li .excerpt" ),

    array("name"  => "Post Title Style",
           "id"   => "typo_post_title",
           "type" => "typography",
           "selector" => ".entry-title"),

    array("name"  => "Individual Post Title Style",
           "id"   => "typo_individual_post_title",
           "type" => "typography",
           "selector" => ".page .has-post-cover .entry-header .entry-title, .single .has-post-cover .entry-header .entry-title"),

    array("name"  => "Widget Title Style",
           "id"   => "typo_widget",
           "type" => "typography",
           "selector" => ".section-title"),
),

"portfolio" => array(
   array(
       "type" => "preheader",
       "name" => "Portfolio Options"
   ),

   array(
       "name" => "Number of items per page in Portfolio Template (paginated) ",
       "desc" => "Default: <strong>9</strong>",
       "id" => "portfolio_posts",
       "std" => "9",
       "type" => "text"
   ),

   array(
       "name" => "Display Porfolio Posts Excerpt",
       "id" => "portfolio_excerpt",
       "std" => "on",
       "type" => "checkbox"
   ),

   array(
       "name" => "Portfolio Page",
       "desc" => "Choose the page to which should link <em>All</em> button.",
       "id" => "portfolio_url",
       "std" => "",
       "type" => "select-page"
   )
)

/* end return */);