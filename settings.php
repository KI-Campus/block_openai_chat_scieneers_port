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
 * Plugin settings
 *
 * @package    block_openai_chat_scieneers
 * @copyright  2022 Bryce Yoder <me@bryceyoder.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configcheckbox(
    'block_openai_chat_scieneers/restrictusage',
    get_string('restrictusage', 'block_openai_chat_scieneers'),
    get_string('restrictusagedesc', 'block_openai_chat_scieneers'),
    1
));

$settings->add(new admin_setting_configtextarea(
    'block_openai_chat_scieneers/prompt',
    get_string('prompt', 'block_openai_chat_scieneers'),
    get_string('promptdesc', 'block_openai_chat_scieneers'),
    "Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning.",
    PARAM_TEXT
));

$settings->add(new admin_setting_configtext(
    'block_openai_chat_scieneers/assistantname',
    get_string('assistantname', 'block_openai_chat_scieneers'),
    get_string('assistantnamedesc', 'block_openai_chat_scieneers'),
    'Assistant',
    PARAM_TEXT
));

$settings->add(new admin_setting_configtext(
    'block_openai_chat_scieneers/username',
    get_string('username', 'block_openai_chat_scieneers'),
    get_string('usernamedesc', 'block_openai_chat_scieneers'),
    'User',
    PARAM_TEXT
));

$settings->add(new admin_setting_configtextarea(
    'block_openai_chat_scieneers/sourceoftruth',
    get_string('sourceoftruth', 'block_openai_chat_scieneers'),
    get_string('sourceoftruthdesc', 'block_openai_chat_scieneers'),
    '',
    PARAM_TEXT
));

$settings->add(new admin_setting_configtext(
    'block_openai_chat_scieneers/apiurl',
    get_string('apiurl', 'block_openai_chat_scieneers'),
    get_string('apiurldesc', 'block_openai_chat_scieneers'),
    'https://scieneers.techtiefen.de/chat',
    PARAM_TEXT
));