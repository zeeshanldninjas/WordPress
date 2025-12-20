<?php

if( ! defined( 'ABSPATH' ) ) exit;

if( file_exists( EXMS_BLOCKS_SRC_DIR . 'student-dashboard-block/callback.php' ) ) {
    require_once EXMS_BLOCKS_SRC_DIR . 'student-dashboard-block/callback.php';
}   

if( file_exists( EXMS_BLOCKS_SRC_DIR . 'leaderboard-block/callback.php' ) ) {
    require_once EXMS_BLOCKS_SRC_DIR . 'leaderboard-block/callback.php';
} 

if( file_exists( EXMS_BLOCKS_SRC_DIR . 'instructor-block/callback.php' ) ) {
    require_once EXMS_BLOCKS_SRC_DIR . 'instructor-block/callback.php';
} 

if( file_exists( EXMS_BLOCKS_SRC_DIR . 'quiz-block/callback.php' ) ) {
    require_once EXMS_BLOCKS_SRC_DIR . 'quiz-block/callback.php';
}
