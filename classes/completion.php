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
 * Base completion object class
 *
 * @package    block_openai_chat_scieneers
 * @copyright  2023 Bryce Yoder <me@bryceyoder.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace block_openai_chat_scieneers;

use block_openai_chat_scieneers\event\response_parsing_error;

defined('MOODLE_INTERNAL') || die;

class completion {

    protected $assistantname;
    protected $username;

    protected $prompt;
    protected $sourceoftruth;
    protected $infosource;

    protected $message;
    protected $history;
	
	protected $kiCourseID;

    /**
     * Initialize all the class properties that we'll need regardless of model
     * @param string message: The most recent message sent by the user
     * @param array history: An array of objects containing the history of the conversation
     * @param string localsourceoftruth: The instance-level source of truth we got from the API call
     */
    public function __construct($message, $history, $localsourceoftruth, $infosource, $courseID) {
        $this->prompt = $this->get_setting('prompt', get_string('defaultprompt', 'block_openai_chat_scieneers'));
        $this->assistantname = $this->get_setting('assistantname', get_string('defaultassistantname', 'block_openai_chat_scieneers'));
        $this->username = $this->get_setting('username', get_string('defaultusername', 'block_openai_chat_scieneers'));

        $this->message = $message;
        $this->history = $history;

        $this->build_source_of_truth($localsourceoftruth);
        $this->infosource = $infosource;
		
		$this->kiCourseID = $courseID;
    }

    /**
     * Attempt to get the saved value for a setting; if this isn't set, return a passed default instead
     * @param string settingname: The name of the setting to fetch
     * @param mixed default_value: The default value to return if the setting isn't already set
     * @return mixed: The saved or default value
     */
    protected function get_setting($settingname, $default_value) {
        $setting = get_config('block_openai_chat_scieneers', $settingname);
        if (!$setting && (float) $setting != 0) {
            $setting = $default_value;
        }
        return $setting;
    }

    /**
     * Make the source of truth ready to add to the prompt by appending some extra information
     * @param string localsourceoftruth: The instance-level source of truth we got from the API call 
     */
    private function build_source_of_truth($localsourceoftruth) {
        $sourceoftruth = get_config('block_openai_chat_scieneers', 'sourceoftruth');
    
        if ($sourceoftruth || $localsourceoftruth) {
            $sourceoftruth = 
                get_string('sourceoftruthpreamble', 'block_openai_chat_scieneers')
                . $sourceoftruth . "\n\n"
                . $localsourceoftruth . "\n\n";
            }
        $this->sourceoftruth = $sourceoftruth;
    }

    /**
     * Given everything we know after constructing the parent, create a completion by constructing the prompt and making the api call
     * @return JSON: The API response from OpenAI
     */
    public function create_completion() {
        if ($this->sourceoftruth) {
            $this->prompt .= get_string('sourceoftruthreinforcement', 'block_openai_chat_scieneers');
        }
        $this->prompt .= "\n\n";

        $history_string = $this->format_history();
        $history_string .= $this->username . ": ";

        return $this->make_api_call($history_string);
    }

    /**
     * Format the history JSON into a string that we can pass in the prompt
     * @return string: The string representing the chat history to add to the prompt
     */
    private function format_history() {
        $history_string = '';
        foreach ($this->history as $message) {
            $history_string .= $message["user"] . ": " . $message["message"] . "\n";
        }
        return $history_string;
    }

	private function buildCurlBody() {
		//https://kic-restapi-dev.azurewebsites.net/docs#/default/chat_api_chat_post
		$body = new \stdClass();
		
		$body->messages = [];
		$body->model = "Llama3"; //"Qwen2";
		
		// course id #1 is used for no course. all valid and existing courses should have an id > 1.
		if($this->kiCourseID > 1) {
			$body->course_id = $this->kiCourseID;
		}
		
		
		foreach($this->history as $h) {
			$newHistoryItem = new \stdClass();
			
			$newHistoryItem->content = $h["message"];
			$newHistoryItem->role = strtolower($h["user"]);
			
			$body->messages[] = $newHistoryItem;
		}
		
		$newestHistoryItem = new \stdClass();
		
		$newestHistoryItem->content = $this->message;
		$newestHistoryItem->role = "user";
		
		$body->messages[] = $newestHistoryItem;
		
		return $body;
	}

    /**
     * Make the actual API call to OpenAI
     * @return JSON: The response from OpenAI
     */
    private function make_api_call($history_string) {
        global $COURSE, $USER, $SESSION;

		$curlbody = $this->buildCurlBody();

		/*
        $curlbody = [
            "prompt" => $this->message,
            "user_id" => $USER->id, // optional field, should make it anonymous later on
        ];
        if(isset($SESSION->block_openai_chat_scieneers_chatid)) {
            $curlbody["chat_id"] = $SESSION->block_openai_chat_scieneers_chatid;
        }        
        if(!empty($this->infosource)) {
            $curlbody["lecture_id"] = $this->infosource;
        } 
        */


        $apikey = get_config('block_openai_chat_scieneers', 'apikey');
        if (empty($apikey)) {
            // Log error or return a user-friendly message
            echo get_string('apikeymissing', 'block_openai_chat_scieneers');
            exit;
        }

		
        $payload = json_encode($curlbody);
        $header = [
            'Content-Type: application/json',
            'Accept: application/json', 
            'Api-Key: ' . $apikey
        ];

        $curl = new \curl();
        $curl->setHeader($header);
        //$curl->setopt(array('CURLOPT_SSL_VERIFYPEER' => false, 'CURLOPT_SSL_VERIFYHOST' => 0));
        $result = $curl->post(trim(get_config('block_openai_chat_scieneers', 'apiurl')), $payload);
        $curlinfo = $curl->get_info();

        $obj = json_decode($result);
        
        if ($curlinfo['http_code'] == 422) {
            if(is_siteadmin($USER)){
                $start_pos = strpos($obj->detail, '[');
                $end_pos = strpos($obj->detail, ']');
                $available_sources = substr($obj->detail, $start_pos, $end_pos - $start_pos + 1);
                $err_array = array('answer' => get_string('error_unknown_info_source_admin', 'block_openai_chat_scieneers', $available_sources));
            } else {
                $err_array = array('answer' => get_string('error_unknown_info_source', 'block_openai_chat_scieneers'));
            }
            return json_encode($err_array);
        }
        
        if ($obj && property_exists($obj, "chat_id")) {
			$SESSION->block_openai_chat_scieneers_chatid = $obj->chat_id;
        } else {
            $context = \context_course::instance($COURSE->id);
            $event = response_parsing_error::create([
                'context' => $context,
                'other' => ['response' => $result],
            ]);
            $event->trigger();

            // Try using regex.
            $ret = preg_match('/(?<=chat_id":")(.*?)(",)/', $result, $matches);
            if ($ret) {
                $SESSION->block_openai_chat_scieneers_chatid = $matches[1];
            }
        }

        return $result;
    }
}