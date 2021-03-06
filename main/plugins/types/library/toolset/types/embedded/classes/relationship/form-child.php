<?php
/*
 * Relationship form class.
 *
 * Used to render child forms
 */
require_once MNCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';

/**
 * Relationship form class.
 *
 * Used on post edit page to show children rows
 */
class MNCF_Relationship_Child_Form
{

    /**
     * Current post.
     *
     * @var type object
     */
    var $post;

    /**
     * Field object.
     *
     * @var type array
     */
    var $cf = array();

    /**
     * Saved data.
     *
     * @var type array
     */
    var $data = array();

    /**
     * Child post object.
     *
     * @var type
     */
    var $child_post_type_object;
    var $parent;
    var $parent_post_type;
    var $child_post_type;
    var $model;
    var $children;
    var $headers = array();
    var $_dummy_post = false;
    private $__params = array('page', '_mncf_relationship_items_per_page', 'sort', 'field');
    private $__urlParams = array();

    /**
     * post type configuration
     */
    private $child_supports = array(
        'title' => false,
        'editor' => false,
        'comments' => false,
        'trackbacks' => false,
        'revisions' => false,
        'author' => false,
        'excerpt' => false,
        'thumbnail' => false,
        'custom-fields' => false,
        'page-attributes' => false,
        'post-formats' => false,
    );

    /**
     * Construct function.
     */
    function __construct( $parent_post, $child_post_type, $data ) {
        MNCF_Loader::loadModel( 'relationship' );
        $this->parent = $parent_post;
        $this->parent_post_type = $parent_post->post_type;
        $this->child_post_type = $child_post_type;
        $this->data = $data;
// Clean data
        if ( empty( $this->data['fields_setting'] ) ) {
            $this->data['fields_setting'] = 'all_cf';
        }
        $this->cf = new MNCF_Field();
        $this->cf->context = 'relationship';
        $this->children = MNCF_Relationship_Model::getChildrenByPostType(
            $this->parent,
            $this->child_post_type,
            $this->data,
            $_GET
        );

        // If no children - use dummy post
        if ( empty( $this->children ) ) {
            $_dummy_post = get_default_post_to_edit( $this->child_post_type, false );
            $this->children = array($_dummy_post);
            $this->_dummy_post = true;
        }
        $this->child_post_type_object = get_post_type_object( $this->child_post_type );
        if (
            !isset($this->child_post_type_object->slug)
            && isset($this->child_post_type_object->name)
        ) {
            $this->child_post_type_object->slug = $this->child_post_type_object->name;
        }

        // Collect params from request
        foreach ( $this->__params as $__param ) {
            if ( isset( $_GET[$__param] ) ) {
                $this->__urlParams[$__param] = $_GET[$__param];
            }
        }
        /**
         * build-in types
         */
        if ( in_array($child_post_type, array('page', 'post', 'attachment', 'revision', 'nav_menu_item') ) ) {
            foreach( array_keys($this->child_supports) as $key ) {
                $this->child_supports[$key] = post_type_supports($child_post_type, $key);
            }
            return;
        }
        /**
         * post types managed by Types
         */
        $post_types = get_option( MNCF_OPTION_NAME_CUSTOM_TYPES, array() );
        if (
            array_key_exists($child_post_type, $post_types )
            && array_key_exists('supports', $post_types[$child_post_type] )
        ) {
            foreach(  $post_types[$child_post_type]['supports'] as $key => $value ) {
                $this->child_supports[$key] = (boolean)$value;
            }

        /**
         * all other post types
         */
        } else {
            foreach( array_keys($this->child_supports) as $key ) {
                $this->child_supports[$key] = post_type_supports($child_post_type, $key);
            }
        }
        unset($post_types);
        /**
         * mn_enqueue_media allways
         */
        add_action('admin_enqueue_scripts', array($this, 'mn_enqueue_media'), PHP_INT_MAX);
    }

    public function mn_enqueue_media()
    {
        global $post;
        mn_enqueue_media(array('post' => $post->ID));
    }

    function getParamsQuery() {
        return count( $this->__urlParams ) ? '&amp;' . http_build_query( $this->__urlParams, '', '&amp;' ) : '';
    }

    /**
     * Sets form.
     *
     * @param type $o
     */
    function _set( $child ) {
        $this->child = $child;
    }

    /**
     * Returns HTML formatted form.
     *
     * Renders children per row.
     *
     * @todo move all here
     *
     * @return type string (HTML formatted)
     */
    function render() {
        static $count = false;
        if ( !$count ) {
            $count = 1;
        }

        /*
         * Pagination will slice children
         */
        $this->pagination();
        $rows = $this->rows();
        $headers = $this->headers();

        // Capture template output
        ob_start();
        include MNCF_EMBEDDED_INC_ABSPATH . '/relationship/child-table.php';
        $table = ob_get_contents();
        ob_end_clean();

        $count++;
        return $table;
    }

    /**
     * Pagination
     */
    function pagination() {

        global $mncf;

        // Pagination
        $total_items = count( $this->children );
        $per_page = $mncf->relationship->get_items_per_page( $this->parent_post_type, $this->child_post_type );
        $page = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : 1;
        $offset = ( $page == 1 ) ? 0 : ( ( $page - 1 ) * $per_page );
        $next = ( $total_items > ( $offset + $per_page ) );
        $prev = ( $page == 1 ) ? false : true;
        if ( $total_items > $per_page ) {
            $this->children = array_splice( $this->children, $offset, $per_page );
        }

        $this->pagination_top = mncf_pr_admin_has_pagination( $this->parent, $this->child_post_type, $page, $prev, $next, $per_page, $total_items );
        /*
         *
         *
         * Add pagination bottom
         */
        $options = array(__( 'All', 'mncf' ) => 'all', 5 => 5, 10 => 10, 15 => 15);
// Add sorting
        $add_data = isset( $_GET['sort'] ) && isset( $_GET['field'] ) ? '&sort=' . sanitize_text_field( $_GET['sort'] ) . '&field='
                . sanitize_text_field( $_GET['field'] ) : '';
        if ( isset( $_GET['post_type_sort_parent'] ) ) {
            $add_data .= '&post_type_sort_parent=' . sanitize_text_field( $_GET['post_type_sort_parent'] );
        }
        $this->pagination_bottom = mncf_form_simple( array(
            'pagination' => array(
                '#type' => 'select',
                '#before' => __( 'Show', 'mncf' ),
                '#after' => $this->child_post_type_object->labels->name,
                '#id' => 'mncf_relationship_num_' . mncf_unique_id( serialize( $this->children ) ),
                '#name' => $mncf->relationship->items_per_page_option_name,
                '#options' => $options,
                '#default_value' => $per_page,
                '#attributes' => array(
                    'class' => 'mncf-relationship-items-per-page',
                    'data-action' => 'action=mncf_ajax&mncf_action=pr_pagination'
                    . '&post_id=' . $this->parent->ID . '&post_type='
                    . $this->child_post_type
                    . '&_mnnonce=' . mn_create_nonce( 'pr_pagination' ) . $add_data,
                ),
            ),
                ) );
    }

    /**
     * Returns rows.
     *
     * @return type
     */
    function rows() {
        $rows = array();
        foreach ( $this->children as $child ) {
            $this->_set( $child );
            $rows[$child->ID] = $this->row();
        }
        return $rows;
    }

    /**
     * Returns HTML formatted row
     *
     * While generating rows we collect headers too.
     *
     * @return type
     */
    function row() {
        /*
         * Start output.
         * Output is returned as array - each element is <td> content.
         */
        $row = array();

        /*
         * LOOP over fields
         * Custom settings (specific)
         */
        if ( $this->data['fields_setting'] == 'specific' && !empty( $this->data['fields'] ) ) {
            // Set title
            if (
                isset( $this->data['fields']['_mn_title'] ) 
                && isset( $this->child_post_type_object->slug)
                && post_type_supports( $this->child_post_type_object->slug, 'title')
            ) {
                $this->headers[] = '_mn_title';
                $row[] = $this->title();
            }
            // Set body
            if (
                isset( $this->data['fields']['_mn_body'] )
                && post_type_supports( $this->child_post_type_object->slug, 'editor')
            ) {
                $this->headers[] = '_mn_body';
                $row[] = $this->body();
            }
            // Set excerpt
            if (
                isset( $this->data['fields']['_mn_excerpt'] )
                && post_type_supports( $this->child_post_type_object->slug, 'excerpt' )
            ) {
                $this->headers[] = '_mn_excerpt';
                $row[] = $this->excerpt();
            }
            // Set thumbnail
            if (
                isset( $this->data['fields']['_mn_featured_image'] )
                && post_type_supports( $this->child_post_type_object->slug, 'thumbnail' )
            ) {
                $this->headers[] = '_mn_featured_image';
                $row[] = $this->thumbnail();
            }

            /**
             * get allowed fields for this post type
             */
            $allowed_fields = array();
            if ( isset( $this->child_post_type_object->slug) ) {
                $allowed_fields = mncf_admin_get_allowed_fields_by_post_type($this->child_post_type_object->slug);
            }

            // Loop over Types fields
            foreach ( $this->data['fields'] as $field_key => $true ) {
                // Skip parents
                if ( in_array( $field_key,
                    array(
                        '_mn_title',
                        '_mn_body',
                        '_mn_excerpt',
                        '_mncf_pr_parents',
                        '_mncf_pr_taxonomies',
                    ) ) ) 
                {
                    continue;
                }
                /**
                 * check field
                 */
                if ( !in_array($field_key, $allowed_fields) ) {
                    continue;
                }

                /*
                 * Set field
                 */
                $this->cf->set( $this->child, $field_key );
                $row[] = $this->field_form();
                $this->_field_triggers();
                // Add to header
                $this->headers[] = $field_key;
            }
            // Add parent forms
            if ( !empty( $this->data['fields']['_mncf_pr_parents'] ) ) {
                $_temp = (array) $this->data['fields']['_mncf_pr_parents'];
                foreach ( $_temp as $_parent => $_true ) {
                    $row[] = $this->_parent_form( $_parent );
                    // Add to header
                    $this->headers['__parents'][$_parent] = $_true;
                }
            }
            // Add taxonomies forms
            if ( !empty( $this->data['fields']['_mncf_pr_taxonomies'] ) ) {
                $_temp = (array) $this->data['fields']['_mncf_pr_taxonomies'];
                foreach ( $_temp as $taxonomy => $_true ) {
                    $_taxonomy = get_taxonomy($taxonomy);
                    if ( !empty( $_taxonomy ) ) {
                        $row[] = $this->taxonomy_form( $_taxonomy );
                        // Add to header
                        $this->headers['__taxonomies'][$taxonomy] = $_taxonomy->label;
                    }
                }
            }
            /*
             *
             *
             *
             *
             * DEFAULT SETTINGS
             */
        } else {
            // Set title
            $row[] = $this->title();
            $this->headers[] = '_mn_title';

            // Set body if needed
            if ( $this->data['fields_setting'] == 'all_cf_standard' ) {
                $this->headers[] = '_mn_body';
                $row[] = $this->body();
                $this->headers[] = '_mn_excerpt';
                $row[] = $this->excerpt();
            }
            /*
             * Loop over groups and fields
             */
            // Get groups
            $groups = mncf_admin_post_get_post_groups_fields( $this->child, 'post_relationships' );
            foreach ( $groups as $group ) {
                if ( empty( $group['fields'] ) ) {
                    continue;
                }
                /*
                 * Loop fields
                 */
                foreach ( $group['fields'] as $field_key => $field ) {
                    /*
                     * Set field
                     */
                    $field_key = $this->cf->__get_slug_no_prefix( $field_key );
                    $this->cf->set( $this->child, $field_key );
                    $row[] = $this->field_form();
                    $this->_field_triggers();
                    // Add to header{
                    $this->headers[] = MNCF_META_PREFIX . $field_key;
                }
            }

            // Add parent forms
            if ( $this->data['fields_setting'] == 'all_cf' ) {
                $this->data['fields']['_mncf_pr_parents'] = mncf_pr_admin_get_belongs( $this->child_post_type );
                if ( !empty( $this->data['fields']['_mncf_pr_parents'] ) ) {
                    $_temp = (array) $this->data['fields']['_mncf_pr_parents'];
                    foreach ( $_temp as $_parent => $_true ) {
                        if ( $_parent == $this->parent_post_type ) {
                            continue;
                        }
                        $row[] = $this->_parent_form( $_parent );
                        // Add to header
                        $this->headers['__parents'][$_parent] = $_true;
                    }
                }
            }
        }
        return $row;
    }

    /**
     * Add here various triggers for field
     */
    function _field_triggers() {
        /*
         * Check if repetitive - add warning
         */
        if ( mncf_admin_is_repetitive( $this->cf->cf ) ) {
            $this->repetitive_warning = true;
        }
        /*
         * Check if date - trigger it
         * TODO Move to date
         */
        if ( $this->cf->cf['type'] == 'date' ) {
            $this->trigger_date = true;
        }
    }

    /**
     * Returns HTML formatted title field.
     *
     * @param type $post
     * @return type
     */
    function title()
    {
        $title = '';
        $type = 'textfield';
        if ( !$this->child_supports['title']) {
            $type = 'hidden';
            $title .= mncf_form_simple(
                array(
                    'field' => array(
                        '#type' => 'markup',
                        '#markup' => sprintf('%s id: %d', $this->child_post_type_object->labels->singular_name, $this->child->ID),
                    ),
                )
            );
        }
        $title .= mncf_form_simple(
            array(
                'field' => array(
                    '#type' =>  $type,
                    '#id' => 'mncf_post_relationship_'
                    . $this->child->ID . '_mn_title',
                    '#name' => 'mncf_post_relationship['
                    . $this->parent->ID . ']['
                    . $this->child->ID . '][_mn_title]',
                    '#value' => trim( $this->child->post_title ),
                    '#inline' => true,
                ),
            )
        );
        return $title;
    }

    /**
     * Returns HTML formatted body field.
     *
     * @return type
     */
    function body() {
        return mncf_form_simple(
                        array('field' => array(
                                '#type' => 'textarea',
                                '#id' => 'mncf_post_relationship_'
                                . $this->child->ID . '_mn_body',
                                '#name' => 'mncf_post_relationship['
                                . $this->parent->ID . ']['
                                . $this->child->ID . '][_mn_body]',
                                '#value' => $this->child->post_content,
                                '#attributes' => array('style' => 'width:300px;height:100px;'),
                                '#inline' => true,
                            )
                        )
        );
    }

    /**
     * Returns HTML formatted excerpt field.
     *
     * @return type
     */
    function excerpt() {
        return mncf_form_simple(
            array('field' => array(
                '#type' => 'textarea',
                '#id' => 'mncf_post_relationship_'
                . $this->child->ID . '_mn_excerpt',
                '#name' => 'mncf_post_relationship['
                . $this->parent->ID . ']['
                . $this->child->ID . '][_mn_excerpt]',
                '#value' => $this->child->post_excerpt,
                '#attributes' => array('style' => 'width:300px;height:100px;'),
                '#inline' => true,
            )
        )
    );
    }

    /**
     * Returns HTML formatted post thumbnail field.
     *
     * @return type
     */
    function thumbnail()
    {
        return mncf_form_simple(
            array('field' => array(
                '#type' => 'thumbnail',
                '#id' => 'mncf_post_relationship_'
                . $this->child->ID . '_mn_featured_image',
                '#name' => 'mncf_post_relationship['
                . $this->parent->ID . ']['
                . $this->child->ID . '][_mn_featured_image]',
                '#value' => get_post_thumbnail_id($this->child->ID),
            )
        )
    );
    }
    /**
     * Returns HTML formatted Taxonomy form.
     *
     * @param type $taxonomy
     * @return type
     */
    function taxonomy_form( $taxonomy, $simple = false ) {
        // SIMPLIFIED VERSION
        if ( $simple ) {
            $terms = mn_get_post_terms( $this->child->ID, $taxonomy->name, array() );
            $selected = ( !empty( $terms ) ) ? array_shift($terms)->term_id : -1;
            $output =  mn_dropdown_categories( array(
                'taxonomy' => $taxonomy->name,
                'selected' => $selected,
                'echo' => false,
                'hide_empty' => false,
                'hide_if_empty' => true,
                'show_option_none' => sprintf( __( 'No %s', 'mncf' ),
                        $taxonomy->name ),
                'name' => 'mncf_post_relationship['
                . $this->parent->ID . '][' . $this->child->ID
                . '][taxonomies][' . $taxonomy->name . ']',
                'id' => 'mncf_pr_' . $this->child->ID . '_' . $taxonomy->name,
                'hierarchical' => true,
                'depth' => 9999
                    )
            );

            return empty( $output ) ? sprintf( __( 'No %s', 'mncf' ),
                    $taxonomy->label ) : $output;
        }

        $data = array(
            'post' => $this->child,
            'taxonomy' => $taxonomy->name,
        );
        if ( $taxonomy->name == 'category' ) {
            $data['_mncf_name'] = "mncf_post_relationship[{$this->parent->ID}][{$this->child->ID}][taxonomies][{$taxonomy->name}][]";
            $output = MNCF_Loader::template('child-tax-category', $data);
            // Reduce JS processing
            return str_replace( "name=\"post_category[]",
                    "name=\"{$data['_mncf_name']}", $output );
        }
        if ( $taxonomy->hierarchical ) {
            $data['_mncf_name'] = "mncf_post_relationship[{$this->parent->ID}][{$this->child->ID}][taxonomies][{$taxonomy->name}][]";
            $output = MNCF_Loader::template('child-tax-category', $data);
            // Reduce JS processing
            return str_replace( "name=\"tax_input[{$taxonomy->name}][]",
                    "name=\"{$data['_mncf_name']}", $output );
        }
        $data['_mncf_name'] = "mncf_post_relationship[{$this->parent->ID}][{$this->child->ID}][taxonomies][{$taxonomy->name}]";
        $output = MNCF_Loader::template('child-tax-tag', $data);
        // Reduce JS processing
        return str_replace( "name=\"tax_input[{$taxonomy->name}]",
                "name=\"{$data['_mncf_name']}", $output );
    }

    /**
     * Returns element form as array.
     *
     * This is done per field.
     *
     * @param type $key Field key as stored
     * @return array
     */
    function field_form() {
        if ( defined( 'MNTOOLSET_FORMS_VERSION' ) ) {
            $field = $this->cf->cf;
            $meta = get_post_meta( $this->child->ID, $field['meta_key'] );
            $field['suffix'] = "-{$this->child->ID}";
            $config = mntoolset_form_filter_types_field( $field, $this->child->ID );
            // Do not allow repetitive
            $config['repetitive'] = false;
            $config['name'] = $this->cf->alter_form_name( 'mncf_post_relationship['
                    . $this->parent->ID . ']', $config['name'] );
            if ( !empty( $config['options'] ) ) {
                foreach ( $config['options'] as &$v ) {
                    if ( isset( $v['name'] ) ) {
                        $v['name'] = $this->alter_form_name( $v['name'] );
                    }
                }
            }
            if ( $config['type'] == 'wysiwyg' ) {
                $config['type'] = 'textarea';
            }
            return mntoolset_form_field( 'post', $config, $meta );
        }
        /*
         *
         * Get meta form for field
         */
        $form = $this->cf->_get_meta_form( $this->cf->__meta,
                $this->cf->meta_object->meta_id, false );
        /*
         *
         * Filter form
         */
        $_filtered_form = $this->__filter_meta_form( $form );

        return mncf_form_simple( apply_filters( 'mncf_relationship_child_meta_form',
                                $_filtered_form, $this->cf ) );
    }

    /**
     * Filters meta form.
     *
     * IMPORTANT: This is place where look of child form is altered.
     * Try not to spread it over other code.
     *
     * @param string $form
     * @return string
     */
    function __filter_meta_form( $form = array() ) {
        foreach ( $form as $k => &$e ) {
            /*
             *
             * Filter name
             */
            if ( isset( $e['#name'] ) ) {
                $e['#name'] = $this->cf->alter_form_name( 'mncf_post_relationship['
                        . $this->parent->ID . ']', $e['#name'] );
            }
            /*
             * Some fields have #options and names set there.
             * Loop over them and adjust.
             */
            if ( !empty( $e['#options'] ) ) {
                foreach ( $e['#options'] as $_k => $_v ) {
                    if ( isset( $_v['#name'] ) ) {
                        $e['#options'][$_k]['#name'] = $this->alter_form_name( $_v['#name'] );
                    }
                }
            }
            if ( isset( $e['#title'] ) ) {
                unset( $e['#title'] );
            }
            if ( isset( $e['#description'] ) ) {
                unset( $e['#description'] );
            }
            $e['#inline'] = true;
        }

        return $form;
    }

    function alter_form_name( $name, $parent_id = null ){
        if ( is_null( $parent_id ) ) {
            $parent_id = $this->parent->ID;
        }
        return $this->cf->alter_form_name(
                        'mncf_post_relationship[' . $parent_id . ']', $name
        );
    }

    /**
     * Content for choose parent column.
     *
     * @return boolean
     */
    function _parent_form( $post_parent = '' ) {
        $item_parents = mncf_pr_admin_get_belongs( $this->child_post_type );
        if ( $item_parents ) {
            foreach ( $item_parents as $parent => $temp_data ) {

                // Skip if only current available
                if ( $parent == $this->parent_post_type ) {
                    continue;
                }

                if ( !empty( $post_parent ) && $parent != $post_parent ) {
                    continue;
                }

                // Get parent ID
                $meta = get_post_meta( $this->child->ID,
                        '_mncf_belongs_' . $parent . '_id', true );
                $meta = empty( $meta ) ? 0 : $meta;

                // Get form
                $belongs_data = array('belongs' => array($parent => $meta));
                $temp_form = mncf_pr_admin_post_meta_box_belongs_form( $this->child,
                        $parent, $belongs_data );

                if ( empty( $temp_form ) ) {
                    return '<span class="types-small-italic">' . __( 'No parents available', 'mncf' ) . '</span>';
                }
                unset(
                        $temp_form[$parent]['#suffix'],
                        $temp_form[$parent]['#prefix'],
                        $temp_form[$parent]['#title']
                );
                $temp_form[$parent]['#name'] = 'mncf_post_relationship['
                        . $this->parent->ID . '][' . $this->child->ID
                        . '][parents][' . $parent . ']';
                // Return HTML formatted output
                return mncf_form_simple( $temp_form );
            }
        }
        return '<span class="types-small-italic">' . __( 'No parents available', 'mncf' ) . '</span>';
    }

    /**
     * HTML formatted row.
     *
     * @return type
     */
    function child_row( $child ) {
        $child_id = $child->ID;
        $this->_set( $child );
        $row = $this->row();
        ob_start();
        include MNCF_EMBEDDED_INC_ABSPATH . '/relationship/child-table-row.php';
        $o = ob_get_contents();
        ob_end_clean();
        return $o;
    }

    /**
     * Header HTML formatted output.
     *
     * Each header <th> is array element. Sortable.
     *
     * @return array 'header_id' => html
     */
    function headers() {

        // Sorting
        $dir = isset( $_GET['sort'] ) && $_GET['sort'] == 'ASC' ? 'DESC' : 'ASC';
        $dir_default = 'ASC';
        $sort_field = isset( $_GET['field'] ) ? sanitize_text_field( $_GET['field'] ) : '';

        // Set values
        $post = $this->parent;
        $post_type = $this->child_post_type;
        $parent_post_type = $this->parent_post_type;
        $data = $this->data;

        $mncf_fields = mncf_admin_fields_get_fields( true );
        $headers = array();

        foreach ( $this->headers as $k => $header ) {
            if ( $k === '__parents' || $k === '__taxonomies' ) {
                continue;
            }

            if ( $header == '_mn_title' ) {
                if ( $this->child_supports['title']) {
                    $title_dir = $sort_field == '_mn_title' ? $dir : 'ASC';
                    $headers[$header] = '';
                    $headers[$header] .= $sort_field == '_mn_title' ? '<div class="mncf-pr-sort-' . $dir . '"></div>' : '';
                    $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=mncf_ajax&amp;mncf_action=pr_sort&amp;field='
                        . '_mn_title&amp;sort=' . $title_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                        . $post_type . '&amp;_mnnonce='
                        . mn_create_nonce( 'pr_sort' ) ) . '">' . __( 'Post Title', 'mncf' ) . '</a>';
                } else {
                    $headers[$header] = 'ID';
                }
            } else if ( $header == '_mn_body' ) {
                $body_dir = $sort_field == '_mn_body' ? $dir : $dir_default;
                $headers[$header] = '';
                $headers[$header] .= $sort_field == '_mn_body' ? '<div class="mncf-pr-sort-' . $dir . '"></div>' : '';
                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=mncf_ajax&amp;mncf_action=pr_sort&amp;field='
                                . '_mn_body&amp;sort=' . $body_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                                . $post_type . '&amp;_mnnonce='
                                . mn_create_nonce( 'pr_sort' ) ) . '">' . __( 'Post Body', 'mncf' ) . '</a>';
            } else if (
                $header == '_mn_excerpt'
                || $header == '_mn_featured_image'
            ) {
                $headers[$header] = $this->get_header($header);
            } else {
                $link_text = $this->get_header($header);
                if (
                    strpos( $header, MNCF_META_PREFIX ) === 0
                    && isset( $mncf_fields[str_replace( MNCF_META_PREFIX, '', $header )] )
                ) {
                    mncf_field_enqueue_scripts( $mncf_fields[str_replace( MNCF_META_PREFIX, '', $header )]['type'] );
                    $link_text = stripslashes( $mncf_fields[str_replace( MNCF_META_PREFIX, '', $header )]['name'] );
                }
                $field_dir = $sort_field == $header ? $dir : $dir_default;
                $headers[$header] = '';
                $headers[$header] .= $sort_field == $header ? '<div class="mncf-pr-sort-' . $dir . '"></div>' : '';
                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=mncf_ajax&amp;mncf_action=pr_sort&amp;field=' . $header . '&amp;sort=' . $field_dir . '&amp;post_id=' . $post->ID . '&amp;post_type=' . $post_type . '&amp;_mnnonce=' . mn_create_nonce( 'pr_sort' ) ) . '">' . $link_text . '</a>';
            }
        }
        if ( !empty( $this->headers['__parents'] ) ) {
            foreach ( $this->headers['__parents'] as $_parent => $data ) {
                if ( $_parent == $parent_post_type ) {
                    continue;
                }
                $temp_parent_type = get_post_type_object( $_parent );
                if ( null == $temp_parent_type ) {
                    continue;
                }
                $parent_dir = $sort_field == '_mncf_pr_parent' ? $dir : $dir_default;
                $headers['_mncf_pr_parent_' . $_parent] = $sort_field == '_mncf_pr_parent' ? '<div class="mncf-pr-sort-' . $dir . '"></div>' : '';
                $headers['_mncf_pr_parent_' . $_parent] .= '<a href="' . admin_url( 'admin-ajax.php?action=mncf_ajax&amp;mncf_action=pr_sort&amp;field='
                                . '_mncf_pr_parent&amp;sort='
                                . $parent_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                                . $post_type . '&amp;post_type_sort_parent='
                                . $_parent . '&amp;_mnnonce='
                                . mn_create_nonce( 'pr_sort' ) ) . '">' . $temp_parent_type->label . '</a>';
            }
        }
        if ( !empty( $this->headers['__taxonomies'] ) ) {
            foreach ( $this->headers['__taxonomies'] as $tax_id => $taxonomy ) {
                $headers["_mncf_pr_taxonomy_$tax_id"] = $taxonomy;
            }
        }
        return $headers;
    }

    public function get_header($header)
    {
        switch( $header) {
        case '_mn_featured_image':
            $header = __('Feature Image', 'mncf');
            break;
        case '_mn_excerpt':
            $header = __('Post excerpt', 'mncf');
            break;
        }
        return stripslashes($header);
    }

}
