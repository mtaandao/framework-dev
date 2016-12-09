<?php
$output = $title = $id = '';
extract(shortcode_atts($this->predefined_atts, $atts));


$css_class =  apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'mnb_tab ui-tabs-panel mnb_ui-tabs-hide clearfix', $this->settings['base']);
$output .= "\n\t\t\t" . '<div id="tab-'. (empty($id) ? sanitize_title( $title ) : $id) .'" class="'.$css_class.'">';
$output .= ($content=='' || $content==' ') ? __("Empty section. Edit page to add content here.", "js_composer") : "\n\t\t\t\t" . mnb_js_remove_mnautop($content);
$output .= "\n\t\t\t" . '</div> ' . $this->endBlockComment('.mnb_tab');

echo $output;