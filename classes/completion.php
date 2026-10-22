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
    protected $kiModuleIDs;
	protected $startSocraticV2;

    /**
     * Initialize all the class properties that we'll need regardless of model
     * @param string message: The most recent message sent by the user
     * @param array history: An array of objects containing the history of the conversation
     * @param string localsourceoftruth: The instance-level source of truth we got from the API call
     */
    public function __construct($message, $history, $localsourceoftruth, $infosource, $courseID, $moduleIDs = [], $startSocraticV2 = false) {
        $this->prompt = $this->get_setting('prompt', get_string('defaultprompt', 'block_openai_chat_scieneers'));
        $this->assistantname = $this->get_setting('assistantname', get_string('defaultassistantname', 'block_openai_chat_scieneers'));
        $this->username = $this->get_setting('username', get_string('defaultusername', 'block_openai_chat_scieneers'));

        $this->message = $message;
        $this->history = $history;

        $this->build_source_of_truth($localsourceoftruth);
        $this->infosource = $infosource;
		
		$this->kiCourseID = $courseID;
        $this->kiModuleIDs = $moduleIDs;
		$this->startSocraticV2 = $startSocraticV2;
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
		global $SESSION;
        $body = new \stdClass();
		
		$body->user_query = new \stdClass();
        $body->user_query->role = "user";
        $body->user_query->content = $this->message;

        $courseid = $this->kiCourseID;

        $body->thread_id = $SESSION->block_openai_chat_scieneers_threadids[$courseid] ?? null;

		// course id #1 is used for no course. all valid and existing courses should have an id > 1.
		if ($courseid > 1) {
            $body->course_id = $courseid;

            if (!empty($this->kiModuleIDs)) {
                $body->module_id = $this->kiModuleIDs;
            }
        }
		
		if($this->startSocraticV2) {
			$body->start_socratic_v2 = true;
		}
		
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
        
        if ($curlinfo['http_code'] != 200) {
            http_response_code($curlinfo['http_code'] ?: 502);
            return $result ?: json_encode(['error' => 'API request failed']);
        }

        if ($obj && property_exists($obj, 'thread_id')) {
            if (!isset($SESSION->block_openai_chat_scieneers_threadids)) {
                $SESSION->block_openai_chat_scieneers_threadids = [];
            }

            $SESSION->block_openai_chat_scieneers_threadids[$this->kiCourseID] = $obj->thread_id;
        }

        return $result;
    }

    public function stream_completion() {
        global $SESSION;

        $apiurl = trim(get_config('block_openai_chat_scieneers', 'apiurl'));
        $apikey = get_config('block_openai_chat_scieneers', 'apikey');

        if (empty($apikey) || empty($apiurl)) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'API configuration missing']);
            return;
        }

        $payload = json_encode($this->buildCurlBody());

        // PHP-Ausgabepuffer deaktivieren, damit Tokens sofort ankommen.
        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ini_set('zlib.output_compression', '0');
        header('Cache-Control: no-cache, no-transform');
        header('X-Accel-Buffering: no');

        $buffer = '';
        $status = 0;
        $courseid = $this->kiCourseID;

        $curl = curl_init($apiurl);

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => 230,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/x-ndjson',
                'Api-Key: ' . $apikey,
            ],

            // HTTP-Fehlerstatus an das Frontend weiterreichen.
            CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$status) {
                if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $matches)) {
                    $status = (int) $matches[1];
                    http_response_code($status);

                    header(
                        'Content-Type: ' .
                        ($status === 200
                            ? 'application/x-ndjson; charset=utf-8'
                            : 'application/json; charset=utf-8')
                    );
                }

                return strlen($header);
            },

            // Eingehende Daten direkt zum Browser weiterreichen.
            CURLOPT_WRITEFUNCTION => function($curl, $chunk)
                    use (&$buffer, &$status, $courseid, &$SESSION) {

                if ($status !== 200) {
                    echo $chunk;
                    flush();
                    return strlen($chunk);
                }

                $buffer .= $chunk;

                // Eine NDJSON-Zeile kann auf mehrere Chunks verteilt sein.
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);

                    if (trim($line) === '') {
                        continue;
                    }

                    $event = json_decode($line, true);

                    // Thread-ID sofort aus dem meta-Event speichern.
                    if (is_array($event) &&
                            ($event['type'] ?? '') === 'meta' &&
                            !empty($event['thread_id'])) {

                        if (!isset($SESSION->block_openai_chat_scieneers_threadids)) {
                            $SESSION->block_openai_chat_scieneers_threadids = [];
                        }

                        $SESSION->block_openai_chat_scieneers_threadids[$courseid] =
                            $event['thread_id'];
                    }

                    echo $line . "\n";
                    flush();
                }

                return strlen($chunk);
            },
        ]);

        $success = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($status === 200) {
            // Letzte Zeile verarbeiten, falls kein abschließendes \n kam.
            if (trim($buffer) !== '') {
                $event = json_decode($buffer, true);

                if (is_array($event)) {
                    if (($event['type'] ?? '') === 'meta' &&
                            !empty($event['thread_id'])) {

                        if (!isset($SESSION->block_openai_chat_scieneers_threadids)) {
                            $SESSION->block_openai_chat_scieneers_threadids = [];
                        }

                        $SESSION->block_openai_chat_scieneers_threadids[$courseid] =
                            $event['thread_id'];
                    }

                    echo $buffer . "\n";
                }
            }

            if (!$success) {
                echo json_encode([
                    'type' => 'error',
                    'message' => $error ?: 'Streaming-Verbindung unterbrochen',
                ]) . "\n";
            }

            flush();
        } else if (!$success && $status === 0) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                'error' => $error ?: 'API request failed',
            ]);
        }
    }
}