<?php

 function getTimeSpentOnActivity($userId,$activityId){
    global $DB;
    //get course
     $records=$DB->get_records_sql("SELECT id,target,action,contextinstanceid,timecreated FROM {logstore_standard_log} WHERE userid=$userId ORDER BY id ASC");

    $logs=array();
    foreach($records as $rec){
        array_push($logs,$rec);
    }

    $sumOfInterval=array();
    $lastRecordedTime=null;
    $length=sizeof($logs);
    $flag=false;
    
    $config = get_config('timespent');
 
    $idletime = 5000;

    if(property_exists($config, 'idletime')){
        $idletime = $config->idletime;
    }

    for($i=0; $i<=$length; $i++){


        //if the user is sitting ideal in a activity then its time won't be calculated.
        if($logs[$i]->contextinstanceid==$activityId && $i==$length-1){
             array_push($sumOfInterval,
                               timeCalculation(
                                                        $logs[$i]->timecreated,
                                                        $logs[$i]->timecreated)
                                );
            break;
        }

           if($logs[$i]->contextinstanceid==$activityId && $logs[$i+1]->contextinstanceid!=$activityId){

                 if($flag!=true){
                 
                 /*
                    array_push($sumOfInterval,
                               timeCalculation(
                                                        $logs[$i]->timecreated,
                                                        $logs[$i+1]->timecreated)
                                );
                 */
                 
                    $timecreated= date('Y-m-d H:i:s',$logs[$i]->timecreated);
                    $timecreatedend= date('Y-m-d H:i:s',$logs[$i+1]->timecreated);

                    $date1 = new DateTime($timecreated);
                    $date2 = new DateTime($timecreatedend);

                    $diff_mins = abs($date2->getTimestamp() - $date1->getTimestamp()) / 60;
                    //print_object("Idle Time".$idletime);die();
                    if($diff_mins < $idletime){
                        array_push($sumOfInterval,
                        timeCalculation(
                                         $logs[$i]->timecreated,
                                         $logs[$i+1]->timecreated)
                         );
                    }
                 
                    //calculate the time interval by taking logs[i]->timecreated as Start Time
                    //add the result of time interval to $sumOfInterval
                }else{

                     //login check
                     if($logs[$i+1]->action=="loggedin"){
                          array_push($sumOfInterval,timeCalculation($lastRecordedTime,$logs[$i]->timecreated));
                     }else{
                        //array_push($sumOfInterval,$lastRecordedTime."hello");
                        array_push($sumOfInterval,timeCalculation($lastRecordedTime,$logs[$i+1]->timecreated));
                     }
                    //here lastRecordedTime will become a Start Time
                    //calculate the time interval by taking $lastRecordedTime as Start Time
                    //here end time will be $logs[$i+1]->timecreated
                    //add the result of time interval to $sumOfInterval

                    $lastRecordedTime=null;
                    $flag=false;
                }//else

                }else if($logs[$i]->contextinstanceid==$activityId && $logs[$i+1]->contextinstanceid==$activityId){
                    if($lastRecordedTime==null){
                        $lastRecordedTime=$logs[$i]->timecreated;
				        $flag=true;
                    }
			 continue;
           }//else-if
        }//foreach

       //$dateTime  = "2003-03-03 03:06:18";
       //print_r(timeFormating($dateTime));die();

        //print_r($sumOfInterval);die();
        $timeInterval="2000-01-01 00:00:00";
        foreach($sumOfInterval as $key => $value){
              $date = new DateTime($timeInterval);
              $date->add(new DateInterval($value));
              $timeInterval=$date->format('Y-m-d H:i:s');
        }


		 //print_r($timeInterval);die();
		 return array(timeFormating($timeInterval),$sumOfInterval);
}

function report_timespent_stringtime_to_seconds($seconds){

   $then = new DateTime(date('Y-m-d H:i:s', 0));
   $now = new DateTime(date('Y-m-d H:i:s', $seconds));
   $diff = $then->diff($now);
   $temp=array('years' => $diff->y, 'months' => $diff->m, 'days' => $diff->d, 'hours' => $diff->h, 'minutes' => $diff->i, 'seconds' => $diff->s);

   return $temp;

}

function timeCalculation($startTimeInSec,$EndTimeInSec){

        $startTime=date('Y-m-d H:i:s', $startTimeInSec);
        $EndTime=date('Y-m-d H:i:s', $EndTimeInSec);

        $datetime1 = new DateTime($startTime);//start time
        $datetime2 = new DateTime($EndTime);//end time
        $interval = $datetime1->diff($datetime2);
        //return $interval->format('%Y years %m months %d days %H hours %i minutes %s seconds');
        //return $interval->format('%Y-%m-%d %H:%i:%s');
        //P8Y5M5DT1H3M2S

        return $interval->format('P%YY%mM%dDT%HH%iM%sS');

    }

function timeFormating($dateTime){

        //$dateTime  = "2003-01-01 00:06:18";
        $pieces = explode(" ", $dateTime);
        $years=$pieces[0];

        $ymd = explode("-", $years);
        $year=$ymd[0];
        $months=$ymd[1]-1;
        $day=$ymd[2]-1;

        $noOfMonths=0;
        for($i=$months; $i>0; $i--)
            $noOfMonths++;

        $days=0;
        for($i=$day; $i>0; $i--)
            $days++;

        $noOfYears=$years-2000;

        $timestamp=$pieces[1];
        $hts = explode(":", $timestamp);
        $hour=$hts[0];
        $min=$hts[1];
        $sec=$hts[2];

        $finalDate="";

        if($noOfYears!=0){
           $finalDate.=$noOfYears." Year ";
        }

         if($noOfMonths!=0){
           $finalDate.=$noOfMonths." Months ";
        }

         if($days!=0){
           $finalDate.=$days." Days ";
        }



        if($hour!=0){
              $finalDate.=$hour." Hours ";
        }

        $finalDate.=$min." Min ".$sec." Sec";

        return $finalDate;
}

function getActivitiesNcolspan($courseId){
			$activities=get_array_of_activities($courseId);

			$visibleActivities=new ArrayObject();
			foreach($activities as $activity){
                //print_r($activities);

                if(!property_exists($activity, 'deletioninprogress')){
                    $activity->deletioninprogress = 0;
                }

				if($activity->visible==1 && $activity->deletioninprogress==0){
				//if($activity->visible==1){
					$visibleActivities->append($activity);
				}
			}
			$count=count($visibleActivities);
			return array($visibleActivities,$count);
}

function report_timespent_getBatchSelectionQuery($object){

    $filter_batchcode_centercode = '';
  
    if(strlen($object->batchcode) !=0){
    
        $batchcode = $object->batchcode;
      
        $filter_batchcode_centercode = " AND batchcode.data IN('$batchcode') AND batchcode.data IS NOT NULL ";
    }
    
    if(strlen($object->centercode) !=0){
    
        $centercode = $object->centercode;
      
        $filter_batchcode_centercode = " AND centercode.data IN('$centercode') AND centercode.data IS NOT NULL ";
    }

    if(strlen($object->batchcode) !=0 && strlen($object->centercode) !=0){
    
        $batchcode =$object->batchcode;
         
        $filter_batch = " AND batchcode.data IN('$batchcode') AND batchcode.data IS NOT NULL ";
        
        $centercode = $object->centercode;
      
        $filter_centercode = " AND centercode.data IN('$centercode') AND centercode.data IS NOT NULL ";
        
        $filter_batchcode_centercode =$filter_batch.$filter_centercode;
    }
    
    return $filter_batchcode_centercode;
}

function calculation($sqlConditons='',$custom=false, $object){

    global $DB, $USER;
    $courselist=implode(',',$custom);

    $systemcontext = context_system::instance();
    
    $filter_batchcode_centercode = report_timespent_getBatchSelectionQuery($object);

    if(sizeof($courselist)==0){
        return;
    }
     

    $user_type=$object->user_type;
    $partial_condition="";
    if($user_type==5){
        $partial_condition="AND  roleid = 5";
    }else if($user_type==3){
        $partial_condition="AND  roleid = 3";
    }else{
        $partial_condition="";//any number
    }

    //for enhancement
    $batch_code_en=array();
    $batch_code_avg_completion_rate_en=array();

    $prepareSQL="";
    if($object->filterStatus==1){
        $startDate=date('Y-m-d', $object->startDate);
        $endDate=date('Y-m-d', $object->endDate);
      
               
      $prepareSQL = "
              SELECT 
              u.id,
              u.username,
              u.firstname,
              u.lastname,
              u.email,
              u.idnumber,
              c.id as courseid,
              roleid,     
              c.fullname as coursename,
              c.fullname,     
              batchcode.data as batchcode,
              centercode.data as centercode,
              DATE_FORMAT(FROM_UNIXTIME(ra.timemodified), '%Y-%m-%d') as timecreated
              FROM
              mdl_role_assignments ra
              JOIN mdl_user u ON u.id = ra.userid
              JOIN mdl_role r ON r.id = ra.roleid
              JOIN mdl_context cxt ON cxt.id = ra.contextid
              JOIN mdl_course c ON c.id = cxt.instanceid
              LEFT JOIN mdl_user_info_data batchcode ON batchcode.userid = ra.userid AND batchcode.fieldid = (SELECT id FROM mdl_user_info_field WHERE shortname ='batchcode')
              LEFT JOIN mdl_user_info_data centercode ON centercode.userid = ra.userid AND centercode.fieldid = (SELECT id FROM mdl_user_info_field WHERE shortname ='centercode')
              WHERE ra.userid = u.id
      
              AND ra.contextid = cxt.id
              AND cxt.contextlevel =50
              AND cxt.instanceid = c.id
              AND c.id IN($courselist) 
              AND u.deleted = 0
              $filter_batchcode_centercode
              $partial_condition
              AND DATE_FORMAT(FROM_UNIXTIME(ra.timemodified), '%Y-%m-%d')  >= '$startDate' AND DATE_FORMAT(FROM_UNIXTIME(ra.timemodified), '%Y-%m-%d') <= '$endDate'
      ";
       
    }else{
                
        $prepareSQL = "
                SELECT 
                u.id,
                u.username,
                u.firstname,
                u.lastname,
                u.email,
                u.idnumber,
                c.id as courseid,
                roleid,     
                c.fullname as coursename,
                c.fullname,     
                batchcode.data as batchcode,
                centercode.data as centercode,
                DATE_FORMAT(FROM_UNIXTIME(ra.timemodified), '%Y-%m-%d') as timecreated
                FROM
                mdl_role_assignments ra
                JOIN mdl_user u ON u.id = ra.userid
                JOIN mdl_role r ON r.id = ra.roleid
                JOIN mdl_context cxt ON cxt.id = ra.contextid
                JOIN mdl_course c ON c.id = cxt.instanceid
                LEFT JOIN mdl_user_info_data batchcode ON batchcode.userid = ra.userid AND batchcode.fieldid = (SELECT id FROM mdl_user_info_field WHERE shortname ='batchcode')
                LEFT JOIN mdl_user_info_data centercode ON centercode.userid = ra.userid AND centercode.fieldid = (SELECT id FROM mdl_user_info_field WHERE shortname ='centercode')
                WHERE ra.userid = u.id
        
                AND ra.contextid = cxt.id
                AND cxt.contextlevel =50
                AND cxt.instanceid = c.id
                AND c.id IN($courselist)
                AND u.deleted = 0
                $filter_batchcode_centercode
                $partial_condition
        ";
 
    }

    $users=$DB->get_records_sql($prepareSQL);


    #Ved
    /*  -------------- Enhancement Nov 23 2020 ------------------  */

    /*  -------------- Enhancement Feb 05 2021 ------------------  */
        /*

            profile_load_custom_fields($USER);
            $logged_user_profiles = $USER->profile;
            $logged_user_batchcodes = $logged_user_profiles['batchcode'];
            $logged_user_batchcodes = array_map('trim', explode(',', $logged_user_batchcodes));


            $site_admin = false;
            if (has_capability('moodle/site:config', context_system::instance())) {
                $site_admin = true;
            }
        */
    /*  -------------- Enhancement Feb 05 2021 END ------------------  */
    //echo $prepareSQL;
    $user_list = [];
    foreach($users as $user){

        profile_load_custom_fields($user);
        $profile_fields=$user->profile;

        $center_codes = $profile_fields['centercode'];
        $batch_codes = $profile_fields['batchcode'];

        /*print_object($center_codes);
        echo 'tst';
        print_object($object->centercode);*/
    #Ved
    /*  -------------- Enhancement Feb 05 2021 ------------------  */

      /*  
            $current_user_batchcode = trim($batch_codes);

            if (!$site_admin && !in_array($current_user_batchcode, $logged_user_batchcodes)){
                continue;
            }
       */

     /*  -------------- Enhancement Feb 05 2021 END------------------  */


        if(in_array($center_codes, $object->centercode) || in_array($batch_codes, $object->batchcode)){
            array_push($user_list, $user);
        }else{
            continue;
        }
    }

    $users = $user_list;
   // print_object($users);
    //die;
    /*  -------------- Enhancement Nov 23 2020 END ------------------  */

    if(count($users)==0){
       //return "<h4 style='color:grey;'>No Student enrolled</h4>";
        return array("<div class='alert alert-danger'>
        <strong class='f-msg2'>No records found</strong></div>");
    }

    echo '
<style>

 .datatable td {
  overflow: hidden; /* this is what fixes the expansion */
  text-overflow: ellipsis; /* not supported in all browsers, but I accepted the tradeoff */
  white-space: nowrap;
  table-layout: fixed;
}

th.sorting {

    white-space: nowrap;
}


th {
    white-space: nowrap;

}

.dataTables_wrapper.no-footer .dataTables_scrollBody {
      border-bottom: 1px solid transparent;
}

#studentlist_wrapper{
    overflow-x: scroll;
}

</style>
    ';

        $courses=$DB->get_records_sql("select id, fullname, shortname from mdl_course where category<>0 AND $sqlConditons");

            //adding a div for scrolling effect in a table
			$htmlContent ="<div>";

			$htmlContent .="<table id='studentlist' class='table table-striped table-bordered users' style='width: 100%;'>
									<thead><tr>
                                        <th rowspan=2>Enrolment ID</th>
                                        <th rowspan=2>Username</th>
										<th rowspan=2>First Name | Last Name</th>
									";

           $userProfileFields=$DB->get_records_sql("select * from {user_info_field}");

            //for additional profile field
            $count=sizeof($userProfileFields);

            //calculate the calspan
            $colspan=0;
            foreach($userProfileFields as $userProfileField){

                if($userProfileField->datatype==="textarea"){

                    $colspan++;
                }
            }

             $temp=$count-$colspan;

             if($temp!=0)
             $htmlContent .="<th colspan=$temp>Additional Fields</th>";

              foreach($courses as $course){
				        //$htmlContent .="<th><span style='color:green'>Course Completion Status</span></th>";
                        $span=getActivitiesNcolspan($course->id)[1]*2;
                        $htmlContent .="
												<th colspan=".$span.">".$course->fullname."
                                                <span id='courseStatus'></span></th>";

                        $htmlContent .="<th></th>";
                        $htmlContent .="<th></th>";
                        $htmlContent .="<th></th>";

                    if ($course === end($courses)){

                     }else{
                              $htmlContent .="<td style=background-color:#F4F4F4></td>";
                    }

				}

        $htmlContent .="<tr>";
        global $USER;
        $profiles=$USER->profile;

/*------------------------------------------------------------------------------*/
    //setting values for Additional profile fields
    $profile_fields=null;
    foreach($users as $user){
        $user_profile = $DB->get_record('user', array('id' => $user->id));
        profile_load_custom_fields($user_profile);
        $profile_fields=$user_profile->profile;

        break;
    }


    foreach($profile_fields as $key => $value){

         foreach($userProfileFields as $userProfileField){


           if($userProfileField->datatype!=="textarea") {

               if($userProfileField->shortname===$key){

                        $htmlContent .="<th> $userProfileField->name</th>";
                    }

            }
        }
    }

/*------------------------------------------------------------------------------*/
				foreach($courses as $course){

							//activity heading fetching loop starts here
							foreach(getActivitiesNcolspan($course->id)[0] as $activity){

                                if($activity->mod=="resource"){
                                    if($activity->name!=''){
                                         $htmlContent .="<th>"
                                                                .$activity->name.
                                                            "</th>";
                                         $htmlContent .="<th>Time Spent</th>";
                                    }else{
                                         $htmlContent .="<th>Unknown Chapter</th>";

                                         $htmlContent .="<th>Time Spent</th>";
                                    }
                                }else{
                                     if($activity->name==''){
                                        //if activity has no name
                                        $htmlContent .="<th>"
                                                                .$activity->mod.
                                                            "</th>";

                                         $htmlContent .="<th>Time Spent</th>";
                                     }else{
                                          $htmlContent .="<th>"
                                                                .$activity->name.
                                                            "</th>";
                                         $htmlContent .="<th>Time Spent</th>";
                                     }
                                }
							}

                $htmlContent .="<td style='font-weight:bold'>Course Time Spent</td>";
                $htmlContent .="<td style='font-weight:bold'>Course Completion Status</td>";
                $htmlContent .="<td style='font-weight:bold'>Course Completion Date</td>";

                    if ($course === end($courses)){

                    }else{
                           $htmlContent .="<td style=background-color:#F4F4F4></td>";
                    }

					}//foreach

				$htmlContent .="</tr></thead><tbody>";

            $courseStatusArr=array();

            $chartObject = new stdClass();
            $chartObject->incomplete=0;
            $chartObject->complete=0;
            $chartObject->notAttempted=0;

            $arr_of_profile_fields_for_filter=explode(",",$profiles['batchcode']);
            $statusArr=null;
            foreach($users as $user){

            $statusArr=array();

                //setting values for Additional profile fields
                $user_profile = $DB->get_record('user', array('id' => $user->id));
                profile_load_custom_fields($user_profile);

                $profile_fields=$user_profile->profile;

            if (!has_capability('report/timespent:localadminview', $systemcontext)){

                if(!in_array($profile_fields['batchcode'], $arr_of_profile_fields_for_filter))
                continue;
            }
                //en
                if (!array_key_exists($profile_fields['batchcode'],$batch_code_en)){
                    $batch_code_en[$profile_fields['batchcode']]=array();

                        $obj_complete_en=new stdClass();
                        $obj_incomplete_en=new stdClass();
                        $obj_not_attempted_en=new stdClass();

                        $obj_complete_en->complete=0;
                        $obj_incomplete_en->incomplete=0;
                        $obj_not_attempted_en->not_attempted=0;

                        $obj_holder_arr=array($obj_complete_en,$obj_incomplete_en,$obj_not_attempted_en);


                        $batch_code_avg_completion_rate_en[$profile_fields['batchcode']]=$obj_holder_arr;

                }

               if($user_profile->idnumber==""){

                   $htmlContent .="<tr>
                                    <th>-</th>
                                    <th>".$user->username."</th>
                                    <th>".$user->firstname." ".$user->lastname."</th>";
               }else{
                    $htmlContent .="<tr>
                                    <th>".$user_profile->idnumber."</th>
                                    <th>".$user->username."</th>
                                    <th>".$user->firstname." ".$user->lastname."</th>";
               }




                 //print_r($profile_fields);
                foreach($profile_fields as $profile_field){

                if(strlen($profile_field)==0){
                     $htmlContent .="<td align=center>-</td>";
                      continue;
                }

                 if (is_numeric($profile_field)) {
                    // print_r($profile_field."------");
                     //numberic value
                     if($profile_field>1){
                        $time=date('Y-m-d', $profile_field);
                         $htmlContent .="<td align=center>$time</td>";
                     }

                    if($profile_field==1){
                         $htmlContent .="<td align=center>Yes</td>";
                     }

                     if($profile_field==0){
                         $htmlContent .="<td align=center>No</td>";
                     }

                  } else {
                      $htmlContent .="<td>$profile_field</td>";
                  }

                }

                foreach($courses as $course){
                        $objj=null;
                        $status="notfound";
                        foreach($courseStatusArr as $obj){

                             if($obj->courseName==$course->fullname){
                                 $status="found";
                                 $objj=$obj;
                                 break;
                             }
                        }
                        if($status==="notfound"){
                                $objj = new stdClass();
                                $objj->totalActivity=0;
                                $objj->completed=0;
                                $objj->courseName=$course->fullname;
                                $sql="
                                SELECT e.courseid,count(*) as total, cr.shortname
                                FROM mdl_user_enrolments ue
                                JOIN mdl_enrol e ON e.id = ue.enrolid AND e.status = 0
                                JOIN mdl_course cr ON cr.id = e.courseid
                                JOIN mdl_user u ON u.id = ue.userid AND u.deleted = 0 AND u.suspended = 0
                                WHERE ue.status = 0  AND e.courseid=$course->id";

                                //Get all records
                                $records = $DB->get_records_sql($sql);

                                foreach($records as $record){
                                    $objj->totalUser=$record->total;
                                }

                                array_push($courseStatusArr,$objj);
                        }


                        $objj->totalActivity=sizeof(getActivitiesNcolspan($course->id)[0]);

                        $totalTime=null;
                        $totalTime=array();

                        $context = context_course::instance($course->id);
						$isEnroll=is_enrolled($context,$user->id, '', true);

                        //pull the activity from the list of activities and fetch the value from it.
						foreach(getActivitiesNcolspan($course->id)[0] as $activity){
						$courseStatus=null;

                            $courseLogStatus=$DB->get_records_sql("select id from mdl_logstore_standard_log where contextinstanceid=".$activity->cm." and userid=".$user->id."");

                             $courseStatus=$DB->get_records_sql("select completionstate from mdl_course_modules_completion where coursemoduleid=".$activity->cm." and userid=".$user->id."");


             if( count( $courseStatus ) == 0 ){

                    if($isEnroll==1){
                            if( count( $courseLogStatus ) == 0 ){

                                $htmlContent .="<td align=center>Not Attempted</td>";

                                array_push($statusArr,"not");

                                $htmlContent .="<td align=center> N.A </td>";//time spent
							 }
                    }else {
                         $htmlContent .="<td align=center>-</td>";
                    }

                     /*bug reported
                            One table is saying course has been attempted
                            Another table is saying course completed
                    */

                    //true: User Enrolled & attempted
                    //false: User not enrolled
                        if($isEnroll==1){
                            if( count( $courseLogStatus ) != 0 ){
                                $htmlContent .="<td align=center>Incomplete 1</td>";

                                 array_push($statusArr,"in");

                                try{

                                    $timeSpent=getTimeSpentOnActivity($user->id,$activity->cm);
                                    array_push($totalTime,$timeSpent[1]);

                                     $htmlContent .=
                                      "<td align=center>".$timeSpent[0]."</td>";


                                }catch(Exception $e){
                                     $htmlContent .=
                                      "<td align=center>Contact IT Team</td>";
                                }
				            }
                         }else {
                            $htmlContent .="<td align=center>-</td>";
                        }


                    }//if:$courseStatus
				    else
				    {

							/*  print the msg based on completion status.
								 * 0. Incomplete
								 * 1. Completed
								 * 2. Complete Pass
								 * 3. Complete Fail
							*/
								$completionState=null;
								foreach($courseStatus as $completionState){
									$completionState =$completionState->completionstate;

								}

								switch ($completionState) {
									case 0:
										/* print_r($statusArr);die();
                                        foreach($statusArr as $arr){
                                            print_r($arr);die();
                                        }*/
										$htmlContent .="
													<td align=center>Incomplete</td>";

                                        try{

                                            $timeSpent=getTimeSpentOnActivity($user->id,$activity->cm);
                                            array_push($totalTime,$timeSpent[1]);

                                            $htmlContent .=
                                                "<td align=center>".$timeSpent[0]."</td>";
                                        }catch(Exception $e){
                                             $htmlContent .=
                                              "<td align=center>N.A</td>";
                                        }

										break;
									case 1:
								  		$htmlContent .="
														<td align=center>Complete</td>";

                                                         array_push($statusArr,"in");


                                                        $objj->completed+=1;
                                        try{
                                            $timeSpent=getTimeSpentOnActivity($user->id,$activity->cm);
                                            array_push($totalTime,$timeSpent[1]);

                                        $htmlContent .=
                                            "<td align=center>".$timeSpent[0]."</td>";

                                        }catch(Exception $e){
                                             $htmlContent .=
                                              "<td align=center>N.A</td>";
                                        }
                                        break;
									case 2:
									 	$htmlContent .="
														<td align=center>Complete Pass</td>";
                                        try{
                                            $timeSpent=getTimeSpentOnActivity($user->id,$activity->cm);
                                            array_push($totalTime,$timeSpent[1]);

                                            $htmlContent .=
                                                    "<td align=center>".$timeSpent[0]."</td>";
                                        }catch(Exception $e){
                                             $htmlContent .=
                                              "<td align=center>N.A</td>";
                                        }

										break;
									case 3:
											$htmlContent .="
														<td align=center>Complete fail</td>";
                                        try{
                                            $timeSpent=getTimeSpentOnActivity($user->id,$activity->cm);
                                            array_push($totalTime,$timeSpent[1]);

                                        $htmlContent .=
                                                "<td align=center>".$timeSpent[0]."</td>";
                                        }catch(Exception $e){
                                             $htmlContent .=
                                              "<th align=center>N.A</th>";
                                        }
											break;

									default:
										$htmlContent .="
												<td align=center>Contact Admin</td>";
                                          $htmlContent .="<th align=center>N.A</th>";
								}

							}//if-else

						  }//foreach

                              $courseStatus=getCourseCompletionStatus($course,$user);

                              $total_time_spent=totalTimeSpent($totalTime);
                        	  $htmlContent .="<td align=center>".$total_time_spent."</td>";

                            if (array_key_exists($profile_fields['batchcode'],$batch_code_en)){
                                    $temp_arr=$batch_code_en[$profile_fields['batchcode']];//get array
                                    array_push($temp_arr,$total_time_spent);
                                    $batch_code_en[$profile_fields['batchcode']]=$temp_arr;
                                }

                                        $text="";
                                        foreach($statusArr as $arr){
                                           $text.= $arr;
                                        }

                            if($courseStatus=="Completed"){
                                  $htmlContent .="<td align=center>$courseStatus</td>";
                                  $chartObject->complete+=1;

                                    //enhancement
                                    if (array_key_exists(
                                                        $profile_fields['batchcode'],
                                                        $batch_code_avg_completion_rate_en)){
                                        $temp_arr=$batch_code_avg_completion_rate_en
                                                  [$profile_fields['batchcode']];//get array
                                        //specific to array
                                        //0 index complete
                                        //1 index incomplete
                                        //2 index not_attempted
                                         $temp_arr[0]->complete+=1;
                                    }
                            }else{
                                if (in_array("in", $statusArr))
                                {
                                     $chartObject->incomplete+=1;

                                  $htmlContent .="<td align=center>Incomplete  </td>";

                                    //enhancement
                                    if (array_key_exists(
                                                        $profile_fields['batchcode'],
                                                        $batch_code_avg_completion_rate_en)){
                                        $temp_arr=$batch_code_avg_completion_rate_en
                                                  [$profile_fields['batchcode']];//get array
                                        //specific to array
                                        //0 index complete
                                        //1 index incomplete
                                        //2 index not_attempted
                                         $temp_arr[1]->incomplete+=1;
                                    }

                                }
                                else
                                {
                                    $htmlContent .="<td align=center>Not Attempted </td>";

                                     $chartObject->notAttempted+=1;

                                    //enhancement
                                    if (array_key_exists(
                                                        $profile_fields['batchcode'],
                                                        $batch_code_avg_completion_rate_en)){
                                        $temp_arr=$batch_code_avg_completion_rate_en
                                                  [$profile_fields['batchcode']];//get array
                                        //specific to array
                                        //0 index complete
                                        //1 index incomplete
                                        //2 index not_attempted
                                         $temp_arr[2]->not_attempted+=1;
                                    }

                                }
                            }

                              if($courseStatus=="Completed"){

                                    $courseCompletionDateQuery="
                                    SELECT u.username,
                                    c.shortname,
                                    cc.timecompleted
                                    FROM mdl_course_completions cc
                                    JOIN mdl_course c ON c.id = cc.course
                                    JOIN mdl_user u ON u.id = cc.userid
                                    WHERE u.id = 3 AND cc.course=3
                                    AND cc.timecompleted <> 'NULL'";

                                  $records=$DB->get_records_sql($courseCompletionDateQuery);

                                  $timeInSec=null;
                                  foreach($records as $record){
                                      $timeInSec=$record->timecompleted;
                                  }
                                  $CourseCompletionDate=date('d-m-Y', $timeInSec);

                                    $htmlContent .="<td align=center>$CourseCompletionDate</td>";
                              }else{
                                   $htmlContent .="<td align=center>-</td>";
                              }



                          if ($course === end($courses)){

                          }else{
                               $htmlContent .="<td style=background-color:#F4F4F4></td>";
                          }

                        }//course - foreach

                }//user - foreach

        $htmlContent .="</tbody></table>";

        $htmlContent .="</div>";

  $htmlContent .="<div>";

        $htmlContent .="<div style=float:left;>";

         $htmlContent .='
        <div class="card" style="padding:15px; margin-top:10px;">
        <h5 class="card-title">Average completion rate</h5>';

        foreach($courseStatusArr as $data){

            //calculation %age of course completion
            $total_activity=$data->totalActivity;
            $total_user=$data->totalUser;
            $result=0;
            if($total_user!=0){
                $total_activity_of_n_user=$total_activity * $total_user;

                $total_completed_activity=$data->completed;
                $result=($total_completed_activity/$total_activity_of_n_user)*100;
                $result=round($result,2);
                $result=$result;
            }


             $htmlContent .="
                 <p>$data->courseName - $result % </p>

             ";
        }





        $htmlContent .="</div>";


      $htmlContent .="
       <div class='card' style='max-height:500px; overflow:scroll;'>
      <table class='table table-striped table-bordered' style='width:100%;'>
      <tr>
        <th>Batch Name</th>
        <th>Avg. Time Spent</th>
        <th>Completion %</th>
      </tr>";


         //$batch_code_avg_completion_rate_en

    foreach($batch_code_en as $batch_code_key => $value){

            $temp_arr=$batch_code_avg_completion_rate_en[$batch_code_key];
            $complete=$temp_arr[0]->complete;
            $incomplete=$temp_arr[1]->incomplete;
            $not_attempted=$temp_arr[2]->not_attempted;
        //echo strtotime('2 year 3 months 1 Days 19 Hours 25 Min 30 Sec', 0);
        //if(strlen($batch_code_key)!=0){
            $htmlContent .="<tr>";
            if(strlen($batch_code_key)!=0)
                $htmlContent .="<td>$batch_code_key</td>";
            else
                $htmlContent .="<td>-</td>";
                        $htmlContent .="<td>";
                         $total_seconds=0;
                         foreach($value as $date){
                            $total_seconds+=strtotime($date,0);
                            //$htmlContent .="<p>$date</p>";
                        }
                        $avg_seconds=$total_seconds/sizeof($value); //$sizeof(value)=total no. of                                                       students
                        $avg_time=report_timespent_stringtime_to_seconds($avg_seconds);
                        $temp_text="";
                        foreach($avg_time as $key => $value){
                            if($value!=0)
                                $temp_text.=$value." ".$key." ";
                        }
                        if(strlen($temp_text)!=0)
                            $htmlContent .="<p>$temp_text</p>";
                        else
                            $htmlContent .="<p>0 seconds</p>";
                         $htmlContent .="</td>";

            $htmlContent .="<td>
                                <p>Complete : $complete </p>
                                <p>Incomplete : $incomplete </p>
                                <p>Not Attempted : $not_attempted </p>
                            </td>";
             $htmlContent .="</tr>";

        //}
    }

 /* <tr>
    <td>Jill</td>
    <td>Smith</td>
    <td>50</td>
  </tr>*/

 $htmlContent .="</table>";
     $htmlContent .="</div>";

        $htmlContent .="</div>";

        $htmlContent .="<div style='float:right;width:45%; text-align:center; margin-top:10px;'>";

            //fetch the value from object
            $incomplete=$chartObject->incomplete;
            $complete=$chartObject->complete;
            $notAttempted=$chartObject->notAttempted;

            $htmlContent .="<span class='label success'>Complete : $complete </span>
            <span class='label info'>Incomplete : $incomplete </span>
            <span class='label danger'>Not Attempted : $notAttempted </span>";

            global $OUTPUT;
            global $CFG;
            $CFG->chart_colorset = ['#2196F3', '#4CAF50', '#f44336'];
            $chart = new \core\chart_pie();

            $serie1 = new core\chart_series('My series title', [$incomplete, $complete,$notAttempted]);
            $chart->add_series($serie1); // On pie charts we just need to set one series.


            $htmlContent .= $OUTPUT->render_chart($chart, false);
    echo '

    <style>
        .label {
            color: white;
            padding: 10px;
            font-size:12px;
            font-family: Arial;
        }
        .success {background-color: #4CAF50;} /* Green */
        .info {background-color: #2196F3;} /* Blue */
        .warning {background-color: #ff9800;} /* Orange */
        .danger {background-color: #f44336;} /* Red */
        .other {background-color: #e7e7e7; color: black;} /* Gray */
   </style>

    ';


    $htmlContent .=" </div>";
    $htmlContent .="</div>";

        return $htmlContent;
}


//total time spent on a course
function totalTimeSpent($totalTimee){
   //var_dump($totalTimee);
     $temp=array();
                foreach($totalTimee as $values)
                    foreach($values as $value){
                        array_push($temp,$value);
                    }

                $timeIntervall="2000-01-01 00:00:00";
                foreach($temp as $key => $value){
                      $date = new DateTime($timeIntervall);
                      $date->add(new DateInterval($value));
                      $timeIntervall=$date->format('Y-m-d H:i:s');
                }

                        //$content="<td align=center>".timeFormating($timeIntervall)."</td>";
                        $content=timeFormating($timeIntervall);
    return $content;
}

 //get course completion status
function getCourseCompletionStatus($course,$user){

                      $object = new stdClass();
                      $object->id = $course->id;
                      $cinfo = new completion_info($object);
                      $iscomplete = $cinfo->is_course_complete($user->id);
                      $status=$iscomplete;

                        if($status==false){

                            return "Incomplete";
                        }else{
                              return "Completed";
                        }
}

function report_timespent_get_code($codetype){

    global $DB;

    $sql = "SELECT
                d.id,
                d.data
            FROM mdl_user_info_data d,
                mdl_user_info_field info
            WHERE d.fieldid=info.id
            AND info.shortname='$codetype'
            GROUP BY d.data";

    $records = $DB->get_records_sql($sql);
    return $records;
}
