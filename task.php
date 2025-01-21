<?php
require_once('loader.inc.php');
require_once('session.inc.php');

$info = null;
$infoClass = null;
$tasks = $db->getTasks();

	// create task if requested
	if(!empty($_POST['action']) && $_POST['action'] == 'task_create') {
		$token = randomString();
		$due = $_POST['due_date'].' '.$_POST['due_time'];
		$done = $_POST['done_date'].' '.$_POST['done_time'];
		$insertId = $db->insertTask(
			$_POST['title'], $_POST['description'],
			$_POST['assignee_email'],
			empty(trim($due)) ? null : $due,
			empty($_POST['reminder']) ? null : $_POST['reminder']*60*60*24,
			empty(trim($done)) ? null : $done,
			$_POST['done_note'],
			$token
		);

		// send invitation
		if(!empty($_POST['assignee_email'])) InvitationMailer::send(
			$_POST['assignee_email'], $insertId,
			$_POST['title'], $_POST['description'],
			empty(trim($due)) ? null : strtotime($due),
			$token
		);
		$info = 'Aufgabe wurde erstellt';
		$infoClass = 'green';
	}

	// edit task if requested
	if(!empty($_POST['action']) && $_POST['action'] == 'task_edit'
	&& !empty($_POST['id'])) {
		$due = $_POST['due_date'].' '.$_POST['due_time'];
		$done = $_POST['done_date'].' '.$_POST['done_time'];

		// send new invitation if assignee changed
		$task = $tasks[$_POST['id']] ?? null;
		if(!$task) throw new Exception('Task not found');
		$token = $task['token'];
		if($task['assignee_email'] !== $_POST['assignee_email']) {
			$token = randomString();
			if(!empty($_POST['assignee_email'])) InvitationMailer::send(
				$_POST['assignee_email'], $task['id'],
				$_POST['title'], $_POST['description'],
				empty(trim($due)) ? null : strtotime($due),
				$token
			);
		}

		$db->updateTask(
			$_POST['id'], $_POST['title'], $_POST['description'],
			$_POST['assignee_email'],
			empty(trim($due)) ? null : $due,
			empty($_POST['reminder']) ? null : $_POST['reminder']*60*60*24,
			empty(trim($done)) ? null : $done,
			$_POST['done_note'],
			$token
		);
		$tasks = $db->getTasks();
		$info = LANG('saved');
		$infoClass = 'green';
	}

	// delete task if requested
	if(!empty($_POST['action']) && $_POST['action'] == 'task_delete'
	&& !empty($_POST['id'])) {
		$db->deleteTask($_POST['id']);
		header('Location: index.php');
		die();
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<?php require_once('head.inc.php'); ?>
		<title><?php echo htmlspecialchars(TITLE); ?> | ToDone</title>
		<script src='js/admin.js'></script>
	</head>
	<body>
		<div id='container'>
			<div id='splash' class='contentbox'>

				<?php foreach(['img/logo-custom.png','img/logo-custom.jpg'] as $file) if(file_exists($file)) { ?>
					<img id='logo' src='<?php echo $file; ?>'>
				<?php } ?>

				<h1><?php echo htmlspecialchars(TITLE); ?></h1>

				<?php if($info) { ?>
					<div class='infobox <?php echo $infoClass; ?>'><?php echo htmlspecialchars($info); ?></div>
				<?php } ?>

				<a id='btnLink' href='index.php'>← alle Aufgaben</a>
				<form method='POST' class='adminForm'>
						<?php
						$selectedTask = null;
						if(!empty($_GET['edit'])) {
							$selectedTask = $tasks[$_GET['edit']] ?? null;
						} ?>

						<label>Titel:</label>
						<div><input type='text' name='title' required='true' value='<?php echo htmlspecialchars($selectedTask ? $selectedTask['title'] : ''); ?>'></div>

						<label>Beschreibung:</label>
						<div><input type='text' name='description' placeholder='<?php echo LANG('optional'); ?>' value='<?php echo htmlspecialchars($selectedTask ? $selectedTask['description'] : ''); ?>'></div>

						<label>E-Mail:</label>
						<div><input type='email' name='assignee_email' value='<?php echo htmlspecialchars($selectedTask ? $selectedTask['assignee_email'] : ''); ?>'></div>

						<div></div>
						<div></div>

						<label>Fällig:</label>
						<?php $preselectDate = ''; $preselectTime = '';
						if($selectedTask && $selectedTask['due']) {
							$preselectDate = date('Y-m-d', strtotime($selectedTask['due']));
							$preselectTime = date('H:i', strtotime($selectedTask['due']));
						} ?>
						<div class='multiinput'>
							<input type='date' name='due_date' value='<?php echo htmlspecialchars($preselectDate); ?>'>
							<input type='time' name='due_time' value='<?php echo htmlspecialchars($preselectTime); ?>'>
						</div>

						<label>Erinnerung:</label>
						<div class='multiinput'>
							<input type='number' name='reminder' value='<?php echo htmlspecialchars(($selectedTask&&!empty($selectedTask['reminder'])) ? $selectedTask['reminder']/24/60/60 : ''); ?>'>
							<span>Tage vorher</span>
						</div>

						<label>Erledigt:</label>
						<?php $preselectDate = ''; $preselectTime = '';
						if($selectedTask && $selectedTask['done']) {
							$preselectDate = date('Y-m-d', strtotime($selectedTask['done']));
							$preselectTime = date('H:i', strtotime($selectedTask['done']));
						} ?>
						<div class='multiinput'>
							<input type='date' name='done_date' value='<?php echo htmlspecialchars($preselectDate); ?>'>
							<input type='time' name='done_time' value='<?php echo htmlspecialchars($preselectTime); ?>'>
						</div>

						<label>Vermerk:</label>
						<div><input type='text' name='done_note' value='<?php echo htmlspecialchars($selectedTask ? $selectedTask['done_note'] : ''); ?>'></div>

						<?php if($selectedTask) { ?>
							<input type='hidden' name='id' value='<?php echo htmlspecialchars($selectedTask['id']); ?>'>
							<input type='submit' name='action' value='task_edit' style='display:none'>
							<button id='btnDelete' name='action' value='task_delete' class='' onclick='return confirm("Sind Sie sicher?")'>Löschen</button>
							<button id='btnSave' name='action' value='task_edit' class='primary'>Änderungen speichern</button>
						<?php } else { ?>
							<button id='btnSave' name='action' value='task_create' class='primary'>Erstellen</button>
						<?php } ?>
					</form>

			</div>
			<?php require('foot.inc.php'); ?>
		</div>
	</body>
</html>
