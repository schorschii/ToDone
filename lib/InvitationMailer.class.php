<?php

class InvitationMailer {

	static function send($address, $id, $title, $description, $dueTime, $token) {
		$template = 'Ihnen wurde eine Aufgabe auf $$URL$$ zugewiesen.'."\n\n"
			.'Aufgabe: $$TITLE$$'."\n"
			.'Beschreibung: $$DESCRIPTION$$'."\n"
			.'Fällig am: $$DUE$$'."\n\n"
			.'Klicken Sie auf den folgenden Link, um die Aufgabe als erledigt zu markieren.'."\n"
			.'$$DONELINK$$';
		$headers = 'From: '.MAIL_SENDER . "\r\n"
			.'Reply-To: '.MAIL_REPLYTO . "\r\n"
			.'Content-Type: text/plain; charset=UTF-8' . "\r\n";
		mail(
			$address,
			'=?UTF-8?B?'.base64_encode($title.' | '.TITLE).'?=',
			self::processTemplate($template, [
				'$$TITLE$$' => $title,
				'$$DESCRIPTION$$' => $description,
				'$$DUE$$' => $dueTime ? date(DATE_FORMAT.' '.TIME_FORMAT, $dueTime) : '',
				'$$URL$$' => (!empty($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']),
				'$$DONELINK$$' => (!empty($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/done.php'
				.'?'.http_build_query(['id'=>$id, 'token'=>$token]),
			]),
			$headers,
			'-f '.MAIL_SENDER
		);
	}

	static function sendReminder($address, $id, $title, $description, $dueTime, $token) {
		$template = 'Erinnerung an eine Aufgabe auf $$URL$$'."\n\n"
			.'Aufgabe: $$TITLE$$'."\n"
			.'Beschreibung: $$DESCRIPTION$$'."\n"
			.'Fällig am: $$DUE$$'."\n\n"
			.'Klicken Sie auf den folgenden Link, um die Aufgabe als erledigt zu markieren.'."\n"
			.'$$DONELINK$$';
		$headers = 'From: '.MAIL_SENDER . "\r\n"
			.'Reply-To: '.MAIL_REPLYTO . "\r\n"
			.'Content-Type: text/plain; charset=UTF-8' . "\r\n";
		mail(
			$address,
			'=?UTF-8?B?'.base64_encode('Erinnerung: '.$title.' | '.TITLE).'?=',
			self::processTemplate($template, [
				'$$TITLE$$' => $title,
				'$$DESCRIPTION$$' => $description,
				'$$DUE$$' => $dueTime ? date(DATE_FORMAT.' '.TIME_FORMAT, $dueTime) : '',
				'$$URL$$' => BASE_URL,
				'$$DONELINK$$' => BASE_URL.'/done.php'
				.'?'.http_build_query(['id'=>$id, 'token'=>$token]),
			]),
			$headers,
			'-f '.MAIL_SENDER
		);
	}

	static function processTemplate(string $template, array $vars) {
		foreach($vars as $key => $value) {
			$template = str_replace($key, $value, $template);
		}
		return $template;
	}

}
