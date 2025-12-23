<?php
/**
 * User Quiz functions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get user data by user id
 */
function exms_get_user_name( $user_id ) {

	$user = get_user_by( 'id', $user_id );
	if( $user ) {
		return $user->display_name;
	}
}

/**
 * Get user assinged quiz id by user id
 * 
 * @param $user_id
 * @param $post_type
 * @param $paged ( Optional ) paged is used to get 10 quiz in per page.
 * with specfic limit like ( 0, 10 ) by default empty
 */
function exms_get_user_post_ids( $user_id, $post_type, $paged = '' ) {

	global $wpdb;

    $limit = '';
    if( ! empty( $paged ) && $paged >= 0 ) {

        $limit = 'LIMIT '.$paged.',10';
    }

    $table_name = EXMS_PR_Fn::exms_user_post_relations_table();

    $quiz_ids = [];
    $quiz_meta = $wpdb->get_results( " SELECT post_id FROM $table_name 
        WHERE user_id = $user_id AND post_type = '$post_type' ORDER BY time $limit " );

    if( ! empty( $quiz_meta ) && ! is_null( $quiz_meta ) ) {
        $quiz_ids = array_map( 'intval', array_column( $quiz_meta, 'post_id' ) );
    }

    return $quiz_ids;
}

/**
 * create a functio to get user email 
 */
function exms_get_user_email( $user_id ) {

    global $wpdb;

    if( ! $user_id ) {
        return;
    }

    $email = $wpdb->get_var( $wpdb->prepare( "SELECT user_email FROM $wpdb->users WHERE ID = %d", $user_id ) );

    return $email;
}
/**
 * Get ids of all users
 * 
 * @param $exclude_users ( opetional )
 */
function exms_get_all_user_ids( $exclude_users = [], $paged = '', $search = '', $data_type = '' ) {

    $args = [
        'fields'      => 'IDs',
        'order'		  => 'ASC',
        'number'      => 10
    ];

    if( empty( $data_type ) || 'un-assigned' == $data_type ) {

    	if( ! empty( $exclude_users ) ) {
	    	$args['exclude'] = $exclude_users;
	    }
    } else {

    	if( ! empty( $exclude_users ) ) {
	    	$args['include'] = $exclude_users;
	    }
    }

    if( ! empty( $paged ) ) {

        $args['paged'] = $paged;   
    }

    if( ! empty( $search ) ) {

    	$args['search'] = '*'.esc_attr( $search ).'*';	
    }

    $user_ids = [];

    $user_query = new WP_User_Query( $args );   
    $users = $user_query->get_results();
    if( empty( $users ) ) {
        return $user_ids;
    }

    $user_ids = array_unique( array_map( 'intval', $users ) );

    return $user_ids;
}

/**
 * Create user assign to group user html content
 * 
 * @param $post
 */
function exms_group_user_assign_to_post_html( $post, $parent_post_type = "" ) {
    
    $post_id   = $post->ID;
    $post_type = get_post_type( $post_id );
    $per_page  = 6;
    $parent_post_type = EXMS_PR_Fn::exms_get_parent_post_type();
    $role_label = "";
    $roles = "user";
    if( $post_type == "exms-groups" && $post_type != $parent_post_type && $parent_post_type != 'exms-quizzes' ) {
        
        $all_user_ids = get_users(
            [
                'role'   => 'exms_student',
                'fields' => 'ID',
            ]
        );

        $role_label = __( 'User', 'wp_exams' );
    }
    
    $assigned_users   = exms_group_get_post_user_ids( $post_id );
    $unassigned_users = array_diff( $all_user_ids, $assigned_users );

    $paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $unassign_paged = isset($_GET['unassign_paged']) ? max(1, intval($_GET['unassign_paged'])) : 1;

    $assigned_offset   = ( $paged - 1 ) * $per_page;
    $unassigned_offset = ( $unassign_paged - 1 ) * $per_page;

    $assigned_paged   = array_slice( $assigned_users, $assigned_offset, $per_page );
    $unassigned_paged = array_slice( $unassigned_users, $unassigned_offset, $per_page );

    $assign_total_pages   = ceil( count( $assigned_users ) / $per_page );
    $unassign_total_pages = ceil( count( $unassigned_users ) / $per_page );

    ob_start();
    ?>
    <div class="exms-sortable-box-wrap">

    <div class="exms-assign-box exms-unassign exms-sortable-lists exms-user-assign-box-left exms-assign-box-left exms-sortable-lists-current" data-relation="current" data-user-id="">
        
        <div class="exms-header">
            <div class="exms-title">
                <span class="exms-status-dot yellow"></span>
                <span><?php echo __( 'Unassigned', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?> </span>
            </div>
            <div class="exms-actions">
                <span class="exms-dots exms-dots-toggle" data-type="unassign">⋯</span>
                <div class="exms-dropdown-menu exms-dropdown-unassign" style="display: none;">
                    <button type="button" class="exms-dropdown-btn" data-target="group-user" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                        data-users='<?php echo esc_attr( wp_json_encode( $unassigned_paged ) ); ?>'
                        data-type="assigned" data-role="<?php echo $role_label ?>">
                        <?php echo __( 'Assign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $role_label ?? '' ) ); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="exms-search-input-wrap" style="position: relative;">
            <input 
                type="text" 
                class="exms-post-search-input"
                placeholder="<?php echo __( 'Search All', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?>"
                data-current-post-type="search_users"
                data-search-post-type="<?php echo esc_attr( $post_type ); ?>"
                data-type="un-assigned"
                data-post-id="<?php echo esc_attr( $post_id ); ?>"
            >
            <span class="dashicons dashicons-search"></span>
        </div>

        <div class="exms-input-sortable-wrap">
            <div class="exms-sortable-items-wrap exms-group-user-sortable-items-wrap-left exms-group-user-sortable-pagination-wrap-left" data-post-type="" data-name="exms_unassign_items">
                <?php if ( ! empty( $unassigned_paged ) ) : ?>
                    <?php foreach ( $unassigned_paged as $user_id ) : ?>
                        <div class="exms-sortable-item" data-name="exms_unassign_items" data-name-key="current">
                            <span class="exms-post-title">
                                <a href="<?php echo admin_url( 'user-edit.php?user_id=' . $user_id ); ?>" target="_blank" class="exms-sortable-ui-link">
                                    <?php echo exms_get_user_name( $user_id ); ?>
                                </a>
                            </span>
                            <div class="exms-img-parent">
                                <img src="<?php echo esc_url( get_avatar_url( $user_id ) ); ?>" class="exms-avatar" />
                                <button class="exms-drag-icon" data-name="exms_unassign_items" data-target="group-user"><span>&#8594;</span></button>
                            </div>
                            <input type="hidden" class="exms-assign-unassign-id" value="<?php echo $user_id; ?>">
                            <input type="hidden" name="exms_current_relation" value="<?php echo esc_attr( $post_type ); ?>">
                            <input type="hidden" name="exms_user_name" value="<?php echo esc_attr( $roles ); ?>">
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="exms-post-not-found"><?php 
                        $role_text = $role_label ? esc_html( $role_label ) . ' ' : '';
                        echo __( 'No ', 'wp_exams' ) . $role_text . __( 'Unassigned.', 'wp_exams' );

                        if ( empty( $all_user_ids ) && current_user_can( 'administrator' ) ) {
                            $add_user_url = admin_url( 'user-new.php' );
                            echo ' <a href="' . esc_url( $add_user_url ) . '" target="_blank">' . __( 'Create New ', 'wp_exams' ) . $role_text . '</a>';
                        } ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php
        echo EXMS_PR_Fn::exms_sortable_unassign_pagination_html( $unassign_total_pages, $post_id, $unassign_paged,'user', 'group-user' );
        ?>
    </div>

    <!-- Assigned Users Box -->
    <div class="exms-assign-box exms-assign exms-sortable-lists exms-user-assign-box-right exms-assign-box-right exms-sortable-lists-current" data-relation="current" data-user-id="">
        
        <!-- Assign Heading -->
        <div class="exms-header">
            <div class="exms-title">
                <span class="exms-status-dot green"></span>
                <span><?php echo __( 'Assigned', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?></span>
            </div>
            <div class="exms-actions">
                <span class="exms-dots exms-dots-toggle" data-type="assign">⋯</span>
                <div class="exms-dropdown-menu exms-dropdown-assign" style="display: none;">
                    <button type="button" class="exms-dropdown-btn" data-target="group-user" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                        data-users='<?php echo esc_attr( wp_json_encode( $assigned_paged ) ); ?>'
                        data-type="unassigned" data-role="<?php echo $role_label ?>">
                        <?php echo __( 'Unassign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $role_label ?? '' ) ); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="exms-search-input-wrap" style="position: relative;">
            <input 
                type="text" 
                class="exms-post-search-input"
                placeholder="<?php echo __( 'Search All', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?>"
                data-current-post-type="search_users"
                data-search-post-type="<?php echo esc_attr( $post_type ); ?>"
                data-type="assigned"
                data-post-id="<?php echo esc_attr( $post_id ); ?>"
            >
            <span class="dashicons dashicons-search"></span>
        </div>

        <div class="exms-input-sortable-wrap">
            <div class="exms-sortable-items-wrap exms-group-user-sortable-items-wrap-right exms-group-user-sortable-pagination-wrap-right" data-name="exms_assign_items">
                <?php if ( ! empty( $assigned_paged ) ) : ?>
                    <?php foreach ( $assigned_paged as $user_id ) : ?>
                        <div class="exms-sortable-item" data-name="exms_assign_items" data-name-key="current">
                            <span class="exms-post-title">
                                <button class="exms-drag-icon" data-name="exms_assign_items" data-target="group-user"><span>&#8592;</span></button>
                                <a href="<?php echo admin_url( 'user-edit.php?user_id=' . $user_id ); ?>" target="_blank" class="exms-sortable-ui-link">
                                    <?php echo exms_get_user_name( $user_id ); ?>
                                </a>
                                </span>
                            <div class="exms-img-parent">
                            <img src="<?php echo esc_url( get_avatar_url( $user_id ) ); ?>" class="exms-avatar" />
                            </div>
                            <input type="hidden" class="exms-assign-unassign-id" value="<?php echo $user_id; ?>">
                            <input type="hidden" name="exms_current_relation" value="<?php echo esc_attr( $post_type ); ?>">
                            <input type="hidden" name="exms_user_name" value="<?php echo esc_attr( $roles ); ?>">
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="exms-post-not-found"><?php 
                        $role_text = $role_label ? esc_html( $role_label ) . ' ' : '';
                        echo __( 'No ', 'wp_exams' ) . $role_text . __( 'Assigned.', 'wp_exams' );

                        if ( empty( $all_user_ids ) && current_user_can( 'administrator' ) ) {
                            $add_user_url = admin_url( 'user-new.php' );
                            echo ' <a href="' . esc_url( $add_user_url ) . '" target="_blank">' . __( 'Create New ', 'wp_exams' ) . $role_text . '</a>';
                        } ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php
        echo EXMS_PR_Fn::exms_sortable_assign_pagination_html( $assign_total_pages, $post_id, $paged, 'user', 'group-user' );
        ?>
    </div>

    <div class="exms-clear-both"></div>
</div>

    <?php
    return ob_get_clean();
}
/**
 * Create user assign to user html content
 * 
 * @param $post
 */
function exms_post_user_assign_to_post_html( $post, $parent_post_type = "" ) {
    
    $post_id   = $post->ID;
    $post_type = get_post_type( $post_id );
    $per_page  = 6;
    $parent_post_type = EXMS_PR_Fn::exms_get_parent_post_type();
    $role_label = "";
    $roles = "instructor";
    if( $post_type == $parent_post_type && $parent_post_type != 'exms-quizzes' ) {
        
        $all_user_ids = get_users(
            [
                'role'   => 'exms_instructor',
                'fields' => 'ID',
            ]
        );

        $role_label = __( 'Instructor', 'wp_exams' );
    }
    
    $assigned_users   = exms_post_get_post_user_ids( $post_id );
    $unassigned_users = array_diff( $all_user_ids, $assigned_users );

    $paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $unassign_paged = isset($_GET['unassign_paged']) ? max(1, intval($_GET['unassign_paged'])) : 1;

    $assigned_offset   = ( $paged - 1 ) * $per_page;
    $unassigned_offset = ( $unassign_paged - 1 ) * $per_page;

    $assigned_paged   = array_slice( $assigned_users, $assigned_offset, $per_page );
    $unassigned_paged = array_slice( $unassigned_users, $unassigned_offset, $per_page );

    $assign_total_pages   = ceil( count( $assigned_users ) / $per_page );
    $unassign_total_pages = ceil( count( $unassigned_users ) / $per_page );

    ob_start();
    ?>
    <div class="exms-sortable-box-wrap">

    <div class="exms-assign-box exms-unassign exms-sortable-lists exms-user-assign-box-left exms-assign-box-left exms-sortable-lists-current" data-relation="current" data-user-id="">
        
        <div class="exms-header">
            <div class="exms-title">
                <span class="exms-status-dot yellow"></span>
                <span><?php echo __( 'Unassigned', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?> </span>
            </div>
            <div class="exms-actions">
                <span class="exms-dots exms-dots-toggle" data-type="unassign">⋯</span>
                <div class="exms-dropdown-menu exms-dropdown-unassign" style="display: none;">
                    <button type="button" class="exms-dropdown-btn" data-target="parent-user" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                        data-users='<?php echo esc_attr( wp_json_encode( $unassigned_paged ) ); ?>'
                        data-type="assigned" data-role="<?php echo $role_label ?>">
                        <?php echo __( 'Assign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $role_label ?? '' ) ); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="exms-search-input-wrap" style="position: relative;">
            <input 
                type="text" 
                class="exms-post-search-input"
                placeholder="<?php echo __( 'Search All', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?>"
                data-current-post-type="search_users"
                data-search-post-type="<?php echo esc_attr( $post_type ); ?>"
                data-type="un-assigned"
                data-post-id="<?php echo esc_attr( $post_id ); ?>"
            >
            <span class="dashicons dashicons-search"></span>
        </div>

        <div class="exms-input-sortable-wrap">
            <div class="exms-sortable-items-wrap exms-parent-user-sortable-items-wrap-left exms-parent-user-sortable-pagination-wrap-left" data-post-type="" data-name="exms_unassign_items">
                <?php if ( ! empty( $unassigned_paged ) ) : ?>
                    <?php foreach ( $unassigned_paged as $user_id ) : ?>
                        <div class="exms-sortable-item" data-name="exms_unassign_items" data-name-key="current">
                            <span class="exms-post-title">
                                <a href="<?php echo admin_url( 'user-edit.php?user_id=' . $user_id ); ?>" target="_blank" class="exms-sortable-ui-link">
                                    <?php echo exms_get_user_name( $user_id ); ?>
                                </a>
                            </span>
                            <div class="exms-img-parent">
                                <img src="<?php echo esc_url( get_avatar_url( $user_id ) ); ?>" class="exms-avatar" />
                                <button class="exms-drag-icon" data-name="exms_unassign_items" data-target="parent-user"><span>&#8594;</span></button>
                            </div>
                            <input type="hidden" class="exms-assign-unassign-id" value="<?php echo $user_id; ?>">
                            <input type="hidden" name="exms_current_relation" value="<?php echo esc_attr( $post_type ); ?>">
                            <input type="hidden" name="exms_user_name" value="<?php echo esc_attr( $roles ); ?>">
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="exms-post-not-found"><?php 
                        $role_text = $role_label ? esc_html( $role_label ) . ' ' : '';
                        echo __( 'No ', 'wp_exams' ) . $role_text . __( 'Unassigned.', 'wp_exams' );

                        if ( empty( $all_user_ids ) && current_user_can( 'administrator' ) ) {
                            $add_user_url = admin_url( 'user-new.php' );
                            echo ' <a href="' . esc_url( $add_user_url ) . '" target="_blank">' . __( 'Create New ', 'wp_exams' ) . $role_text . '</a>';
                        } ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php
        echo EXMS_PR_Fn::exms_sortable_unassign_pagination_html( $unassign_total_pages, $post_id, $unassign_paged,'user', 'user' );
        ?>
    </div>

    <!-- Assigned Users Box -->
    <div class="exms-assign-box exms-assign exms-sortable-lists exms-user-assign-box-right exms-assign-box-right exms-sortable-lists-current" data-relation="current" data-user-id="">
        
        <!-- Assign Heading -->
        <div class="exms-header">
            <div class="exms-title">
                <span class="exms-status-dot green"></span>
                <span><?php echo __( 'Assigned', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?></span>
            </div>
            <div class="exms-actions">
                <span class="exms-dots exms-dots-toggle" data-type="assign">⋯</span>
                <div class="exms-dropdown-menu exms-dropdown-assign" style="display: none;">
                    <button type="button" class="exms-dropdown-btn" data-target="parent-user" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                        data-users='<?php echo esc_attr( wp_json_encode( $assigned_paged ) ); ?>'
                        data-type="unassigned" data-role="<?php echo $role_label ?>">
                        <?php echo __( 'Unassign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $role_label ?? '' ) ); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="exms-search-input-wrap" style="position: relative;">
            <input 
                type="text" 
                class="exms-post-search-input"
                placeholder="<?php echo __( 'Search All', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?>"
                data-current-post-type="search_users"
                data-search-post-type="<?php echo esc_attr( $post_type ); ?>"
                data-type="assigned"
                data-post-id="<?php echo esc_attr( $post_id ); ?>"
            >
            <span class="dashicons dashicons-search"></span>
        </div>

        <div class="exms-input-sortable-wrap">
            <div class="exms-sortable-items-wrap exms-parent-user-sortable-items-wrap-right exms-parent-user-sortable-pagination-wrap-right" data-name="exms_assign_items">
                <?php if ( ! empty( $assigned_paged ) ) : ?>
                    <?php foreach ( $assigned_paged as $user_id ) : ?>
                        <div class="exms-sortable-item" data-name="exms_assign_items" data-name-key="current">
                            <span class="exms-post-title">
                                <button class="exms-drag-icon" data-name="exms_assign_items" data-target="parent-user"><span>&#8592;</span></button>
                                <a href="<?php echo admin_url( 'user-edit.php?user_id=' . $user_id ); ?>" target="_blank" class="exms-sortable-ui-link">
                                    <?php echo exms_get_user_name( $user_id ); ?>
                                </a>
                                </span>
                            <div class="exms-img-parent">
                            <img src="<?php echo esc_url( get_avatar_url( $user_id ) ); ?>" class="exms-avatar" />
                            </div>
                            <input type="hidden" class="exms-assign-unassign-id" value="<?php echo $user_id; ?>">
                            <input type="hidden" name="exms_current_relation" value="<?php echo esc_attr( $post_type ); ?>">
                            <input type="hidden" name="exms_user_name" value="<?php echo esc_attr( $roles ); ?>">
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="exms-post-not-found"><?php 
                        $role_text = $role_label ? esc_html( $role_label ) . ' ' : '';
                        echo __( 'No ', 'wp_exams' ) . $role_text . __( 'Assigned.', 'wp_exams' );

                        if ( empty( $all_user_ids ) && current_user_can( 'administrator' ) ) {
                            $add_user_url = admin_url( 'user-new.php' );
                            echo ' <a href="' . esc_url( $add_user_url ) . '" target="_blank">' . __( 'Create New ', 'wp_exams' ) . $role_text . '</a>';
                        } ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php
        echo EXMS_PR_Fn::exms_sortable_assign_pagination_html( $assign_total_pages, $post_id, $paged, 'user', 'user' );
        ?>
    </div>

    <div class="exms-clear-both"></div>
</div>

    <?php
    return ob_get_clean();
}
/**
 * Create user assign to user html content
 * 
 * @param $post
 */
function exms_user_assign_to_post_html( $post, $parent_post_type = "" ) {
    
    $post_id   = $post->ID;
    $post_type = get_post_type( $post_id );
    $per_page  = 6;
    $parent_post_type = EXMS_PR_Fn::exms_get_parent_post_type();
    $role_label = "";
    $roles = "user";
    if( $post_type === "exms-groups" ) {

        $all_user_ids     = get_users( 
            [ 
                'role'   => 'exms_group_leader',
                'fields' => 'ids' 
            ] 
        );
        $role_label = __( 'Leaders', 'wp_exams' );
        $roles = 'leader';
    } else {
        
        $all_user     = get_users( [ 'fields' => 'ids' ] );
        $all_user_ids = array_filter( $all_user, function( $user_id ) {
            $user = get_userdata( $user_id );
            return !in_array( 'exms_group_leader', (array) $user->roles ) &&
            !in_array( 'exms_instructor', (array) $user->roles ) &&
            !in_array( 'administrator', (array) $user->roles );
        } );
        $role_label = __( 'Users', 'wp_exams' );
        $roles = 'user';
    }
    
    $assigned_users   = exms_get_post_user_ids( $post_id, $roles );
    $unassigned_users = array_diff( $all_user_ids, $assigned_users );

    $paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $unassign_paged = isset($_GET['unassign_paged']) ? max(1, intval($_GET['unassign_paged'])) : 1;

    $assigned_offset   = ( $paged - 1 ) * $per_page;
    $unassigned_offset = ( $unassign_paged - 1 ) * $per_page;

    $assigned_paged   = array_slice( $assigned_users, $assigned_offset, $per_page );
    $unassigned_paged = array_slice( $unassigned_users, $unassigned_offset, $per_page );

    $assign_total_pages   = ceil( count( $assigned_users ) / $per_page );
    $unassign_total_pages = ceil( count( $unassigned_users ) / $per_page );

    ob_start();
    ?>
    <div class="exms-sortable-box-wrap">

    <div class="exms-assign-box exms-unassign exms-sortable-lists exms-user-assign-box-left exms-assign-box-left exms-sortable-lists-current" data-relation="current" data-user-id="">
        
        <div class="exms-header">
            <div class="exms-title">
                <span class="exms-status-dot yellow"></span>
                <span><?php echo __( 'Unassigned', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?> </span>
            </div>
            <div class="exms-actions">
                <span class="exms-dots exms-dots-toggle" data-type="unassign">⋯</span>
                <div class="exms-dropdown-menu exms-dropdown-unassign" style="display: none;">
                    <button type="button" class="exms-dropdown-btn" data-target="user" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                        data-users='<?php echo esc_attr( wp_json_encode( $unassigned_paged ) ); ?>'
                        data-type="assigned" data-role="<?php echo $role_label ?>">
                        <?php echo __( 'Assign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $role_label ?? '' ) ); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="exms-search-input-wrap" style="position: relative;">
            <input 
                type="text" 
                class="exms-post-search-input"
                placeholder="<?php echo __( 'Search All', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?>"
                data-current-post-type="search_users"
                data-search-post-type="<?php echo esc_attr( $post_type ); ?>"
                data-type="un-assigned"
                data-post-id="<?php echo esc_attr( $post_id ); ?>"
            >
            <span class="dashicons dashicons-search"></span>
        </div>

        <div class="exms-input-sortable-wrap">
            <div class="exms-sortable-items-wrap exms-user-sortable-items-wrap-left exms-user-sortable-pagination-wrap-left" data-post-type="" data-name="exms_unassign_items">
                <?php if ( ! empty( $unassigned_paged ) ) : ?>
                    <?php foreach ( $unassigned_paged as $user_id ) : ?>
                        <div class="exms-sortable-item" data-name="exms_unassign_items" data-name-key="current">
                            <span class="exms-post-title">
                                <a href="<?php echo admin_url( 'user-edit.php?user_id=' . $user_id ); ?>" target="_blank" class="exms-sortable-ui-link">
                                    <?php echo exms_get_user_name( $user_id ); ?>
                                </a>
                            </span>
                            <div class="exms-img-parent">
                                <img src="<?php echo esc_url( get_avatar_url( $user_id ) ); ?>" class="exms-avatar" />
                                <button class="exms-drag-icon" data-name="exms_unassign_items" data-target="user"><span>&#8594;</span></button>
                            </div>
                            <input type="hidden" class="exms-assign-unassign-id" value="<?php echo $user_id; ?>">
                            <input type="hidden" name="exms_current_relation" value="<?php echo esc_attr( $post_type ); ?>">
                            <input type="hidden" name="exms_user_name" value="<?php echo esc_attr( $roles ); ?>">
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="exms-post-not-found"><?php 
                        $role_text = $role_label ? esc_html( $role_label ) . ' ' : '';
                        echo __( 'No ', 'wp_exams' ) . $role_text . __( 'Unassigned.', 'wp_exams' );

                        if ( empty( $all_user_ids ) && current_user_can( 'administrator' ) ) {
                            $add_user_url = admin_url( 'user-new.php' );
                            echo ' <a href="' . esc_url( $add_user_url ) . '" target="_blank">' . __( 'Create New ', 'wp_exams' ) . $role_text . '</a>';
                        } ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php
        echo EXMS_PR_Fn::exms_sortable_unassign_pagination_html( $unassign_total_pages, $post_id, $unassign_paged,'user', 'user' );
        ?>
    </div>

    <!-- Assigned Users Box -->
    <div class="exms-assign-box exms-assign exms-sortable-lists exms-user-assign-box-right exms-assign-box-right exms-sortable-lists-current" data-relation="current" data-user-id="">
        
        <!-- Assign Heading -->
        <div class="exms-header">
            <div class="exms-title">
                <span class="exms-status-dot green"></span>
                <span><?php echo __( 'Assigned', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?></span>
            </div>
            <div class="exms-actions">
                <span class="exms-dots exms-dots-toggle" data-type="assign">⋯</span>
                <div class="exms-dropdown-menu exms-dropdown-assign" style="display: none;">
                    <button type="button" class="exms-dropdown-btn" data-target="user" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                        data-users='<?php echo esc_attr( wp_json_encode( $assigned_paged ) ); ?>'
                        data-type="unassigned" data-role="<?php echo $role_label ?>">
                        <?php echo __( 'Unassign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $role_label ?? '' ) ); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="exms-search-input-wrap" style="position: relative;">
            <input 
                type="text" 
                class="exms-post-search-input"
                placeholder="<?php echo __( 'Search All', 'wp_exams' ) . ( $role_label ? ' ' . esc_html( $role_label ) : '' ); ?>"
                data-current-post-type="search_users"
                data-search-post-type="<?php echo esc_attr( $post_type ); ?>"
                data-type="assigned"
                data-post-id="<?php echo esc_attr( $post_id ); ?>"
            >
            <span class="dashicons dashicons-search"></span>
        </div>

        <div class="exms-input-sortable-wrap">
            <div class="exms-sortable-items-wrap exms-user-sortable-items-wrap-right exms-user-sortable-pagination-wrap-right" data-name="exms_assign_items">
                <?php if ( ! empty( $assigned_paged ) ) : ?>
                    <?php foreach ( $assigned_paged as $user_id ) : ?>
                        <div class="exms-sortable-item" data-name="exms_assign_items" data-name-key="current">
                            <span class="exms-post-title">
                                <button class="exms-drag-icon" data-name="exms_assign_items" data-target="user"><span>&#8592;</span></button>
                                <a href="<?php echo admin_url( 'user-edit.php?user_id=' . $user_id ); ?>" target="_blank" class="exms-sortable-ui-link">
                                    <?php echo exms_get_user_name( $user_id ); ?>
                                </a>
                            </span>
                            <div class="exms-img-parent">
                            <img src="<?php echo esc_url( get_avatar_url( $user_id ) ); ?>" class="exms-avatar" />
                            </div>
                            <input type="hidden" class="exms-assign-unassign-id" value="<?php echo $user_id; ?>">
                            <input type="hidden" name="exms_current_relation" value="<?php echo esc_attr( $post_type ); ?>">
                            <input type="hidden" name="exms_user_name" value="<?php echo esc_attr( $roles ); ?>">
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="exms-post-not-found"><?php 
                        $role_text = $role_label ? esc_html( $role_label ) . ' ' : '';
                        echo __( 'No ', 'wp_exams' ) . $role_text . __( 'Assigned.', 'wp_exams' );

                        if ( empty( $all_user_ids ) && current_user_can( 'administrator' ) ) {
                            $add_user_url = admin_url( 'user-new.php' );
                            echo ' <a href="' . esc_url( $add_user_url ) . '" target="_blank">' . __( 'Create New ', 'wp_exams' ) . $role_text . '</a>';
                        } ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php
        echo EXMS_PR_Fn::exms_sortable_assign_pagination_html( $assign_total_pages, $post_id, $paged, 'user', 'user' );
        ?>
    </div>

    <div class="exms-clear-both"></div>
</div>

    <?php
    return ob_get_clean();
}

/**
 * Post assign to user html content
 * 
 * @param $post_type
 */
function exms_post_assign_to_user_html( $user_id, $post_type ) {

    if( empty( $post_type ) ) {
        return false;
    }
    ?>
    
    <!-- Assign and un-assign quizzes to the user -->
    <div class="exms-sortable-box-wrap">

        <?php 
        echo EXMS_PR_Fn::get_post_relation_heading( $post_type ); 

        $quiz_ids = exms_get_user_post_ids( $user_id, $post_type );
        $quizzes = EXMS_PR_Fn::exms_get_query_posts( $post_type, $quiz_ids );

        /* Next post count */
        $next_page_post = EXMS_PR_Fn::exms_get_query_posts( $post_type, $quiz_ids, 2 );
        $post_count = count( $next_page_post->posts ) ? count( $next_page_post->posts ) : 0;
        /* Next post count */

        $un_assing_style = $post_count == 0 ? 'style="visibility: hidden"' : '';
        $assing_style = count( $quiz_ids ) <= 10 ? 'style="visibility: hidden"' : '';

        ?>
        
        <div class="exms-sortable-lists exms-assign-box-left exms-sortable-lists-current" data-relation="current" data-user-id="<?php echo $user_id; ?>">
            <div class="exms-input-sortable-wrap">
                <!-- Search input html -->
                <?php echo EXMS_PR_Fn::exms_search_input_html( '', '', $post_type, 'un-assigned' ); ?>
                <!-- /Search input html -->

                <!-- Sortable un-assign html -->
                <?php echo EXMS_PR_Fn::exms_sortable_unassign_html( $quizzes, $post_type, 'current', $post_type ); ?>
                <!-- Sortable un-assign html -->
            </div>
            <!-- Sortable pagination html -->
            <?php echo EXMS_PR_Fn::exms_sortable_pagination_html( $un_assing_style ); ?>
            <!-- /Sortable pagination html -->
        </div>

        <div class="exms-sortable-lists exms-assign-box-right exms-sortable-lists-current" data-relation="current" data-user-id="<?php echo $user_id; ?>">
            <div class="exms-input-sortable-wrap">

                <?php $quiz_ids = exms_get_user_post_ids( $user_id, $post_type, 0 ); ?>

                <!-- Search input html -->
                <?php echo EXMS_PR_Fn::exms_search_input_html( '', '', $post_type, 'assigned' ); ?>
                <!-- /Search input html -->

                <!-- Sortable un-assign html -->
                <?php echo EXMS_PR_Fn::exms_sortable_assign_html( $quiz_ids, 'true', 'current', $post_type ); ?>
                <!-- Sortable un-assign html -->
            </div>
            <!-- Sortable pagination html -->
            <?php echo EXMS_PR_Fn::exms_sortable_pagination_html( $assing_style ); ?>
            <!-- /Sortable pagination html -->
        </div>
        <div class="exms-clear-both"></div>
    </div>
    <!-- /Assign and un-assign quizzes to the user -->
    <?php
}

function exms_user_id_exists( $user ){

    global $wpdb;
    $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user) );
    if( $count == 1 ) { 
        return true; 
    } else { 
        return false; 
    }
}

/**
 * Get user enrolled quizzes
 *
 * @param Mixed     $user_id    User ID     
 * 
 * @return Array
 */
function exms_get_user_enrolled_quizzes( $user_id ) {

    global $wpdb;
    $table_name = EXMS_PR_Fn::exms_user_post_relations_table();

    $quiz_ids = [];
    $meta = $wpdb->get_results( " SELECT post_id FROM $table_name 
        WHERE user_id = $user_id AND post_type = 'exms_quizzes' ORDER BY time ASC " );

    if( ! empty( $meta ) && ! is_null( $meta ) ) {
        $quiz_ids = array_map( 'intval', array_column( $meta, 'post_id' ) );
    }

    return $quiz_ids;
}

/**
 * Get user enrolled posts
 *
 * @param Mixed     $user_id    User ID     
 * 
 * @return Array
 */
function exms_get_user_enrolled_posts( $user_id, $post_id ) {

    global $wpdb;
    $table_name = EXMS_PR_Fn::exms_user_post_relations_table();

    $meta = $wpdb->get_results( " SELECT post_id FROM $table_name 
        WHERE user_id = $user_id AND post_id = $post_id ORDER BY time ASC " );
    
    if( count( $meta ) > 0 ) {
        return true;
    }

    return false;
}

/**
 * Get structure post id
 * 
 * @param $post_id
 */
function exms_get_parent_structure_id( $parent_post_type, $quiz_id ) {
    global $wpdb;
    $table_name = EXMS_PR_Fn::exms_relation_table_name();
    $post_type = get_post_type( $quiz_id );
    $post_id = $wpdb->get_var( " SELECT parent_post_id
        FROM $table_name 
        WHERE parent_post_type = '$parent_post_type' and child_post_id = $quiz_id and assigned_post_type = '$post_type'" );

    if( ! empty( $post_id ) && ! is_null( $post_id ) ) {
        
        return $post_id;
    }

    return 0;
}

/**
 * Enroll user on the post 
 * 
 * @param $user_id
 * @param $post_id
 */
function exms_enroll_user_on_post( $user_id, $post_id, $time = '' ) {

    if( empty( $time ) ) {
        $time = time();
    }

    echo EXMS_PR_Fn::exms_insert_user_assign_post( $post_id, $user_id, $time );
}

/**
 * Un-enroll user on the post
 * 
 * @param $user_id
 * @param $post_id
 */
function exms_unenroll_user_on_post( $user_id, $post_id ) {

    global $wpdb;
    $table_name = EXMS_PR_Fn::exms_user_post_relations_table();
    $post_type = get_post_type( $post_id );

    $wpdb->query( 
        "DELETE FROM $table_name
         WHERE user_id = $user_id
         AND post_type = '$post_type' 
         AND post_id = $post_id "
    );

    /**
     * Fires after un-enroll user on the post
     * 
     * @param $user_id
     * @param $post_id
     * @param current time ( timestamp )
     * @param false ( means user un-assign in the post )
     */
    do_action( 'exms_assign_user_on_post', $user_id, $post_id, time(), false );
}

/**
 * Check wheater user is in post
 * 
 * @param $user_id
 * @param $post_id
 * @return bool
 */
function exms_is_user_in_post( $user_id = 0, $post_id = 0 ) {

    global $wpdb;

    $is_access = false;

    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) 
            FROM {$wpdb->prefix}exms_user_enrollments 
            WHERE user_id = %d 
            AND post_id = %d",
            $user_id,
            $post_id
        )
    );

    if( $exists ) {
        $is_access = true;
    }

    return $is_access;
}
// function exms_is_user_in_post_duplicate( $user_id = 0, $post_id = 0 ) {

//     global $wpdb;

//     $is_access = false;
//     $req_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
//     $path    = trim( parse_url( $req_uri, PHP_URL_PATH ), '/' );
//     $parts   = array_values( array_filter( explode( '/', $path ) ) );

//     $is_groups_context = ( isset($parts[0]) && $parts[0] === 'exms-groups' && count($parts) >= 3 );

//     if( $is_groups_context ) {

//         $group_slug = $parts[1];
//         $group_id = (int) $wpdb->get_var(
//             $wpdb->prepare(
//                 "SELECT ID
//                  FROM {$wpdb->posts}
//                  WHERE post_name = %s
//                    AND post_type = %s
//                    AND post_status IN ('publish')",
//                 $group_slug,
//                 'exms-groups'
//             )
//         );
        
//         if( $group_id > 0 ) {

//             $exists = (int) $wpdb->get_var(
//                 $wpdb->prepare(
//                     "SELECT COUNT(*)
//                      FROM {$wpdb->prefix}exms_user_enrollments
//                      WHERE user_id     = %d
//                        AND post_id     = %d
//                        AND enrolled_by = %d
//                        AND type        = %s",
//                     (int) $user_id,
//                     (int) $post_id,
//                     (int) $group_id,
//                     'group-student'
//                 )
//             );
//         } else {
//             $exists = 0;
//         }

//     } else {

//         $exists = (int) $wpdb->get_var(
//             $wpdb->prepare(
//                 "SELECT COUNT(*)
//                  FROM {$wpdb->prefix}exms_user_enrollments
//                  WHERE user_id = %d
//                    AND post_id = %d
//                    AND type = %s",
//                 (int) $user_id,
//                 (int) $post_id,
//                 "student"
//             )
//         );
//     }

//     if( $exists ) {
//         $is_access = true;
//     }
//     return $is_access;
// }


/**
 * create a function to user course data
 * 
 * @param $course_id
 * @param $user_id ( If no `user_id` is provided to the function, it will use the currently logged-in user’s ID. )
 */
function exms_get_user_data( $course_ids, $post_type, $user_id = 0 ) {

    global $wpdb;
    $table_name = $wpdb->prefix . 'exms_user_enrollments';

    if( empty( $course_ids ) ) {
        wp_die();
    }

    if( empty( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    $course_ids_string = implode( ',', $course_ids );

    $query = "
    SELECT *
    FROM {$table_name}
    WHERE user_id = {$user_id}
    AND post_type = %s
    AND post_id IN ( {$course_ids_string} )
    ";

    $query = $wpdb->prepare( $query, $post_type );
    $results = $wpdb->get_results( $query, ARRAY_A );

    return $results;
}