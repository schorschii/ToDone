<?php

function LANG($key) {
	return LanguageController::getMessageFromSingleton($key);
}

function randomString($length=20, $alphabet='23456789ABCDEFGHKMNPQRSTUVWX') {
	// 1, l, 0, O excluded by default because of possible confusion
	// Y and Z excluded by default to avoid problems with EN/DE keyboard layout
	$charactersLength = strlen($alphabet);
	$randomString = '';
	for($i = 0; $i < $length; $i++) {
		$randomString .= $alphabet[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function shortenDateRange($start, $end) {
	if(!$start && !$end) return '';
	$startTime = empty($start) ? null : strtotime($start);
	$endTime = empty($end) ? null : strtotime($end);
	if(!$start) return date(DATE_FORMAT.' '.TIME_FORMAT, $startTime);
	if(!$end) return date(DATE_FORMAT.' '.TIME_FORMAT, $endTime);
	if($startTime && $endTime
	&& date('Y', $startTime) === date('Y', $endTime)
	&& date('m', $startTime) === date('m', $endTime)
	&& date('d', $startTime) === date('d', $endTime)) {
		return date(DATE_FORMAT.' '.TIME_FORMAT, $startTime).' - '.date(TIME_FORMAT, $endTime);
	} else {
		return date(DATE_FORMAT.' '.TIME_FORMAT, $startTime).' - '.date(DATE_FORMAT.' '.TIME_FORMAT, $endTime);
	}
}

function progressBar($percent, $cid=null, $tid=null, $class=''/*hidden big stretch animated*/, $style='', $text=null) {
	$percent = intval($percent);
	return
		'<span class="progressbar-container '.$class.'" style="--progress:'.$percent.'%; '.$style.'" '.($cid==null ? '' : 'id="'.htmlspecialchars($cid).'"').'>'
			.'<span class="progressbar"><span class="progress"></span></span>'
			.'<span class="progresstext" '.($tid==null ? '' : 'id="'.htmlspecialchars($tid).'"').'>'.(
				$text ? htmlspecialchars($text) : (strpos($class,'animated')!==false ? LANG('in_progress') : $percent.'%')
			).'</span>'
		.'</span>';
}
