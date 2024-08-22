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

namespace block_openai_chat_scieneers\event;

use tool_brickfield\manager;

class response_parsing_error extends \core\event\base {

    /**
     * Init function.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Get name.
     * @return \lang_string|string
     */
    public static function get_name() {
        return get_string('response_parsing_error', 'block_openai_chat_scieneers');
    }

    /**
     * Get description.
     * @return \lang_string|string|null
     */
    public function get_description() {
        return get_string('response_parsing_errordesc', 'block_openai_chat_scieneers', $this->other['response']);
    }
}
