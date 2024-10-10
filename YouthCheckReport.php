<?php

namespace Stanford\YouthCheckReport;

require_once "emLoggerTrait.php";

use ExternalModules\ExternalModules;
class YouthCheckReport extends \ExternalModules\AbstractExternalModule
{

    use emLoggerTrait;

    private $forms = [];

    public function __construct()
    {
        parent::__construct();
        // Other code to run when object is instantiated
    }


    // allow all users to see the report link
    public function redcap_module_link_check_display($project_id, $link)
    {
        if (ExternalModules::isNoAuth()) {
            // Anyone can view NOAUTH pages.
            // Remember, redcap_module_link_check_display() is currently only called for pages defined in config.json.
            return $link;
        }

        if ($this->isSuperUser()) {
            // superusers can see all pages
            return $link;
        }

        $username = ExternalModules::getUsername();
        if (!empty($project_id) && $username) {
            return $link;
        }

        return null;
    }

    public function redcap_module_ajax($action, $payload, $project_id, $record, $instrument, $event_id, $repeat_instance, $survey_hash, $response_id, $survey_queue_hash, $page, $page_full, $user_id, $group_id)
    {
        if (isset($action)) {
            if ($action == 'get_record' and $payload[\REDCap::getRecordIdField()] != '') {
                $user_rights = \REDCap::getUserRights(USERID)[USERID];
                $record_id = $payload[\REDCap::getRecordIdField()];
                // if user is assigned to DAG. make sure requested record is same dag as user.
                if ($user_rights['group_id'] != '') {
                    if ($user_rights['group_id'] != $this->getDAG($record_id)) {
                        throw new \Exception("You can not view this record");
                    }
                }

                $param = array(
                    'project_id' => $this->getProjectId(),
                    'records' => [$record_id],
                    'format' => 'array'
                );
                $data = \REDCap::getData($param);
                $data = $data[$record_id][$this->getFirstEventId()];
                if(is_null($data)){
                    throw new \Exception("No data found for this record <b>$record_id</b>");
                }
                $result = $this->processSummaryData($data, []);
                $result = $this->processFurtherEval($data, $result);
                $result = $this->processClinicallySignificant($data, $result);
                $result = $this->processCheckList($data, $result);
                $result = $this->processScoreTable($data, $result);
                $result = $this->processImpairStatus($data, $result);
                $result = $this->processImpairSymptom($data, $result);
                $result = $this->processSymptomScaleReconstruction($data, $result);
                return $result;
            }
        }
    }

    private function processSymptomScaleReconstruction($data, $result)
    {
        global $Proj;

        $forms = parseEnum($Proj->metadata['sections']['element_enum']);
        foreach ($this->getForms() as $item) {



            $formName = $Proj->forms[$item['form']]['menu'];

            if(!$this->isFormEnabled($formName, $data, $forms)){
                continue;
            }

            if ($item['has-score'] == '1') {
                $result['symptom_scale_reconstruction'][$formName]['header'] = array('Question', 'Answer', 'Score');
            } else {
                $result['symptom_scale_reconstruction'][$formName]['header'] = array('Question', 'Answer');
            }

            $fieldNames = \REDCap::getFieldNames($item['form']);

            foreach ($fieldNames as $fieldname) {
                //if fieldname is <something>_skip,then skip
                if (preg_match('/' . preg_quote('_skip', '/') . '$/', $fieldname)) {
                    //Plugin::log($foo, "DEBUG" ,"endswith status for $fieldname is $foo");
                    continue;
                }

                //if fieldname is instrument_complete,then skip
                if (trim($fieldname) == $item['form'] . '_complete') {
                    continue;
                }

                //if fieldname is threshold present skip
                if (trim($fieldname) == 'sc_pr_' . $item['form']) {
                    continue;
                }

                //if fieldname is sc_total_dps
                if (trim($fieldname) == 'sc_total_dps') {
                    continue;
                }


                // skip empty fields
                if (($data[$fieldname]) == '') {
                    continue;
                } else {
                    $value = $data[$fieldname];
                    $array = array();

                    //if array, then it's a checkbox, check if all are 0s
                    if (is_array($value) and (array_sum($value) == 0)) {
                        continue;
                    }

                    if (trim($fieldname) == $item['score-field']) {
                        $array['label'] = '<strong>' . str_replace('(HIDDEN-SURVEY)', '', $Proj->metadata[$fieldname]['element_label']) . '</strong>';
                        $array['decode'] = '';

                    } else {
                        $array['label'] = $Proj->metadata[$fieldname]['element_label'];
                        if($Proj->metadata[$fieldname]['element_type'] == 'text'){
                            $array['decode'] = $value;
                        }
                        else{
                            $array['decode'] = $this->getEnumLabel($fieldname, $value);
                        }

                    }

                    if ($item['has-score'] and !is_array($value)) {
                        $array['value'] = $value;
                    }
                    $result['symptom_scale_reconstruction'][$formName]['rows'][] = $array;
                }


            }

        }
        return $result;
    }

    private function processImpairStatus($data, $result)
    {
        $impairStatus = $this->getImpairStatusData($data);
        if (!empty($impairStatus)) {
            $result['impair_status']['header'] = ['Status'];
            $result['impair_status']['rows'] = [$impairStatus];
        } else {
            $result['impair_status'] = [];
        }
        return $result;
    }

    private function processImpairSymptom($data, $result)
    {
        $impairSymptom = $this->getImpairSymptom($data);
        if (!empty($impairSymptom)) {
            $result['impair_symptom']['header'] = ['Symptom Area'];
            $result['impair_symptom']['rows'] = [$impairSymptom];
        } else {
            $result['impair_symptom'] = [];
        }
        return $result;
    }

    private function getImpairSymptom($screen)
    {
        $symptom = array();
        /**
         * [q111a(A)] = '1' or [q112a(A)] = '1' or [q113a(A)] = '1' or [q114a(A)] = '1' or [q115a(A)] = '1' or [q116a(A)] = '1' or [q117a(A)] = '1'
         */
        if (($screen['q111a___a'] == '1') || ($screen['q112a___a'] == '1') || ($screen['q113a___a'] == '1') || ($screen['q114a___a'] == '1') ||
            ($screen['q115a___a'] == '1') || ($screen['q116a___a'] == '1') || ($screen['q117a___a'] == '1')) {
            $symptom[][] = 'Anxious/worried';
        }
        if (($screen['q111a___b'] == '1') || ($screen['q112a___b'] == '1') || ($screen['q113a___b'] == '1') || ($screen['q114a___b'] == '1') ||
            ($screen['q115a___b'] == '1') || ($screen['q116a___b'] == '1') || ($screen['q117a___b'] == '1')) {
            $symptom[][] = 'Sad/depressed';
        }
        if (($screen['q111a___c'] == '1') || ($screen['q112a___c'] == '1') || ($screen['q113a___c'] == '1') || ($screen['q114a___c'] == '1') ||
            ($screen['q115a___c'] == '1') || ($screen['q116a___c'] == '1') || ($screen['q117a___c'] == '1')) {
            $symptom[][] = 'Angry/irritable';
        }
//exclude Food, Eating or Weight
//     if (($screen['q111a___d'] == '1') || ($screen['q112a___d'] == '1') || ($screen['q113a___d'] == '1') || ($screen['q114a___d'] == '1') ||
//             ($screen['q115a___d'] == '1') || ($screen['q116a___d'] == '1') ||($screen['q117a___d'] == '1') ){
//                 $symptom[] =  'Food/Weight';
//     }
        if (($screen['q111a___e'] == '1') || ($screen['q112a___e'] == '1') || ($screen['q113a___e'] == '1') || ($screen['q114a___e'] == '1') ||
            ($screen['q115a___e'] == '1') || ($screen['q116a___e'] == '1') || ($screen['q117a___e'] == '1')) {
            $symptom[][] = 'Behavior';
        }
        if (($screen['q111a___f'] == '1') || ($screen['q112a___f'] == '1') || ($screen['q113a___f'] == '1') || ($screen['q114a___f'] == '1') ||
            ($screen['q115a___f'] == '1') || ($screen['q116a___f'] == '1') || ($screen['q117a___f'] == '1')) {
            $symptom[][] = 'Alcohol/drugs';
        }
        if (($screen['q111a___g'] == '1') || ($screen['q112a___g'] == '1') || ($screen['q113a___g'] == '1') || ($screen['q114a___g'] == '1') ||
            ($screen['q115a___g'] == '1') || ($screen['q116a___g'] == '1') || ($screen['q117a___g'] == '1')) {
            $symptom[][] = 'Other';
        }

        //get the Rx impairment symptoms
        if (($screen['q118a___a'] == '1')) {
            $symptom[][] = 'Rx: Anxious/worried';
        }
        if (($screen['q118a___b'] == '1')) {
            $symptom[][] = 'Rx: Sad/depressed';
        }
        if (($screen['q118a___c'] == '1')) {
            $symptom[][] = 'Rx: Angry/irritable';
        }
        //exclude Food, Eating or Weight
        //     if (($screen['q118a___d'] == '1')){
        //                 $symptom[] =  'Rx: Food/Weight';
        //     }
        if (($screen['q118a___e'] == '1')) {
            $symptom[][] = 'Rx: Behavior';
        }
        if (($screen['q118a___f'] == '1')) {
            $symptom[][] = 'Rx: Alcohol/drugs';
        }
        if (($screen['q118a___g'] == '1')) {
            $symptom[][] = 'Rx: Other';
        }


        return $symptom;
    }

    private function getImpairStatusData($screen)
    {
        if ($screen['sc_impairment'] >= 6) {
            return 'PRESENT';
        } else {
            return 'ABSENT';
        }
    }

    private function processScoreTable($data, $result)
    {
        $scoreTable = $this->getScoreTableData($data);
        if (!empty($scoreTable)) {
            $result['score_table']['header'] = array('Section', 'Status', 'Survey Score', 'Threshold');
            $result['score_table']['rows'] = $scoreTable;
        } else {
            $result['score_table'] = [];
        }
        return $result;
    }

    private function getScoreTableData($screen)
    {
        $table_array = array();
//        $modules[3] = array('sc_social_phobia', 'Social Phobia', 2);
//        $modules[4] = array('sc_separation_anxiety', 'Separation Anxiety', 4);
//        $modules[5] = array('sc_agoraphobia', 'Agoraphobia', 2);
//        $modules[6] = array('sc_panic_attacks', 'Panic Attacks', 2);
//        $modules[7] = array('sc_general_anxiety', 'General Anxiety', 3);
//        $modules[8] = array('sc_specific_phobia', 'Specific Phobia', 2);
//        $modules[9] = array('sc_ocd', 'OCD', 3);
//        $modules[10] = array('sc_ptsd', 'PTSD', 6);
//        $modules[11] = array('sc_eating_disorder', 'Eating Disorder', 2);
//        $modules[12] = array('sc_depression', 'Depression', 5);
//        $modules[13] = array('sc_mania', 'Mania', 2);
//        $modules[14] = array('sc_adhd', 'Attention Deficit Hyperactivity', 4);
//        $modules[15] = array('sc_odd', 'Oppositional Defiant', 4);
//        $modules[16] = array('sc_conduct_disorder', 'Conduct', 2);
//        $modules[17] = array('sc_alcohol', 'Alcohol', 2);
//        $modules[18] = array('sc_marijuana', 'Marijuana', 2);
//        $modules[19] = array('sc_other_substances', 'Other Substance', 1);
        $modules = $this->getForms();

        global $Proj;

        $forms = parseEnum($Proj->metadata['sections']['element_enum']);
        foreach ($modules as $module) {

            // for forms without score like health and demographics skip this loop
            if (!$module['has-score']) {
                continue;
            }

            $formName = $Proj->forms[$module['form']]['menu'];
            if (!$this->isFormEnabled($formName, $screen, $forms)) {
                continue;
            }

            //only if module is selected

            if (!$screen[$module['score-field']] or $screen[$module['score-field']] == 0) {
                $status = 'Absent';
            } else if ($screen[$module['score-field']] >= $module['score-value']) {
                $status = '<b>Present<b>';
            } else {
                $status = 'Possible';
            }
//            Plugin::log("$key and $value[1] has $status: $value[0]: " . $screen[$value[0]]);
            $table_array[] = array($formName, $status, $screen[$module['score-field']], $module['score-value']);

        }
        return $table_array;
    }

    private function getEnumLabel($field, $value)
    {
        global $Proj;

        $options = parseEnum($Proj->metadata[$field]['element_enum']);

        // this mean field is a checkbox and user might select multiple values. So we need to loop through the values
        if(is_array($value)){
            // we need the check the value of each selected option.
            $found = array_keys($value, '1');
            $result = '';
            foreach ($found as $item){
                $result  .= $options[$item] . '<br>';
            }
            return $result ? rtrim($result, '<br>') : '';
        }
        return $options[$value];
    }

    /**
     * @throws \Exception
     */
    public function isFormEnabled($formName, $data, $forms)
    {
        foreach ($forms as $key => $item) {
            if (strtolower($formName) === trim(strtolower(strip_tags($item)))) {
                return $data['sections'][$key];
            }
        }
        throw new \Exception("No match was found for $formName");
    }

    private function processSummaryData($data, $result)
    {
        $result['summary']['test_id'] = $data['test_id'];
        $result['summary']['score_total_dps'] = $data['sc_total_dps'] ?: 0;
        $result['summary']['score_impairment'] = $data['sc_impairment'] ?: 0;
        $result['summary']['interview_date'] = $data['interview_date'];
        return $result;
    }

    private function processFurtherEval($data, $result)
    {
        $impair_list = $this->listFurtherEvaluation($data);
        $result['further_eval']['impair_list'] = $impair_list;
        $result['further_eval']['open_ended_response'] = $data['q119'];
        $result['further_eval']['recommend_clinical_interview'] = '';
        return $result;
    }

    private function processClinicallySignificant($data, $result)
    {
        $clinicalSign = $this->getClinicallySignificant($data);
        if (!empty($clinicalSign)) {
            $result['clinical_significant']['header'] = [];
            $result['clinical_significant']['rows'] = $clinicalSign;
        } else {
            $result['clinical_significant'] = [];
        }

        return $result;
    }

    private function processCheckList($data, $result)
    {
        $list = $this->getCheckList($data);
        if (!empty($list)) {
            $result['check_list']['header'] = [];
            $result['check_list']['rows'] = $list;
        } else {
            $result['check_list'] = [];
        }

        return $result;
    }


    private function getCheckList($screen)
    {
        if ($screen['q14'] == '1') {
            $check_list[][] = '<p style="color:red;">Check AGORAPHOBIA: most people in neighborhood worried about going out</p>';
        }
        if ($screen['q16'] == '1') {
            $check_list[][] = '<p style="color:red;">Check AGORAPHOBIA: could travel/go out alone if had to...</p>';
        }
        if ($screen['q21'] == '1') {
            $check_list[][] = '<p style="color:red;">Check PANIC: afraid/couldn\'t breathe during asthma attack...</p>';
        }
        if ($screen['q38'] == '1') {
            $check_list[][] = '<p style="color:red;">Check OCD: liked to count/check/wash things repeatedly...</p>';
        }
        if ($screen['q39'] == '1') {
            $check_list[][] = '<p style="color:red;">Check OCD: told to count/check/wash things...</p>';
        }
        if ($screen['q40'] == '0') {
            $check_list[][] = '<p style="color:red;">Check OCD: didn\'t wish to stop counting/checking/washing...</p>';
        }
        if ($screen['q41'] == '0') {
            $check_list[][] = '<p style="color:red;">Check OCD: didn\'t spend much time doing things repeatedly...</p>';
        }

        return $check_list;
    }

    private function listFurtherEvaluation($screen)
    {
        //check suicide

        if ($screen['q66'] == '1') {
            $list[] = '<span style="color:#FF0000"> Suicidal ideation endorsed:</span> Has there been a time when you thought seriously about killing yourself?';
        }
        if ($screen['q67'] == '1') {
            $list[] = '<span style="color:#FF0000"> Suicidal attempt endorsed:</span> Have you tried to kill yourself in the last year?';
        }
        if ($screen['sc_impairment'] >= 6) {
//        $sum = $screen['sc_pr_social_phobia']+ $screen['sc_pr_panic_attacks']+
//                $screen['sc_pr_general_anxiety'] + $screen['sc_pr_ocd'] +
//                $screen['sc_pr_depression'];
            //php8 empty string no longer equals 0 and null coalesce
            $sum = ($screen['sc_pr_social_phobia'] == '' ? 0 : $screen['sc_pr_social_phobia'] ?? 0) +
                ($screen['sc_pr_panic_attacks'] == '' ? 0 : $screen['sc_pr_panic_attacks'] ?? 0) +
                ($screen['sc_pr_general_anxiety'] == '' ? 0 : $screen['sc_pr_general_anxiety'] ?? 0) +
                ($screen['sc_pr_ocd'] == '' ? 0 : $screen['sc_pr_ocd'] ?? 0) +
                ($screen['sc_early_psychosis'] == '' ? 0 : $screen['sc_early_psychosis'] ?? 0) +
                ($screen['sc_pr_depression'] == '' ? 0 : $screen['sc_pr_depression'] ?? 0);

            if ($sum > 0) {
                $impair_list = '';
                if ($screen['sc_pr_social_phobia'] == '1') {
                    $impair_list .= ' <br>Social Phobia';
                }
                if ($screen['sc_pr_panic_attacks'] == '1') {
                    $impair_list .= ' <br>Panic Attack';
                }
                if ($screen['sc_pr_general_anxiety'] == '1') {
                    $impair_list .= ' <br>General Anxiety';
                }
                if ($screen['sc_pr_ocd'] == '1') {
                    $impair_list .= ' <br>OCD';
                }
                if ($screen['sc_pr_depression'] == '1') {
                    $impair_list .= ' <br>Depression';
                }
                if ($screen['sc_pr_early_psychosis'] == '1') {
                    $impair_list .= ' <br>Early Psychosis';
                }
                $list[] = '<span style="color:#FF0000">Impairment score of ' . $screen['sc_impairment'] . ' (threshold of 6) and the following disorders are present: </span>' . $impair_list;
            }
        }

        if ($screen['sc_total_dps'] >= 9) {
            $list[] = '<span style="color:#FF0000"> Total DPS Symptom Score >= 9 : </span> ' . $screen['sc_total_dps'];
        }
        if ($screen['sc_pr_alcohol'] == '1') {
            $list[] = '<span style="color:#FF0000">Alcohol is present</span>';
        }
        if ($screen['sc_pr_marijuana'] == '1') {
            $list[] = '<span style="color:#FF0000">Marijuana is present</span>';
        }
        if ($screen['sc_pr_other_substances'] == '1') {
            $list[] = '<span style="color:#FF0000">Other substance is present</span>';
        }


        return $list;
    }

    private function getClinicallySignificant($screen)
    {
        //if eyeproblems but no eye doctor
        if (($screen['hlth_see'] == '1') && ($screen['hlth_eye_doctor'] == '0')) {
            $significant[][] = 'Vision Problems';
        }

        //if ear problems but no ear doctor
        if (($screen['hlth_hear'] == '1') && ($screen['hlth_ear_dr'] == '0')) {
            $significant[][] = 'Hearing Problems';
        }

        //if tooth problems but no dentist
        if (($screen['hlth_tooth'] == '1') && ($screen['hlth_dentist'] == '0')) {
            $significant[][] = 'Dental Problems';
        }
        if (($screen['q66'] == '1')) {
            $significant[][] = 'Suicidal Ideation';
        }
        if (($screen['q67'] == '1')) {
            $significant[][] = 'Suicidal Attempt';
        }
        if (($screen['q118'] == '1')) {
            $significant[][] = 'Seen Professional';
        }

        return $significant;
    }


    public function getRecordsList()
    {
        // get user dag id to list only records attached to the same dag
        $user_rights = \REDCap::getUserRights(USERID)[USERID];
        $records = \Records::getRecordListSingleDag($this->getProjectId(), $user_rights['group_id']);
        $result = array();
        foreach ($records as $key => $record) {
            $result[] = array('id' => $key, 'label' => $record);
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getForms(): array
    {
        if (!$this->forms) {
            $this->setForms();
        }
        return $this->forms;
    }

    /**
     * @param array $forms
     */
    public function setForms(): void
    {
        $this->forms = $this->getSubSettings('forms', $this->getProjectId());;;
    }


}
