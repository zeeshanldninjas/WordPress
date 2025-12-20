<?php
/**
 * Custom Lesson Template
 */
get_header();
?>

  <main>
    <?php 
      $course_id = get_the_ID();
      echo do_shortcode( '[exms_course id='.$course_id.']' );
    ?>
  </main>

<?php
get_footer();
?>
