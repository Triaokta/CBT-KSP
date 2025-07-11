<?php

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_USERS;
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/config/tce_user_registration.php');

$thispage_title = $l['t_user_editor'];
$thispage_title_icon = '<i class="pe-7s-user icon-gradient bg-happy-itmeo"></i> ';
$thispage_help = $l['hp_edit_user'];
$enable_calendar = true;
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form_admin.php');
require_once('../../shared/code/tce_functions_otp.php');
require_once('tce_functions_user_select.php');

if (isset($_REQUEST['user_id'])) {
    $user_id = intval($_REQUEST['user_id']);
    if (!F_isAuthorizedEditorForUser($user_id)) {
        F_print_error('ERROR', $l['m_authorization_denied'], true);
    }
}

if (isset($_REQUEST['group_id'])) {
    $group_id = intval($_REQUEST['group_id']);
    if (!F_isAuthorizedEditorForGroup($group_id)) {
        F_print_error('ERROR', $l['m_authorization_denied'], true);
    }
}
if (isset($_REQUEST['user_level'])) {
    $user_level = intval($_REQUEST['user_level']);
}

// Keamanan: Jika yang mengedit BUKAN seorang admin,
// jangan izinkan mereka mengubah level sama sekali.
if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
    if ($user_id != $_SESSION['session_user_id']) {
        // non-admin tidak bisa mengubah level orang lain
        F_print_error('ERROR', $l['m_authorization_denied']);
        // (kode di bawah ini akan mencegah perubahan disimpan)
    }
}

// comma separated list of required fields
$_REQUEST['ff_required'] = 'user_name';
$_REQUEST['ff_required_labels'] = htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']);

switch ($menu_mode) { // process submitted data

    case 'delete':{
        F_stripslashes_formfields(); // ask confirmation
        if (($_SESSION['session_user_level'] < K_AUTH_DELETE_USERS) or ($user_id == $_SESSION['session_user_id']) or ($user_id == 1)) {
            F_print_error('ERROR', $l['m_authorization_denied']);
            break;
        }
        // F_print_error('WARNING', $l['m_delete_confirm']);
        ?>
        
        <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_delete">
        
        <input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id; ?>" />
        <input type="hidden" name="user_name" id="user_name" value="<?php echo stripslashes($user_name); ?>" />
        <?php
		echo '<div class="alert alert-warning fade show" role="alert">'.K_NEWLINE;
		echo '<h4 class="alert-heading">'.$l['m_delete_confirm'].'</h4>'.K_NEWLINE;
        echo '<p>Apakah yakin ingin melanjutkan proses penghapusan?</p>'.K_NEWLINE;
        echo '<hr>'.K_NEWLINE;
        F_submit_button_alt('forcedelete', $l['w_delete'], $l['h_delete'], 'mb-2 mr-2 btn btn-danger');
        F_submit_button_alt('cancel', $l['w_cancel'], $l['h_cancel'], 'mb-2 mr-2 btn-transition btn btn-outline-danger');
        echo F_getCSRFTokenField().K_NEWLINE;
		echo '</div>'.K_NEWLINE;
        ?>
        
        </form>
        
        <?php
        break;
    }

    case 'forcedelete':{
        F_stripslashes_formfields(); // Delete specified user
        if (($_SESSION['session_user_level'] < K_AUTH_DELETE_USERS) or ($user_id == $_SESSION['session_user_id']) or ($user_id == 1)) {
            F_print_error('ERROR', $l['m_authorization_denied']);
            break;
        }
        if ($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
            if ($user_id==1) { //can't delete anonymous user
                F_print_error('WARNING', $l['m_delete_anonymous']);
            } else {
                $sql = 'DELETE FROM '.K_TABLE_USERS.' WHERE user_id='.$user_id.'';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error(false);
                } else {
                    $user_id=false;
                    F_print_error('MESSAGE', '['.stripslashes($user_name).'] '.$l['m_user_deleted']);
                }
            }
        }
        break;
    }

    case 'update':{ // Update user
        // check if the confirmation chekbox has been selected
        if (!isset($_REQUEST['confirmupdate']) or ($_REQUEST['confirmupdate'] != 1)) {
            F_print_error('WARNING', $l['m_form_missing_fields'].': '.$l['w_confirm'].' &rarr; '.$l['w_update']);
            F_stripslashes_formfields();
            break;
        }
        if ($formstatus = F_check_form_fields()) {
            // check if name is unique
            if (!F_check_unique(K_TABLE_USERS, 'user_name=\''.F_escape_sql($db, $user_name).'\'', 'user_id', $user_id)) {
                F_print_error('WARNING', $l['m_duplicate_name']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            // check if registration number is unique
            if (isset($user_regnumber) and (strlen($user_regnumber) > 0) and (!F_check_unique(K_TABLE_USERS, 'user_regnumber=\''.F_escape_sql($db, $user_regnumber).'\'', 'user_id', $user_id))) {
                F_print_error('WARNING', $l['m_duplicate_regnumber']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            // check if ssn is unique
            if (isset($user_ssn) and (strlen($user_ssn) > 0) and (!F_check_unique(K_TABLE_USERS, 'user_ssn=\''.F_escape_sql($db, $user_ssn).'\'', 'user_id', $user_id))) {
                F_print_error('WARNING', $l['m_duplicate_ssn']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            // check password
            if (!empty($newpassword) or !empty($newpassword_repeat)) {
                if ($newpassword == $newpassword_repeat) {
                    $user_password = getPasswordHash($newpassword);
                    // update OTP key
                    $user_otpkey = F_getRandomOTPkey();
                } else { //print message and exit
                    F_print_error('WARNING', $l['m_different_passwords']);
                    $formstatus = false;
                    F_stripslashes_formfields();
                    break;
                }
            }
            $sql = 'UPDATE '.K_TABLE_USERS.' SET
				user_regdate=\''.F_escape_sql($db, $user_regdate).'\',
				user_ip=\''.F_escape_sql($db, $user_ip).'\',
				user_name=\''.F_escape_sql($db, $user_name).'\',
				user_email='.F_empty_to_null($user_email).',
				user_password=\''.F_escape_sql($db, $user_password).'\',
				user_regnumber='.F_empty_to_null($user_regnumber).',
				user_firstname='.F_empty_to_null($user_firstname).',
				user_lastname='.F_empty_to_null($user_lastname).',
				user_birthdate='.F_empty_to_null($user_birthdate).',
				user_birthplace='.F_empty_to_null($user_birthplace).',
				user_ssn='.F_empty_to_null($user_ssn).',
				user_level=\''.$user_level.'\',
				user_otpkey='.F_empty_to_null($user_otpkey).'
				WHERE user_id='.$user_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                F_print_error('MESSAGE', stripslashes($user_name).': '.$l['m_user_updated']);
            }
            // remove old groups
            $old_user_groups = F_get_user_groups($user_id);
            foreach ($old_user_groups as $group_id) {
                if (F_isAuthorizedEditorForGroup($group_id)) {
                    // delete previous groups
                    $sql = 'DELETE FROM '.K_TABLE_USERGROUP.'
						WHERE usrgrp_user_id='.$user_id.' AND usrgrp_group_id='.$group_id.'';
                    if (!$r = F_db_query($sql, $db)) {
                        F_display_db_error(false);
                    }
                }
            }
            // update user's groups
            if (!empty($user_groups)) {
                foreach ($user_groups as $group_id) {
                    if (F_isAuthorizedEditorForGroup($group_id)) {
                        $sql = 'INSERT INTO '.K_TABLE_USERGROUP.' (
							usrgrp_user_id,
							usrgrp_group_id
							) VALUES (
							\''.$user_id.'\',
							\''.$group_id.'\'
							)';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error(false);
                        }
                    }
                }
            }
        }
        break;
    }

    case 'add':{ // Add user
        if ($formstatus = F_check_form_fields()) { // check submittef form fields
            // check if name is unique
            if (!F_check_unique(K_TABLE_USERS, 'user_name=\''.$user_name.'\'')) {
                F_print_error('WARNING', $l['m_duplicate_name']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            // check if registration number is unique
            if (isset($user_regnumber) and (strlen($user_regnumber) > 0) and (!F_check_unique(K_TABLE_USERS, 'user_regnumber=\''.F_escape_sql($db, $user_regnumber).'\''))) {
                F_print_error('WARNING', $l['m_duplicate_regnumber']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            // check if ssn is unique
            if (isset($user_ssn) and (strlen($user_ssn) > 0) and (!F_check_unique(K_TABLE_USERS, 'user_ssn=\''.F_escape_sql($db, $user_ssn).'\''))) {
                F_print_error('WARNING', $l['m_duplicate_ssn']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            // check password
            if (!empty($newpassword) or !empty($newpassword_repeat)) { // update password
                if ($newpassword == $newpassword_repeat) {
                    $user_password = getPasswordHash($newpassword);
                    // update OTP key
                    $user_otpkey = F_getRandomOTPkey();
                } else { //print message and exit
                    F_print_error('WARNING', $l['m_different_passwords']);
                    $formstatus = false;
                    F_stripslashes_formfields();
                    break;
                }
            } else { //print message and exit
                F_print_error('WARNING', $l['m_empty_password']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }

            $user_ip = getNormalizedIP($_SERVER['REMOTE_ADDR']); // get the user's IP number
            $user_regdate = date(K_TIMESTAMP_FORMAT); // get the registration date and time

            $sql = 'INSERT INTO '.K_TABLE_USERS.' (
				user_regdate,
				user_ip,
				user_name,
				user_email,
				user_password,
				user_regnumber,
				user_firstname,
				user_lastname,
				user_birthdate,
				user_birthplace,
				user_ssn,
				user_level,
				user_otpkey
				) VALUES (
				\''.F_escape_sql($db, $user_regdate).'\',
				\''.F_escape_sql($db, $user_ip).'\',
				\''.F_escape_sql($db, $user_name).'\',
				'.F_empty_to_null($user_email).',
				\''.F_escape_sql($db, $user_password).'\',
				'.F_empty_to_null($user_regnumber).',
				'.F_empty_to_null($user_firstname).',
				'.F_empty_to_null($user_lastname).',
				'.F_empty_to_null($user_birthdate).',
				'.F_empty_to_null($user_birthplace).',
				'.F_empty_to_null($user_ssn).',
				\''.$user_level.'\',
				'.F_empty_to_null($user_otpkey).'
				)';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                $user_id = F_db_insert_id($db, K_TABLE_USERS, 'user_id');
				F_print_error('MESSAGE', 'User berhasil ditambahkan');						
            }
            // add user's groups
            if (!empty($user_groups)) {
                foreach ($user_groups as $group_id) {
                    if (F_isAuthorizedEditorForGroup($group_id)) {
                        $sql = 'INSERT INTO '.K_TABLE_USERGROUP.' (
							usrgrp_user_id,
							usrgrp_group_id
							) VALUES (
							\''.$user_id.'\',
							\''.$group_id.'\'
							)';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error(false);
                        }
                    }
                }
            }
        }
        break;
    }

    case 'clear':{ // Clear form fields
        $user_regdate = '';
        $user_ip = '';
        $user_name = '';
        $user_email = '';
        $user_password = '';
        $user_regnumber = '';
        $user_firstname = '';
        $user_lastname = '';
        $user_birthdate = '';
        $user_birthplace = '';
        $user_ssn = '';
        $user_level = '';
        $user_otpkey = '';
        break;
    }

    default :{
        break;
    }
} //end of switch

// --- Initialize variables
if ($formstatus) {
    if ($menu_mode != 'clear') {
        if (!isset($user_id) or empty($user_id)) {
            $user_id = 0;
            $user_regdate = '';
            $user_ip = '';
            $user_name = '';
            $user_email = '';
            $user_password = '';
            $user_regnumber = '';
            $user_firstname = '';
            $user_lastname = '';
            $user_birthdate = '';
            $user_birthplace = '';
            $user_ssn = '';
            $user_level = '';
            $user_otpkey = '';
        } else {
            $sql = 'SELECT * FROM '.K_TABLE_USERS.' WHERE user_id='.$user_id.' LIMIT 1';
            if ($r = F_db_query($sql, $db)) {
                if ($m = F_db_fetch_array($r)) {
                    $user_id = $m['user_id'];
                    $user_regdate = $m['user_regdate'];
                    $user_ip = $m['user_ip'];
                    $user_name = $m['user_name'];
                    $user_email = $m['user_email'];
                    $user_password = $m['user_password'];
                    $user_regnumber = $m['user_regnumber'];
                    $user_firstname = $m['user_firstname'];
                    $user_lastname = $m['user_lastname'];
                    $user_birthdate = substr($m['user_birthdate'], 0, 10);
                    $user_birthplace = $m['user_birthplace'];
                    $user_ssn = $m['user_ssn'];
                    $user_level = $m['user_level'];
                    $user_otpkey = $m['user_otpkey'];
                } else {
                    $user_regdate = '';
                    $user_ip = '';
                    $user_name = '';
                    $user_email = '';
                    $user_password = '';
                    $user_regnumber = '';
                    $user_firstname = '';
                    $user_lastname = '';
                    $user_birthdate = '';
                    $user_birthplace = '';
                    $user_ssn = '';
                    $user_level = '';
                    $user_otpkey = '';
                }
            } else {
                F_display_db_error();
            }
        }
    }
}
echo '<div class="main-card mb-3 card">'.K_NEWLINE;
if($user_id>0){
	$form_title = '<i class="pe-7s-pen mr-2"></i>Edit User';
}else{
	$form_title = '<i class="pe-7s-plus mr-2"></i>Tambah User';
}
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_usereditor">'.K_NEWLINE;
echo '<div class="card-header text-left">'.$form_title.'</div>'.K_NEWLINE;
echo '<div class="card-body">'.K_NEWLINE;
echo '<div class="position-relative form-group">'.K_NEWLINE;
// echo '<span class="label">'.K_NEWLINE;
echo '<label for="user_id">'.ucfirst($l['w_user']).'</label>'.K_NEWLINE;
// echo '</span>'.K_NEWLINE;
// echo '<span class="formw">'.K_NEWLINE;
echo '<select class="custom-select select2-single" name="user_id" id="user_id" onchange="document.getElementById(\'form_usereditor\').submit()">'.K_NEWLINE;
echo '<option value="0" class="mb-2 mr-2 btn btn-success"';
if ($user_id == 0) {
    echo ' selected="selected"';
}
echo '>+ tambah user baru</option>'.K_NEWLINE;
$sql = 'SELECT user_id, user_lastname, user_firstname, user_name FROM '.K_TABLE_USERS.' WHERE (user_id>1)';
if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
    // filter for level (logika ini tidak lagi relevan di sistem 2-level, jadi kita nonaktifkan)
    // $sql .= ' AND ((user_level<'.$_SESSION['session_user_level'].') OR (user_id='.$_SESSION['session_user_id'].'))';
    
    // filter for groups (biarkan baris ini aktif agar non-admin tetap hanya bisa melihat grupnya)
    $sql .= ' AND user_id IN (SELECT tb.usrgrp_user_id
        FROM '.K_TABLE_USERGROUP.' AS ta, '.K_TABLE_USERGROUP.' AS tb
        WHERE ta.usrgrp_group_id=tb.usrgrp_group_id
            AND ta.usrgrp_user_id='.intval($_SESSION['session_user_id']).'
            AND tb.usrgrp_user_id=user_id)';
}
$sql .= ' ORDER BY user_lastname, user_firstname, user_name';
if ($r = F_db_query($sql, $db)) {
    $countitem = 1;
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['user_id'].'"';
        if ($m['user_id'] == $user_id) {
            echo ' selected="selected"';
        }
        echo '>'.$countitem.'. '.htmlspecialchars($m['user_lastname'].' '.$m['user_firstname'].' - '.$m['user_name'].'', ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
        $countitem++;
    }
} else {
    echo '</select>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;

// link for user selection popup
// $jsaction = 'selectWindow=window.open(\'tce_select_users_popup.php?cid=user_id\', \'selectWindow\', \'dependent, height=600, width=800, menubar=no, resizable=yes, scrollbars=yes, status=no, toolbar=no\');return false;';
// echo '<a href="#" onclick="'.$jsaction.'" class="xmlbutton" title="'.$l['w_select'].'"><i class="fas fa-users"></i></a>';

// echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

// echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo '<div class="form-row">'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowTextInput('user_name', ucfirst($l['w_username']), $l['h_login_name'], '', $user_name, '', 255, false, false, false, '', true);
echo '</div>'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowTextInput('user_email', ucfirst($l['w_email']), $l['h_usered_email'], '', $user_email, K_EMAIL_RE_PATTERN, 255, false, false, false, '', true);
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="form-row">'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowTextInput('newpassword', ucfirst($l['w_password']), $l['h_password'], ' ('.$l['d_password_lenght'].')', '', K_USRREG_PASSWORD_RE, 255, false, false, true, '', true);
echo '</div>'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowTextInput('newpassword_repeat', ucfirst('ulangi '.$l['w_password']), $l['h_password_repeat'], ' ('.$l['w_repeat'].')', '', '', 255, false, false, true, '', true);
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="form-row">'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowFixedValue('user_regdate', $l['w_regdate'], $l['h_regdate'], '', $user_regdate, false, '', true);
echo '</div>'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowFixedValue('user_ip', $l['w_ip'], $l['h_ip'], '', $user_ip, false, '', true);
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="form-row">'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowSelectBox('user_level', $l['w_level'], $l['h_level'], '', $user_level, array(0,1,2), '', true);
echo '</div>'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowTextInput('user_regnumber', $l['w_regcode'], $l['h_regcode'], '', $user_regnumber, '', 255, false, false, false, '', true);
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="form-row">'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowTextInput('user_firstname', $l['w_firstname'], $l['h_firstname'], '', $user_firstname, '', 255, false, false, false, '', true);
echo '</div>'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowTextInput('user_lastname', $l['w_lastname'], $l['h_lastname'], '', $user_lastname, '', 255, false, false, false, '', true);
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="form-row">'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowTextInput('user_birthplace', $l['w_birth_place'], $l['h_birth_place'], '', $user_birthplace, '', 255, false, false, false, '', true);
echo '</div>'.K_NEWLINE;
echo '<div class="col-md-6">'.K_NEWLINE;
echo getFormRowTextInput('user_birthdate', $l['w_birth_date'], $l['h_birth_date'].' '.$l['w_date_format'], '', $user_birthdate, '', 10, false, false, 'date', '', true);
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormRowTextInput('user_ssn', $l['w_fiscal_code'], $l['h_fiscal_code'], '', $user_ssn, '', 255, false, false, false, '', true);

echo '<div class="position-relative form-group">'.K_NEWLINE;
// echo '<span class="label">'.K_NEWLINE;
echo '<label for="user_groups">'.ucfirst($l['w_groups']).'</label>'.K_NEWLINE;
// echo '</span>'.K_NEWLINE;
// echo '<span class="formw">'.K_NEWLINE;
echo '<select class="form-control select2-multiple" name="user_groups[]" id="user_groups" size="5" multiple="multiple">'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_GROUPS.' ORDER BY group_name';
if ($r = F_db_query($sql, $db)) {
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['group_id'].'"';
        if (!F_isAuthorizedEditorForGroup($m['group_id'])) {
            echo ' style="text-decoration:line-through;"';
        }
        if (F_isUserOnGroup($user_id, $m['group_id'])) {
            echo ' selected="selected"';
            $m['group_name'] = '* '.$m['group_name'];
        }
        echo '>'.htmlspecialchars($m['group_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
    }
} else {
    echo '</select></div>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;

// echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormRowTextInput('user_otpkey', $l['w_otpkey'], $l['h_otpkey'], '', $user_otpkey, '', 255, false, false, false, '', true);

// display QR-Code for Google authenticator
if (!empty($user_otpkey)) {
    require_once('../../shared/tcpdf/tcpdf_barcodes_2d.php');
    $host = preg_replace('/[h][t][t][p][s]?[:][\/][\/]/', '', K_PATH_HOST);
    $qrcode = new TCPDF2DBarcode('otpauth://totp/'.$user_name.'@'.$host.'?secret='.$user_otpkey, 'QRCODE,H');
    echo '<div class="d-flex align-items-center position-relative form-group flex-column">'.K_NEWLINE;
    echo '<span class="font-weight-bold">'.$l['w_otp_qrcode'].'</span>'.K_NEWLINE;
    echo '<span class="formw" style="margin:10px 0px 20px 0px;">'.K_NEWLINE;
    echo $qrcode->getBarcodeHTML(6, 6, 'black');
    echo '</span>'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;
}

echo '</div>'.K_NEWLINE;

echo '<div class="d-block text-center card-footer">'.K_NEWLINE;
// show buttons by case
if (isset($user_id) and ($user_id > 0)) {
    if (($user_level < $_SESSION['session_user_level']) or ($user_id == $_SESSION['session_user_id']) or ($_SESSION['session_user_level'] >= K_AUTH_ADMINISTRATOR)) {
        echo '<span class="d-iflex c-update jc-center">';
        echo '<input class="custom-control-input" type="checkbox" checked="checked" name="confirmupdate" id="confirmupdate" value="1" title="confirm &rarr; update" />';
        F_submit_button_alt('update', $l['w_update'], $l['h_update'], 'mr-2 btn btn-primary');
        echo '</span>';
    }
    if (($user_id > 1) and ($_SESSION['session_user_level'] >= K_AUTH_DELETE_USERS) and ($user_id != $_SESSION['session_user_id'])) {
        // your account and anonymous user can't be deleted
        F_submit_button_alt('delete', $l['w_delete'], $l['h_delete'], "mr-2 btn-transition btn btn-outline-danger");
    }
} else {
    F_submit_button_alt('add', $l['w_add'], $l['h_add'], 'mr-2 btn btn-primary');
}
F_submit_button_alt('clear', $l['w_clear'], $l['h_clear'], "mr-2 btn-transition btn btn-outline-warning");

echo '<input type="hidden" name="user_password" id="user_password" value="'.$user_password.'" />'.K_NEWLINE;
echo F_getCSRFTokenField().K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
// echo '<div class="pagehelp">'.$l['hp_edit_user'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
