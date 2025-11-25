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
 
global $DB;

$PAGE->requires->jquery();
$PAGE->requires->css('/report/timespent/css/jquery.dataTables.css');
$PAGE->requires->js('/report/timespent/js/jquery.dataTables.min.js', true);
$PAGE->requires->js('/report/timespent/js/select2.full.js', true);
$PAGE->requires->css('/report/timespent/css/select2.min.css');
 
 
// Start the page.
admin_externalpage_setup('report_timespent');

echo $OUTPUT->header().
    $OUTPUT->heading(get_string('heading', 'report_timespent'));
  
$mform =new report_timespent_form();
 
$mform->display();
 
$htmlContent="";
$status=0;

if ($data = $mform->get_data()) {
   
    $newData=$data;
   
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
     
    $object = new stdClass();
    $object->startDate=$newData->startdate;
    $object->endDate=$newData->enddate;
    $object->filterStatus=$data->checkbox;
     
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
    width: 0.01em;
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
 

</style>

';

if(strlen($htmlContent)>0 && $status==0){ 
echo ' 
<a id="dlink"  style="display:none;"></a>
    <a href="#" onclick="tableToExcel(`studentlist`, `W3C Example Table`, `MyFile.xls`)">Download Excel</a><br><br>';
}

echo $htmlContent;
 
    /***SELECT u.id as userid,u.firstname,u.lastname,u.email,u.idnumber,
				c.id as courseid,c.fullname as coursename, c.fullname,
				ue.timecreated		 
		        FROM {course} AS c
                JOIN {enrol} AS e ON c.id = e.courseid and e.enrol in('auto','manual','self')
                JOIN {user_enrolments} AS ue ON ue.enrolid = e.id
                JOIN {user} AS u ON u.id = ue.userid
                JOIN {course_completions} AS cc ON cc.course = e.courseid and cc.userid=ue.userid

                
                WHERE e.courseid={$filter_courses} AND u.deleted=0 AND u.suspended=0******/
								  
echo $OUTPUT->footer();
?>
<!--
<script>
$(document).ready(function(){
     $('#studentlist').DataTable();
    $(".assign_training_at").select2();
    
    $(".learningplan-assign-course").select2({
        placeholder: "Select Courses"
    });
    $( ".assign_courses_container" ).hide();
    
    //$(".learningplan-assign-users").select2({
    //    placeholder: "Select Users"
    //});
    $( ".assign_users_container" ).hide();
});  
</script>
-->

<script type="text/javascript">
var tableToExcel = (function() {
  var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
    , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
  return function(table, name) {
    if (!table.nodeType) table = document.getElementById(table)
    var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
    window.location.href = uri + base64(format(template, ctx))
  }
})()
</script>
 

<script>
$(document).ready(function(){
     $('#studentlist').DataTable( );
    $(".assign_training_at").select2();
    
    $(".learningplan-assign-course").select2({
        placeholder: "Select Courses"
    });
    $( ".assign_courses_container" ).hide();
    
    //$(".learningplan-assign-users").select2({
    //    placeholder: "Select Users"
    //});
    $( ".assign_users_container" ).hide();
});
 
    
</script>
 


