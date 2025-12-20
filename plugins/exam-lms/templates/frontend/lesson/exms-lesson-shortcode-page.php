<?php
/**
 * Custom Lesson Template
 */
get_header();
?>

  <main>
    <?php 
      $lesson_id = get_the_ID();
      echo do_shortcode('[exms_lesson id='.$lesson_id.']');
    ?>
  </main>

<?php
get_footer();
?>
