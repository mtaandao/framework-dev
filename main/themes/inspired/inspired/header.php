<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php ui::title(); ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

    <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700|Merriweather:400,700,400italic,700italic|Montserrat:400,700' rel='stylesheet' type='text/css'>

    <?php mn_head(); ?>
</head>
<body <?php body_class(); ?>>

<header class="site-header">
    <nav class="navbar <?php if (inspired_maybeWithCover()) echo 'page-with-cover'; ?> " role="navigation">
        <div class="wrap">
             <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-main-collapse">
                    <span class="sr-only"><?php _e( 'Toggle navigation', 'mtaa' ); ?></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <div class="navbar-brand">
                    <?php if ( ! option::get( 'misc_logo_path' ) ) echo '<h1>'; ?>

                    <a href="<?php echo home_url(); ?>" title="<?php bloginfo( 'description' ); ?>">
                        <?php if ( ! option::get( 'misc_logo_path' ) ) { bloginfo( 'name' ); } else { ?>
                            <img src="<?php echo ui::logo(); ?>" alt="<?php bloginfo( 'name' ); ?>" />
                        <?php } ?>
                    </a>

                    <?php if ( ! option::get( 'misc_logo_path' ) ) echo '</h1>'; ?>
                </div><!-- .navbar-brand -->
            </div>

            <div class="navbar-collapse collapse" id="navbar-main-collapse">

                <?php mn_nav_menu( array(
                        'menu_class'     => 'nav navbar-nav dropdown sf-menu',
                        'theme_location' => 'primary',
                        'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s' . diamond_wc_menu_cartitem() . '</ul>'
                    ) );
                ?>

            </div><!-- .navbar-collapse -->
        </div>
    </nav><!-- .navbar -->
</header><!-- .site-header -->
