<?php
require_once(__DIR__.'/loader.inc.php');

foreach($db->getTasks() as $task) {
	if(!$task['due'] || $task['done']
	|| !$task['reminder'] || $task['reminder_sent']
	|| empty($task['assignee_email'])) continue;

	if(strtotime($task['due']) - $task['reminder'] <= time()) {
		echo 'Send reminder for #'.$task['id']."\n";
		InvitationMailer::sendReminder(
			$task['assignee_email'], $task['id'],
			$task['title'], $task['description'],
			strtotime($task['due']),
			$task['token']
		);
		$db->updateTaskReminderSent($task['id']);
	}
}
