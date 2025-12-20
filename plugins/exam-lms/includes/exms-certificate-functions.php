<?php
/**
 * WP Exams Badge functions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Award badges to user
 *
 * @param  Mixed $user_id           User ID 
 * @param  Mixed $download_link     Show download link
 *
 * @return HTML 
 */
function exms_get_badges_html( $badges_ids, $download_link ) {
    
    $boxed_html = '';

    foreach( $badges_ids as $badge_id ) {
                
        $options = exms_get_post_options( $badge_id );
        $badge_img_url = isset( $options['exms_badges_image_url'] ) ? esc_url( $options['exms_badges_image_url'] ) : '';  
        $badge_image = $badge_img_url != 'http://undefined_images' ? '<img src="'.$badge_img_url.'" />' : '';           

        $boxed_html .= '<div class="exms-badges-wrapper">';
        $boxed_html .= '<div class="exms-badge-box">';
        $boxed_html .= '<div class="exms-badge-image">'.$badge_image.'</div>';
        $boxed_html .= '<p>'.ucwords( get_the_title( $badge_id ) ).'</p>';
        if( $download_link ) {

            $boxed_html .= '<p><a href="'.site_url().'/?exms_badge_pdf='.$badge_id.'" target="_blank">'.__( 'Download', 'exms' ).'</a></p>';
        }
        $boxed_html .= '</div>';
        $boxed_html .= '</div>';
    }

    return $boxed_html;
}

/**
 * Award badges to user
 *
 * @param  Mixed $user_id   User ID 
 * @param  Array $badges    Post ids of badges 
 */
function exms_award_badges_to_user( $user_id, $badges ) {

    if( $badges ) {

        update_user_meta( $user_id, 'exms_user_badges', $badges );
    }
}

/**
 * Get user awarded badges
 * 
 * @param Mixed     $user_id    User ID
 * @return HTML
 */
function exms_get_user_awarded_badges( $user_id ) {

    $badges_ids = get_user_meta( $user_id, 'exms_user_badges', true );
    $boxed_html = '';

    if( $badges_ids ) {
        
        $boxed_html = exms_get_badges_html( $badges_ids, false );

    } else {

        $boxed_html .=  '<p>'.__( 'No badges yet', 'exms' ).'</p>';
    }
    return $boxed_html; 
}

/**
 * create a function to get all certificate id
 */
function exms_get_all_certificate_ids() {

    $args = [
        'post_type'     => 'exms_certificates',
        'numberposts'   => -1,
        'post_status'   => 'publish',
        'fields'        => 'ids'
    ];

    $certificates = get_posts( $args );
    return $certificates;
}