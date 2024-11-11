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
 * Block class
 *
 * @package    block_openai_chat_scieneers
 * @copyright  2022 Bryce Yoder <me@bryceyoder.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_openai_chat_scieneers extends block_base {
    public function init() {
        $this->title = get_string('openai_chat_scieneers', 'block_openai_chat_scieneers');
    }

    public function has_config() {
        return true;
    }

    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        }
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function get_content() {
        global $COURSE;
        if ($this->content !== null) {
            return $this->content;
        }

        $sourceoftruth = !empty($this->config) && $this->config->sourceoftruth ? $this->config->sourceoftruth : '';
        $infosource = $this->config->infosource;

        $this->page->requires->js('/blocks/openai_chat_scieneers/lib.js');
        $this->page->requires->js_init_call('init', [$sourceoftruth, $infosource, $this->instance->id]);

        // Determine if name labels should be shown.
        $showlabelscss = '';
        if (!empty($this->config) && !$this->config->showlabels) {
            $showlabelscss = '
                .openai_message:before {
                    display: none;
                }
                .openai_message {
                    margin-bottom: 0.5rem;
                }
            ';
        }

        $assistantname = get_config('block_openai_chat_scieneers', 'assistantname') ? get_config('block_openai_chat_scieneers', 'assistantname') : get_string('defaultassistantname', 'block_openai_chat_scieneers');
        $username = get_config('block_openai_chat_scieneers', 'username') ? get_config('block_openai_chat_scieneers', 'username') : get_string('defaultusername', 'block_openai_chat_scieneers');

        $this->content = new stdClass;
        $this->content->text = '
            <script>
                var assistantName = "' . $assistantname . '";
                var userName = "' . $username . '";
				var kiCampusChatBotCourseID = "' . $COURSE->id . '";
            </script>

            <style>
                ' . $showlabelscss . '
                .openai_message.user:before {
                    content: "' . $username . '";
                }
                .openai_message.bot:before {
                    content: "' . $assistantname . '";
                }
            </style>

            <div id="openai_chat_scieneers_log-' . $this->instance->id . '"></div>
        ';

        $this->content->footer = '<input id="openai_input-' . $this->instance->id . '" placeholder="' . get_string('askaquestion', 'block_openai_chat_scieneers') . '" type="text" name="message" />';

        return $this->content;
    }
}
