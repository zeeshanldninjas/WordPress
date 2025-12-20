<?php 

/** Course structure template in setup wizard */
if( ! defined( 'ABSPATH' ) ) exit;

$dynamic_steps = EXMS_Setup_Wizard::get_dynamic_structure_steps( 'exms_post_types' );
$custom_steps = EXMS_Setup_Wizard::get_dynamic_structure_steps( 'exms_custom_post_types' );
$has_dynamic = !empty($dynamic_steps);
$selected_structure = get_option( 'exms_selected_structure' );
?>
<div class="exms-setup-course exms-setup-p1">
    <?php echo EXMS_Setup_Functions::exms_course_structures_html( true ); ?>
    <div class="exms-setup-course-child">
        <div class="exms-course-structure <?php echo ( $selected_structure == 'default' ) ? 'active' : ''; ?> <?php echo ( $selected_structure == '' ) ? 'active' : ''; ?>" data-structure="default">
            <div class="exms-course-structure-heading">
                <h4> <?php _e( 'Default', 'exms' ); ?> </h4>
            </div>
            <div class="exms-course-structure-steps" data-structure-steps='<?php echo $has_dynamic && $selected_structure == 'default' ? json_encode($dynamic_steps) : '["Quizzes"]'; ?>'>
                <p> <?php _e( 'Quizzes', 'exms' ); ?> </p>
            </div>
        </div>
        <div class="exms-course-structure <?php echo ( $selected_structure == 'standard' ) ? 'active' : ''; ?>" data-structure="standard">
            <div class="exms-course-structure-heading">
                <h4> <?php _e( 'Standard', 'exms' ); ?> </h4>
                <span class="edit-structure-step-names dashicons dashicons-edit"></span>
            </div>
            <div class="exms-course-structure-steps" data-structure-steps='<?php echo $has_dynamic && $selected_structure == 'standard' ? json_encode($dynamic_steps) : '["Courses", "Lessons", "Topics", "Quizzes"]'; ?>'>
            <?php 
                    if ( $has_dynamic && $selected_structure == 'standard' ) {
                        foreach ( $dynamic_steps as $step ) {
                            echo '<p>' . esc_html($step) . '</p>';
                        }
                    } else {
                        echo '<p>' . __( 'Courses', 'exms' ) . '</p>';
                        echo '<p>' . __( 'Lessons', 'exms' ) . '</p>';
                        echo '<p>' . __( 'Topics', 'exms' ) . '</p>';
                        echo '<p>' . __( 'Quizzes', 'exms' ) . '</p>';
                    }
                ?>
            </div>
        </div>
        <div class="exms-course-structure <?php echo ( $selected_structure == 'modular' ) ? 'active' : ''; ?>" data-structure="modular">
            <div class="exms-course-structure-heading">
                <h4><?php _e( 'Modular Learning', 'exms' ); ?></h4>
                <span class="edit-structure-step-names dashicons dashicons-edit"></span>
            </div>
            <div class="exms-course-structure-steps" data-structure-steps='<?php echo $has_dynamic && $selected_structure == 'modular' ? json_encode($dynamic_steps) : '["Modular", "Units", "Quizzes"]'; ?>'>
                <?php 
                    if ( $has_dynamic && $selected_structure == 'modular' ) {
                        foreach ( $dynamic_steps as $step ) {
                            echo '<p>' . esc_html($step) . '</p>';
                        }
                    } else {
                        echo '<p>' . __( 'Modular', 'exms' ) . '</p>';
                        echo '<p>' . __( 'Units', 'exms' ) . '</p>';
                        echo '<p>' . __( 'Quizzes', 'exms' ) . '</p>';
                    }
                ?>
            </div>
        </div>


        <?php if ( $selected_structure === "custom" || !empty( $custom_steps ) ) { ?>
    <div class="exms-course-structure <?php echo ( $selected_structure === 'custom' ) ? 'active' : ''; ?>" data-structure="custom">
        <div class="exms-course-structure-heading">
            <h4><?php _e( 'Custom Course Structure', 'exms' ); ?></h4>
            <span class="delete-custom-structure dashicons dashicons-trash" title="Delete structure"></span>
            <span class="edit-structure-step-names dashicons dashicons-edit"></span>
        </div>
        <div class="exms-course-structure-steps"
            data-structure-steps='<?php 
                echo $selected_structure === 'custom' && $has_dynamic
                    ? json_encode( $dynamic_steps )
                    : ( !empty( $custom_steps ) ? json_encode( $custom_steps ) : '[]' );
            ?>'>
            <?php 
            $steps_to_display = [];

            if ( $selected_structure === 'custom' && $has_dynamic && is_array( $dynamic_steps ) ) {
                $steps_to_display = $dynamic_steps;
            } elseif ( !empty( $custom_steps ) && is_array( $custom_steps ) ) {
                $steps_to_display = $custom_steps;
            }

            if ( !empty( $steps_to_display ) ) {
                foreach ( $steps_to_display as $step ) {
                    echo '<p>' . esc_html( $step ) . '</p>';
                }
            } else {
                echo '<p>' . __( 'No dynamic steps available', 'exms' ) . '</p>';
            }
            ?>
        </div>
    </div>
<?php } ?>

    </div>
</div>