<?php

require_once('../config/tce_config.php');
require_once __DIR__ . '/../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$pagelevel = K_AUTH_ADMIN_IMPORT;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_question_importer'];
$thispage_title_icon = '<i class="pe-7s-upload icon-gradient bg-sunny-morning"></i> ';
$thispage_help = $l['hp_import_xml_questions'];

require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form_admin.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_auth_sql.php');

if (!isset($type) or (empty($type))) {
    $type = 1;
} else {
    $type = intval($type);
}

if (isset($menu_mode) and ($menu_mode == 'upload')) {
    if ($_FILES['userfile']['name']) {
        require_once('../code/tce_functions_upload.php');
        // upload file
        $uploadedfile = F_upload_file('userfile', K_PATH_CACHE);
        if ($uploadedfile !== false) {
            $qimp = false;
            switch ($type) {
                case 1: {
                    // standard TCExam XML format
                    require_once('../code/tce_class_import_xml.php');
                    $qimp = new XMLQuestionImporter(K_PATH_CACHE.$uploadedfile);
                    break;
                }
                case 2: {
                    // standard TCExam TSV format
                    $qimp = F_TSVQuestionImporter(K_PATH_CACHE.$uploadedfile);
                    break;
                }
                case 3: {
					$new_filename = $uploadedfile.'_'.date('Y-m-d_H_i_s').'.tsv';
                    $spreadsheet = IOFactory::load(K_PATH_CACHE.$uploadedfile);
                    $writer = IOFactory::createWriter($spreadsheet, 'Csv');
					$writer->setDelimiter("\t");
					$writer->setEnclosure("\"");
					$writer->save(K_PATH_CACHE.$new_filename);
					$qimp = F_TSVQuestionImporter(K_PATH_CACHE.$new_filename);
					break;
                }
				case 4: {
                    // Custom TCExam XML format
                    require_once('../code/tce_import_custom.php');
                    $qimp = new CustomQuestionImporter(K_PATH_CACHE.$uploadedfile);
                    break;
                }
            }
            if ($qimp) {
                F_print_error('MESSAGE', $l['m_importing_complete']);
            }
        }
    }
}
echo '<div class="card mb-3">'.K_NEWLINE;

echo '<div class="card-header"><i class="pe-7s-upload"></i>&nbsp;Unggah file sesuai type yang dipilih</div>'.K_NEWLINE;
echo '<div class="card-body">';
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_importquestions">'.K_NEWLINE;

echo '<div class="form-group border p-2 rounded">'.K_NEWLINE;
echo '<label for="userfile" class="font-weight-bold"><i class="fa fa-upload"></i>&nbsp;'.$l['w_upload_file'].'</label>'.K_NEWLINE;
echo '<input type="hidden" name="MAX_FILE_SIZE" value="'.K_MAX_UPLOAD_SIZE.'" />'.K_NEWLINE;
echo '<input class="form-control" type="file" name="userfile" id="userfile" size="20" title="'.$l['h_upload_file'].'" />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="form-group border rounded p-2">'.K_NEWLINE;
echo '<span class="font-weight-bold" title="'.$l['w_type'].'"><i class="fa fa-file"></i>&nbsp;'.$l['w_type'].'</span>'.K_NEWLINE;
echo '<div class="col-sm-10 p-3">'.K_NEWLINE;
echo '<div class="position-relative custom-radio custom-control"><input type="radio" class="custom-control-input" name="type" id="type_xml" value="1" title="TCExam XML Format"';
if ($type == 1) {
    echo ' checked="checked"';
}
echo ' />';
echo '<label for="type_xml" class="custom-control-label"> TCExam XML</label></div>'.K_NEWLINE;

echo '<div class="position-relative custom-radio custom-control"><input class="custom-control-input" type="radio" name="type" id="type_tsv" value="2" title="TCExam TSV Format"'.K_NEWLINE;
if ($type == 2) {
    echo ' checked="checked"';
}
echo ' />';
echo '<label class="custom-control-label" for="type_tsv"> TCExam TSV</label></div>'.K_NEWLINE;

echo '<div class="position-relative custom-radio custom-control"><input class="custom-control-input" type="radio" name="type" id="type_xlsx" value="3" title="XLSX Format"'.K_NEWLINE;
if ($type == 3) {
    echo ' checked="checked"';
}
echo ' />';
echo '<label class="custom-control-label" for="type_xlsx"> Excel Format (.XLSX)</label> <a href="../../template/template_soal_excel_tcexam.xlsm" class="btn btn-success"><i class="fas fa-file-excel"></i> Download Template Soal</a></div>'.K_NEWLINE;

$custom_import = K_ENABLE_CUSTOM_IMPORT;
if (!empty($custom_import)) {
	echo '<div class="position-relative custom-radio custom-control">';
    echo '<input class="custom-control-input" type="radio" name="type" id="type_custom" value="3" title="'.$custom_import.'"'.K_NEWLINE;
    if ($type == 3) {
        echo ' checked="checked"';
    }
    echo ' />';
    echo '<label class="custom-control-label" for="type_custom">'.$custom_import.'</label>'.K_NEWLINE;
	echo '</div>';
}
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="d-flex justify-content-center">'.K_NEWLINE;

// show upload button
F_submit_button('upload', $l['w_upload'], $l['h_submit_file'].' " class="btn btn-success btn-block');

echo '</div>'.K_NEWLINE;
echo F_getCSRFTokenField().K_NEWLINE;
echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

// ---------------------------------------------------------------------

/**
 * Import questions from TSV file (tab delimited text).
 * The format of TSV is the same obtained by exporting data from TCExam interface.
 * @param $tsvfile (string) TSV (tab delimited text) file name
 * @return boolean TRUE in case of success, FALSE otherwise
 */
function F_TSVQuestionImporter($tsvfile)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    require_once('../../shared/code/tce_functions_auth_sql.php');
    $qtype = array('S' => 1, 'M' => 2, 'T' => 3, 'O' => 4);
    $tsvfp = fopen($tsvfile, 'r');
    if ($tsvfp === false) {
        return false;
    }
    $current_module_id = 0;
    $current_subject_id = 0;
    $current_question_id = 0;
    $current_answer_id = 0;
    $questionhash = array();
    // for each row
    while ($qdata=fgetcsv($tsvfp, 0, "\t", '"')) {
        if ($qdata === null) {
            continue;
        }
        // get user data into array
        switch ($qdata[0]) {
            case 'M': { // MODULE
                $current_module_id = 0;
                if (!isset($qdata[2]) or empty($qdata[2])) {
                    break;
                }
                $module_enabled = intval($qdata[1]);
                $module_name = F_escape_sql($db, F_tsv_to_text($qdata[2]), false);
                // check if this module already exist
                $sql = 'SELECT module_id
					FROM '.K_TABLE_MODULES.'
					WHERE module_name=\''.$module_name.'\'
					LIMIT 1';
                if ($r = F_db_query($sql, $db)) {
                    if ($m = F_db_fetch_array($r)) {
                        // get existing module ID
                        if (!F_isAuthorizedUser(K_TABLE_MODULES, 'module_id', $m['module_id'], 'module_user_id')) {
                            // unauthorized user
                            $current_module_id = 0;
                        } else {
                            $current_module_id = $m['module_id'];
                        }
                    } else {
                        // insert new module
                        $sql = 'INSERT INTO '.K_TABLE_MODULES.' (
							module_name,
							module_enabled,
							module_user_id
							) VALUES (
							\''.$module_name.'\',
							\''.$module_enabled.'\',
							\''.$_SESSION['session_user_id'].'\'
							)';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error();
                        } else {
                            // get new module ID
                            $current_module_id = F_db_insert_id($db, K_TABLE_MODULES, 'module_id');
                        }
                    }
                } else {
                    F_display_db_error();
                }
                break;
            }
            case 'S': { // SUBJECT
                $current_subject_id = 0;
                if ($current_module_id == 0) {
                    return;
                }
                if (!isset($qdata[2]) or empty($qdata[2])) {
                    break;
                }
                $subject_enabled = intval($qdata[1]);
                $subject_name = F_escape_sql($db, F_tsv_to_text($qdata[2]), false);
                $subject_description = '';
                if (isset($qdata[3])) {
                    $subject_description = F_empty_to_null(F_tsv_to_text($qdata[3]));
                }
                // check if this subject already exist
                $sql = 'SELECT subject_id
					FROM '.K_TABLE_SUBJECTS.'
					WHERE subject_name=\''.$subject_name.'\'
						AND subject_module_id='.$current_module_id.'
					LIMIT 1';
                if ($r = F_db_query($sql, $db)) {
                    if ($m = F_db_fetch_array($r)) {
                        // get existing subject ID
                        $current_subject_id = $m['subject_id'];
                    } else {
                        // insert new subject
                        $sql = 'INSERT INTO '.K_TABLE_SUBJECTS.' (
							subject_name,
							subject_description,
							subject_enabled,
							subject_user_id,
							subject_module_id
							) VALUES (
							\''.$subject_name.'\',
							'.$subject_description.',
							\''.$subject_enabled.'\',
							\''.$_SESSION['session_user_id'].'\',
							'.$current_module_id.'
							)';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error();
                        } else {
                            // get new subject ID
                            $current_subject_id = F_db_insert_id($db, K_TABLE_SUBJECTS, 'subject_id');
                        }
                    }
                } else {
                    F_display_db_error();
                }
                break;
            }
            case 'Q': { // QUESTION
                $current_question_id = 0;
                if (($current_module_id == 0) or ($current_subject_id == 0)) {
                    return;
                }
                if (!isset($qdata[5])) {
                    break;
                }
                $question_enabled = intval($qdata[1]);
                $question_description = F_escape_sql($db, F_tsv_to_text($qdata[2]), false);
                $question_explanation = F_empty_to_null(F_tsv_to_text($qdata[3]));
                $question_type = $qtype[$qdata[4]];
                $question_difficulty = intval($qdata[5]);
                if (isset($qdata[6])) {
                    $question_position = F_zero_to_null($qdata[6]);
                } else {
                    $question_position = F_zero_to_null(0);
                }
                if (isset($qdata[7])) {
                    $question_timer = intval($qdata[7]);
                } else {
                    $question_timer = 0;
                }
                if (isset($qdata[8])) {
                    $question_fullscreen = intval($qdata[8]);
                } else {
                    $question_fullscreen = 0;
                }
                if (isset($qdata[9])) {
                    $question_inline_answers = intval($qdata[9]);
                } else {
                    $question_inline_answers = 0;
                }
                if (isset($qdata[10])) {
                    $question_auto_next = intval($qdata[10]);
                } else {
                    $question_auto_next = 0;
                }
                // check if this question already exist
                $sql = 'SELECT question_id
					FROM '.K_TABLE_QUESTIONS.'
					WHERE ';
                if (K_DATABASE_TYPE == 'ORACLE') {
                    $sql .= 'dbms_lob.instr(question_description,\''.$question_description.'\',1,1)>0';
                } elseif ((K_DATABASE_TYPE == 'MYSQL') and K_MYSQL_QA_BIN_UNIQUITY) {
                    $sql .= 'question_description=\''.$question_description.'\' COLLATE utf8_bin';
                } else {
                    $sql .= 'question_description=\''.$question_description.'\'';
                }
                $sql .= ' AND question_subject_id='.$current_subject_id.' LIMIT 1';
                if ($r = F_db_query($sql, $db)) {
                    if ($m = F_db_fetch_array($r)) {
                        // get existing question ID
                        $current_question_id = $m['question_id'];
                        continue 2;
                    }
                } else {
                    F_display_db_error();
                }
                if (K_DATABASE_TYPE == 'MYSQL') {
                    // this section is to avoid the problems on MySQL string comparison
                    $maxkey = 240;
                    $strkeylimit = min($maxkey, strlen($question_description));
                    $stop = $maxkey / 3;
                    while (in_array(md5(strtolower(substr($current_subject_id.$question_description, 0, $strkeylimit))), $questionhash) and ($stop > 0)) {
                        // a similar question was already imported, so we change it a little bit to avoid duplicate keys
                        $question_description = '_'.$question_description;
                        $strkeylimit = min($maxkey, ($strkeylimit + 1));
                        $stop--; // variable used to avoid infinite loop
                    }
                    if ($stop == 0) {
                        F_print_error('ERROR', 'Unable to get unique question ID');
                        return;
                    }
                }
                $sql = 'START TRANSACTION';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error();
                }
                // insert question
                $sql = 'INSERT INTO '.K_TABLE_QUESTIONS.' (
					question_subject_id,
					question_description,
					question_explanation,
					question_type,
					question_difficulty,
					question_enabled,
					question_position,
					question_timer,
					question_fullscreen,
					question_inline_answers,
					question_auto_next
					) VALUES (
					'.$current_subject_id.',
					\''.$question_description.'\',
					'.$question_explanation.',
					\''.$question_type.'\',
					\''.$question_difficulty.'\',
					\''.$question_enabled.'\',
					'.$question_position.',
					\''.$question_timer.'\',
					\''.$question_fullscreen.'\',
					\''.$question_inline_answers.'\',
					\''.$question_auto_next.'\'
					)';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error(false);
                } else {
                    // get new question ID
                    $current_question_id = F_db_insert_id($db, K_TABLE_QUESTIONS, 'question_id');
                    if (K_DATABASE_TYPE == 'MYSQL') {
                        $questionhash[] = md5(strtolower(substr($current_subject_id.$question_description, 0, $strkeylimit)));
                    }
                }
                $sql = 'COMMIT';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error();
                }

                break;
            }
            case 'A': { // ANSWER
                $current_answer_id = 0;
                if (($current_module_id == 0) or ($current_subject_id == 0) or ($current_question_id == 0)) {
                    return;
                }
                if (!isset($qdata[4])) {
                    break;
                }
                $answer_enabled = intval($qdata[1]);
                $answer_description = F_escape_sql($db, F_tsv_to_text($qdata[2]), false);
                $answer_explanation = F_empty_to_null(F_tsv_to_text($qdata[3]));
                $answer_isright = intval($qdata[4]);
                if (isset($qdata[5])) {
                    $answer_position = F_zero_to_null($qdata[5]);
                } else {
                    $answer_position = F_zero_to_null(0);
                }
                if (isset($qdata[6])) {
                    $answer_keyboard_key = F_empty_to_null(F_tsv_to_text($qdata[6]));
                } else {
                    $answer_keyboard_key = F_empty_to_null('');
                }
                // check if this answer already exist
                $sql = 'SELECT answer_id
					FROM '.K_TABLE_ANSWERS.'
					WHERE ';
                if (K_DATABASE_TYPE == 'ORACLE') {
                    $sql .= 'dbms_lob.instr(answer_description, \''.$answer_description.'\',1,1)>0';
                } elseif ((K_DATABASE_TYPE == 'MYSQL') and K_MYSQL_QA_BIN_UNIQUITY) {
                    $sql .= 'answer_description=\''.$answer_description.'\' COLLATE utf8_bin';
                } else {
                    $sql .= 'answer_description=\''.$answer_description.'\'';
                }
                $sql .= ' AND answer_question_id='.$current_question_id.' LIMIT 1';
                if ($r = F_db_query($sql, $db)) {
                    if ($m = F_db_fetch_array($r)) {
                        // get existing subject ID
                        $current_answer_id = $m['answer_id'];
                    } else {
                        $sql = 'START TRANSACTION';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error();
                        }
						
						if($answer_isright==1){
							$answer_weight=100;
						}else{
							$answer_weight=0;
						}
                        $sql = 'INSERT INTO '.K_TABLE_ANSWERS.' (
							answer_question_id,
							answer_description,
							answer_explanation,
							answer_isright,
							answer_enabled,
							answer_position,
							answer_keyboard_key,
							answer_weight
							) VALUES (
							'.$current_question_id.',
							\''.$answer_description.'\',
							'.$answer_explanation.',
							\''.$answer_isright.'\',
							\''.$answer_enabled.'\',
							'.$answer_position.',
							'.$answer_keyboard_key.',
							'.$answer_weight.'
							)';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error(false);
                            F_db_query('ROLLBACK', $db);
                        } else {
                            // get new answer ID
                            $current_answer_id = F_db_insert_id($db, K_TABLE_ANSWERS, 'answer_id');
                        }
                        $sql = 'COMMIT';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error();
                        }
                    }
                } else {
                    F_display_db_error();
                }
                break;
            }
        } // end of switch
    } // end of while
    return true;
}

//============================================================+
// END OF FILE
//============================================================+
