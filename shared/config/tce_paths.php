<?php

/**
 * Host URL (e.g.: "http://www.yoursite.com").
 */
define('K_PATH_HOST', '');
/* if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)
{
	define('K_PATH_HOST', 'http://localhost'.$_SERVER['SERVER_NAME']);
}else{
	define('K_PATH_HOST', 'http://localhost'.$_SERVER['SERVER_NAME']);
} */
/**
 * Relative URL where this program is installed (e.g.: "/").
 */
define('K_PATH_TCEXAM', '/CBT-KSP/');

/**
 * Real full path where this program is installed (e.g: "/var/www/html/TCExam/").
 */
define('K_PATH_MAIN', 'C:/laragon/www/CBT-KSP/');

/**
 * Constant used on TCPDF library.
 */
define('K_PATH_URL', K_PATH_HOST.K_PATH_TCEXAM);

/**
 * Standard port for http (80) or https (443).
 */
define('K_STANDARD_PORT', 80);

// -----------------------------------------------------------------------------
// --- DO NOT CHANGE THE FOLLOWING VALUES --------------------------------------
// -----------------------------------------------------------------------------

/**
 * Path to public code.
 */
define('K_PATH_PUBLIC_CODE', K_PATH_HOST.K_PATH_TCEXAM.'public/code/');

/**
 * Server path to public code.
 */
define('K_PATH_PUBLIC_CODE_REAL', K_PATH_MAIN.'public/code/');

/**
 * Full path to cache directory.
 */
define('K_PATH_CACHE', K_PATH_MAIN.'cache/');

/**
 * URL path to to cache directory.
 */
define('K_PATH_URL_CACHE', K_PATH_TCEXAM.'cache/');

/**
 * Full path to cache directory used for language files.
 */
define('K_PATH_LANG_CACHE', K_PATH_CACHE.'lang/');

/**
 * Full path to backup directory.
 */
define('K_PATH_BACKUP', K_PATH_CACHE.'backup/');

/**
 * Full path to fonts directory.
 */
define('K_PATH_FONTS', K_PATH_MAIN.'fonts/');

/**
 * Full path to qblock directory.
 */
define('K_PATH_QBLOCK', K_PATH_MAIN.'qblock/');/**
 
 * Full path to answdata directory.
 */
define('K_PATH_ANSWDATA', K_PATH_MAIN.'answdata/');

/**
 * Relative path to stylesheets directory.
 */
define('K_PATH_STYLE_SHEETS', '../styles/');

/**
 * Relative path to javascript directory.
 */
define('K_PATH_JSCRIPTS', '../jscripts/');

/**
 * Relative path to shared javascript directory.
 */
define('K_PATH_SHARED_JSCRIPTS', '../../shared/jscripts/');

/**
 * Relative path to images directory.
 */
define('K_PATH_IMAGES', '../../images/');

/**
 * Full path to TMX language file.
 */
define('K_PATH_TMX_FILE', K_PATH_MAIN.'shared/config/lang/language_tmx.xml');

/**
 * Full path to a blank image.
 */
define('K_BLANK_IMAGE', K_PATH_IMAGES.'_blank.png');

// DOCUMENT_ROOT fix for IIS Webserver
if ((!isset($_SERVER['DOCUMENT_ROOT'])) or (empty($_SERVER['DOCUMENT_ROOT']))){
    if(isset($_SERVER['SCRIPT_FILENAME'])){
        $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
    }elseif(isset($_SERVER['PATH_TRANSLATED'])) {
        $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
    }else{
        // define here your DOCUMENT_ROOT path if the previous fails
        $_SERVER['DOCUMENT_ROOT'] = '/var/www';
    }
}

//============================================================+
// END OF FILE
//============================================================+
