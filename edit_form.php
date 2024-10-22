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
 * Per-block settings
 *
 * @package    block_openai_chat_scieneers
 * @copyright  2022 Bryce Yoder <me@bryceyoder.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_openai_chat_scieneers_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $USER, $COURSE;
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_openai_chat_scieneers'));
        $mform->setDefault('config_title', 'KI Campus Chat');
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'config_showlabels', get_string('showlabels', 'block_openai_chat_scieneers'), '&nbsp;');
        $mform->setDefault('config_showlabels', 1);

        $mform->addElement('textarea', 'config_sourceoftruth', get_string('sourceoftruth', 'block_openai_chat_scieneers'), 'd');
        $mform->setDefault('config_sourceoftruth', '');
        $mform->setType('config_sourceoftruth', PARAM_TEXT);
        $mform->addHelpButton('config_sourceoftruth', 'config_sourceoftruth', 'block_openai_chat_scieneers');

        if(is_siteadmin($USER)){
            $mform->addElement('text', 'config_infosource', get_string('config_infosource', 'block_openai_chat_scieneers'));
            $mform->setDefault('config_infosource', 'M' . $COURSE->id);
            $mform->setType('config_infosource', PARAM_TEXT);
            $mform->addHelpButton('config_infosource','config_infosource', 'block_openai_chat_scieneers');
        }
    }
}
