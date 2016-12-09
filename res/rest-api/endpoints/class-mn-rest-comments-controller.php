<?php
/**
 * REST API: MN_REST_Comments_Controller class
 *
 * @package Mtaandao
 * @subpackage REST_API
 * @since 4.7.0
 */

/**
 * Core controller used to access comments via the REST API.
 *
 * @since 4.7.0
 *
 * @see MN_REST_Controller
 */
class MN_REST_Comments_Controller extends MN_REST_Controller {

	/**
	 * Instance of a comment meta fields object.
	 *
	 * @since 4.7.0
	 * @access protected
	 * @var MN_REST_Comment_Meta_Fields
	 */
	protected $meta;

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function __construct() {
		$this->namespace = 'mn/v2';
		$this->rest_base = 'comments';

		$this->meta = new MN_REST_Comment_Meta_Fields();
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'   => MN_REST_Server::READABLE,
				'callback'  => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'      => $this->get_collection_params(),
			),
			array(
				'methods'  => MN_REST_Server::CREATABLE,
				'callback' => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'     => $this->get_endpoint_args_for_item_schema( MN_REST_Server::CREATABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'  => MN_REST_Server::READABLE,
				'callback' => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'     => array(
					'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
					'password' => array(
						'description' => __( 'The password for the post if it is password protected.' ),
						'type'        => 'string',
					),
				),
			),
			array(
				'methods'  => MN_REST_Server::EDITABLE,
				'callback' => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'     => $this->get_endpoint_args_for_item_schema( MN_REST_Server::EDITABLE ),
			),
			array(
				'methods'  => MN_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'     => array(
					'force'    => array(
						'type'        => 'boolean',
						'default'     => false,
						'description' => __( 'Whether to bypass trash and force deletion.' ),
					),
					'password' => array(
						'description' => __( 'The password for the post if it is password protected.' ),
						'type'        => 'string',
					),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Checks if a given request has access to read comments.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param MN_REST_Request $request Full details about the request.
	 * @return MN_Error|bool True if the request has read access, error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! empty( $request['post'] ) ) {
			foreach ( (array) $request['post'] as $post_id ) {
				$post = get_post( $post_id );

				if ( ! empty( $post_id ) && $post && ! $this->check_read_post_permission( $post, $request ) ) {
					return new MN_Error( 'rest_cannot_read_post', __( 'Sorry, you are not allowed to read the post for this comment.' ), array( 'status' => rest_authorization_required_code() ) );
				} elseif ( 0 === $post_id && ! current_user_can( 'moderate_comments' ) ) {
					return new MN_Error( 'rest_cannot_read', __( 'Sorry, you are not allowed to read comments without a post.' ), array( 'status' => rest_authorization_required_code() ) );
				}
			}
		}

		if ( ! empty( $request['context'] ) && 'edit' === $request['context'] && ! current_user_can( 'moderate_comments' ) ) {
			return new MN_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit comments.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			$protected_params = array( 'author', 'author_exclude', 'author_email', 'type', 'status' );
			$forbidden_params = array();

			foreach ( $protected_params as $param ) {
				if ( 'status' === $param ) {
					if ( 'approve' !== $request[ $param ] ) {
						$forbidden_params[] = $param;
					}
				} elseif ( 'type' === $param ) {
					if ( 'comment' !== $request[ $param ] ) {
						$forbidden_params[] = $param;
					}
				} elseif ( ! empty( $request[ $param ] ) ) {
					$forbidden_params[] = $param;
				}
			}

			if ( ! empty( $forbidden_params ) ) {
				return new MN_Error( 'rest_forbidden_param', sprintf( __( 'Query parameter not permitted: %s' ), implode( ', ', $forbidden_params ) ), array( 'status' => rest_authorization_required_code() ) );
			}
		}

		return true;
	}

	/**
	 * Retrieves a list of comment items.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param MN_REST_Request $request Full details about the request.
	 * @return MN_Error|MN_REST_Response Response object on success, or error object on failure.
	 */
	public function get_items( $request ) {

		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();

		/*
		 * This array defines mappings between public API query parameters whose
		 * values are accepted as-passed, and their internal MN_Query parameter
		 * name equivalents (some are the same). Only values which are also
		 * present in $registered will be set.
		 */
		$parameter_mappings = array(
			'author'         => 'author__in',
			'author_email'   => 'author_email',
			'author_exclude' => 'author__not_in',
			'exclude'        => 'comment__not_in',
			'include'        => 'comment__in',
			'offset'         => 'offset',
			'order'          => 'order',
			'parent'         => 'parent__in',
			'parent_exclude' => 'parent__not_in',
			'per_page'       => 'number',
			'post'           => 'post__in',
			'search'         => 'search',
			'status'         => 'status',
			'type'           => 'type',
		);

		$prepared_args = array();

		/*
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $prepared_args.
		 */
		foreach ( $parameter_mappings as $api_param => $mn_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$prepared_args[ $mn_param ] = $request[ $api_param ];
			}
		}

		// Ensure certain parameter values default to empty strings.
		foreach ( array( 'author_email', 'search' ) as $param ) {
			if ( ! isset( $prepared_args[ $param ] ) ) {
				$prepared_args[ $param ] = '';
			}
		}

		if ( isset( $registered['orderby'] ) ) {
			$prepared_args['orderby'] = $this->normalize_query_param( $request['orderby'] );
		}

		$prepared_args['no_found_rows'] = false;

		$prepared_args['date_query'] = array();

		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $registered['before'], $request['before'] ) ) {
			$prepared_args['date_query'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $registered['after'], $request['after'] ) ) {
			$prepared_args['date_query'][0]['after'] = $request['after'];
		}

		if ( isset( $registered['page'] ) && empty( $request['offset'] ) ) {
			$prepared_args['offset'] = $prepared_args['number'] * ( absint( $request['page'] ) - 1 );
		}

		/**
		 * Filters arguments, before passing to MN_Comment_Query, when querying comments via the REST API.
		 *
		 * @since 4.7.0
		 *
		 * @link https://developer.mtaandao.co.ke/reference/classes/mn_comment_query/
		 *
		 * @param array           $prepared_args Array of arguments for MN_Comment_Query.
		 * @param MN_REST_Request $request       The current request.
		 */
		$prepared_args = apply_filters( 'rest_comment_query', $prepared_args, $request );

		$query = new MN_Comment_Query;
		$query_result = $query->query( $prepared_args );

		$comments = array();

		foreach ( $query_result as $comment ) {
			if ( ! $this->check_read_permission( $comment, $request ) ) {
				continue;
			}

			$data = $this->prepare_item_for_response( $comment, $request );
			$comments[] = $this->prepare_response_for_collection( $data );
		}

		$total_comments = (int) $query->found_comments;
		$max_pages      = (int) $query->max_num_pages;

		if ( $total_comments < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $prepared_args['number'], $prepared_args['offset'] );

			$query = new MN_Comment_Query;
			$prepared_args['count'] = true;

			$total_comments = $query->query( $prepared_args );
			$max_pages = ceil( $total_comments / $request['per_page'] );
		}

		$response = rest_ensure_response( $comments );
		$response->header( 'X-MN-Total', $total_comments );
		$response->header( 'X-MN-TotalPages', $max_pages );

		$base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $request['page'] > 1 ) {
			$prev_page = $request['page'] - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}

		if ( $max_pages > $request['page'] ) {
			$next_page = $request['page'] + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Checks if a given request has access to read the comment.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param MN_REST_Request $request Full details about the request.
	 * @return MN_Error|bool True if the request has read access for the item, error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$id = (int) $request['id'];

		$comment = get_comment( $id );

		if ( ! $comment ) {
			return true;
		}

		if ( ! empty( $request['context'] ) && 'edit' === $request['context'] && ! current_user_can( 'moderate_comments' ) ) {
			return new MN_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit comments.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		$post = get_post( $comment->comment_post_ID );

		if ( ! $this->check_read_permission( $comment, $request ) ) {
			return new MN_Error( 'rest_cannot_read', __( 'Sorry, you are not allowed to read this comment.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( $post && ! $this->check_read_post_permission( $post, $request ) ) {
			return new MN_Error( 'rest_cannot_read_post', __( 'Sorry, you are not allowed to read the post for this comment.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieves a comment.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param MN_REST_Request $request Full details about the request.
	 * @return MN_Error|MN_REST_Response Response object on success, or error object on failure.
	 */
	public function get_item( $request ) {
		$id = (int) $request['id'];

		$comment = get_comment( $id );
		if ( empty( $comment ) ) {
			return new MN_Error( 'rest_comment_invalid_id', __( 'Invalid comment ID.' ), array( 'status' => 404 ) );
		}

		if ( ! empty( $comment->comment_post_ID ) ) {
			$post = get_post( $comment->comment_post_ID );
			if ( empty( $post ) ) {
				return new MN_Error( 'rest_post_invalid_id', __( 'Invalid post ID.' ), array( 'status' => 404 ) );
			}
		}

		$data = $this->prepare_item_for_response( $comment, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Checks if a given request has access to create a comment.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param MN_REST_Request $request Full details about the request.
	 * @return MN_Error|bool True if the request has access to create items, error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! is_user_logged_in() ) {
			if ( get_option( 'comment_registration' ) ) {
				return new MN_Error( 'rest_comment_login_required', __( 'Sorry, you must be logged in to comment.' ), array( 'status' => 401 ) );
			}

			/**
			 * Filter whether comments can be created without authentication.
			 *
			 * Enables creating comments for anonymous users.
			 *
			 * @since 4.7.0
			 *
			 * @param bool $allow_anonymous Whether to allow anonymous comments to
			 *                              be created. Default `false`.
			 * @param MN_REST_Request $request Request used to generate the
			 *                                 response.
			 */
			$allow_anonymous = apply_filters( 'rest_allow_anonymous_comments', false, $request );
			if ( false === $allow_anonymous ) {
				return new MN_Error( 'rest_comment_login_required', __( 'Sorry, you must be logged in to comment.' ), array( 'status' => 401 ) );
			}
		}

		// Limit who can set comment `author`, `author_ip` or `status` to anything other than the default.
		if ( isset( $request['author'] ) && get_current_user_id() !== $request['author'] && ! current_user_can( 'moderate_comments' ) ) {
			return new MN_Error( 'rest_comment_invalid_author',
				/* translators: %s: request parameter */
				sprintf( __( "Sorry, you are not allowed to edit '%s' for comments." ), 'author' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		if ( isset( $request['author_ip'] ) && ! current_user_can( 'moderate_comments' ) ) {
			if ( empty( $_SERVER['REMOTE_ADDR'] ) || $request['author_ip'] !== $_SERVER['REMOTE_ADDR'] ) {
				return new MN_Error( 'rest_comment_invalid_author_ip',
					/* translators: %s: request parameter */
					sprintf( __( "Sorry, you are not allowed to edit '%s' for comments." ), 'author_ip' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}
		}

		if ( isset( $request['status'] ) && ! current_user_can( 'moderate_comments' ) ) {
			return new MN_Error( 'rest_comment_invalid_status',
				/* translators: %s: request parameter */
				sprintf( __( "Sorry, you are not allowed to edit '%s' for comments." ), 'status' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		if ( empty( $request['post'] ) ) {
			return new MN_Error( 'rest_comment_invalid_post_id', __( 'Sorry, you are not allowed to create this comment without a post.' ), array( 'status' => 403 ) );
		}

		$post = get_post( (int) $request['post'] );
		if ( ! $post ) {
			return new MN_Error( 'rest_comment_invalid_post_id', __( 'Sorry, you are not allowed to create this comment without a post.' ), array( 'status' => 403 ) );
		}

		if ( 'draft' === $post->post_status ) {
			return new MN_Error( 'rest_comment_draft_post', __( 'Sorry, you are not allowed to create a comment on this post.' ), array( 'status' => 403 ) );
		}

		if ( 'trash' === $post->post_status ) {
			return new MN_Error( 'rest_comment_trash_post', __( 'Sorry, you are not allowed to create a comment on this post.' ), array( 'status' => 403 ) );
		}

		if ( ! $this->check_read_post_permission( $post, $request ) ) {
			return new MN_Error( 'rest_cannot_read_post', __( 'Sorry, you are not allowed to read the post for this comment.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! comments_open( $post->ID ) ) {
			return new MN_Error( 'rest_comment_closed', __( 'Sorry, comments are closed for this item.' ), array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Creates a comment.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param MN_REST_Request $request Full details about the request.
	 * @return MN_Error|MN_REST_Response Response object on success, or error object on failure.
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new MN_Error( 'rest_comment_exists', __( 'Cannot create existing comment.' ), array( 'status' => 400 ) );
		}

		// Do not allow comments to be created with a non-default type.
		if ( ! empty( $request['type'] ) && 'comment' !== $request['type'] ) {
			return new MN_Error( 'rest_invalid_comment_type', __( 'Cannot create a comment with that type.' ), array( 'status' => 400 ) );
		}

		$prepared_comment = $this->prepare_item_for_database( $request );
		if ( is_mn_error( $prepared_comment ) ) {
			return $prepared_comment;
		}

		$prepared_comment['comment_type'] = '';

		/*
		 * Do not allow a comment to be created with missing or empty
		 * comment_content. See mn_handle_comment_submission().
		 */
		if ( empty( $prepared_comment['comment_content'] ) ) {
			return new MN_Error( 'rest_comment_content_invalid', __( 'Invalid comment content.' ), array( 'status' => 400 ) );
		}

		// Setting remaining values before mn_insert_comment so we can use mn_allow_comment().
		if ( ! isset( $prepared_comment['comment_date_gmt'] ) ) {
			$prepared_comment['comment_date_gmt'] = current_time( 'mysql', true );
		}

		// Set author data if the user's logged in.
		$missing_author = empty( $prepared_comment['user_id'] )
			&& empty( $prepared_comment['comment_author'] )
			&& empty( $prepared_comment['comment_author_email'] )
			&& empty( $prepared_comment['comment_author_url'] );

		if ( is_user_logged_in() && $missing_author ) {
			$user = mn_get_current_user();

			$prepared_comment['user_id'] = $user->ID;
			$prepared_comment['comment_author'] = $user->display_name;
			$prepared_comment['comment_author_email'] = $user->user_email;
			$prepared_comment['comment_author_url'] = $user->user_url;
		}

		// Honor the discussion setting that requires a name and email address of the comment author.
		if ( get_option( 'require_name_email' ) ) {
			if ( empty( $prepared_comment['comment_author'] ) || empty( $prepared_comment['comment_author_email'] ) ) {
				return new MN_Error( 'rest_comment_author_data_required', __( 'Creating a comment requires valid author name and email values.' ), array( 'status' => 400 ) );
			}
		}

		if ( ! isset( $prepared_comment['comment_author_email'] ) ) {
			$prepared_comment['comment_author_email'] = '';
		}

		if ( ! isset( $prepared_comment['comment_author_url'] ) ) {
			$prepared_comment['comment_author_url'] = '';
		}

		if ( ! isset( $prepared_comment['comment_agent'] ) ) {
			$prepared_comment['comment_agent'] = '';
		}

		$check_comment_lengths = mn_check_comment_data_max_lengths( $prepared_comment );
		if ( is_mn_error( $check_comment_lengths ) ) {
			$error_code = $check_comment_lengths->get_error_code();
			return new MN_Error( $error_code, __( 'Comment field exceeds maximum length allowed.' ), array( 'status' => 400 ) );
		}

		$prepared_comment['comment_approved'] = mn_allow_comment( $prepared_comment, true );

		if ( is_mn_error( $prepared_comment['comment_approved'] ) ) {
			$error_code    = $prepared_comment['comment_approved']->get_error_code();
			$error_message = $prepared_comment['comment_approved']->get_error_message();

			if ( 'comment_duplicate' === $error_code ) {
				return new MN_Error( $error_code, $error_message, array( 'status' => 409 ) );
			}

			if ( 'comment_flood' === $error_code ) {
				return new MN_Error( $error_code, $error_message, array( 'status' => 400 ) );
			}

			return $prepared_comment['comment_approved'];
		}

		/**
		 * Filters a comment before it is inserted via the REST API.
		 *
		 * Allows modification of the comment right before it is inserted via mn_insert_comment().
		 *
		 * @since 4.7.0
		 *
		 * @param array           $prepared_comment The prepared comment data for mn_insert_comment().
		 * @param MN_REST_Request $request          Request used to insert the comment.
		 */
		$prepared_comment = apply_filters( 'rest_pre_insert_comment', $prepared_comment, $request );

		$comment_id = mn_insert_comment( mn_filter_comment( mn_slash( (array) $prepared_comment ) ) );

		if ( ! $comment_id ) {
			return new MN_Error( 'rest_comment_failed_create', __( 'Creating comment failed.' ), array( 'status' => 500 ) );
		}

		if ( isset( $request['status'] ) ) {
			$this->handle_status_param( $request['status'], $comment_id );
		}

		$comment = get_comment( $comment_id );

		/**
		 * Fires after a comment is created or updated via the REST API.
		 *
		 * @since 4.7.0
		 *
		 * @param MN_Comment      $comment  Inserted or updated comment object.
		 * @param MN_REST_Request $request  Request object.
		 * @param bool            $creating True when creating a comment, false
		 *                                  when updating.
		 */
		do_action( 'rest_insert_comment', $comment, $request, true );

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
			$meta_update = $this->meta->update_value( $request['meta'], $comment_id );

			if ( is_mn_error( $meta_update ) ) {
				return $meta_update;
			}
		}

		$fields_update = $this->update_additional_fields_for_object( $comment, $request );

		if ( is_mn_error( $fields_update ) ) {
			return $fields_update;
		}

		$context = current_user_can( 'moderate_comments' ) ? 'edit' : 'view';

		$request->set_param( 'context', $context );

		$response = $this->prepare_item_for_response( $comment, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $comment_id ) ) );


		return $response;
	}

	/**
	 * Checks if a given REST request has access to update a comment.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param MN_REST_Request $request Full details about the request.
	 * @return MN_Error|bool True if the request has access to update the item, error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {

		$id = (int) $request['id'];

		$comment = get_comment( $id );

		if ( $comment && ! $this->check_edit_permission( $comment ) ) {
			return new MN_Error( 'rest_cannot_edit', __( 'Sorry, you are not allowed to edit this comment.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Updates a comment.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param MN_REST_Request $request Full details about the request.
	 * @return MN_Error|MN_REST_Response Response object on success, or error object on failure.
	 */
	public function update_item( $request ) {
		$id = (int) $request['id'];

		$comment = get_comment( $id );

		if ( empty( $comment ) ) {
			return new MN_Error( 'rest_comment_invalid_id', __( 'Invalid comment ID.' ), array( 'status' => 404 ) );
		}

		if ( isset( $request['type'] ) && get_comment_type( $id ) !== $request['type'] ) {
			return new MN_Error( 'rest_comment_invalid_type', __( 'Sorry, you are not allowed to change the comment type.' ), array( 'status' => 404 ) );
		}

		$prepared_args = $this->prepare_item_for_database( $request );

		if ( is_mn_error( $prepared_args ) ) {
			return $prepared_args;
		}

		if ( empty( $prepared_args ) && isset( $request['status'] ) ) {
			// Only the comment status is being changed.
			$change = $this->handle_status_param( $request['status'], $id );

			if ( ! $change ) {
				return new MN_Error( 'rest_comment_failed_edit', __( 'Updating comment status failed.' ), array( 'status' => 500 ) );
			}
		} elseif ( ! empty( $prepared_args ) ) {
			if ( is_mn_error( $prepared_args ) ) {
				return $prepared_args;
			}

			if ( isset( $prepared_args['comment_content'] ) && empty( $prepared_args['comment_content'] ) ) {
				return new MN_Error( 'rest_comment_content_invalid', __( 'Invalid comment content.' ), array( 'status' => 400 ) );
			}

			$prepared_args['comment_ID'] = $id;

			$check_comment_lengths = mn_check_comment_data_max_lengths( $prepared_args );
			if ( is_mn_error( $check_comment_lengths ) ) {
				$error_code = $check_comment_lengths->get_error_code();
				return new MN_Error( $error_code, __( 'Comment field exceeds maximum length allowed.' ), array( 'status' => 400 ) );
			}

			$updated = mn_update_comment( mn_slash( (array) $prepared_args ) );

			if ( 0 === $updated ) {
				return new MN_Error( 'rest_comment_failed_edit', __( 'Updating comment failed.' ), array( 'status' => 500 ) );
			}

			if ( isset( $request['status'] ) ) {
				$this->handle_status_param( $request['status'], $id );
			}
		}

		$comment = get_comment( $id );

		/* This action is documented in lib/endpoints/class-mn-rest-comments-controller.php */
		do_action( 'rest_insert_comment', $comment, $request, false );

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
			$meta_update = $this->meta->update_value( $request['meta'], $id );

			if ( is_mn_error( $meta_update ) ) {
				return $meta_update;
			}
		}

		$fields_update = $this->update_additional_fields_for_object( $comment, $request );

		if ( is_mn_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $comment, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if a given request has access to delete a comment.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param MN_REST_Request $request Full details about the request.
	 * @return MN_Error|bool True if the request has access to delete the item, error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		$id      = (int) $request['id'];
		$comment = get_comment( $id );

		if ( ! $comment ) {
			return new MN_Error( 'rest_comment_invalid_id', __( 'Invalid comment ID.' ), array( 'status' => 404 ) );
		}

		if ( ! $this->check_edit_permission( $comment ) ) {
			return new MN_Error( 'rest_cannot_delete', __( 'Sorry, you are not allowed to delete this comment.' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Deletes a comment.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param MN_REST_Request $request Full details about the request.
	 * @return MN_Error|MN_REST_Response Response object on success, or error object on failure.
	 */
	public function delete_item( $request ) {
		$id    = (int) $request['id'];
		$force = isset( $request['force'] ) ? (bool) $request['force'] : false;

		$comment = get_comment( $id );

		if ( empty( $comment ) ) {
			return new MN_Error( 'rest_comment_invalid_id', __( 'Invalid comment ID.' ), array( 'status' => 404 ) );
		}

		/**
		 * Filters whether a comment can be trashed.
		 *
		 * Return false to disable trash support for the post.
		 *
		 * @since 4.7.0
		 *
		 * @param bool    $supports_trash Whether the post type support trashing.
		 * @param MN_Post $comment        The comment object being considered for trashing support.
		 */
		$supports_trash = apply_filters( 'rest_comment_trashable', ( EMPTY_TRASH_DAYS > 0 ), $comment );

		$request->set_param( 'context', 'edit' );

		if ( $force ) {
			$previous = $this->prepare_item_for_response( $comment, $request );
			$result = mn_delete_comment( $comment->comment_ID, true );
			$response = new MN_REST_Response();
			$response->set_data( array( 'deleted' => true, 'previous' => $previous->get_data() ) );
		} else {
			// If this type doesn't support trashing, error out.
			if ( ! $supports_trash ) {
				return new MN_Error( 'rest_trash_not_supported', __( 'The comment does not support trashing. Set force=true to delete.' ), array( 'status' => 501 ) );
			}

			if ( 'trash' === $comment->comment_approved ) {
				return new MN_Error( 'rest_already_trashed', __( 'The comment has already been trashed.' ), array( 'status' => 410 ) );
			}

			$result = mn_trash_comment( $comment->comment_ID );
			$comment = get_comment( $comment->comment_ID );
			$response = $this->prepare_item_for_response( $comment, $request );
		}

		if ( ! $result ) {
			return new MN_Error( 'rest_cannot_delete', __( 'The comment cannot be deleted.' ), array( 'status' => 500 ) );
		}

		/**
		 * Fires after a comment is deleted via the REST API.
		 *
		 * @since 4.7.0
		 *
		 * @param MN_Comment       $comment  The deleted comment data.
		 * @param MN_REST_Response $response The response returned from the API.
		 * @param MN_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'rest_delete_comment', $comment, $response, $request );

		return $response;
	}

	/**
	 * Prepares a single comment output for response.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param MN_Comment      $comment Comment object.
	 * @param MN_REST_Request $request Request object.
	 * @return MN_REST_Response Response object.
	 */
	public function prepare_item_for_response( $comment, $request ) {
		$data = array(
			'id'                 => (int) $comment->comment_ID,
			'post'               => (int) $comment->comment_post_ID,
			'parent'             => (int) $comment->comment_parent,
			'author'             => (int) $comment->user_id,
			'author_name'        => $comment->comment_author,
			'author_email'       => $comment->comment_author_email,
			'author_url'         => $comment->comment_author_url,
			'author_ip'          => $comment->comment_author_IP,
			'author_user_agent'  => $comment->comment_agent,
			'date'               => mysql_to_rfc3339( $comment->comment_date ),
			'date_gmt'           => mysql_to_rfc3339( $comment->comment_date_gmt ),
			'content'            => array(
				/** This filter is documented in res/comment-template.php */
				'rendered' => apply_filters( 'comment_text', $comment->comment_content, $comment ),
				'raw'      => $comment->comment_content,
			),
			'link'               => get_comment_link( $comment ),
			'status'             => $this->prepare_status_response( $comment->comment_approved ),
			'type'               => get_comment_type( $comment->comment_ID ),
		);

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['author_avatar_urls'] ) ) {
			$data['author_avatar_urls'] = rest_get_avatar_urls( $comment->comment_author_email );
		}

		if ( ! empty( $schema['properties']['meta'] ) ) {
			$data['meta'] = $this->meta->get_value( $comment->comment_ID, $request );
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $comment ) );

		/**
		 * Filters a comment returned from the API.
		 *
		 * Allows modification of the comment right before it is returned.
		 *
		 * @since 4.7.0
		 *
		 * @param MN_REST_Response  $response The response object.
		 * @param MN_Comment        $comment  The original comment object.
		 * @param MN_REST_Request   $request  Request used to generate the response.
		 */
		return apply_filters( 'rest_prepare_comment', $response, $comment, $request );
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param MN_Comment $comment Comment object.
	 * @return array Links for the given comment.
	 */
	protected function prepare_links( $comment ) {
		$links = array(
			'self' => array(
				'href' => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $comment->comment_ID ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		if ( 0 !== (int) $comment->user_id ) {
			$links['author'] = array(
				'href'       => rest_url( 'mn/v2/users/' . $comment->user_id ),
				'embeddable' => true,
			);
		}

		if ( 0 !== (int) $comment->comment_post_ID ) {
			$post = get_post( $comment->comment_post_ID );

			if ( ! empty( $post->ID ) ) {
				$obj = get_post_type_object( $post->post_type );
				$base = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;

				$links['up'] = array(
					'href'       => rest_url( 'mn/v2/' . $base . '/' . $comment->comment_post_ID ),
					'embeddable' => true,
					'post_type'  => $post->post_type,
				);
			}
		}

		if ( 0 !== (int) $comment->comment_parent ) {
			$links['in-reply-to'] = array(
				'href'       => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $comment->comment_parent ) ),
				'embeddable' => true,
			);
		}

		// Only grab one comment to verify the comment has children.
		$comment_children = $comment->get_children( array(
			'number' => 1,
			'count'  => true
		) );

		if ( ! empty( $comment_children ) ) {
			$args = array(
				'parent' => $comment->comment_ID
			);

			$rest_url = add_query_arg( $args, rest_url( $this->namespace . '/' . $this->rest_base ) );

			$links['children'] = array(
				'href' => $rest_url,
			);
		}

		return $links;
	}

	/**
	 * Prepends internal property prefix to query parameters to match our response fields.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param string $query_param Query parameter.
	 * @return string The normalized query parameter.
	 */
	protected function normalize_query_param( $query_param ) {
		$prefix = 'comment_';

		switch ( $query_param ) {
			case 'id':
				$normalized = $prefix . 'ID';
				break;
			case 'post':
				$normalized = $prefix . 'post_ID';
				break;
			case 'parent':
				$normalized = $prefix . 'parent';
				break;
			case 'include':
				$normalized = 'comment__in';
				break;
			default:
				$normalized = $prefix . $query_param;
				break;
		}

		return $normalized;
	}

	/**
	 * Checks comment_approved to set comment status for single comment output.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param string|int $comment_approved comment status.
	 * @return string Comment status.
	 */
	protected function prepare_status_response( $comment_approved ) {

		switch ( $comment_approved ) {
			case 'hold':
			case '0':
				$status = 'hold';
				break;

			case 'approve':
			case '1':
				$status = 'approved';
				break;

			case 'spam':
			case 'trash':
			default:
				$status = $comment_approved;
				break;
		}

		return $status;
	}

	/**
	 * Prepares a single comment to be inserted into the database.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param MN_REST_Request $request Request object.
	 * @return array|MN_Error Prepared comment, otherwise MN_Error object.
	 */
	protected function prepare_item_for_database( $request ) {
		$prepared_comment = array();

		/*
		 * Allow the comment_content to be set via the 'content' or
		 * the 'content.raw' properties of the Request object.
		 */
		if ( isset( $request['content'] ) && is_string( $request['content'] ) ) {
			$prepared_comment['comment_content'] = $request['content'];
		} elseif ( isset( $request['content']['raw'] ) && is_string( $request['content']['raw'] ) ) {
			$prepared_comment['comment_content'] = $request['content']['raw'];
		}

		if ( isset( $request['post'] ) ) {
			$prepared_comment['comment_post_ID'] = (int) $request['post'];
		}

		if ( isset( $request['parent'] ) ) {
			$prepared_comment['comment_parent'] = $request['parent'];
		}

		if ( isset( $request['author'] ) ) {
			$user = new MN_User( $request['author'] );

			if ( $user->exists() ) {
				$prepared_comment['user_id'] = $user->ID;
				$prepared_comment['comment_author'] = $user->display_name;
				$prepared_comment['comment_author_email'] = $user->user_email;
				$prepared_comment['comment_author_url'] = $user->user_url;
			} else {
				return new MN_Error( 'rest_comment_author_invalid', __( 'Invalid comment author ID.' ), array( 'status' => 400 ) );
			}
		}

		if ( isset( $request['author_name'] ) ) {
			$prepared_comment['comment_author'] = $request['author_name'];
		}

		if ( isset( $request['author_email'] ) ) {
			$prepared_comment['comment_author_email'] = $request['author_email'];
		}

		if ( isset( $request['author_url'] ) ) {
			$prepared_comment['comment_author_url'] = $request['author_url'];
		}

		if ( isset( $request['author_ip'] ) && current_user_can( 'moderate_comments' ) ) {
			$prepared_comment['comment_author_IP'] = $request['author_ip'];
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) && rest_is_ip_address( $_SERVER['REMOTE_ADDR'] ) ) {
			$prepared_comment['comment_author_IP'] = $_SERVER['REMOTE_ADDR'];
		} else {
			$prepared_comment['comment_author_IP'] = '127.0.0.1';
		}

		if ( ! empty( $request['author_user_agent'] ) ) {
			$prepared_comment['comment_agent'] = $request['author_user_agent'];
		} elseif ( $request->get_header( 'user_agent' ) ) {
			$prepared_comment['comment_agent'] = $request->get_header( 'user_agent' );
		}

		if ( ! empty( $request['date'] ) ) {
			$date_data = rest_get_date_with_gmt( $request['date'] );

			if ( ! empty( $date_data ) ) {
				list( $prepared_comment['comment_date'], $prepared_comment['comment_date_gmt'] ) = $date_data;
			}
		} elseif ( ! empty( $request['date_gmt'] ) ) {
			$date_data = rest_get_date_with_gmt( $request['date_gmt'], true );

			if ( ! empty( $date_data ) ) {
				list( $prepared_comment['comment_date'], $prepared_comment['comment_date_gmt'] ) = $date_data;
			}
		}

		/**
		 * Filters a comment after it is prepared for the database.
		 *
		 * Allows modification of the comment right after it is prepared for the database.
		 *
		 * @since 4.7.0
		 *
		 * @param array           $prepared_comment The prepared comment data for `mn_insert_comment`.
		 * @param MN_REST_Request $request          The current request.
		 */
		return apply_filters( 'rest_preprocess_comment', $prepared_comment, $request );
	}

	/**
	 * Retrieves the comment's schema, conforming to JSON Schema.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'              => 'http://json-schema.org/schema#',
			'title'                => 'comment',
			'type'                 => 'object',
			'properties'           => array(
				'id'               => array(
					'description'  => __( 'Unique identifier for the object.' ),
					'type'         => 'integer',
					'context'      => array( 'view', 'edit', 'embed' ),
					'readonly'     => true,
				),
				'author'           => array(
					'description'  => __( 'The ID of the user object, if author was a user.' ),
					'type'         => 'integer',
					'context'      => array( 'view', 'edit', 'embed' ),
				),
				'author_email'     => array(
					'description'  => __( 'Email address for the object author.' ),
					'type'         => 'string',
					'format'       => 'email',
					'context'      => array( 'edit' ),
					'arg_options'  => array(
						'sanitize_callback' => array( $this, 'check_comment_author_email' ),
						'validate_callback' => null, // skip built-in validation of 'email'.
					),
				),
				'author_ip'     => array(
					'description'  => __( 'IP address for the object author.' ),
					'type'         => 'string',
					'format'       => 'ip',
					'context'      => array( 'edit' ),
				),
				'author_name'     => array(
					'description'  => __( 'Display name for the object author.' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit', 'embed' ),
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'author_url'       => array(
					'description'  => __( 'URL for the object author.' ),
					'type'         => 'string',
					'format'       => 'uri',
					'context'      => array( 'view', 'edit', 'embed' ),
				),
				'author_user_agent'     => array(
					'description'  => __( 'User agent for the object author.' ),
					'type'         => 'string',
					'context'      => array( 'edit' ),
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'content'          => array(
					'description'     => __( 'The content for the object.' ),
					'type'            => 'object',
					'context'         => array( 'view', 'edit', 'embed' ),
					'arg_options'     => array(
						'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database()
					),
					'properties'      => array(
						'raw'         => array(
							'description'     => __( 'Content for the object, as it exists in the database.' ),
							'type'            => 'string',
							'context'         => array( 'edit' ),
						),
						'rendered'    => array(
							'description'     => __( 'HTML content for the object, transformed for display.' ),
							'type'            => 'string',
							'context'         => array( 'view', 'edit', 'embed' ),
							'readonly'        => true,
						),
					),
				),
				'date'             => array(
					'description'  => __( "The date the object was published, in the site's timezone." ),
					'type'         => 'string',
					'format'       => 'date-time',
					'context'      => array( 'view', 'edit', 'embed' ),
				),
				'date_gmt'         => array(
					'description'  => __( 'The date the object was published, as GMT.' ),
					'type'         => 'string',
					'format'       => 'date-time',
					'context'      => array( 'view', 'edit' ),
				),
				'link'             => array(
					'description'  => __( 'URL to the object.' ),
					'type'         => 'string',
					'format'       => 'uri',
					'context'      => array( 'view', 'edit', 'embed' ),
					'readonly'     => true,
				),
				'parent'           => array(
					'description'  => __( 'The ID for the parent of the object.' ),
					'type'         => 'integer',
					'context'      => array( 'view', 'edit', 'embed' ),
					'default'      => 0,
				),
				'post'             => array(
					'description'  => __( 'The ID of the associated post object.' ),
					'type'         => 'integer',
					'context'      => array( 'view', 'edit' ),
					'default'      => 0,
				),
				'status'           => array(
					'description'  => __( 'State of the object.' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit' ),
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_key',
					),
				),
				'type'             => array(
					'description'  => __( 'Type of Comment for the object.' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit', 'embed' ),
					'readonly'     => true,
				),
			),
		);

		if ( get_option( 'show_avatars' ) ) {
			$avatar_properties = array();

			$avatar_sizes = rest_get_avatar_sizes();
			foreach ( $avatar_sizes as $size ) {
				$avatar_properties[ $size ] = array(
					/* translators: %d: avatar image size in pixels */
					'description' => sprintf( __( 'Avatar URL with image size of %d pixels.' ), $size ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'embed', 'view', 'edit' ),
				);
			}

			$schema['properties']['author_avatar_urls'] = array(
				'description'   => __( 'Avatar URLs for the object author.' ),
				'type'          => 'object',
				'context'       => array( 'view', 'edit', 'embed' ),
				'readonly'      => true,
				'properties'    => $avatar_properties,
			);
		}

		$schema['properties']['meta'] = $this->meta->get_field_schema();

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Retrieves the query params for collections.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @return array Comments collection parameters.
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$query_params['after'] = array(
			'description'       => __( 'Limit response to comments published after a given ISO8601 compliant date.' ),
			'type'              => 'string',
			'format'            => 'date-time',
		);

		$query_params['author'] = array(
			'description'       => __( 'Limit result set to comments assigned to specific user IDs. Requires authorization.' ),
			'type'              => 'array',
			'items'             => array(
				'type'          => 'integer',
			),
		);

		$query_params['author_exclude'] = array(
			'description'       => __( 'Ensure result set excludes comments assigned to specific user IDs. Requires authorization.' ),
			'type'              => 'array',
			'items'             => array(
				'type'          => 'integer',
			),
		);

		$query_params['author_email'] = array(
			'default'           => null,
			'description'       => __( 'Limit result set to that from a specific author email. Requires authorization.' ),
			'format'            => 'email',
			'type'              => 'string',
		);

		$query_params['before'] = array(
			'description'       => __( 'Limit response to comments published before a given ISO8601 compliant date.' ),
			'type'              => 'string',
			'format'            => 'date-time',
		);

		$query_params['exclude'] = array(
			'description'        => __( 'Ensure result set excludes specific IDs.' ),
			'type'               => 'array',
			'items'              => array(
				'type'           => 'integer',
			),
			'default'            => array(),
		);

		$query_params['include'] = array(
			'description'        => __( 'Limit result set to specific IDs.' ),
			'type'               => 'array',
			'items'              => array(
				'type'           => 'integer',
			),
			'default'            => array(),
		);

		$query_params['offset'] = array(
			'description'        => __( 'Offset the result set by a specific number of items.' ),
			'type'               => 'integer',
		);

		$query_params['order']      = array(
			'description'           => __( 'Order sort attribute ascending or descending.' ),
			'type'                  => 'string',
			'default'               => 'desc',
			'enum'                  => array(
				'asc',
				'desc',
			),
		);

		$query_params['orderby']    = array(
			'description'           => __( 'Sort collection by object attribute.' ),
			'type'                  => 'string',
			'default'               => 'date_gmt',
			'enum'                  => array(
				'date',
				'date_gmt',
				'id',
				'include',
				'post',
				'parent',
				'type',
			),
		);

		$query_params['parent'] = array(
			'default'           => array(),
			'description'       => __( 'Limit result set to comments of specific parent IDs.' ),
			'type'              => 'array',
			'items'             => array(
				'type'          => 'integer',
			),
		);

		$query_params['parent_exclude'] = array(
			'default'           => array(),
			'description'       => __( 'Ensure result set excludes specific parent IDs.' ),
			'type'              => 'array',
			'items'             => array(
				'type'          => 'integer',
			),
		);

		$query_params['post']   = array(
			'default'           => array(),
			'description'       => __( 'Limit result set to comments assigned to specific post IDs.' ),
			'type'              => 'array',
			'items'             => array(
				'type'          => 'integer',
			),
		);

		$query_params['status'] = array(
			'default'           => 'approve',
			'description'       => __( 'Limit result set to comments assigned a specific status. Requires authorization.' ),
			'sanitize_callback' => 'sanitize_key',
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$query_params['type'] = array(
			'default'           => 'comment',
			'description'       => __( 'Limit result set to comments assigned a specific type. Requires authorization.' ),
			'sanitize_callback' => 'sanitize_key',
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$query_params['password'] = array(
			'description' => __( 'The password for the post if it is password protected.' ),
			'type'        => 'string',
		);

		/**
		 * Filter collection parameters for the comments controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal MN_Comment_Query parameter. Use the
		 * `rest_comment_query` filter to set MN_Comment_Query parameters.
		 *
		 * @since 4.7.0
		 *
		 * @param $params JSON Schema-formatted collection parameters.
		 */
		return apply_filters( 'rest_comment_collection_params', $query_params );
	}

	/**
	 * Sets the comment_status of a given comment object when creating or updating a comment.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param string|int $new_status New comment status.
	 * @param int        $comment_id Comment ID.
	 * @return bool Whether the status was changed.
	 */
	protected function handle_status_param( $new_status, $comment_id ) {
		$old_status = mn_get_comment_status( $comment_id );

		if ( $new_status === $old_status ) {
			return false;
		}

		switch ( $new_status ) {
			case 'approved' :
			case 'approve':
			case '1':
				$changed = mn_set_comment_status( $comment_id, 'approve' );
				break;
			case 'hold':
			case '0':
				$changed = mn_set_comment_status( $comment_id, 'hold' );
				break;
			case 'spam' :
				$changed = mn_spam_comment( $comment_id );
				break;
			case 'unspam' :
				$changed = mn_unspam_comment( $comment_id );
				break;
			case 'trash' :
				$changed = mn_trash_comment( $comment_id );
				break;
			case 'untrash' :
				$changed = mn_untrash_comment( $comment_id );
				break;
			default :
				$changed = false;
				break;
		}

		return $changed;
	}

	/**
	 * Checks if the post can be read.
	 *
	 * Correctly handles posts with the inherit status.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param MN_Post         $post    Post object.
	 * @param MN_REST_Request $request Request data to check.
	 * @return bool Whether post can be read.
	 */
	protected function check_read_post_permission( $post, $request ) {
		$posts_controller = new MN_REST_Posts_Controller( $post->post_type );
		$post_type = get_post_type_object( $post->post_type );

		$has_password_filter = false;

		// Only check password if a specific post was queried for or a single comment
		$requested_post = ! empty( $request['post'] ) && 1 === count( $request['post'] );
		$requested_comment = ! empty( $request['id'] );
		if ( ( $requested_post || $requested_comment ) && $posts_controller->can_access_password_content( $post, $request ) ) {
			add_filter( 'post_password_required', '__return_false' );

			$has_password_filter = true;
		}

		if ( post_password_required( $post ) ) {
			$result = current_user_can( $post_type->cap->edit_post, $post->ID );
		} else {
			$result = $posts_controller->check_read_permission( $post );
		}

		if ( $has_password_filter ) {
			remove_filter( 'post_password_required', '__return_false' );
		}

		return $result;
	}

	/**
	 * Checks if the comment can be read.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param MN_Comment      $comment Comment object.
	 * @param MN_REST_Request $request Request data to check.
	 * @return bool Whether the comment can be read.
	 */
	protected function check_read_permission( $comment, $request ) {
		if ( ! empty( $comment->comment_post_ID ) ) {
			$post = get_post( $comment->comment_post_ID );
			if ( $post ) {
				if ( $this->check_read_post_permission( $post, $request ) && 1 === (int) $comment->comment_approved ) {
					return true;
				}
			}
		}

		if ( 0 === get_current_user_id() ) {
			return false;
		}

		if ( empty( $comment->comment_post_ID ) && ! current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		if ( ! empty( $comment->user_id ) && get_current_user_id() === (int) $comment->user_id ) {
			return true;
		}

		return current_user_can( 'edit_comment', $comment->comment_ID );
	}

	/**
	 * Checks if a comment can be edited or deleted.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param object $comment Comment object.
	 * @return bool Whether the comment can be edited or deleted.
	 */
	protected function check_edit_permission( $comment ) {
		if ( 0 === (int) get_current_user_id() ) {
			return false;
		}

		if ( ! current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		return current_user_can( 'edit_comment', $comment->comment_ID );
	}

	/**
	 * Checks a comment author email for validity.
	 *
	 * Accepts either a valid email address or empty string as a valid comment
	 * author email address. Setting the comment author email to an empty
	 * string is allowed when a comment is being updated.
	 *
	 * @since 4.7.0
	 *
	 * @param string          $value   Author email value submitted.
	 * @param MN_REST_Request $request Full details about the request.
	 * @param string          $param   The parameter name.
	 * @return MN_Error|string The sanitized email address, if valid,
	 *                         otherwise an error.
	 */
	public function check_comment_author_email( $value, $request, $param ) {
		$email = (string) $value;
		if ( empty( $email ) ) {
			return $email;
		}

		$check_email = rest_validate_request_arg( $email, $request, $param );
		if ( is_mn_error( $check_email ) ) {
			return $check_email;
		}

		return $email;
	}
}