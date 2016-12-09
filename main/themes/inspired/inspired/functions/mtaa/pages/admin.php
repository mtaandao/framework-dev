<div class="clear"></div>
<div id="mtaaWrap">
    <div id="mtaaHead">
        <script type="text/javascript">
        var mtaa_ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';
        </script>
        <div id="mtaaLoading">
            <p>Loading</p>
        </div>
        <div id="mtaaSuccess">
            <p>Options successful saved</p>
        </div>
        <div id="mtaaFail"> 
            <p>Can't save options. Please contact <a href="http://mtaandao.co.ke/forum">Mtaa support</a>.</p>
        </div>
        <div id="mtaaLogo">
            <?php if (!mtaa::$tf) : ?>
            <a href="http://mtaandao.co.ke/" target="_blank">
            <?php endif; ?>
                <img src="<?php echo Mtaa::$mtaaPath; ?>/assets/images/logo.png" alt="Mtaa" />
            <?php if (!mtaa::$tf) : ?>
            </a>
            <?php endif; ?>
        </div>
        <div id="mtaaTheme">
            <h3><?php echo Mtaa::$themeName . ' <span>' . Mtaa::$themeVersion; ?></span></h3>
        </div>
     </div><!-- /#mtaaHead -->

     <div class="head_meta">
        <div id="mtaaFramework">
            <h5>Framework version <?php echo Mtaa::$mtaaVersion ?></h5>
        </div>
        <div id="mtaaInfo">
            <ul>
                <?php if (!mtaa::$tf) : ?>
                <li class="documentation">
                    <a href="http://www.mtaandao.co.ke/documentation/<?php echo str_replace('_', '-', Mtaa::$theme_raw_name); ?>" target="_blank">Documentation</a>
                </li>
                <?php endif; ?>

                <li class="support">
                    <a href="http://www.mtaandao.co.ke/forum" target="_blank">Support Forum</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="admin_main">
        <div id="mtaaNav">
            <?php Mtaa_Admin_Settings_Page::menu(); ?>
            <div class="cleaner">&nbsp;</div>
        </div><!-- end #zooNav -->

        <div class="tab_container">
            <form id="mtaaForm" method="post">
                <?php Mtaa_Admin_Settings_Page::content(); ?>

                <input type="hidden" name="action" value="save" />
                <?php mn_nonce_field('mtaa-ajax-save'); ?>
                <input type="hidden" id="nonce" name="_ajax_nonce" value="<?php echo mn_create_nonce('mtaa-ajax-save'); ?>" />
            </form>
            
        </div><!-- end .tab_container -->
        <div class="clear"></div>
    </div> <!-- /.admin_main -->
    
    <div class="mtaaActionButtons">
       
        <form id="mtaaReset" method="post">
            <p class="submit" style="float:right;" />
                <input name="reset" class="button-secondary" type="submit" value="Reset settings" />
                <input type="hidden" name="action" value="reset" />
            </p>
        </form>

        <p class="submit">
            <input id="submitZoomForm" name="save" class="button button-primary button-large" type="submit" value="Save all changes" />
        </p>
    </div><!-- end of .mtaaActionButtons -->

</div><!-- end #mtaaWrap -->

<div class="clear"></div>
