<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    report_timespent
 * @copyright  2018 Web Era Technology Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class report_timespent_form extends moodleform {

      public function definition() {
        global $CFG, $USER;
        global $DB;
        global $PAGE;
        global $COURSE;
        global $OUTPUT;

        $mform = $this->_form;

         //get course
		$courses=$DB->get_records_sql("select id, fullname, shortname from mdl_course where category<>0");

        $enrolledCourses=array();
        $enrolledCourses["Select"]=null;

        $context = context_course::instance($COURSE->id);

        $systemcontext = context_system::instance();

        if (!has_capability('report/timespent:localadminview', $systemcontext))
        {
            foreach($courses as $course){

                    //get course enrollment status of a user
                    $isEnroll=$this->getEnrollment($course);

                    if($isEnroll==1)
                        //array_push($enrolledCourses,$course);
                        $enrolledCourses[$course->id]=$course->fullname;

                }//foreach

        }//for
        else
        {
           foreach($courses as $course){

		  		$enrolledCourses[$course->id]=$course->fullname;
           }//foreach

        }//else

        $searchareas = $enrolledCourses;

        $areanames = array();
        foreach ($searchareas as $id => $searcharea) {
           $areanames[$id] = $searcharea;
        }
        $options = array(
            //'multiple' => false,

            'multiple' => true,
        );
        $select = $mform->addElement('select', 'courseid', get_string('selectcategory', 'report_timespent'), array(null=>'---Select---')+$areanames);
        $select->setMultiple(true);

         /* ---------------- Ehancement 23 Nov 2020 ---------------------------*/

       $batch_codes = report_timespent_get_code("batchcode");

       /* ---------------- Ehancement 05 FEB 2021 ---------------------------*/
	if (!has_capability('moodle/site:config', context_system::instance())) {
            profile_load_custom_fields($USER);
            $logged_user_profiles = $USER->profile;
            $logged_user_batchcodes = array_map('trim', explode(',', $logged_user_profiles['batchcode']));

            $list_temp = array();

            foreach($logged_user_batchcodes as $batch_code){
                $object = new stdClass;
                $object->data = $batch_code;
                array_push($list_temp, $object);
            }

           // $batch_codes = $list_temp;
       }

         /* ---------------- Ehancement 05 FEB 2021 END ---------------------------*/

       $center_codes = report_timespent_get_code("centercode");

       $search_batch = [];
       foreach($batch_codes as $batchcode){
            $lists = explode(",", $batchcode->data);
            foreach($lists as $list){
                $list = trim($list);
                $search_batch[$list] = $list;
            }
       }

       $search_centercode = [];
       foreach($center_codes as $centercode){
            $lists = explode(",", $centercode->data);
            foreach($lists as $list){
                $list = trim($list);
                $search_centercode[$list] = $list;
            }
       }

        $options = array(
            'multiple' => false,
        );

        $centerselect = $mform->addElement('select', 'centercode', get_string('centercode', 'report_timespent'), $search_centercode);
        $centerselect->setMultiple(true);
        $batchselect = $mform->addElement('select', 'batchcode', get_string('batchcode', 'report_timespent'), $search_batch);
        $batchselect->setMultiple(true);


        /* --------------------------- Ehancement 23 Nov 2020 END---------------------------------------- */

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'type', '', "Student", 5);
        $radioarray[] = $mform->createElement('radio', 'type', '', "Teacher", 3);
        $radioarray[] = $mform->createElement('radio', 'type', '', "All", 0);
        $mform->addGroup($radioarray, 'type_group', "", ' <br> ');
        $mform->setDefault('type', 0);

        //$mform->addHelpButton('type_group', 'usertype', "timespent");

         $mform->addElement('advcheckbox','checkbox','', 'Filter By ', array('group' => 1), array(0, 1));

        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'report_timespent'));

        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'report_timespent'));



        $this->add_action_buttons($cancel = false, $submitlabel="Submit");
        $mform->addElement('static', 'spacer', '', '');
}


  public function validation($data, $files) {
        global $CFG, $DB, $USER;
        $errors= array();
        $errors = parent::validation($data, $files);

        if(array_key_exists("courseid",$data)==0){

            echo "<div class='alert alert-danger'>
            <strong class='f-msg2'>Select a course</strong></div>";
            $errors['spacer'] = "error";
             // $errors['spacer'] = get_string('selectcategory', 'report_timespent');
         return $errors;
        }

        if($data['startdate']>$data['enddate'] && $data['checkbox']==1){
            $msg=get_string('datevalidation', 'report_timespent');

            echo "<div class='alert alert-danger'>
            <strong class='f-msg2'>$msg</strong></div>";

            //if start date is greater then end date
            $errors['spacer'] = "Hello";//get_string('datevalidation', 'report_timespent');

        }
        return $errors;

    }

    private function getEnrollment($course){
            global $USER;
            $context = context_course::instance($course->id);
            $isEnroll=is_enrolled($context,$USER->id, '', true);

        return $isEnroll;
    }
}

