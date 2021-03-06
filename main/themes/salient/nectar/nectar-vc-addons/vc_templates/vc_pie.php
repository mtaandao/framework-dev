<?php



$title = $el_class = $value = $label_value= $units = '';
extract(shortcode_atts(array(
'title' => '',
'el_class' => '',
'value' => '50',
'units' => '',
'color' => '#21b68e',
'label_value' => ''
), $atts));

mn_enqueue_script('vc_pie');

$el_class = $this->getExtraClass( $el_class );
$css_class =  apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'vc_pie_chart mnb_content_element'.$el_class, $this->settings['base']);
$output = "\n\t".'<div class= "'.$css_class.'" data-pie-value="'.$value.'" data-pie-label-value="'.$label_value.'" data-pie-units="'.$units.'" data-pie-color="'.htmlspecialchars(hex2rgba($color, 1)).'">';
    $output .= "\n\t\t".'<div class="mnb_wrapper">';
        $output .= "\n\t\t\t".'<div class="vc_pie_wrapper '.strtolower($color).'">';
            $output .= "\n\t\t\t".'<span style="border-color: '.htmlspecialchars(hex2rgba($color, 1)).';" class="vc_pie_chart_back"></span>';
            $output .= "\n\t\t\t".'<span style="color: '.$color.';" class="vc_pie_chart_value"></span>';
            $output .= "\n\t\t\t".'<canvas width="101" height="101"></canvas>';
            $output .= "\n\t\t\t".'</div>';
        if ($title!='') {
        $output .= '<h4 class="mnb_heading mnb_pie_chart_heading">'.$title.'</h4>';
        }
        //mnb_widget_title(array('title' => $title, 'extraclass' => 'mnb_pie_chart_heading'));
    $output .= "\n\t\t".'</div>'.$this->endBlockComment('.mnb_wrapper');
    $output .= "\n\t".'</div>'.$this->endBlockComment('.mnb_pie_chart')."\n";

echo $output;