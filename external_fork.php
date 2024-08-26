<?php

/**
 * @global stdClass $CFG
 */
if ($CFG->branch > 401) {
//    require_once($CFG->libdir . "/externallib.php");
    class_alias(\core_external\external_api::class, 'external_api');
    class_alias(\core_external\external_description::class, 'external_description');
    class_alias(\core_external\external_value::class, 'external_value');
    class_alias(\core_external\external_format_value::class, 'external_format_value');
    class_alias(\core_external\external_single_structure::class, 'external_single_structure');
    class_alias(\core_external\external_multiple_structure::class, 'external_multiple_structure');
    class_alias(\core_external\external_function_parameters::class, 'external_function_parameters');
    class_alias(\core_external\util::class, 'external_util');
    class_alias(\core_external\external_files::class, 'external_files');
    class_alias(\core_external\external_warnings::class, 'external_warnings');
    class_alias(\core_external\external_settings::class, 'external_settings');
} else {
    require_once($CFG->libdir . "/externallib.php");
}