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
$string['openai_chat_scieneers:addinstance'] = 'Einen neuen KI Campus Chat Block hinzufügen';
$string['openai_chat_scieneers:myaddinstance'] = 'Einen neuen KI Campus Chat Block zur My Moodle Seite hinzufügen';
$string['privacy:metadata'] = 'Der KI-Campus-Chat speichert keine personenbezogenen Nutzerdaten und übermittelt standardmäßig auch keine personenbezogenen Daten an Scieneers und die GWDG. Von Nutzern übermittelte Chat-Nachrichten werden jedoch vollständig an Scieneers und die GWDG übermittelt, die die Nachrichten ggf. zur Verbesserung der API speichern.';

$string['blocktitle'] = 'Block Titel';

$string['restrictusage'] = 'Beschränken Sie die Chat-Nutzung auf angemeldete Benutzer';
$string['restrictusagedesc'] = 'Wenn dieses Kontrollkästchen aktiviert ist, können nur angemeldete Benutzer das Chatfenster verwenden.';
$string['prompt'] = 'Prompt für die Antworten';
$string['promptdesc'] = 'Der Prompt, den die KI vor dem Gesprächsprotokoll erhält';
$string['assistantname'] = 'Name des Assistenten';
$string['assistantnamedesc'] = 'Der Name, den die KI intern für sich selbst verwendet';
$string['username'] = 'Name des Benutzers';
$string['usernamedesc'] = 'Der Name, den die KI intern für den Benutzer verwendet';
$string['sourceoftruth'] = 'Wissensquelle';
$string['sourceoftruthdesc'] = 'Obwohl die KI bereits sehr leistungsfähig ist, liefert sie, wenn sie die Antwort nicht kennt, eher falsche Informationen selbstbewusst, anstatt keine Antwort zu geben. In diesem Textfeld können Sie häufig gestellte Fragen und deren Antworten eintragen, auf die die KI zurückgreifen kann. Bitte verwenden Sie folgendes Format: <pre>Q: Frage 1<br />A: Antwort 1<br /><br />Q: Frage 2<br />A: Antwort 2</pre>';
$string['apiurl'] = 'API-URL';
$string['apiurldesc'] = 'Die API-URL, mit der das Plugin kommuniziert';
$string['showlabels'] = 'Labels anzeigen';
$string['config_sourceoftruth'] = 'Wissensquelle';
$string['config_sourceoftruth_help'] = "Hier können Sie Informationen hinzufügen, auf die die KI beim Beantworten von Fragen zurückgreift. Die Informationen müssen exakt im Frage-Antwort-Format vorliegen:\n\nQ: Wann ist Abschnitt 3 fällig?<br />A: Donnerstag, 16. März.\n\nQ: Wann sind die Sprechstunden?<br />A: Professorin Shown ist dienstags und donnerstags zwischen 14:00 und 16:00 Uhr in ihrem Büro anzutreffen.";
$string['config_infosource'] = 'Informationsquelle angeben';
$string['config_infosource_help'] = "Geben Sie die Quelle an, aus der der Block seine Antworten bezieht. Wenn Sie M{courseid} verwenden, wird das Modell zum aktuellen Kurs genutzt. Wenn dieses Feld leer bleibt, wird die Anfrage an ChatGPT weitergeleitet.";

$string['defaultprompt'] = "Unten sehen Sie ein Gespräch zwischen einem Benutzer und einem Support-Assistenten für eine Moodle-Seite, auf der Nutzer Online-Lernen nutzen:";
$string['defaultassistantname'] = 'Assistent';
$string['defaultusername'] = 'Benutzer';
$string['askaquestion'] = 'Nachricht schreiben';
$string['apikeymissing'] = 'Bitte fügen Sie Ihren API-Schlüssel in die globalen Blockeinstellungen ein.';
$string['erroroccurred'] = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später erneut.';
$string['response_parsing_error'] = 'Fehler beim Parsen der Antwort';
$string['response_parsing_errordesc'] = 'Fehler beim Parsen der Antwort als JSON: {$a}';
$string['sourceoftruthpreamble'] = "Unten finden Sie eine Liste von Fragen und deren Antworten. Diese Informationen dienen als Referenz für Anfragen:\n\n";
$string['sourceoftruthreinforcement'] = 'Der Assistent wurde so trainiert, dass er versucht, die obigen Referenzen für Antworten zu nutzen. Wenn der Text einer der oben genannten Fragen auftaucht, soll die dazugehörige Antwort ausgegeben werden, auch wenn die Frage keinen Sinn ergibt. Ist das Thema jedoch nicht in den Referenzen abgedeckt, verwendet der Assistent externes Wissen zur Beantwortung.';

$string['error_unknown_info_source'] = "In den Blockeinstellungen wurde eine unbekannte Informationsquelle angegeben. Bitte wenden Sie sich an den Administrator der Website, um den Block korrekt zu konfigurieren.";
$string['error_unknown_info_source_admin'] = 'Unbekannte Informationsquelle. Setzen Sie die Informationsquelle auf eine der folgenden Klassen: {$a}. Lassen Sie die Quelle leer, um direkten Zugriff auf ChatGPT zu erhalten.';

$string['chatbot_greeting'] = '👋&nbsp;&nbsp;Ich bin dein KI-Assistent.<br /><br />Frag mich zu Kursen, Inhalten oder allem rund um den KI-Campus.';