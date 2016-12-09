<?php
if ( ! class_exists( 'Mtaa' ) ) die();
$support_shortcodes = file_exists(Mtaa::get_mtaa_root() . '/assets/css/shortcodes.css');
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
</head>
<body>
<div id="mnz-dialog">

<?php if ( $support_shortcodes ) { ?>

<div id="mnz-options-buttons" class="clear">
    <div class="alignleft">

        <input type="button" id="mnz-btn-cancel" class="button" name="cancel" value="Cancel" accesskey="C" />

    </div>
    <div class="alignright">

        <input type="button" id="mnz-btn-preview" class="button" name="preview" value="Preview" accesskey="P" />
        <input type="button" id="mnz-btn-insert" class="button-primary" name="insert" value="Insert" accesskey="I" />

    </div>
    <div class="clear"></div><!--/.clear-->
</div><!--/#mnz-options-buttons .clear-->

<div id="mnz-options" class="alignleft">
    <h3><?php echo __( 'Customize the Shortcode', 'mtaa' ); ?></h3>

    <table id="mnz-options-table">
    </table>

</div>

<div id="mnz-preview" class="alignleft">

    <h3><?php echo __( 'Preview', 'mtaa' ); ?></h3>

    <iframe id="mnz-preview-iframe" frameborder="0" style="width:100%;height:250px" scrolling="no"></iframe>

</div>
<div class="clear"></div>

<script type="text/javascript">var shortcode_generator_url = '<?php echo Mtaa::$assetsPath . '/js/shortcode-generator/'; ?>';</script>
<script type="text/javascript" src="<?php echo Mtaa::$assetsPath; ?>/js/shortcode-generator/column-control.js"></script>
<script type="text/javascript" src="<?php echo Mtaa::$assetsPath; ?>/js/shortcode-generator/tab-control.js"></script>
<script type="text/javascript" src="<?php echo Mtaa::$assetsPath; ?>/js/shortcode-generator/dialog.js"></script>
<?php  }  else { ?>

<div id="mnz-options-error">
    <p><?php echo __( 'Your version of theme does not yet support shortcodes.', 'mtaa' ); ?></p>
</div>

<?php  } ?>

</div>

</body>
</html>
