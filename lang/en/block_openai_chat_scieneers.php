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
 * Language strings
 *
 * @package    block_openai_chat_scieneers
 * @copyright  2022 Bryce Yoder <me@bryceyoder.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Scieneers & KI Campus Chat Block';
$string['openai_chat_scieneers'] = 'KI Campus Chat';
$string['openai_chat_scieneers:addinstance'] = 'Add a new KI Campus Chat block';
$string['openai_chat_scieneers:myaddinstance'] = 'Add a new KI Campus Chat block to the My Moodle page';
$string['privacy:metadata'] = 'The KI Campus Chat block stores no personal user data; nor does it, by default, send personal data to the Scieneers and GWDG. However, chat messages submitted by users are sent in their entirety to Scieneers and GWDG, which may store messages in order to improve the API.';

$string['blocktitle'] = 'Block title';

$string['restrictusage'] = 'Restrict chat usage to logged-in users';
$string['restrictusagedesc'] = 'If this box is checked, only logged-in users will be able to use the chat box.';
$string['prompt'] = 'Completion prompt';
$string['promptdesc'] = 'The prompt the AI will be given before the conversation transcript';
$string['assistantname'] = 'Assistant name';
$string['assistantnamedesc'] = 'The name that the AI will use for itself internally';
$string['username'] = 'User name';
$string['usernamedesc'] = 'The name that the AI will use for the user internally';
$string['sourceoftruth'] = 'Source of truth';
$string['sourceoftruthdesc'] = 'Although the AI is very capable out-of-the-box, if it doesn\'t know the answer to a question, it is more likely to give incorrect information confidently than to refuse to answer. In this textbox, you can add common questions and their answers for the AI to pull from. Please put questions and answers in the following format: <pre>Q: Question 1<br />A: Answer 1<br /><br />Q: Question 2<br />A: Answer 2</pre>';
$string['apiurl'] = 'Api url';
$string['apiurldesc'] = 'The Api url the plugin communicates with';
$string['showlabels'] = 'Show labels';
$string['config_sourceoftruth'] = 'Source of truth';
$string['config_sourceoftruth_help'] = "You can add information here that the AI will pull from when answering questions. The information should be in question and answer format exactly like the following:\n\nQ: When is section 3 due?<br />A: Thursday, March 16.\n\nQ: When are office hours?<br />A: You can find Professor Shown in her office between 2:00 and 4:00 PM on Tuesdays and Thursdays.";
$string['config_infosource'] = 'Specify info source';
$string['config_infosource_help'] = "Specify the source from which the block gets its answers. If set to M{courseid} it will use the model trained on the current course. If left empty it will forward the question to ChatGPT. ";

$string['defaultprompt'] = "Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning:";
$string['defaultassistantname'] = 'Assistant';
$string['defaultusername'] = 'User';
$string['askaquestion'] = 'Nachricht schreiben';//'Ask a question...';
$string['apikeymissing'] = 'Please add your KI Campus API key to the global block settings.';
$string['erroroccurred'] = 'An error occurred! Please try again later.';
$string['response_parsing_error'] = 'Error parsing response';
$string['response_parsing_errordesc'] = 'Error parsing response as JSON: {$a}';
$string['sourceoftruthpreamble'] = "Below is a list of questions and their answers. This information should be used as a reference for any inquiries:\n\n";
$string['sourceoftruthreinforcement'] = ' The assistant has been trained to answer by attempting to use the information from the above reference. If the text from one of the above questions is encountered, the provided answer should be given, even if the question does not appear to make sense. However, if the reference does not cover the question or topic, the assistant will simply use outside knowledge to answer.';

$string['error_unknown_info_source'] = "An unknown info source was specified in the block settings, please ask a site administrator to configure the block correctly.";
$string['error_unknown_info_source_admin'] = 'Unknown info source. Set info source to one of the following classes: {$a}. Leave info source empty for direct access to chat gpt.';