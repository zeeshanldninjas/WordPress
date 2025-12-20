<?php
/**
 * WP Exams core functions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

class Exms_Core_Functions {

    /**
     * @var self
     */
    private static $instance;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof Exms_Core_Functions ) ) {

            self::$instance = new Exms_Core_Functions;
        }

        return self::$instance;
    }

    /**
     * Call method to save db to options table
     *
     * @param $option_name
     * @param $data
     */
    public static function save_options( $option_name, $data ) {

        self::$instance->exms_save_options( $option_name, $data );
    }

    /**
     * Call method to get options table data
     *
     * @param $option_name
     * @param $data
     */
    public static function get_options( $option_name ) {

        return self::$instance->exms_get_options( $option_name );
    }

    /**
     * Save data to options table
     *
     * @param $option_name
     * @param $data
     */
    private function exms_save_options( $option_name, $data ) {

        update_option( 'exms_' . $option_name, $data );
    }

    /**
     * Fetch data to options table
     *
     * @param $option_name
     * @param $data
     */
    private function exms_get_options( $option_name ) {
       
        return get_option( 'exms_' . $option_name );
    }
}

Exms_Core_Functions::instance();

/**
 * Core function to get/use all classes functions
 */
function wp_exams() { 
    $classes = ( object ) array(
        'dbpmttran'         => class_exists( 'EXMS_DB_Payment_transation' ) ? new EXMS_DB_Payment_transation : false,
        'dbpostcomp'        => class_exists( 'EXMS_DB_USER_PROGRESS_TRACKING' ) ? new EXMS_DB_USER_PROGRESS_TRACKING : false,
        'dbpostrel'         => class_exists( 'EXMS_DB_POST_RELATIONSHIP' ) ? new EXMS_DB_POST_RELATIONSHIP : false,
        'dbqstres'          => class_exists( 'EXMS_DB_QUESTION_RESULTS' ) ? new EXMS_DB_QUESTION_RESULTS : false,
        'dbquizres'         => class_exists( 'EXMS_DB_QUIZ_RESULTS' ) ? new EXMS_DB_QUIZ_RESULTS : false,
        'dbuploads'         => class_exists( 'EXMS_DB_Uploads' ) ? new EXMS_DB_Uploads : false,
        'dbuserrel'         => class_exists( 'EXMS_DB_USER_POST_RELATIONS' ) ? new EXMS_DB_USER_POST_RELATIONS : false,
        'db'                => class_exists( 'EXMS_DB' ) ? new EXMS_DB : false,
        'selector'          => class_exists( 'EXMS_Assignment_Selector' ) ? new EXMS_Assignment_Selector : false
    );
    
    return $classes;
}

/**
 * Check if template file exist in active theme
 *
 * @param String    $template_path      Relative path from WP EXAMS template folder
 */
function exms_is_overriden_template( $template_path ) {

    if( file_exists( EXMS_THEME_TEMPLATES_DIR . $template_path ) ) {

        return true;
    }
    return false;
}

/**
 * Include WP EXAMS template
 *
 * @param String    $file   Relative path from WP EXAMS template folder
 * @param Array     $atts   Pass parameters to included template 
 */
function exms_include_template( $file, $atts ) {

    $template = exms_is_overriden_template( $file ) ? EXMS_THEME_TEMPLATES_DIR . $file : EXMS_TEMPLATES_DIR . $file;

    /**
     * Add quiz shortcode template if exists
     */
    if( file_exists( $template ) ) {

        require_once apply_filters( 'exms_template_include', $template );    
    } 
}

/**
 * Product payment html content
 */
function exms_payment_html_content( $user_id, $product_id, $currency, $price, $price_type, $subs_days, $payee_email ) {
    
    $exms_options = get_option( 'exms_settings' );
    
    ob_start();
    $stripe_enable          = $exms_options['stripe_enable'];
    $paypal_enable          = $exms_options['paypal_enable'];
    
    $stripe_redirect_url    = $exms_options['stripe_redirect_url'];
    $stripe_currency        = isset($exms_options['stripe_currency']) ? $exms_options['stripe_currency'] : '';
    $stripe_vender_email    = isset($exms_options['stripe_vender_email']) ? $exms_options['stripe_vender_email'] : '';
    $stripe_api_key         = isset($exms_options['stripe_api_key']) ? $exms_options['stripe_api_key'] : '';
    $stripe_client_secret   = isset($exms_options['stripe_client_secret']) ? $exms_options['stripe_client_secret'] : '';

    ?>
    <div class="exms-product-wrap">
        <div class="exms-product-sections">
            <div class="exms-section-heading"><?php _e( 'Status', 'exms' ); ?></div>
            <div class="exms-enrollment"><?php _e( 'Not Enrolled', 'exms' ); ?></div>
        </div>
        <div class="exms-product-sections">
            <div class="exms-section-heading"><?php _e( 'Price', 'exms' ); ?></div>
            <div class="exms-product-price">

                <?php 
                if( 'free' == $price_type ) {

                    ?>
                    <span class="exms-currency"><?php echo __( 'Free', 'exms' ); ?></span>
                    <?php

                } else {

                    ?>
                    <span class="exms-currency"><?php echo $currency; ?></span>
                    <span class="exms-price"><?php echo $price; ?></span>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="exms-pay-info exms-quiz-pay-button" data-user-id="<?php echo $user_id; ?>" 
            data-post-id="<?php echo $product_id; ?>" 
            data-price="<?php echo $price; ?>"
            data-quiz-type="<?php echo $price_type; ?>"
            data-subs-days="<?php echo $subs_days; ?>"
            data-payee-email="<?php echo $payee_email; ?>" >
            <!-- <div class="exms-section-heading"><?php _e( 'Get Started', 'exms' ); ?></div> -->
            <?php 
                if( ! is_user_logged_in() ) {
                    ?>
                        <div class="exms-login">
                            <a href="<?php echo wp_login_url(); ?>"><?php _e( 'Login', 'exms' ); ?></a>
                        </div>
                    <?php
                } else {
                    if( 'free' == $price_type ) {
                        ?>
                            <form method="post" action="<?php echo get_permalink(); ?>">
                                <?php echo wp_nonce_field( 'exms_free_nonce', 'exms_free_nonce_fields' ); ?>
                                <input type="hidden" name="action" value="exms_free_type_action">
                                <button type="submit" name="exms_assign_post" class="free-button-container"><?php echo __( 'Click here', 'exms' ); ?></button>
                            </form>
                        <?php
                    } else {
                            if( $stripe_enable == 'on' ||  $paypal_enable == 'on' ) { ?>
                                <div class="dropdown">
                                    <button class="dropbtn payment-button-container"><?php echo __( 'Purchase', 'exms' ); ?></button>
                                </div>
                                <div style="display: none;" class="exms-pop-outer">
                                    <div class="exms-pop-inner"> 
                                        <div>
                                            <button style="float:right;padding:5px;" class="exms-close">X</button>
                                            <div style="clear:both;display: none;">&nbsp;</div>
                                        </div>
                                        <div id="payment-box" class="exms-stripe-form-wrapper">
                                            <div class="exms-payment-tab">
                                                <?php if( $paypal_enable == 'on' ) { ?>
                                                    <button class="exms-payment-tablinks" data-id="exms-payment-paypal"><?php echo __( 'Paypal', 'exms' );?></button>
                                                <?php } ?>
                                                <?php if( $stripe_enable == 'on' ) { ?>
                                                    <button class="exms-payment-tablinks" data-id="exms-payment-stripe"><?php echo __( 'Stripe', 'exms' );?></button>
                                                <?php } ?>
                                            </div>
                                            <?php if( $paypal_enable == 'on' ) { ?>
                                                <div id="exms-payment-paypal" class="exms-payment-tabcontent">
                                                    <div id="paypal-button-container"></div>
                                                </div>
                                            <?php } ?>
                                            <?php if( $stripe_enable == 'on' ) { ?>
                                                <script src="https://js.stripe.com/v2/"></script> 
                                                <div id="exms-payment-stripe" class="exms-payment-tabcontent"  <?php if( $paypal_enable == 'on' ) { ?> style="display:none"<?php } ?>>
                                                    <form id="frmStripePayment" method="post">
                                                        <div class="field-row">
                                                            <label class="exms-stripe-form-block-label"><?php echo __( 'Name', 'exms' );?></label>
                                                            <input type="text" id="name" name="name" value="abbas" class="demoInputBox">
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="exms-stripe-form-block-label"><?php echo __( 'Email', 'exms' );?></label>
                                                            <input type="text" id="email" value="coordinator947@gmail.com" name="email" class="demoInputBox">
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="exms-stripe-form-block-label"><?php echo __( 'Card Number', 'exms' );?></label>
                                                            <input type="text" id="card-number" value="4242424242424242" name="card-number" class="demoInputBox">
                                                        </div>
                                                        <div class="field-row">
                                                            <label><?php echo __( 'Card Expiry', 'exms' );?></label>
                                                            <div class="contact-row column-right">
                                                                <select name="month" id="month" class="demoSelectBox">
                                                                    <?php for( $i=1;$i<=12;$i++){ ?>
                                                                        <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?>"><?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?></option>
                                                                    <?php } ?>
                                                                </select> 
                                                                <select name="year" id="year" class="demoSelectBox">
                                                                    <?php for( $i=intval(date('Y'));$i<=intval(date('Y'))+15;$i++){ ?>
                                                                        <option value="<?php echo $i;?>" <?php echo $i==25?'selected':'';?> ><?php echo $i;?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div> 
                                                        <div class="field-row">
                                                            <label><?php echo __( 'CVC', 'exms' );?></label>
                                                            <input type="text" name="cvc" value="123" id="cvc" class="demoInputBox cvv-input">
                                                        </div>
                                                        <div>
                                                            <input type="button" name="pay_now" value="<?php echo __( 'submitpayment', 'exms' );?>" id="submit-btn" class="submit-btn-action button btn btn-primary">
                                                            <div id="exms-stripe-error-message"></div>
                                                        </div>
                                                        <input type="hidden" name="item_name" value="asdasd" />
                                                        <input type="hidden" name="item_number" value="<?php echo $product_id;?>" />
                                                        <input type="hidden" name="custom" value="<?php echo $user_id;?>"  />
                                                        <input type="hidden" name="currency_code" value="USD" />
                                                        <input type="hidden" name="amount" value="<?php echo $price;?>" />
                                                        
                                                    </form> 
                                                </div>
                                            <?php } ?>
                                         </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php
                    }
                }
            ?>
        </div>
    </div>
    <?php

    $content = ob_get_contents();
    ob_get_clean();
    return $content;
}

function exms_has_children( $post_id ) {
    global $wpdb;
    $relation_table = $wpdb->prefix . 'exms_post_relationship';
    if ( ! $post_id ) {
        return false;
    }

    $child_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $relation_table WHERE parent_post_id = %d",
            $post_id
        )
    );

    return $child_exists > 0;
}

/**
 * Render the HTML for a child step inside a course/module structure.
 *
 * This function generates the markup for displaying a single child step,
 * including progress status, lock state (if the user is not enrolled),
 * and optional expandable child steps if present.
 *
 * @param int    $post_id     The ID of the child step (post).
 * @param int    $course_id   The ID of the related course.
 * @param string $class       Additional CSS classes to apply to the wrapper.
 * @param string $final_slug  The base slug/URL path to build the child step permalink.
 *
 * @return string             The rendered HTML for the child step.
 */
function exms_render_child_step_html( $post_id, $course_id, $class = '', $final_slug = '' ) {

    global $wpdb;

    if ( ! $post_id ) {
        return '';
    }

    $post_type  = get_post_type( $post_id );
    $title      = get_the_title( $post_id );
    $post_slug  = get_post_field( 'post_name', $post_id );
    $structure  = get_option( 'exms_post_types', true );
    $label      = isset( $structure[ $post_type ]['plural_name'] ) ? $structure[ $post_type ]['plural_name'] : ucfirst( $post_type );
    $icon_label = strtoupper( substr( $label, 0, 1 ) );

    $final_slug = trim( str_replace( home_url(), '', $final_slug ), '/' );

    $permalink  = home_url( trailingslashit( $final_slug ) . $post_slug );

    $children = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT child_post_id 
             FROM {$wpdb->prefix}exms_post_relationship
             WHERE parent_post_id = %d AND parent_post_type = %s",
            $post_id,
            $post_type
        )
    );

    $has_children = ! empty( $children );
    $is_enrolled  = exms_is_user_in_post( get_current_user_id(), $course_id );
    $user_id      = get_current_user_id();
    $progress     = exms_calculate_progress( $post_id, $user_id, $course_id, $permalink );

    $toggle_class = $has_children ? 'js-exms-toggle-step' : '';
    $arrow_icon   = $has_children ? '<span class="exms-course-child__toggle-icon ' . esc_attr( $toggle_class ) . '"><span class="dashicons dashicons-arrow-down-alt2"></span></span>' : '';

    ob_start();
    ?>
    <div id="exms-wrapper-handler-<?php echo $post_id; ?>" class="exms-child-steps <?php echo esc_attr( $class ); ?>"
         data-post-id="<?php echo esc_attr( $post_id ); ?>"
         data-post_slug="<?php echo esc_attr( $post_slug ); ?>">
        <div class="exms-course-child__left">
            <div class="exms-course-child__content">
                <div class="exms-legacy-course__lesson-status <?php echo $progress >= 100 ? 'complete' : ''; ?>"
                     style="--percent: <?php echo esc_attr( round( $progress ) ); ?>;">
                    <?php if ( $progress >= 100 ) { ?>
                        <span class="dashicons dashicons-yes"></span>
                    <?php } ?>
                </div>
                <span class="exms-course-child__icon"><?php echo esc_html( $icon_label ); ?></span>

                <?php if ( $is_enrolled ) { ?>
                    <a href="<?php echo esc_url( $permalink ); ?>" class="exms-course-child__title">
                        <?php echo esc_html( $title ); ?>
                    </a>
                <?php } else { ?>
                    <span class="exms-course-module_child__lock exms-tooltip-parent">
                        <span class="dashicons dashicons-lock"></span>
                        <span class="exms-tooltip"><?php echo __( 'You don’t have access to this content yet. Please enroll in the course to unlock this step.', 'exms'); ?></span>
                    </span>
                    <span class="exms-course-child__title exms-disabled"><?php echo esc_html( $title ); ?></span>
                <?php } ?>
            </div>
            <?php echo $arrow_icon; ?>
        </div>
        <?php if ( $has_children ) { ?>
            <div class="exms-course-steps__child js-exms-child-container" style="display: none;"></div>
        <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render Step Html
 *
 * Generates the markup for a step/module including progress,
 * enrollment checks, and child step handling.
 *
 * @param int     $post_id            The step/post ID.
 * @param string  $class              Additional CSS classes for wrapper.
 * @param bool    $is_course_progress Whether we are rendering inside course progress view.
 * @param int     $parent_id          The parent step ID (used when course progress view).
 *
 * @return string Rendered HTML for the step.
 */
function exms_render_step_html( $post_id, $class = '', $is_course_progress = false, $parent_id = 0 ) {
    global $wpdb;

    if ( ! $post_id ) {
        return '';
    }

    $post_type  = get_post_type( $post_id );
    $course_id  = exms_get_course_id();
    $course_post_type = get_post_type( $course_id );
    $course_slug_without_prefix = str_replace( "exms-", "", $course_post_type );
    $post_slug_without_prefix   = str_replace( "exms-", "", $post_type );
    $title      = get_the_title( $post_id );
    $course_slug = get_post_field( 'post_name', $course_id );
    $post_slug   = get_post_field( 'post_name', $post_id );
    $current_page_url = exms_get_current_url();
    $permalink   = $current_page_url.$post_slug;
    $permalink   = preg_replace('#/([^/]+)/\1(/|$)#', '/$1$2', $permalink);

    if ( $is_course_progress == true ) {
        $parent_slug = get_post_field( 'post_name', $parent_id );
        $permalink = home_url( $course_slug_without_prefix.'/'.$parent_slug. '/'. $post_slug);              
    }

    $structure   = get_option( 'exms_post_types', true );
    $label       = isset( $structure[ $post_type ]['plural_name'] ) ? $structure[ $post_type ]['plural_name'] : ucfirst( $post_type );
    $icon_label  = strtoupper( substr( $label, 0, 1 ) );

    $children = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT child_post_id 
             FROM {$wpdb->prefix}exms_post_relationship
             WHERE parent_post_id = %d AND parent_post_type = %s",
            $post_id,
            $post_type
        )
    );

    $has_children = ! empty( $children );
    $clean_type   = str_replace( 'exms-', '', $post_type );

    $is_enrolled = exms_is_user_in_post( get_current_user_id(), $course_id );
    $wrapper_class = 'exms-' . $clean_type . '-wrapper';
    $active_class  = ( get_the_ID() == $post_id ) ? 'exms-active' : '';

    $user_id  = get_current_user_id();
    $progress = exms_calculate_progress( $post_id, $user_id, $course_id, $permalink );

    ob_start();
    ?>
    <div data-course_id = "<?php echo $course_id; ?>" class="exms-course-module js-exms-course-module <?php echo esc_attr( $wrapper_class . ' ' . $class . ' ' . $active_class ); ?>" data-post-id="<?php echo esc_attr( $post_id ); ?>">
        <div class="exms-course-module__header">
            <div class="exms-legacy-course__lesson-status <?php echo $progress >= 100 ? 'complete' : ''; ?>"
                 style="--percent: <?php echo esc_attr( round( $progress ) ); ?>;">
                <?php if ( $progress >= 100 ) { ?>
                    <span class="dashicons dashicons-yes"></span>
                <?php } ?>
            </div>
            <span class="exms-course-module__icon"><?php echo esc_html( $icon_label ); ?></span>

            <?php if ( $is_enrolled ) { ?>
                <a href="<?php echo esc_url( $permalink ); ?>" class="exms-course-module__title">
                    <?php echo esc_html( $title ); ?>
                </a>
            <?php } else { ?>
                <span class="exms-course-module__lock exms-tooltip-parent">
                    <span class="dashicons dashicons-lock"></span>
                    <span class="exms-tooltip"><?php echo __( 'You don’t have access to this content yet. Please enroll in the course to unlock this step.', 'exms'); ?></span>
                </span>
                <span class="exms-course-module__title exms-disabled"><?php echo esc_html( $title ); ?></span>
            <?php } ?>

            <?php if ( $has_children ) { ?>
                <span class="exms-course-module__toggle-icon js-exms-toggle-step">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </span>
            <?php } ?>
        </div>

        <?php if ( $has_children ) { ?>
            <div class="exms-course-module__lessons js-exms-child-container"></div>
        <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Calculate progress percentage for a step (skip post & page types)
 */
function exms_calculate_progress( $post_id, $user_id, $course_id, $final_slug = '', $existing_chain = [] ) {

    global $wpdb;
    if( ! $post_id || ! $user_id || ! $course_id ) {
        return 0;
    }

    $info = exms_get_post_settings( $course_id );
    $is_auto_parent_complete = isset( $info['progress_type'] ) ? $info['progress_type'] : '';

    if( ! empty( $existing_chain ) ) {
        $chain = $existing_chain;
    } else {
        if( empty( $final_slug ) ) {
            $current_url = exms_get_current_url();
            $current_url = str_replace( site_url(), '', $current_url );
            $final_slug  = $current_url;
        }

        $chain = [];
        if( $final_slug ) {
            $path  = parse_url( home_url( $final_slug ), PHP_URL_PATH );
            $slugs = explode( '/', trim( $path, '/' ) );

            foreach( $slugs as $slug ) {
                $post = get_page_by_path( $slug, OBJECT, get_post_types( [ 'public' => true ] ) );
                if( $post ) {
                    $ptype = get_post_type( $post->ID );

                    if( in_array( $ptype, [ 'post', 'page' ], true ) ) {
                        continue;
                    }

                    $chain[] = [
                        'id'   => $post->ID,
                        'type' => $ptype,
                    ];
                }
            }
        }
    }

    $direct_progress = exms_calculate_direct_progress( $post_id, $user_id, $course_id, $chain );
    if( $direct_progress == 1 ) {
        return 100;
    }

    $children = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT child_post_id 
             FROM {$wpdb->prefix}exms_post_relationship
             WHERE parent_post_id = %d",
            $post_id
        )
    );

    if( ! empty( $children ) ) {
        $completed = 0;

        foreach( $children as $child_id ) {
            $child_chain = array_merge( $chain, [
                [ 'id' => (int) $child_id, 'type' => get_post_type( $child_id ) ]
            ]);

            $child_progress = exms_calculate_direct_progress( $child_id, $user_id, $course_id, $child_chain );

            if( $child_progress < 100 ) {
                $child_progress = exms_calculate_progress( $child_id, $user_id, $course_id, '', $child_chain );
            }

            if( $child_progress == 100 ) {
                $completed++;
            }
        }

        if( $completed === count( $children ) ) {
            if ( $is_auto_parent_complete === 'on' ) {
                return 100;
            } else {
                return 99; 
            }
        }

        if( $completed > 0 ) {
            return intval( ( $completed / count( $children ) ) * 100 );
        }
    }

    return 0;
}

/**
 * Calculate overall course progress based on ALL assigned post types
 * (lessons, topics, quizzes, etc.), multiple levels deep.
 */
function exms_calculate_course_progress( $course_id, $user_id ) {
    global $wpdb;

    if( ! $course_id || ! $user_id ) {
        return 0;
    }

    $steps   = [];
    $queue   = [];
    $visited = [];

    $queue[] = [
        'id'    => (int) $course_id,
        'chain' => [
            [
                'id'   => (int) $course_id,
                'type' => get_post_type( $course_id ),
            ],
        ],
    ];

    while( ! empty( $queue ) ) {
        $current   = array_shift( $queue );
        $parent_id = $current['id'];
        $chain     = $current['chain'];

        $children = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT child_post_id 
                 FROM {$wpdb->prefix}exms_post_relationship
                 WHERE parent_post_id = %d",
                $parent_id
            )
        );

        if( empty( $children ) ) {
            continue;
        }

        foreach( $children as $child_id ) {
            $child_id = (int) $child_id;

            if( isset( $visited[ $child_id ] ) ) {
                continue;
            }
            $visited[ $child_id ] = true;

            $child_type  = get_post_type( $child_id );
            $child_chain = array_merge(
                $chain,
                [
                    [
                        'id'   => $child_id,
                        'type' => $child_type,
                    ],
                ]
            );

            $steps[] = [
                'id'    => $child_id,
                'chain' => $child_chain,
            ];

            $queue[] = [
                'id'    => $child_id,
                'chain' => $child_chain,
            ];
        }
    }

    if( empty( $steps ) ) {
        return 0;
    }

    $total     = count( $steps );
    $completed = 0;

    foreach( $steps as $step ) {
        $step_id    = $step['id'];
        $step_chain = $step['chain'];
        $progress = exms_calculate_progress( $step_id, $user_id, $course_id, '', $step_chain );
        if( $progress >= 99 ) {
            $completed++;
        }
    }

    if( $completed === $total ) {
        return 100;
    }
    return intval( ( $completed / $total ) * 100 );
}

/**
 * Calculate direct progress
 */
function exms_calculate_direct_progress( $post_id, $user_id, $course_id, $chain = [] ) {
    
    global $wpdb;
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT status 
             FROM {$wpdb->prefix}exms_user_progress_tracking
             WHERE user_id = %d 
               AND item_id = %d 
               AND course_id = %d",
            $user_id,
            $post_id,
            $course_id
        )
    );

    if ( empty( $rows ) ) {
        return 0;
    }

    foreach ( $rows as $row ) {
        return (int) $row->status;
    }

    return 0;
}

/**
 * Build nested permalink for a node using the BEST (longest) path
 * from the current course to this node, via the relationship table.
 *
 * This is a "canonical" URL used when we don't have an explicit chain.
 *
 * @param int $current_id The node we're building the URL for.
 * @return string
 */
function exms_build_nested_permalink( $current_id ) {
    $course_id  = (int) exms_get_course_id();
    $current_id = (int) $current_id;

    if( ! $course_id || ! $current_id ) {
        return '';
    }

    static $path_cache = array();

    if( isset( $path_cache[ $course_id ][ $current_id ] ) ) {
        $path_ids = $path_cache[ $course_id ][ $current_id ];
    } else {
        $visited = array();
        $memo    = array();

        $path_ids = exms_find_path_to_course( $current_id, $course_id, $visited, $memo );

        if( ! $path_ids ) {
            $path_ids = array( $current_id );
        }

        if( ! isset( $path_cache[ $course_id ] ) ) {
            $path_cache[ $course_id ] = array();
        }
        $path_cache[ $course_id ][ $current_id ] = $path_ids;
    }

    return exms_build_nested_permalink_for_chain( $path_ids );
}

/**
 * Recursively find the BEST (longest) path from the current course
 * to a node (post) using the exms_post_relationship table.
 *
 * Return array of IDs in order: [course_id, ..., parent_id, node_id]
 * or null if no path exists.
 *
 * No hard-coded post types; purely relationship based.
 *
 * @param int   $node_id
 * @param int   $course_id
 * @param array $visited   (for cycle protection)
 * @param array $memo      (local memoization)
 *
 * @return array|null
 */
function exms_find_path_to_course( $node_id, $course_id, &$visited, &$memo ) {
    global $wpdb;

    $node_id   = (int) $node_id;
    $course_id = (int) $course_id;

    if( isset( $memo[ $node_id ] ) ) {
        return $memo[ $node_id ];
    }

    if( $node_id === $course_id ) {
        return $memo[ $node_id ] = array( $course_id );
    }

    if( isset( $visited[ $node_id ] ) ) {
        return null;
    }

    $visited[ $node_id ] = true;
    $parents = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT parent_post_id
             FROM {$wpdb->prefix}exms_post_relationship
             WHERE child_post_id = %d
             ORDER BY id ASC",
            $node_id
        )
    );

    if( empty( $parents ) ) {
        return $memo[ $node_id ] = null;
    }

    $best_path = null;
    foreach( $parents as $parent_id ) {
        $parent_id = (int) $parent_id;

        $path = exms_find_path_to_course( $parent_id, $course_id, $visited, $memo );

        if( $path ) {
            $candidate = $path;
            $candidate[] = $node_id;

            if( ! $best_path || count( $candidate ) > count( $best_path ) ) {
                $best_path = $candidate;
            }
        }
    }
    return $memo[ $node_id ] = $best_path;
}

/**
 * Build nested permalink from an explicit chain of IDs:
 * [course_id, ..., parent_id, current_id]
 *
 * No globals, no hard-coded post types.
 *
 * @param int[] $ids
 * @return string
 */
function exms_build_nested_permalink_for_chain( array $ids ) {
    $ids = array_map( 'intval', $ids );
    $ids = array_values( array_unique( $ids ) );
    $slugs = array();
    foreach( $ids as $id ) {
        if( ! $id ) {
            continue;
        }
        $slug = get_post_field( 'post_name', $id );
        if( ! empty( $slug ) ) {
            $slugs[] = $slug;
        }
    }

    if( empty( $slugs ) ) {
        return '';
    }

    $base_segment = 'exms-courses';

    return trailingslashit(
        site_url(
            '/' . $base_segment . '/' . implode( '/', $slugs )
        )
    );
}

/**
 * Renders all course steps recursively (no AJAX).
 *
 * @param int    $parent_id          Parent post ID.
 * @param string $parent_post_type   Optional. Defaults to detected post type.
 * @param bool   $is_course_progress Optional. Used for progress mode.
 * @param array  $chain              Optional. IDs from course -> ... -> parent.
 * @return string
 * If no chain is provided, try to compute a chain from the current course
 * to this parent using the helper we already wrote (exms_find_path_to_course).
 *
 * This keeps behavior consistent when you start rendering
 * from a mid-level node (topic/lesson).
 */
function exms_render_all_course_steps( $parent_id, $parent_post_type = '', $is_course_progress = false, $chain = array() ) {
    global $wpdb;

    if( ! $parent_id ) {
        return '';
    }

    if( empty( $parent_post_type ) ) {
        $parent_post_type = get_post_type( $parent_id );
    }

    $structure = get_option( 'exms_post_types', true );
    if( empty( $structure ) ) {
        return '';
    }

    if( empty( $chain ) ) {
        $course_id = (int) exms_get_course_id();
        if( $course_id ) {
            $visited = array();
            $memo    = array();

            $path = exms_find_path_to_course( (int) $parent_id, $course_id, $visited, $memo );
            if( $path ) {
                $chain = $path;
            } else {
                $chain = array( (int) $parent_id );
            }
        } else {
            $chain = array( (int) $parent_id );
        }
    }

    $output = '';
    $rendered_ids = array();

    foreach( $structure as $post_type => $settings ) {
        $relationship_type = str_replace( 'exms-', '', $parent_post_type ) . '-' . str_replace( 'exms-', '', $post_type );

        $child_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT child_post_id FROM {$wpdb->prefix}exms_post_relationship
             WHERE parent_post_id = %d
               AND parent_post_type = %s
               AND assigned_post_type = %s
               AND relationship_type = %s",
            $parent_id, $parent_post_type, $post_type, $relationship_type
        ) );

        if( empty( $child_ids ) ) {
            continue;
        }

        foreach( $child_ids as $post_id ) {
            if( in_array( $post_id, $rendered_ids, true ) ) {
                continue;
            }

            $child_chain   = $chain;
            $child_chain[] = (int) $post_id;

            $output .= exms_render_step_html_recursive(
                $post_id,
                $is_course_progress,
                $parent_id,
                $child_chain
            );

            $rendered_ids[] = $post_id;
        }
    }
    return $output;
}

/**
 * Renders a single step/module with recursive child rendering.
 *
 * @param int   $post_id           The post ID.
 * @param bool  $is_course_progress Optional. Whether we are in progress view.
 * @param int   $parent_id         Optional. Parent post ID.
 * @param array $chain             Optional. IDs from course -> ... -> this $post_id.
 * @return string
 */
function exms_render_step_html_recursive( $post_id, $is_course_progress = false, $parent_id = 0, $chain = array() ) {
    global $wpdb;

    if( ! $post_id ) {
        return '';
    }

    $post_type  = get_post_type( $post_id );
    $course_id  = exms_get_course_id();
    $title      = get_the_title( $post_id );

    if( ! empty( $chain ) ) {
        $permalink = exms_build_nested_permalink_for_chain( $chain );
    } else {
        $permalink = exms_build_nested_permalink( $post_id );
    }

    $structure  = get_option( 'exms_post_types', true );
    $label      = isset( $structure[ $post_type ]['plural_name'] ) ? $structure[ $post_type ]['plural_name'] : ucfirst( $post_type );
    $icon_label = strtoupper( substr( $label, 0, 1 ) );

    $children = $wpdb->get_col( $wpdb->prepare(
        "SELECT child_post_id FROM {$wpdb->prefix}exms_post_relationship
         WHERE parent_post_id = %d AND parent_post_type = %s",
        $post_id, $post_type
    ) );

    $has_children = ! empty( $children );
    $is_enrolled  = exms_is_user_in_post( get_current_user_id(), $course_id );
    $progress     = exms_calculate_progress(
        $post_id,
        get_current_user_id(),
        $course_id,
        get_permalink( $post_id )
    );

    ob_start();
    ?>
    <div class="exms-course-module js-exms-course-module exms-<?php echo esc_attr( str_replace( 'exms-', '', $post_type ) ); ?>-wrapper"
         data-post-id="<?php echo esc_attr( $post_id ); ?>">
        <div class="exms-course-module__header">
            <div class="exms-legacy-course__lesson-status <?php echo $progress >= 100 ? 'complete' : ''; ?>"
                 style="--percent: <?php echo esc_attr( round( $progress ) ); ?>;">
                <?php if( $progress >= 100 ) { ?>
                    <span class="dashicons dashicons-yes"></span>
                <?php } ?>
            </div>
            <span class="exms-course-module__icon"><?php echo esc_html( $icon_label ); ?></span>
            <?php if( $is_enrolled ) { ?>
                <a href="<?php echo esc_url( $permalink ); ?>" class="exms-course-module__title">
                    <?php echo esc_html( $title ); ?>
                </a>
            <?php } else { ?>
                <span class="exms-course-module__lock exms-tooltip-parent">
                    <span class="dashicons dashicons-lock"></span>
                    <span class="exms-tooltip"><?php echo __( 'Enroll to access this content.', 'exms' ); ?></span>
                </span>
                <span class="exms-course-module__title exms-disabled"><?php echo esc_html( $title ); ?></span>
            <?php } ?>
            <?php if( $has_children ) { ?>
                <span class="exms-course-module__toggle-icon js-exms-toggle-step">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </span>
            <?php } ?>
        </div>

        <?php if( $has_children ) { ?>
            <div class="exms-course-module__lessons js-exms-child-container" style="display:none;">
                <?php
                foreach( $children as $child_id ) {
                    $child_chain   = $chain;
                    $child_chain[] = (int) $child_id;

                    echo exms_render_step_html_recursive(
                        $child_id,
                        $is_course_progress,
                        $post_id,
                        $child_chain
                    );
                }
                ?>
            </div>
        <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}


/**
 * Get full parent chain for the current page/post
 * (skip post & page post types)
 */
function exms_get_current_chain( $final_slug = '' ) {

    if ( empty( $final_slug ) ) {
        $current_url = exms_get_current_url();
        $current_url = str_replace( site_url(), '', $current_url );
        $final_slug  = $current_url;
    }

    $chain = [];

    if ( $final_slug ) {
        $path  = parse_url( home_url( $final_slug ), PHP_URL_PATH );
        $slugs = explode( '/', trim( $path, '/' ) );

        foreach ( $slugs as $slug ) {
            $post = get_page_by_path( $slug, OBJECT, get_post_types( [ 'public' => true ] ) );
            if ( $post ) {
                $ptype = get_post_type( $post->ID );

                if ( in_array( $ptype, [ 'post', 'page' ], true ) ) {
                    continue;
                }

                $chain[] = [
                    'id'   => $post->ID,
                    'type' => $ptype,
                ];
            }
        }
    }

    return array_values( $chain );
}

/**
 * create a function to get posts
 * @param $post_type
 */
function exms_get_all_posts( $post_type ) {

    if( ! $post_type ) {
        return;
    }

    $args = [
        'post_type'      => $post_type,
        'posts_per_page' => -1, 
        'post_status'    => 'publish',
    ];

    $post_ids = get_posts( $args );
    if( is_array( $post_ids ) && ! empty( $post_ids ) ) {
        $post_ids = array_column( $post_ids, 'ID' );
    }
    return $post_ids;
}

add_action( 'wp', 'exms_assign_free_type_course' );

function exms_assign_free_type_course() {

    if( isset( $_POST['exms_assign_post'] )
        && isset( $_POST['exms_free_nonce_fields'] ) 
        && wp_verify_nonce( $_POST['exms_free_nonce_fields'], 'exms_free_nonce') 
        && 'exms_free_type_action' == $_POST['action'] ) {

        echo exms_enroll_user_on_post( get_current_user_id(), get_the_ID() );
    }
}

/**
 * Retrieves the latest template part content from the database or fallback file.
 *
 * @param string $name The name of the template part.
 * @return string HTML content of the template part.
 */
function exms_get_template_part( $name ) {
    global $wpdb;

    $result = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT post_content FROM $wpdb->posts 
             WHERE post_name = %s AND post_type = %s AND post_status = %s 
             ORDER BY ID DESC LIMIT 1",
            $name,
            'wp_template_part',
            'publish'
        )
    );

    if ( $result ) {
        return $result->post_content;
    }

    $filename = get_template_directory() . '/parts/' . $name . '.html';
    if ( file_exists( $filename ) ) {
        $content = file_get_contents( $filename );
        return $content !== false ? $content : '<p>Could not read fallback template part.</p>';
    }

    return '';
}

/**
 * create a function to assign user into quiz 
 * 
 * @param $quiz_id
 * @param $user_id
 */
function exms_assign_user_into_post( $post_id, $user_id ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'exms_user_enrollments';
    $current_timestamp = current_time('mysql');

    $wpdb->insert(
        $table_name,
        [
            'user_id'           => $user_id,
            'post_id'           => $post_id,
            'post_type'         => 0,
            'enrolled_by'       => 0,
            'status'            => null,
            'progress_percent'  => 0,
            'start_date'        => '0000-00-00 00:00:00',
            'end_date'          => '0000-00-00 00:00:00',
            'created_timestamp' => $current_timestamp,
            'updated_timestamp' => $current_timestamp,
        ],
        [
            '%d', '%d', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s'
        ]
    );
}

/**
 * Get item type (post_type) by item_id
 *
 * @param int $item_id
 * @return string
 */
function exms_get_item_type( $item_id ) {
    if ( ! $item_id ) {
        return '';
    }

    $post_type = get_post_type( $item_id );

    return $post_type ? $post_type : '';
}

/**
 * Check if a step is complete (100%)
 */
function exms_is_step_complete( $post_id, $user_id, $course_id, $chain = [] ) {

    if ( empty( $chain ) ) {
        $chain = exms_get_current_chain();
    }

    $progress = exms_calculate_progress( $post_id, $user_id, $course_id, '', $chain );
    return ( $progress === 100 );
}

/** create a function to get post type
 * 
 * @param $slub
 */
function exms_get_post_type_by_slug( $slug ) {
    
    global $wpdb;

    return $wpdb->get_var( $wpdb->prepare(
        "SELECT post_type FROM {$wpdb->posts} WHERE post_name = %s LIMIT 1",
        $slug
    ) );
}

/**
 * create a function to get current url 
 */
function exms_get_current_url() {

    $scheme = is_ssl() ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $uri    = $_SERVER['REQUEST_URI'];
    $current_url = $scheme . '://' . $host . $uri;
    $current_url = str_replace('exms-courses', 'courses', $current_url);
    return $current_url;
}

/**
 * create a function to get course id
 */
function exms_get_course_id() {

    $current_permalink = exms_get_current_url();
    $replaced_url = str_replace( site_url(), '', $current_permalink );
    $course_id = 0;

    if( strpos( $replaced_url, '/courses/' ) !== false || strpos( $replaced_url, '/exms-courses/' ) !== false ) {
        $url_array = array_filter( explode( '/', str_replace( '/courses/', '', $replaced_url ) ) );
        if( strpos( $replaced_url, '/exms-courses/' ) !== false ) {
            $url_array = array_filter( explode( '/', str_replace( '/exms-courses/', '', $replaced_url ) ) );
        }
        $course_slug = isset( $url_array[0] ) ? $url_array[0] : 0;
        $post = get_page_by_path( $course_slug, OBJECT, 'exms-courses' );
        $course_id = isset( $post->ID ) ? $post->ID : 0;
    }
    return $course_id;
}
/**
 * Get Child step
 */
function exms_get_children( $parent_id ) {
    global $wpdb;
    $relation_table = $wpdb->prefix . 'exms_post_relationship';

    return $wpdb->get_col(
        $wpdb->prepare(
            "SELECT child_post_id FROM $relation_table WHERE parent_post_id = %d",
            $parent_id
        )
    );
}

/**
 * create a function to convert any time into second
 * 
 * @param $time
 */
function time_to_seconds( $time ) {

    $parts = explode( ":", $time );

    if ( count( $parts ) === 3 ) {
        list($hours, $minutes, $seconds) = $parts;
    } elseif ( count( $parts ) === 2 ) {
        $hours = 0;
        list( $minutes, $seconds ) = $parts;
    } else {
        return 0;
    }

    return ($hours * 3600) + ($minutes * 60) + $seconds;
}

/**
 * create a function to check logged in user is admin / student
 */
function exms_is_admin_user() {
    return current_user_can( 'administrator' );
}

/**
 * create a function to get skeleton loader
 */
function exms_get_skeleton_loader() {

    ob_start();
    ?>
    <div class="exms-skeleton-wrapper">
        <div class="exms-skeleton">
            <div class="exms-line"></div>
            <div class="exms-line exms-short"></div>
            <div class="exms-line"></div>
        </div>
    </div>
    <?php
    $content = ob_get_contents();
    ob_get_clean();
    return $content;
}