<?php
/*
 * Copyright 2005-2013 the original author or authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

function demo_print_message($msg, $format)
{
	global $webim_encoding;
	if ($format == "xml") {
		print "<message>" . myiconv($webim_encoding, "utf-8", escape_with_cdata(message_to_html($msg))) . "</message>\n";
	} else {
		print topage(message_to_html($msg));
	}
}

function demo_process_thread($act, $outformat, $lastid, $isuser, $canpost, $istyping, $postmessage)
{
	global $webimroot;
	if ($act == "refresh" || $act == "post") {
		$lastid++;
		if ($outformat == "xml") {
			start_xml_output();
			print("<thread lastid=\"$lastid\" typing=\"" . ($istyping ? 1 : 0) . "\" canpost=\"" . ($canpost ? 1 : 0) . "\">");
		} else {
			start_html_output();
			$url = "$webimroot/thread.php?act=refresh&amp;thread=0&amp;token=123&amp;html=on&amp;user=" . ($isuser ? "true" : "false");

			print(
					"<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">" .
					"<html>\n<head>\n" .
					"<link href=\"$webimroot/styles/default/chat.css\" rel=\"stylesheet\" type=\"text/css\">\n" .
					"<meta http-equiv=\"Refresh\" content=\"" . Settings::get('updatefrequency_oldchat') . "; URL=$url&amp;sn=11\">\n" .
					"<meta http-equiv=\"Pragma\" content=\"no-cache\">\n" .
					"<title>chat</title>\n" .
					"</head>\n" .
					"<body bgcolor='#FFFFFF' text='#000000' link='#C28400' vlink='#C28400' alink='#C28400'>" .
					"<table width='100%' cellspacing='0' cellpadding='0' border='0'><tr><td valign='top' class='message'>");

		}
		if ($lastid == 1) {
			demo_print_message(
				array('ikind' => Thread::KIND_FOR_AGENT, 'created' => time() - 15, 'tname' => '',
					 'tmessage' => getstring2('chat.came.from', array("http://google.com"))), $outformat);
			demo_print_message(
				array('ikind' => Thread::KIND_INFO, 'created' => time() - 15, 'tname' => '',
					 'tmessage' => getstring('chat.wait')), $outformat);
			demo_print_message(
				array('ikind' => Thread::KIND_EVENTS, 'created' => time() - 10, 'tname' => '',
					 'tmessage' => getstring2("chat.status.operator.joined", array("Administrator"))), $outformat);
			demo_print_message(
				array('ikind' => Thread::KIND_AGENT, 'created' => time() - 9, 'tname' => 'Administrator',
					 'tmessage' => getstring("demo.chat.welcome")), $outformat);
			demo_print_message(
				array('ikind' => Thread::KIND_USER, 'created' => time() - 5, 'tname' => getstring("chat.default.username"),
					 'tmessage' => getstring("demo.chat.question")), $outformat);
			if ($canpost && $outformat == 'xml') {
				demo_print_message(
					array('ikind' => Thread::KIND_INFO, 'created' => time() - 5, 'tname' => '',
						 'tmessage' => 'Hint: type something in message field to see typing notification'), $outformat);
			}
		}
		if ($act == 'post') {
			demo_print_message(
				array('ikind' => $isuser ? Thread::KIND_USER : Thread::KIND_AGENT, 'created' => time(), 'tmessage' => $postmessage,
					 'tname' => $isuser ? getstring("chat.default.username") : "Administrator"), $outformat);
		}
		if ($outformat == "xml") {
			print("</thread>");
		} else {
			print(
					"</td></tr></table><a name='aend'></a>" .
					"</body></html>");
		}
	}
}

?>