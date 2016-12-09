<?php

require_once 'class.textarea.php';

/**
 * Description of class
 *
 * @author Srdjan
 *
 *
 */
class MNToolset_Field_Wysiwyg extends MNToolset_Field_Textarea {

    protected $_settings = array('min_mn_version' => '3.3');

    public function metaform() {

        $attributes = $this->getAttr();
        $form = array();
        $markup = '';
        $mnml_action = $this->getMNMLAction();

        if (is_admin()) {
            $markup .= '<div class="form-item form-item-markup">';
            $extra_markup = '';
            if (
                    defined('MNML_TM_VERSION') && intval($mnml_action) === 1 && function_exists('mncf_mnml_post_is_original') && !mncf_mnml_post_is_original() && function_exists('mncf_mnml_have_original') && mncf_mnml_have_original()
            ) {
                $attributes['readonly'] = 'readonly';
                $extra_markup .= sprintf(
                        '<img src="%s/images/locked.png" alt="%s" title="%s" style="position:relative;left:2px;top:2px;" />', MNCF_EMBEDDED_RES_RELPATH, __('This field is locked for editing because MNML will copy its value from the original language.', 'mncf'), __('This field is locked for editing because MNML will copy its value from the original language.', 'mncf')
                );
            }
            $markup .= sprintf(
                    '<label class="mnt-form-label mnt-form-textfield-label">%1$s%2$s</label>', stripcslashes($this->getTitle()), $extra_markup
            );
        }
        
        if(is_admin()){
            $markup .= '<div class="description mnt-form-description mnt-form-description-textfield description-textfield">'.stripcslashes($this->getDescription()).'</div>';
        } else {
            $markup .= stripcslashes($this->getDescription());
        }
        $markup .= $this->_editor($attributes);
        if (is_admin()) {
            $markup .= '</div>';
        }
        $form[] = array(
            '#type' => 'markup',
            '#markup' => $markup
        );
        return $form;
    }

    protected function _editor(&$attributes) {

        $media_buttons = $this->_data['has_media_button'];
        $quicktags = true;

        if (
                isset($attributes['readonly']) && $attributes['readonly'] == 'readonly'
        ) {
            add_filter('tiny_mce_before_init', array(&$this, 'tiny_mce_before_init_callback'));
            $media_buttons = false;
            $quicktags = false;
        }

        //EMERSON: Rewritten to set do_concat to TRUE so Mtaandao won't echo styles directly to the browser
        //This will fix a lot of issues as Mtaandao will not be echoing content to the browser before header() is called
        //This fix is important so we will not be necessarily adding ob_start() here in this todo:
        //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/185336518/comments#282283111
        //Using ob_start in that code will have some side effects of some styles from other plugins not being properly loaded.

        global $mn_styles;
        $mn_styles->do_concat = TRUE;
        ob_start();
        mn_editor($this->getValue(), $this->getId(), array(
            'mnautop' => true, // use mnautop?
            'media_buttons' => $media_buttons, // show insert/upload button(s)
            'textarea_name' => $this->getName(), // set the textarea name to something different, square brackets [] can be used here
            'textarea_rows' => get_option('default_post_edit_rows', 10), // rows="..."
            'tabindex' => '',
            'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
            'editor_class' => 'mnt-wysiwyg', // add extra class(es) to the editor textarea
            'teeny' => false, // output the minimal editor config used in Press This
            'dfw' => false, // replace the default fullscreen with DFW (needs specific DOM elements and css)
            'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
            'quicktags' => $quicktags // load Quicktags, can be used to pass settings directly to Quicktags using an array(),
        ));
        $return = ob_get_clean() . "\n\n";
        if (
                isset($attributes['readonly']) && $attributes['readonly'] == 'readonly'
        ) {
            remove_filter('tiny_mce_before_init', array(&$this, 'tiny_mce_before_init_callback'));
            $return = str_replace('<textarea', '<textarea readonly="readonly"', $return);
        }
        $mn_styles->do_concat = FALSE;
        return $return;
    }

    public function tiny_mce_before_init_callback($args) {
        $args['readonly'] = 1;
        return $args;
    }

}