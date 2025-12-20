<?php
if( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp', 'exms_generate_certificate' );

function exms_generate_certificate() {
    
    if( is_admin() ) {
        return false;
    }

    if( ! is_user_logged_in() ) {
        return false;
    }
    
    $exms_certificate_awarded_user = isset( $_GET['wpd-details'] ) ? $_GET['wpd-details'] : '';
    $exms_certificate_awarded_user = substr( $exms_certificate_awarded_user, 0, -3 );
    $exms_certificate_awarded_user = substr( $exms_certificate_awarded_user, 3 );
    $user_id = get_current_user_id();

    if( $user_id != intval($exms_certificate_awarded_user) ) {
        return false;
    }

    $user_prifile = get_avatar( $user_id );
    $user_name = ucwords( exms_get_user_name( $user_id ) );
    $post_type = get_post_type();
    
    if( 'exms_certificates' != $post_type ) {
        return false;
    }

    $file_exits = file_exists( EXMS_INCLUDES_DIR . '/lib/PDF/tcpdf.php' );

    if( $file_exits ) {
        require_once EXMS_INCLUDES_DIR . '/lib/PDF/tcpdf.php';
    }

    $post_id = get_the_ID();
    $quiz_title = get_the_title( $post_id );
    $pdf_content = get_the_content( $post_id );
    $pdf_title = get_the_title( $post_id );
    $post_setting = Exms_Post_Types_Functions::exms_get_post_data( $post_id, 'exms_certificates_opts' );
    $page_size = isset( $post_setting['exms_pdf-page-size'] ) ? $post_setting['exms_pdf-page-size'] : 'LETTER';
    $pdf_page_format = isset( $post_setting['exms_pdf-page-orientation'] ) ? $post_setting['exms_pdf-page-orientation'] : 'A4';
    $certificate_title = isset( $post_setting['exms_certificate-title'] ) ? $post_setting['exms_certificate-title'] : '';
    $certificate_user_name = isset( $post_setting['exms_certificate-user-name'] ) ? $post_setting['exms_certificate-user-name'] : '';
    $certificate_content = isset( $post_setting['exms_certificate-content'] ) ? $post_setting['exms_certificate-content'] : '';

require_once EXMS_INCLUDES_DIR . '/lib/PDF/tcpdf.php';

class MYPDF extends TCPDF {
    
    public function Header() {

        $certificate_id = get_the_ID();
        $cert_option = exms_get_post_options( $certificate_id );
        $selected_cert = isset( $cert_option['exms_selected-certificate'] ) ? $cert_option['exms_selected-certificate'] : '';
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->AutoPageBreak;
        $this->SetAutoPageBreak(false, 0);
        $img_file = EXMS_ASSETS_URL.'/imgs/CERT-'.$selected_cert.'.png';
        $v_one = 210;
        $v_two = 297;
        $page_format = isset( $cert_option['exms_pdf-page-orientation'] ) ? $cert_option['exms_pdf-page-orientation'] : '';
        
        $v_one = 210;
        $v_two = 297;

        if( 'l' == $page_format ) {
            $v_one = 297;
            $v_two = 210;
        }

        $this->Image($img_file, 0, 0, $v_one, $v_two, '', '', '', false, 300, '', false, false, 0);
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        $this->setPageMark();
    }

    public function Footer() {

        $certificate_id = get_the_ID();
        $cert_option = exms_get_post_options( $certificate_id );
        $selected_cert = isset( $cert_option['exms_selected-certificate'] ) ? $cert_option['exms_selected-certificate'] : '';
        $certificate_sign = isset( $cert_option['exms_certificate-sign'] ) ? $cert_option['exms_certificate-sign'] : '';
        $wp_signature = isset( $cert_option['exms_certificate-signed'] ) ? $cert_option['exms_certificate-signed'] : '';
        $this->SetY( -80 );

        $sig_top_margin = 180;
        
        if( isset ( $cert_option['exms_pdf-page-orientation'] ) && 'l' == $cert_option['exms_pdf-page-orientation'] ) {

            $sig_top_margin = 135;
        }

        if( $wp_signature ) {
            
            $this->Image( $wp_signature, 190, $sig_top_margin, 50, '', 'PNG', '', 'T', false, 300, 'C', false, false, 0, false, false, false );
        }
    }
}

$cert_option = exms_get_post_options( $post_id );
$selected_cert = isset( $cert_option['exms_selected-certificate'] ) ? $cert_option['exms_selected-certificate'] : '';

$title_color = isset( $cert_option['exms_title-color'] ) ? $cert_option['exms_title-color'] : '';
$title_size = isset( $cert_option['exms_title-f-size'] ) ? $cert_option['exms_title-f-size'] : '';
$u_color = isset( $cert_option['exms_user-name-color'] ) ? $cert_option['exms_user-name-color'] : '';
$u_size = isset( $cert_option['exms_user-f-size'] ) ? $cert_option['exms_user-f-size'] : '';

$pdf = new MYPDF( $pdf_page_format, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 051');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

$pdf->setHeaderFont( Array( PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN ) );

$pdf->SetDefaultMonospacedFont( PDF_FONT_MONOSPACED );

$pdf->SetMargins( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);

$pdf->SetFont('times', '', 48);

$pdf->AddPage();

/**
 * repace {user_name} tag current user name
 */
$replace_tags = [
    '{user_name}'  => $user_name,
];
$certificate_user_name = str_replace( array_keys( $replace_tags ), array_values( $replace_tags ), $certificate_user_name );

$html = '<br><div style="text-align: center; color:'.$title_color.'; font-size:'.$title_size.'px;">'.$certificate_title.'</div> 
<span style="font-size:'.$u_size.'px; text-align: center; color: '.$u_color.';">'.$certificate_user_name.'</span>
<span style="color:blue; text-align: center; font-size: 15px;">'.$certificate_content.'</span>
<p style="font-size: 12px; text-align: center;">'.$pdf_content.'</p>';
$pdf->writeHTML( $html, true, false, true, false, '' );

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// *** set signature appearance ***

// create content for signature (image and/or text)
// $wp_signature = isset( $cert_option['exms_certificate-signed'] ) ? $cert_option['exms_certificate-signed'] : '';

// $pdf->Image( $wp_signature, 180, 60, 15, 15, 'PNG');

// define active area for signature appearance
// $pdf->setSignatureAppearance(380, 60, 15, 15);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// *** set an empty signature appearance ***
// $pdf->addEmptySignatureAppearance(380, 80, 15, 15);

// ---------------------------------------------------------

$pdf->Output('example_051.pdf', 'I');
}
?>