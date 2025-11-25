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
 
if (is_null($ADMIN->locate('wetpl'))) {

    $ADMIN->add('reports', new admin_category( 'wetpl', 'WETPL'));

}

$ADMIN->add('wetpl', new admin_externalpage('report_timespent',
        get_string('pluginname', 'report_timespent'),
        new moodle_url('/report/timespent/index.php'),'report/timespent:view'));
        
      
if ($ADMIN->fulltree) {
    include_once __DIR__.'/lib.php';
    
    $idletime_in_min = [1 => "1 min", 2 => "2 min", 3 => "3 min", 4 => "4 min"];

    for($i = 5; $i<=250; $i = $i+5){
        $idletime_in_min[$i] = $i." min";
    }
        
    // General settings
    $name = "Idle Time";
    $description = "Idle time is a time that an user
                    is unproductive due to factors that can either be controlled or 
                    uncontrolled by LMS. 
                    Idle time can be classified either as normal or abnormal. 
                    Minimizing idle time is key if a LMS wants to track users 
                    actual time spent on a particular activity or course in the 
                    LMS over a period of time.";

    $setting = $settings->add(
        new admin_setting_configselect(
            'timespent/idletime',
            $name,
            $description,
            'none',
            $idletime_in_min
        )
    );
}
