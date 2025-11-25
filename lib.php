<?php


function report_timespent_extend_navigation_course($navigation, $course, $context) {

    if (has_capability('report/timespent:view', $context)) {
        $url = new moodle_url('/report/timespent/index.php', array('id' => $course->id));
        $navigation->add(get_string('pluginname', 'report_timespent'), $url, navigation_node::TYPE_SETTING, null, null,
                new pix_icon('i/report', ''));
    }
}
