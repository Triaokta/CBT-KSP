<?php

/*
* General Settings
*/
$genset = unserialize(file_get_contents('../../shared/config/tmf_general_settings.json'));

/**
 * TCExam version (do not change).
 */
define ('K_TCEXAM_VERSION', file_get_contents('../../VERSION'));

/**
 * 2-letters code for default language.
 */
define('K_LANGUAGE', $genset['defLang']);

/**
 * If true, display a language selector.
 */
define('K_LANGUAGE_SELECTOR', $genset['enable_langsel']);

define('K_APP_DESC',urldecode($genset['appName']));
define('K_INSTITUTION_NAME',urldecode($genset['institutionName']));
define('K_INSTITUTION_LOGO',$genset['logoImg']);
define('K_ADDRESS_LINE1',urldecode($genset['addrLine1']));
define('K_ADDRESS_LINE2',urldecode($genset['addrLine2']));
define('K_ADDRESS_LINE3',urldecode($genset['addrLine3']));

/**
* If true, force user to mark all question before terminate test
*/
define('K_REALTIME_GRADING',$genset['realtime_grading']);

/**
* If true, force user to mark all question before terminate test
*/
define('K_FORCE_ANSWER_ALL',$genset['answer_all_questions']);

/**
* If true, enable chat feature
*/
define('K_CHAT_FEATURE',$genset['chat_feature']);

/**
 * Defines a serialized array of available languages.
 * Each language is indexed using a 2-letters code (ISO 639).
 */
define('K_AVAILABLE_LANGUAGES', serialize(array(
    'ar' => 'Arabian',
    'az' => 'Azerbaijani',
    'bg' => 'Bulgarian',
    'br' => 'Brazilian Portuguese',
    'cn' => 'Chinese',
    'de' => 'German',
    'el' => 'Greek',
    'en' => 'English',
    'es' => 'Spanish',
    'fa' => 'Farsi',
    'fr' => 'French',
    'he' => 'Hebrew',
    'hi' => 'Hindi',
    'hu' => 'Hungarian',
    'id' => 'Indonesian',
    'it' => 'Italian',
    'jp' => 'Japanese',
    'mr' => 'Marathi',
    'ms' => 'Malay (Bahasa Melayu)',
    'nl' => 'Dutch',
    'pl' => 'Polish',
    'ro' => 'Romanian',
    'ru' => 'Russian',
    'tr' => 'Turkish',
    'ur' => 'Urdu',
    'vn' => 'Vietnamese'
)));

ini_set('zend.ze1_compatibility_mode', false); // disable PHP4 compatibility mode

// -- INCLUDE files -- (INI BAGIAN YANG DIPERBAIKI)
// Urutan ini sangat penting untuk mencegah Fatal Error.
require_once('../../shared/config/tce_db_config.php');
require_once('../../shared/code/tce_functions_general.php'); // <-- KAMUS FUNGSI DIMUAT DULU
require_once('../../shared/code/tce_db_connect.php');      // <-- BARU FILE KONEKSI YANG MEMBUTUHKAN FUNGSI ITU
require_once('../../shared/config/tce_paths.php');
require_once('../../shared/config/tce_general_constants.php');

/**
 * If true enable One-Time-Password authentication on login.
 */
define('K_OTP_LOGIN', false);

/**
 * Ratio at which the delay will be increased after every failed login attempt.
 */
define('K_BRUTE_FORCE_DELAY_RATIO', 0);

/**
 * Number of difficulty levels for questions.
 */
define('K_QUESTION_DIFFICULTY_LEVELS', 10);

/**
 * If true enable virtual keyboard on some textarea fields.
 */
define('K_ENABLE_VIRTUAL_KEYBOARD', false);

/**
 * Popup window height in pixels for test info.
 */
define('K_TEST_INFO_HEIGHT', 400);

/**
 * Popup window width in pixels for test info.
 */
define('K_TEST_INFO_WIDTH', 700);

/**
 * Number of columns for answer textarea.
 */
define('K_ANSWER_TEXTAREA_COLS', 70);

/**
 * Number of rows for answer textarea.
 */
define('K_ANSWER_TEXTAREA_ROWS', 15);

/**
 * If true enable explanation field for questions.
 */
define('K_ENABLE_QUESTION_EXPLANATION', true);

/**
 * If true enable explanation field for answers.
 */
define('K_ENABLE_ANSWER_EXPLANATION', true);

/**
 * If true display test description before executing the test.
 */
define('K_DISPLAY_TEST_DESCRIPTION', $genset['display_test_desc']);

/**
 * If true compare short answers in binary mode.
 */
define('K_SHORT_ANSWERS_BINARY', false);

/**
 * User's session life time in seconds.
 */
define('K_SESSION_LIFE', K_SECONDS_IN_HOUR);

/**
 * When an alternate authentication method is used,
 * if this constant is true the default user groups for the selected
 * authentication method are always added to the user.
 */
define('K_USER_GROUP_RSYNC', false);

/**
 * Define timestamp format using PHP notation (do not change).
 */
define('K_TIMESTAMP_FORMAT', 'Y-m-d H:i:s');

/**
 * Define max line length in chars for question navigator on test execution interface.
 */
define('K_QUESTION_LINE_MAX_LENGTH', 70);

/**
 * If true, check for possible session hijacking (set to false if you have login problems).
 */
define('K_CHECK_SESSION_FINGERPRINT', false);

// Client Cookie settings

/**
 * Cookie domain.
 */
define('K_COOKIE_DOMAIN', '');

/**
 * Cookie path.
 */
define('K_COOKIE_PATH', '/');

/**
 * If true use secure cookies.
 */
define('K_COOKIE_SECURE', false);

/**
 * Expiration time for cookies.
 */
define('K_COOKIE_EXPIRE', K_SECONDS_IN_DAY);

/**
 * Various pages redirection modes after login (valid values are 1, 2, 3 and 4).
 */
define('K_REDIRECT_LOGIN_MODE', 4);

/**
 * If true enable password reset feature.
 */
define('K_PASSWORD_RESET', $genset['forgotPass']);

/**
 * URL to be redirected at logout (leave empty for default).
 */
define('K_LOGOUT_URL', urldecode($genset['logoutURL']));


// Error settings

/**
 * Define error reporting types for debug.
 */
define('K_ERROR_TYPES', 0);
//define ('K_ERROR_TYPES', E_ERROR | E_WARNING | E_PARSE);

/**
 * Enable error logs (../log/tce_errors.log).
 */
define('K_USE_ERROR_LOG', false);

/**
 * If true display messages and errors on Javascript popup window.
 */
define('K_ENABLE_JSERRORS', false);

/**
 * If true display regular HTML tags.
 */
define('K_ENABLE_HTML', true);

/**
 * Set your own timezone here.
 */
define('K_TIMEZONE', $genset['timezone']);

/**
 * Default minutes used to extend test duration.
 */
define('K_EXTEND_TIME_MINUTES', 5);


// ---------- * ---------- * ---------- * ---------- * ----------

/**
 * Error handlers.
 */
require_once('../../shared/code/tce_functions_errmsg.php');

// load language resources

// set user's selected language or default language
if (isset($_REQUEST['lang'])
    and (strlen($_REQUEST['lang']) == 2)
    and (array_key_exists($_REQUEST['lang'], unserialize(K_AVAILABLE_LANGUAGES)))) {
    define('K_USER_LANG', $_REQUEST['lang']);
// set client cookie
setcookie('SessionUserLang', K_USER_LANG, time() + K_COOKIE_EXPIRE, K_COOKIE_PATH, K_COOKIE_DOMAIN, K_COOKIE_SECURE);
} elseif (isset($_COOKIE['SessionUserLang'])
    and (strlen($_COOKIE['SessionUserLang']) == 2)
    and (array_key_exists($_COOKIE['SessionUserLang'], unserialize(K_AVAILABLE_LANGUAGES)))) {
    define('K_USER_LANG', $_COOKIE['SessionUserLang']);
} else {
    define('K_USER_LANG', K_LANGUAGE);
}

// TMX class
require_once('../../shared/code/tce_tmx.php');
// instantiate new TMXResourceBundle object
$lang_resources = new TMXResourceBundle(K_PATH_TMX_FILE, K_USER_LANG, K_PATH_LANG_CACHE.basename(K_PATH_TMX_FILE, '.xml').'_'.K_USER_LANG.'.php');
$l = $lang_resources->getResource(); // language array

ini_set('arg_separator.output', '&amp;');
date_default_timezone_set(K_TIMEZONE);

if (!defined('PHP_VERSION_ID')) {
$version = PHP_VERSION;
define('PHP_VERSION_ID', (($version[0] * 10000) + ($version[2] * 100) + $version[4]));
}
if (PHP_VERSION_ID < 50300) {
    @set_magic_quotes_runtime(false); //disable magic quotes
    ini_set('magic_quotes_gpc', 'On');
    ini_set('magic_quotes_runtime', 'Off');
    ini_set('register_long_arrays', 'On');
}

// --- get 'post', 'get' and 'cookie' variables
foreach ($_REQUEST as $postkey => $postvalue) {
    if (($postkey[0] != '_') and (!preg_match('/[A-Z]/', $postkey[0]))) {
        if (!function_exists('get_magic_quotes_gpc') or (PHP_VERSION_ID >= 70400) or !get_magic_quotes_gpc()) {
            $postvalue = addSlashesArray($postvalue);
            $_REQUEST[$postkey] = $postvalue;
            if (isset($_GET[$postkey])) {
                $_GET[$postkey] = $postvalue;
            } elseif (isset($_POST[$postkey])) {
                $_POST[$postkey] = $postvalue;
            } elseif (isset($_COOKIE[$postkey])) {
                $_COOKIE[$postkey] = $postvalue;
            }
        }
        $$postkey = $postvalue;
    }
}

function addSlashesArray($data)
{
    if (is_array($data)) {
         return array_map('addSlashesArray', $data);
    }
    if (is_string($data)) {
         return addslashes($data);
    }
    return $data;
}
//============================================================+
// END OF FILE
//============================================================+