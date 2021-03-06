<?php
require_once MNCF_INC_ABSPATH . '/classes/class.types.admin.page.php';
/**
 * Summary.
 *
 * Description.
 *
 * @since x.x.x
 * @access (for functions: only use if private)
 *
 * @see Function/method/class relied on
 * @link URL
 * @global type $varname Description.
 * @global type $varname Description.
 *
 * @param type $var Description.
 * @param type $var Optional. Description.
 * @return type Description.
 */
class Types_Fields_Conditional extends Types_Admin_Page
{
    public function __construct()
    {
        $this->init_admin();
    }

    public function init_admin()
    {
        /**
         * fields
         */
        add_action('mn_ajax_mncf_edit_field_condition_get_row', array($this, 'field_get_row'));
        add_action('mn_ajax_mncf_edit_field_condition_get', array($this, 'field_condition_get'));
        add_action('mn_ajax_mncf_edit_field_condition_save', array($this, 'field_save'));
        /**
         * group
         */
        add_action('mn_ajax_mncf_edit_custom_field_group_get', array($this, 'group_condition_get'));
    }

    /**
     * Summary.
     *
     * Description.
     *
     * @since x.x.x
     * @access (for functions: only use if private)
     *
     * @see Function/method/class relied on
     * @link URL
     * @global type $varname Description.
     * @global type $varname Description.
     *
     * @param type $var Description.
     * @param type $var Optional. Description.
     * @return type Description.
     */
    public function get_field_conditionals($form, $data)
    {
        /**
         * Sanitize form
         */
        if ( !is_array($form) ) {
            $form = array();
        }

        if( isset( $data['meta_type'] ) ) {
            switch( $data['meta_type'] ) {

                case 'postmeta':
                    $form['data-dependant-meta'] = array(
                        '#type' => 'markup',
                        '#markup' => '<span id="data-dependant-meta" style="display:none;"
                            data-mncf-action="mncf_edit_field_condition_get"
                            data-mncf-id="'.$data['name'].'"
                            data-mncf-group-id="'.$data['id'].'"
                            data-mncf-buttons-apply-nonce="'. mn_create_nonce( 'mncf-conditional-apply-'.$data['id'] ) .'"
                            data-mncf-meta-type="postmeta"></span>'
                    );
                    break;
                case 'custom_fields_group':
                case 'custom-fields-group':
                    $form['data-dependant-meta'] = array(
                        '#type' => 'markup',
                        '#markup' => '<span id="data-dependant-meta" style="display:none;"
                            data-mncf-action="mncf_edit_custom_field_group_get"
                            data-mncf-id="'.$data['name'].'"
                            data-mncf-group-id="'.$data['id'].'"
                            data-mncf-buttons-apply-nonce="'. mn_create_nonce( 'mncf-conditional-apply-'.$data['id'] ) .'"
                            data-mncf-meta-type="custom_fields_group"></span>'
                    );
                    break;
            }
        }

        $use_custom_logic = $this->use_custom_logic($data);

        $datas = mncf_admin_fields_get_fields(true, false, true);
        ksort( $datas, SORT_STRING );

        $form['form-begin'] = array(
            '#type' => 'markup',
            '#markup' => '<form>',
        );

        $form['simple-logic-open'] = array(
            '#type' => 'markup',
            '#markup' => sprintf(
                '<div class="js-mncf-simple-logic %s">',
                $use_custom_logic ? 'hidden':''
            ),
        );

        $form['description'] = array(
            '#type' => 'markup',
            '#markup' => mnautop(__("Specify additional filters that control this fields group display, based on values of other custom fields.", 'mncf')),
        );

        $show_relation = (
            true
            && isset( $data['data']['conditional_display'])
            && isset( $data['data']['conditional_display']['conditions'])
            && !empty( $data['data']['conditional_display']['conditions'])
        );

        $form['cd-relation'] = array(
            '#title' => __('Relation between conditions:', 'mncf'),
            '#type' => 'radios',
            '#name' => 'relation',
            '#options' => array(
                'AND' => array(
                    '#title' => 'AND',
                    '#inline' => true,
                    '#value' => 'AND',
                ),
                'OR' => array(
                    '#title' => 'OR',
                    '#inline' => true,
                    '#value' => 'OR'
                ),
            ),
            '#default_value' => isset( $data['data']['conditional_display']['relation'] ) ? $data['data']['conditional_display']['relation'] : 'AND',
            '#inline' => true,
            '#before' => sprintf('<div class="mncf-cd-relation simple-logic %s">', $show_relation && 1 < count( $data['data']['conditional_display']['conditions'])?'':'hidden'),
            '#after' => '</div>',
        );

        $form['options-markup-open'] = array(
            '#type' => 'markup',
            '#title' => __( 'Options', 'mncf' ),
            '#markup' => sprintf(
                '<table id="mncf-conditional"><thead><tr>'
                .'<td class="mncf-form-options-header-title">%s</th>'
                .'<td class="mncf-form-options-header-value">%s</th>'
                .'<td class="mncf-form-options-header-default">%s</th>'
                .'</tr></thead>'
                .'<tbody id="%s-sortable" class="js-mncf-fields-conditions">',
                __( 'Field', 'mncf' ),
                __( 'Condition', 'mncf' ),
                __( 'Value', 'mncf' ),
                esc_attr($data['id'])
            ),
        );

        if ( $show_relation ) {
            $group = isset($data['meta_type']) && 'custom-fields-group' == $data['meta_type'];
            require_once MNCF_ABSPATH . '/includes/conditional-display.php';
            if( is_array( $data['data']['conditional_display']['conditions'] ) && ! empty( $data['data']['conditional_display']['conditions'] ) ) {
                foreach( $data['data']['conditional_display']['conditions'] as $condition ) {
                    $form += mncf_cd_admin_form_single_filter($data, $condition, null, $group);
                }
            }
        }

        $form['options-response-close'] = array(
            '#type' => 'markup',
            '#markup' => '</table>',
        );
        $form['button-add'] = array(
            '#type' => 'button',
            '#inline' => true,
            '#value' => isset( $data['data']['conditional_display']['conditions'] ) && !empty( $data['data']['conditional_display']['conditions'] )
                ? __( 'Add another condition', 'mncf' ) : __( 'Add condition', 'mncf' ),
            '#name' => 'button-add',
            '#attributes' => array(
                'class' => sprintf( 'js-mncf-condition-button-add-row %s', isset( $data['data']['conditional_display']['conditions'] ) ? 'alignright' : 'mncf-block-center' ),
                'data-mncf-nonce' => mn_create_nonce($this->get_nonce(__CLASS__, $data['id'], $data['meta_type'] ) ),
                'data-mncf-id' => $data['id'],
                'data-mncf-meta-type' => $data['meta_type'],
                'data-mncf-label-add-condition' => __( 'Add condition', 'mncf' ),
                'data-mncf-label-add-another-condition' => __( 'Add another condition', 'mncf' )
            ),
            '#before' => '<p>',
            '#after' => '</p>',
        );
        $form['simple-logic-close'] = array(
            '#type' => 'markup',
            '#markup' => '</div>',
        );
        /**
         * Advanced conditional
         */

        $form['toggle_open_area'] = array(
            '#type' => 'markup',
            '#markup' => sprintf(
                '<div class="js-mncf-advance-logic %s">',
                $use_custom_logic ? '':'hidden'
            ),
        );
        $form['custom-description'] = array(
            '#type' => 'markup',
            '#markup' => mnautop(__('Customize conditions', 'mncf')),
        );
        $form['custom'] = array(
            '#type' => 'textarea',
            '#name' => 'custom',
            '#inline' => true,
            '#value' => isset( $data['data']['conditional_display']['custom'] ) ? $data['data']['conditional_display']['custom'] : '',
        );
        $form['date_notice'] = array(
            '#type' => 'markup',
            '#markup' =>
	            sprintf(
		            '<div style="display:none; margin-top:15px;" class="mncf-cd-notice-date">
						<a href="%s" target="_blank">%s &raquo;</a></div>',
	                Types_Helper_Url::get_url( 'date-filters', true ),
		            __( 'Dates can be entered using the date filters', 'mncf' )
                ),
        );
        $form['toggle_close'] = array(
            '#type' => 'markup',
            '#markup' => '</div>',
        );

        /**
         * switch button
         */
        $form['switch'] = array(
            '#type' => 'markup',
            '#markup' => sprintf(
                '<a href="#" data-mncf-content-advanced="%s" data-mncf-content-simple="%s" data-mncf-custom-logic="%s" class="js-mncf-condition-button-display-logic">%s</a>',
                __('Advanced text mode', 'mncf'),
                __('Switch to basic mode', 'mncf'),
                $use_custom_logic? 'advance-logic':'simple-logic',
                $use_custom_logic? __('Switch to basic mode', 'mncf'):__('Advanced text mode', 'mncf')
            ),
            '#before' => '<p class="mncf-fields-conditional-switch-container">',
            '#after' => '</p>',
            '#pattern' => '<BEFORE><ELEMENT><AFTER>',
        );

        $form['custom_use'] = array(
            '#type' => 'hidden',
            '#name' => 'custom_use',
            '#value' => $use_custom_logic,
            '#attributes' => array(
                'class' => 'js-mncf-condition-custom-use',
            ),
        );

        $form['form-end'] = array(
            '#type' => 'markup',
            '#markup' => '</form>',
        );

        return $form;
    }

    /**
     * Summary.
     *
     * Description.
     *
     * @since x.x.x
     * @access (for functions: only use if private)
     *
     * @see Function/method/class relied on
     * @link URL
     * @global type $varname Description.
     * @global type $varname Description.
     *
     * @param type $var Description.
     * @param type $var Optional. Description.
     * @return type Description.
     */
    private function use_custom_logic($data)
    {
        return (
            true
            && array_key_exists( 'data', $data )
            && is_array( $data['data'] )
            && array_key_exists( 'conditional_display', $data['data'] )
            && is_array( $data['data']['conditional_display'] )
            && array_key_exists( 'custom_use', $data['data']['conditional_display'] )
            && !empty( $data['data']['conditional_display']['custom_use'] )
        );
    }

    /**
     * Summary.
     *
     * Description.
     *
     * @since x.x.x
     * @access (for functions: only use if private)
     *
     * @see Function/method/class relied on
     * @link URL
     * @global type $varname Description.
     * @global type $varname Description.
     *
     * @param type $var Description.
     * @param type $var Optional. Description.
     * @return type Description.
     */
    public function field_get_row()
    {
        /**
         * check nonce
         */
        if (
            0
            || !isset($_REQUEST['_mnnonce'])
            || !isset($_REQUEST['id'])
            || !isset($_REQUEST['meta_type'])
            || !mn_verify_nonce($_REQUEST['_mnnonce'], $this->get_nonce(__CLASS__, $_REQUEST['id'], $_REQUEST['meta_type']))
        ) {
            $this->verification_failed_and_die();
        }
        $field = mncf_admin_fields_get_field( sanitize_text_field( $_REQUEST['id'] ), false, true );
        require_once MNCF_ABSPATH . '/includes/conditional-display.php';

        $is_group = $_REQUEST['meta_type'] == 'custom-fields-group'
            ? true
            : false;

        echo mncf_form_simple( mncf_cd_admin_form_single_filter( $field, array(), null, $is_group ) );

        die;
    }

    /**
     * Summary.
     *
     * Description.
     *
     * @since x.x.x
     * @access (for functions: only use if private)
     *
     * @see Function/method/class relied on
     * @link URL
     * @global type $varname Description.
     * @global type $varname Description.
     *
     * @param type $var Description.
     * @param type $var Optional. Description.
     * @return type Description.
     */
    public function field_save()
    {
        /**
         * check nonce
         */
        if (
            false
            || !isset($_REQUEST['_mnnonce'])
            || !isset($_REQUEST['id'])
            || !isset($_REQUEST['group_id'])
            || !isset($_REQUEST['meta_type'])
            || !isset($_REQUEST['conditions'])
            || !isset($_REQUEST['group_conditions'])
            || !mn_verify_nonce($_REQUEST['_mnnonce'], $this->get_nonce('mncf-conditional-apply', $_REQUEST['group_id']))
        ) {
            $this->verification_failed_and_die();
        }
        $id                 = $_REQUEST['id'];
        $group_conditions   = $_REQUEST['group_conditions'] == 1 ? true : false;
        $conditions_set     = false;

        $conditions = array();
        parse_str($_REQUEST['conditions'], $conditions);

        // group conditions
        if( $group_conditions ) {
            $id = $_REQUEST['group_id'];

            if( isset( $conditions['mncf']['group']['conditional_display']['conditions'] ) )
                $conditions_set = $conditions['mncf']['group']['conditional_display']['conditions'];

        // single field conditions
        } elseif( isset( $conditions['mncf']['fields'][$id]['conditional_display']['conditions'] ) ) {
            $conditions_set = $conditions['mncf']['fields'][$id]['conditional_display']['conditions'];
        }

        // update condtions
        if( $conditions_set || ( isset( $conditions['custom'] ) && ! empty( $conditions['custom'] ) ) ) {

            // filter unfilled fields
            $conditions_to_save = array();

            if( $conditions_set ) {
                foreach( $conditions_set as $key => $one ) {
                    if ( empty($one['field']) ) {
                        continue;
                    }
                    $conditions_to_save[$key] = $one;
                }
            }

            $this->update_conditions(
                $id,
                $_REQUEST['meta_type'],
                array(
                    'relation' => isset($conditions['relation'])? $conditions['relation']:'AND',
                    'conditions' => $conditions_to_save,
                    'custom' => isset($conditions['custom'])? $conditions['custom']:'',
                    'custom_use' => isset($conditions['custom_use'])? intval($conditions['custom_use']):0,
                ),
                $group_conditions
            );
        } else {
            $this->remove_conditions( $id, $group_conditions );
            die( '' );
        }

        $field = ( $group_conditions )
            ? array( 'data' => array('conditional_display' => get_post_meta( $id, '_mncf_conditional_display', true ) ) )
            : mncf_admin_fields_get_field( sanitize_text_field( $_REQUEST['id'] ), false, true );

        require_once MNCF_ABSPATH . '/includes/conditional-display.php';
        echo mncf_conditional_get_curent($field);
        die;
    }

    /**
     * Summary.
     *
     * Description.
     *
     * @since x.x.x
     * @access (for functions: only use if private)
     *
     * @see Function/method/class relied on
     * @link URL
     * @global type $varname Description.
     * @global type $varname Description.
     *
     * @param type $var Description.
     * @param type $var Optional. Description.
     * @return type Description.
     */
    public function update_conditions( $id, $field_type, $conditions, $group_conditions = false )
    {
        /*
         * Group Conditions
         */
        if( $group_conditions ) {

	        // See the comment below
	        $conditions['custom'] = $this->transform_operators_to_text_equivalents(
		        mncf_getarr( $conditions, 'custom' )
	        );

            $sanitized_conditions = sanitize_text_field_recursively( $conditions );

            // restore condition operators (lost through sanitize_text_field_recursively())
            $sanitized_conditions['conditions'] = $this->mncf_conditions_restore_original_operators(
                $sanitized_conditions['conditions'],
                $conditions['conditions']
            );

            // Tokanizer ($this->transform_operators_to_text_equivalents)
            // transforms slugs like "my-field-slug" to "my - field - slug"
            // following will restore the slugs
            if( isset( $sanitized_conditions['custom'] ) && ! empty( $sanitized_conditions['custom'] ) ) {
                $sanitized_conditions['custom'] = preg_replace_callback(
                    '#\$\(([A-z0-9]*\s[\-]\s[A-z0-9]*)*\)#',
                    array( $this, 'callback_restore_slug_format' ),
                    $sanitized_conditions['custom']
                );
            }

            update_post_meta( $id, '_mncf_conditional_display', $sanitized_conditions );

        /*
         * Field Conditions
         */
        } else {
            $field = mncf_fields_get_field_by_slug($id);
            if ( empty( $field ) ) {
	            return;
            }

	        // We're running into problems with oversanitizing comparison operators like <, >, <=, >= and <>.
	        // For conditions defined in the "simple" mode, this is easy to revert because they're unfolded
	        // into tokens from the very beginning and we can handle just the operators.
	        //
	        // For custom conditions, however, this is more difficult to do while keeping the effect of sanitization and
	        // not breaking the expression. That is why we're going to use the Toolset_Tokenizer to break down the
	        // expression, replace problematic operators by their text-only equivalents and glue the expression back
	        // together. Then we can pass it through sanitize_text_field without further issues.
	        //
	        // Note that this method has side-effects and limitations, see transform_operators_to_text_equivalents()
	        // for details.
	        $conditions['custom'] = $this->transform_operators_to_text_equivalents(
		        mncf_getarr( $conditions, 'custom' )
	        );

	        $sanitized_conditions = sanitize_text_field_recursively( $conditions );

	        // restore condition operators (lost through sanitize_text_field_recursively())
            $sanitized_conditions['conditions'] =
                $this->mncf_conditions_restore_original_operators(
                    $sanitized_conditions['conditions'],
                    $conditions['conditions']
                );

            // Tokanizer ($this->transform_operators_to_text_equivalents)
            // transforms slugs like "my-field-slug" to "my - field - slug"
            // following will restore the slugs
            if( isset( $sanitized_conditions['custom'] ) && ! empty( $sanitized_conditions['custom'] ) ) {
                $sanitized_conditions['custom'] = preg_replace_callback(
                    '#\$\(([A-z0-9]*\s[\-]\s[A-z0-9]*)*\)#',
                    array( $this, 'callback_restore_slug_format' ),
                    $sanitized_conditions['custom']
                );
            }


	        // Store the sanitized conditions.
	        $field['data']['conditional_display'] = $sanitized_conditions;

            $all_types_fields = get_option( 'mncf-fields', array() );
            $all_types_fields[$id] = $field;
            update_option('mncf-fields', $all_types_fields);
        }

    }


    /**
     * Remove all conditions.
     *
     * @param $id
     */
    public function remove_conditions( $id, $group_conditions = false )
    {
        if( $group_conditions ) {
            delete_post_meta( $id, '_mncf_conditional_display' );
        } else {
            $field = mncf_fields_get_field_by_slug($id);
            if ( empty( $field ) ) {
                return;
            }

            $all_types_fields = get_option( 'mncf-fields', array() );
            $all_types_fields[$id]['data']['conditional_display'] = array();
            update_option('mncf-fields', $all_types_fields);
        }
    }

    
    /**
     * Summary.
     *
     * Description.
     *
     * @since x.x.x
     * @access (for functions: only use if private)
     *
     * @see Function/method/class relied on
     * @link URL
     * @global type $varname Description.
     * @global type $varname Description.
     *
     * @param type $var Description.
     * @param type $var Optional. Description.
     * @return type Description.
     */
    public function field_condition_get()
    {
        /**
         * check nonce
         */
        if (
            0
            || !isset($_REQUEST['_mnnonce'])
            || !isset($_REQUEST['id'])
            || !mn_verify_nonce($_REQUEST['_mnnonce'], 'mncf-conditional-get-'.$_REQUEST['id'])
        ) {
            $this->verification_failed_and_die();
        }
        /**
         * get field definition
         */
        require_once MNCF_EMBEDDED_INC_ABSPATH . '/fields.php';
        $field = mncf_admin_fields_get_field( sanitize_text_field( $_REQUEST['id'] ), false, true );
        if ( empty( $field ) ) {
            __('Wrong field.', 'mncf');
            die;
        }
        /**
         * define conditional
         */
        require_once MNCF_INC_ABSPATH.'/classes/class.types.fields.conditional.php';
        new Types_Fields_Conditional();
        /**
         * get form data
         */
        /**
         * Summary.
         *
         * Description.
         *
         * @since x.x.x
         *
         * @param type  $var Description.
         * @param array $args {
         *     Short description about this hash.
         *
         *     @type type $var Description.
         *     @type type $var Description.
         * }
         * @param type  $var Description.
         */
        $form = $this->get_field_conditionals(array(), $field);
        if ( empty($form) ) {
            __('Wrong field.', 'mncf');
            die;
        }
        /**
         * produce form
         */
        echo mncf_form_simple($form);
        die;
    }

    /**
     * Summary.
     *
     * Description.
     *
     * @since x.x.x
     * @access (for functions: only use if private)
     *
     * @see Function/method/class relied on
     * @link URL
     * @global type $varname Description.
     * @global type $varname Description.
     *
     * @param boolean $return_form Description.
     * @return type Description.
     */
    public function group_condition_get( $return_form = false )
    {

        if( !isset( $_REQUEST['group_id'] ) && isset( $_REQUEST['id'] ) )
            $_REQUEST['group_id'] = $_REQUEST['id'];

        /**
         * check nonce
         */
        if (
            ! ( // called through custom fields group edit -> no additional nonce verify needed
                isset( $_REQUEST['id'] )
                && $return_form === true
            )
            && ! (
                isset($_REQUEST['group_id'])
                && isset($_REQUEST['_mnnonce'])
                && mn_verify_nonce($_REQUEST['_mnnonce'], 'mncf-conditional-get-'.$_REQUEST['group_id'] )
            )
        ) {
            $this->verification_failed_and_die();
        }
        /**
         * get group definition
         */
        $group = mncf_admin_fields_get_group($_REQUEST['group_id']);
        if ( empty( $group ) ) {
            __( 'Wrong group.', 'mncf' );
            die;
        }
        $group['meta_type'] = 'custom-fields-group';
        $group['data'] = array(
            'conditional_display' => get_post_meta( $group['id'], '_mncf_conditional_display', true ),
        );
        /**
         * define conditional
         */
        require_once MNCF_INC_ABSPATH.'/classes/class.types.fields.conditional.php';
        new Types_Fields_Conditional();
        /**
         * get form data
         */
        /**
         * Summary.
         *
         * Description.
         *
         * @since x.x.x
         *
         * @param type  $var Description.
         * @param array $args {
         *     Short description about this hash.
         *
         *     @type type $var Description.
         *     @type type $var Description.
         * }
         * @param type  $var Description.
         */
        $form = $this->get_field_conditionals(array(), $group);
        if ( empty($form) ) {
            __('Wrong group.', 'mncf');
            die;
        }

        /**
         * return form
         */
        if( $return_form )
            return $form;

        /**
         * produce form
         */
        echo mncf_form_simple($form);
        die;
    }

    /**
     * @param $sanitized_conditions
     * @param $original_conditions
     *
     * @return array
     */
    protected function mncf_conditions_restore_original_operators( $sanitized_conditions, $original_conditions ) {
        // we have to restore the operator as it get sanitized by sanitize_text_field_recursively()
        foreach( $sanitized_conditions as $key => $condition ) {
            if(
                isset( $sanitized_conditions[ $key ]['operation'] )
                && isset( $original_conditions[ $key ]['operation'] )
            ) {
                $original_conditions[ $key ]['operation'] = trim( $original_conditions[ $key ]['operation'] );

                if( preg_match( '#^([\<\>\=\!]){1,3}$#', $original_conditions[ $key ]['operation'] ) )
                    $sanitized_conditions[ $key ]['operation'] = $original_conditions[ $key ]['operation'];
            }
        }

        return $sanitized_conditions;
    }


	/**
	 * Uses the Toolset_Tokenizer to break down the expression, replace problematic operators by their text-only
	 * equivalents and glue the expression back together.
	 *
	 * Side-effects: Loses custom whitespace characters. All operators (except parentheses) will be surrounded by spaces
	 * while everywhere else the whitespace characters will be trimmed.
	 *
	 * Note: If an invalid expression is provided, it doesn't do anything with it.
	 *
	 * @param string $expression Condition expression.
	 * @return string Equivalent expression but without <, <=, etc.
	 * @since 2.0
	 */
	protected function transform_operators_to_text_equivalents( $expression ) {

		try {

			// The expression may come directly from parse_str() which may add backslashes to quotes. The tokenizer
			// wouldn't survive that.
			$expression = stripslashes( $expression );

			$toolset_bootstrap = Toolset_Common_Bootstrap::getInstance();
			$toolset_bootstrap->register_parser();

			$tokenizer = new Toolset_Tokenizer();
			$tokens = $tokenizer->Tokanize( $expression );

			$token_value_replacements = array(
				'<' => 'lt',
				'>' => 'gt',
				'<=' => 'lte',
				'>=' => 'gte',
				'<>' => 'ne',
				'=' => 'eq'
			);

			$result = '';

			foreach ( $tokens as $token ) {
				if ( $token->isCompOp ) {
					$token->val = mncf_getarr( $token_value_replacements, $token->val, $token->val );
				}
				if ( $token->isCompOp || $token->isArithmeticOp || $token->isLogicOp ) {
					$result .= ' ' . $token->val . ' ';
				} else if( $token->isStringLiteral ) {
					$result .= '\'' . $token->val . '\'';
				} else {
					$result .= $token->val;
				}
			}

			return $result;

		} catch( Exception $e ) {
			// Most probably we were unable to tokenize the expression. We give up.
			return $expression;
		}
	}

    /**
     * Used to restore field slugs in custom logic which are ruined by Toolset_Tokenizer
     * which transforms "my-field-slug" to "my - field - slug".
     *
     * Special thank to Mtaandao for not supporting anonymous functions. (>= PHP 5.3)
     * 
     * @param $condition
     * @return string
     * @since 2.1
     */
    protected function callback_restore_slug_format( $condition ) {
        $restored_slug = str_replace( ' - ', '-', $condition[0] );
        return $restored_slug;
    }
}
