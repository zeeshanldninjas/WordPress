<?php
/**
 * Custom Quiz Template
 */
  get_header();
?>

<!-- <main class="exms-quiz-wrapper"> -->
  <?php 
    $quiz_id = get_the_ID();
    echo do_shortcode('[exms_quiz id=' . $quiz_id . ']');
  ?>
<!-- </main> -->

<?php
  get_footer();
?>