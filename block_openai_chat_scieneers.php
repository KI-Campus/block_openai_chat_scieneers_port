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
        $infosource    = !empty($this->config) && $this->config->infosource    ? $this->config->infosource    : '';

        $this->page->requires->js_call_amd(
            'block_openai_chat_scieneers/resizedrawer',
            'init'
        );
        
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
        $greeting = get_string('chatbot_greeting', 'block_openai_chat_scieneers');

        $this->content->text = '<ul class="chatnavbar" style="list-style: none; display: inline">
            <li class="active-elem" style="display: inline">Chatbot</li>
            <li class="inactive-elem" style="display: inline">
            <a href="https://ki-campus.org/kontakt" target="_blank">Kontaktformular</a></li>
        </ul>';

        $this->content->text .= '
            <button type="button"
                    class="scieneers-fullscreen-toggle"
                    id="scieneers-fullscreen-' . $this->instance->id . '"
                    title="Vollbild umschalten"
                    aria-label="Vollbild umschalten">
                <i class="fa fa-expand" aria-hidden="true"></i>
            </button>
        ';

        $this->content->text .= '<div class="greeting-wrapper">
            <div id="chatbotgreeting" style="margin-top: 5px; color: white; padding: 10px; background: #3a2a78; border-radius: 8px;">' . $greeting . '</div>
        </div>';

        $this->content->text .= '
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

        $this->content->footer = '<div class="infotext"> ' . get_string('askaquestion', 'block_openai_chat_scieneers') . ' </div>';

        $this->content->footer .= '<input id="openai_input-' . $this->instance->id . '" placeholder="' . get_string('askaquestion', 'block_openai_chat_scieneers') . '" type="text" name="message" />';

        $this->content->footer .= '
            <div class="scieneers-socratic-toggle">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="scieneers-socratic-toggle-' . $this->instance->id . '">
                    <label class="custom-control-label" for="scieneers-socratic-toggle-' . $this->instance->id . '">&nbsp;</label>
                </div>
                <span class="scieneers-socratic-toggle-label">' . get_string('socraticmode', 'block_openai_chat_scieneers') . '</span>
                <span class="scieneers-socratic-info" id="scieneers-socratic-info-' . $this->instance->id . '" aria-label="Info" role="button" tabindex="0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                    </svg>
                </span>
                <div class="scieneers-socratic-info-popup" id="scieneers-socratic-info-popup-' . $this->instance->id . '">' . get_string('socraticmodeinfo', 'block_openai_chat_scieneers') . '</div>
            </div>
        ';

        $this->content->footer .= '
            <button id="openai_send-' . $this->instance->id . '" class="btn btn-primary" type="button" style="margin: 10px 0;">
                ' . get_string('sendmessage', 'block_openai_chat_scieneers') . ' <i class="fa fa-arrow-right" aria-hidden="true" style="font-size: 0.8rem"></i>
            </button>
        ';
        return $this->content;
    }


}
