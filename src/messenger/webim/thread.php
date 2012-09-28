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

require_once('libs/init.php');
require_once('libs/chat.php');
require_once('libs/operator.php');
require_once('libs/classes/thread.php');

$act = verifyparam( "act", "/^(refresh|post|rename|close|ping)$/");
$token = verifyparam( "token", "/^\d{1,9}$/");
$threadid = verifyparam( "thread", "/^\d{1,9}$/");
$isuser = verifyparam( "user", "/^true$/", "false") == 'true';
$outformat = ((verifyparam( "html", "/^on$/", "off") == 'on') ? "html" : "xml");
$istyping = verifyparam( "typed", "/^1$/", "") == '1';

if($threadid == 0 && ($token == 123 || $token == 124)) {
	require_once('libs/demothread.php');
	$lastid = verifyparam( "lastid", "/^\d{1,9}$/", 0);
	demo_process_thread($act,$outformat,$lastid,$isuser,$token == 123,$istyping,$act=="post"?getrawparam('message') : "");
	exit;
}

$thread = Thread::load($threadid, $token);
if (! $thread) {
	die("wrong thread");
}

function show_ok_result($resid) {
	start_xml_output();
	echo "<$resid></$resid>";
	exit;
}

function show_error($message) {
	start_xml_output();
	echo "<error><descr>$message</descr></error>";
	exit;
}

$thread->ping($isuser, $istyping);

if( !$isuser && $act != "rename" ) {
	$operator = check_login();
	$thread->checkForReassign($operator);
}

if( $act == "refresh" ) {
	$lastid = verifyparam( "lastid", "/^\d{1,9}$/", -1);
	print_thread_messages($thread, $token, $lastid, $isuser,$outformat, $isuser ? null : $operator['operatorid']);
	exit;

} else if( $act == "post" ) {
	$lastid = verifyparam( "lastid", "/^\d{1,9}$/", -1);
	$message = getrawparam('message');

	$kind = $isuser ? Thread::KIND_USER : Thread::KIND_AGENT;
	$from = $isuser ? $thread->userName : $thread->agentName;

	if(!$isuser && $operator['operatorid'] != $thread->agentId) {
		show_error("cannot send");
	}

	$postedid = $thread->postMessage(
		$kind,
		$message,
		$from,
		$isuser ? null : $operator['operatorid']
	);
	if($isuser && $thread->shownMessageId == 0) {
		$thread->shownMessageId = $postedid;
		$thread->save();
	}
	print_thread_messages($thread, $token, $lastid, $isuser, $outformat, $isuser ? null : $operator['operatorid']);
	exit;

} else if( $act == "rename" ) {

	if( Settings::get('usercanchangename') != "1" ) {
		show_error("server: forbidden to change name");
	}

	$newname = getrawparam('name');

	$thread->renameUser($newname);
	$data = strtr(base64_encode(myiconv($webim_encoding,"utf-8",$newname)), '+/=', '-_,');
	setcookie($namecookie, $data, time()+60*60*24*365);
	show_ok_result("rename");

} else if( $act == "ping" ) {
	show_ok_result("ping");

} else if( $act == "close" ) {

	if( $isuser || $thread->agentId == $operator['operatorid']) {
		$thread->close($isuser);
	}
	show_ok_result("closed");

}

?>