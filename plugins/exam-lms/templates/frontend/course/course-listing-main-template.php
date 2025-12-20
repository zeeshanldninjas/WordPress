<?php

/**
 * require search bar 
 */
require EXMS_TEMPLATES_DIR . 'frontend/course/course-listing-searchbar-template.php';

?>
<div class="exms-course-listing-main-wrapper">
	<?php 
	require EXMS_TEMPLATES_DIR . 'frontend/course/course-listing-template.php';
	?>
</div>
<?php

/**
 * require pagination
 */
require EXMS_TEMPLATES_DIR . 'frontend/course/course-listing-pagination-template.php';