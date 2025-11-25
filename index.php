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

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/timespent_form.php');
require_once($CFG->libdir . '/adminlib.php');

global $DB, $USER, $DB, $SESSION;

$PAGE->requires->jquery();
$PAGE->requires->js(new \moodle_url('https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js'), true);
$PAGE->requires->css(new \moodle_url('https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css'), true);
$PAGE->requires->css(new \moodle_url('https://cdn.datatables.net/buttons/1.6.4/css/buttons.dataTables.min.css'), true);
$PAGE->requires->js(new \moodle_url('https://cdn.datatables.net/buttons/1.6.4/js/dataTables.buttons.min.js'), true);
$PAGE->requires->js(new \moodle_url('https://cdn.datatables.net/buttons/1.6.4/js/buttons.flash.min.js'), true);
$PAGE->requires->js(new \moodle_url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'), true);
$PAGE->requires->js(new \moodle_url('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js'), true);
$PAGE->requires->js(new \moodle_url('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js'), true);
$PAGE->requires->js(new \moodle_url('https://cdn.datatables.net/buttons/1.6.4/js/buttons.html5.min.js'), true);
$PAGE->requires->js(new \moodle_url('https://cdn.datatables.net/buttons/1.6.4/js/buttons.print.min.js'), true);
///
$PAGE->requires->jquery('ui');
$PAGE->requires->js('/report/timespent/select2.min.js',true);
$PAGE->requires->css('/report/timespent/select2.min.css',true);
/* **************Update*****************  */

// Ved 05-02-2021

$id = optional_param('id', 1, PARAM_INT); // course id

if($id == 1){
    $course_id = property_exists($SESSION, 'course_id') ? $SESSION->course_id : 1;
    $course = $DB->get_record('course', array('id'=>$course_id), '*', MUST_EXIST);
}else{
    $course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
    $SESSION->course_id = $course->id;
}

 $PAGE->set_url('/report/timespent/index.php');
 $PAGE->set_pagelayout('report');
 require_login($course);
 $context = context_course::instance($course->id);
 require_capability('report/timespent:view', $context);

if($course->id == 1){
    admin_externalpage_setup('report_timespent', '', null, '', array('pagelayout'=>'report'));

    $PAGE->set_title(get_string('heading', 'report_timespent'));
    $PAGE->set_heading(get_string('heading', 'report_timespent'));
    echo $OUTPUT->header();

}else{
    $PAGE->set_title(get_string('heading', 'report_timespent'));
    $PAGE->set_heading(get_string('heading', 'report_timespent'));
    echo $OUTPUT->header();
}

/* **************END*****************  */



$mform =new report_timespent_form();

$mform->display();

$htmlContent="";
$status=0;

$course_name="";

$date = date('m-d-Y h:i:s', time());

if ($data = $mform->get_data()) {
    $data = data_submitted();
    $newData=$data;

    $user_type=$newData->type_group['type'];

    //for multiple selection
    $courseId=null;
    $couseIdLists=array();
    $flag=1;
    $sqlConditons="";
    $custom=$newData->courseid;
    foreach($newData->courseid as $key=>$values){

             $strTemp = trim($values);

            if(strlen($strTemp) > 0){
                if($flag==1){
                    $sqlConditons.='id='.$values." ";

                    $flag=0;
                }else{
                     $sqlConditons.='OR id='.$values." ";

                }
            }
    }

    $course = $DB->get_record('course', array('id' => $custom[0]));
    $course_name=$course->shortname;

    $object = new stdClass();
    $object->startDate=$newData->startdate;
    $object->endDate=$newData->enddate;
    $object->filterStatus=$data->checkbox;
    $object->user_type=$user_type;

    // Batch & Center code
    $object->centercode=$newData->centercode;
    $object->batchcode=$newData->batchcode;

    $content=calculation($sqlConditons,$custom,$object);
    $status=is_array($content);

    if($status==1){
         $htmlContent.=$content[0];
    }else{
        $htmlContent.=$content;
    }
}


echo '

<style>

::-webkit-scrollbar {
    width: 0.4em;
    height: 1em;
}

::-webkit-scrollbar-button {
    background: #f4f4f4;
}

::-webkit-scrollbar-thumb {
    background: #d6d5d5;
}

::-webkit-scrollbar-track-piece {
    background: #f4f4f4;
}

.dt-buttons{
    margin-bottom:10px;
}

.dt-button{
     background-color:#10b2b1;
     padding:6px;
     padding-left:7px;
     padding-right:7px;
     border:none;
     color:white;
     border-radius:3px;
}

</style>

';

echo $htmlContent;

echo $OUTPUT->footer();
echo html_writer::script('
    $("#id_courseid").select2();
    $("#id_centercode").select2();
    $("#id_batchcode").select2();
    
	$("#id_courseid").change(function(){
		     $("#id_centercode").find("option").remove();
		      $("#id_centercode").find("ul").remove();
             $("#id_batchcode").find("option").remove();
             var courseids = $(this).val();
             console.log(courseids);
             var response;
             $.ajax({
                type: "POST",
                url: "'.$CFG->wwwroot.'/report/timespent/process.php?action=filterajax&course=1",
                dataType: "json",
                data:{courseids:courseids},
                success: function (r) {
                       response += "<option value =null>--Select Centercode--</option>";
                     $.each(r, function( index, value){
                      response += "<option value = " + index + " >" +value + "</option>";

                     });
                     $("#id_centercode").find("option").remove();
                     $("#id_centercode").html(response);
                }
            });       
    });
    $("#id_courseid").change(function(){
             var courseids = $(this).val();
             console.log(courseids);
             var response;
             $("#id_centercode").find("option").remove();
             $("#id_centercode").find("ul").remove();
             $("#id_batchcode").find("option").remove();
             $.ajax({
                type: "POST",
                url: "'.$CFG->wwwroot.'/report/timespent/process.php?action=batchajax&batch=1",
                dataType: "json",
                data:{courseids:courseids},
                success: function (r) {
                       response += "<option value =null>--Select Batchcode--</option>";
                     $.each(r, function( index, value){
                      response += "<option value = " + index + " >" +value + "</option>";

                     });
                     $("#id_batchcode").find("option").remove();
                     $("#id_batchcode").html(response);
                }
            });       
    });

');
?>


<script>
 $(document).ready(function() {
    $('#studentlist').DataTable( {
        dom: 'Bfrtip',
        'scrollY': 400,
        'scrollX': true,
        buttons: [
            {
                extend: 'csvHtml5',
                text: 'CSV',
                filename: "<?php  echo $course_name."_".$date ?>"
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                filename: "<?php echo $course_name."_".$date ?>"
            }
        ]
    });
  
} );

</script>

