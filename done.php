<?php
require_once('loader.inc.php');

$info = null;
$infoClass = null;
$tasks = $db->getTasks();
$task = null;

try {
	if(!empty($_GET['id']) && !empty($_GET['token'])) {
		$task = $tasks[$_GET['id']] ?? null;
		if(!$task)
			throw new Exception('Diese Aufgabe existiert nicht mehr');
		if($task['token'] !== $_GET['token'])
			throw new Exception('Sie haben keine Berechtigungen diese Aufgabe zu bearbeiten');

		if(!empty($_POST['action'])) {
			$db->updateTask(
				$task['id'],
				$task['title'], $task['description'],
				$task['assignee_email'],
				$task['due'],
				$task['reminder'],
				($_POST['done']??null) ? date('Y-m-d H:i:s') : null,
				$_POST['done_note']??'',
				$task['token']
			);
			$task = $db->getTask($task['id']);
		}
	}
} catch(Exception $e) {
	$info = $e->getMessage();
	$infoClass = 'red';
	$task = null;
}
?>

<!DOCTYPE html>
<html>
	<head>
		<?php require_once('head.inc.php'); ?>
		<title><?php echo htmlspecialchars(TITLE); ?> | ToDone</title>
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
				<?php if($task) { ?>
					<h2><?php echo htmlspecialchars($task['title']); ?></h2>
					<p><?php echo htmlspecialchars($task['description']); ?></p>

					<form method='POST' class=''>
						<label><input type='checkbox' name='done' value='1' <?php if($task['done']) echo 'checked'; ?>> erledigt</label>

						<label>
							<p>Erledigungsvermerk:</p>
							<input type='text' name='done_note' class='fullwidth' placeholder='<?php echo LANG('optional'); ?>' value='<?php echo htmlspecialchars($task['done_note'] ? $task['done_note'] : '', ENT_QUOTES); ?>'></input>
						</label>

						<button id='btnSave' name='action' value='task_done' class='fullwidth'>Speichern</button>
					</form>
				<?php } ?>

			</div>
			<?php require('foot.inc.php'); ?>
		</div>

		<script>
			toggleVoucher();
		</script>
	</body>
</html>
