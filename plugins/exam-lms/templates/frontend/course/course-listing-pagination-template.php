<?php
if ( $total_pages <= 1 ) return;

if( $total_pages > 1 ) {
    ?>
    <div class="exms-course-listing-pagination">
        <?php 
        if( $current_page > 1 ) {
            ?>
            <a class="page-numbers prev" href="<?php echo get_pagenum_link($current_page - 1); ?>">&lsaquo;</a>
            <?php
        } else {
            ?>
            <span class="page-numbers disabled">&lsaquo;</span>
            <?php
        }
        ?>
        <a class="page-numbers<?php echo ( $current_page == 1 ? ' current' : '' ); ?>" href="<?php echo get_pagenum_link(1); ?>">1</a>
        <?php 
        if ($total_pages >= 2) {
            ?>
            <a class="page-numbers<?php echo ( $current_page == 2 ? ' current' : '' ); ?>" href="<?php echo get_pagenum_link(2); ?>">2</a>

            <?php
        }

        // Show ellipsis if needed
        if ($current_page > 4) {
            ?>
            <span class="page-numbers dots">...</span>
            <?php
        }

        for ($i = $current_page - 1; $i <= $current_page + 1; $i++) {
            if ($i > 2 && $i < $total_pages - 1) {
                ?>
                <a class="page-numbers<?php echo ( $i == $current_page ? ' current' : '' ); ?>" href="<?php echo get_pagenum_link( $i ); ?>"><?php echo $i; ?></a>

                <?php
            }
        }

        // Show ellipsis if needed
        if ($current_page < $total_pages - 3) {
            ?>
            <span class="page-numbers dots">...</span>
            <?php
        }

        // Always show last 2 pages
        if ($total_pages > 3) {
            ?>
            <a class="page-numbers<?php echo ( $current_page == $total_pages - 1 ? ' current' : '' ); ?>" href="<?php echo get_pagenum_link( $total_pages - 1 ); ?>">
                <?php echo $total_pages - 1; ?>
            </a>
            <a class="page-numbers<?php echo ( $current_page == $total_pages ? ' current' : '' ); ?>" href="<?php echo get_pagenum_link( $total_pages ); ?>">
                <?php echo $total_pages; ?>
            </a>
            <?php
        }

        if ($current_page < $total_pages) {
            ?>
            <a class="page-numbers next" href="<?php echo get_pagenum_link( $current_page + 1 ); ?>">&rsaquo;</a>

            <?php
        } else {
            ?>
            <span class="page-numbers disabled">&rsaquo;</span>
            <?php
        }
        ?>
    </div>
    <?php
}