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

$action = optional_param('action',  '',  PARAM_RAW);//userid
$courses = $_REQUEST['courseids'];
$courselist = implode(',',$courses);

if($action == 'filterajax' && $courselist){
    $final_result = array();
       $prepareSQL = "
                SELECT distinct centercode.data as centercode
                FROM
                mdl_role_assignments ra
                JOIN mdl_user u ON u.id = ra.userid
                JOIN mdl_role r ON r.id = ra.roleid
                JOIN mdl_context cxt ON cxt.id = ra.contextid
                JOIN mdl_course c ON c.id = cxt.instanceid
                LEFT JOIN mdl_user_info_data centercode ON centercode.userid = ra.userid AND centercode.fieldid = (SELECT id FROM mdl_user_info_field WHERE shortname ='centercode')
                WHERE ra.userid = u.id
                AND ra.contextid = cxt.id
                AND cxt.contextlevel =50
                AND cxt.instanceid = c.id
                AND c.id IN($courselist)
                AND u.deleted = 0
                 GROUP BY centercode.data,ra.userid";
          //echo $prepareSQL;die;
          $records = $DB->get_recordset_sql($prepareSQL);
          foreach($records as $r){
              //$centerid = $DB->get_field('user_info_data','id',['data'=>$r->centercode]);
              $final_result[$r->centercode] = $r->centercode;
          }

    $display = json_encode($final_result);
    echo $display;
    die;
}
//
if($action == 'batchajax' && $courselist && optional_param('batch',0,PARAM_INT)){
    $final_result = array();
       $prepareSQL = "
                SELECT distinct batchcode.data as batchcode
                FROM
                mdl_role_assignments ra
                JOIN mdl_user u ON u.id = ra.userid
                JOIN mdl_role r ON r.id = ra.roleid
                JOIN mdl_context cxt ON cxt.id = ra.contextid
                JOIN mdl_course c ON c.id = cxt.instanceid
                LEFT JOIN mdl_user_info_data batchcode ON batchcode.userid = ra.userid AND batchcode.fieldid = (SELECT id FROM mdl_user_info_field WHERE shortname ='batchcode')
                WHERE ra.userid = u.id
                AND ra.contextid = cxt.id
                AND cxt.contextlevel =50
                AND cxt.instanceid = c.id
                AND c.id IN($courselist)
                AND u.deleted = 0
                 GROUP BY batchcode.data,ra.userid";
          //echo $prepareSQL;die;
          $records = $DB->get_recordset_sql($prepareSQL);
          foreach($records as $r){
              //$centerid = $DB->get_field('user_info_data','id',['data'=>$r->centercode]);
               $exparr = explode(',',$r->batchcode);
               if(count($exparr) > 1){
                  continue;
               }
              $final_result[$r->batchcode] = $r->batchcode;
          }

    $display = json_encode($final_result);
    echo $display;
    die;
}