<?php
require_once('loader.inc.php');

$info = null;
$infoClass = null;
$tasks = $db->getTasks();

try {
	if(!empty($_POST['action']) && $_POST['action'] === 'task_take'
	&& !empty($_POST['id']) && !empty($_POST['assignee_email'])) {
		if(!filter_var($_POST['assignee_email'], FILTER_VALIDATE_EMAIL)) {
			http_response_code(400);
			die('Die eingegebene E-Mail-Adresse ist ungültig');
		}
		$task = $tasks[$_POST['id']??null];
		if(!$task) {
			http_response_code(400);
			die('Diese Aufgabe existiert nicht mehr');
		}
		if($task['assignee_email']) {
			http_response_code(400);
			die('Diese Aufgabe wurde bereits übernommen');
		}
		$db->updateTask(
			$task['id'],
			$task['title'], $task['description'],
			$_POST['assignee_email'],
			$task['due'],
			$task['reminder'],
			$task['done'],
			$task['done_note'],
			$task['token']
		);
		$tasks = $db->getTasks();
		InvitationMailer::send(
			$_POST['assignee_email'], $task['id'],
			$task['title'], $task['description'],
			empty(trim($task['due'])) ? null : strtotime($task['due']),
			$task['token']
		);
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
		<style>
			.red { color:red; }
			.green { color: green; }
		</style>
		<script>
			function takeTask(id) {
				email = prompt('Bitte Ihre E-Mailadresse eingeben');
				if(!email) return;

				data = new FormData()
				data.set('action', 'task_take');
				data.set('id', id);
				data.set('assignee_email', email);

				let request = new XMLHttpRequest();
				request.open('POST', 'index.php', true);
				request.send(data);
				request.onreadystatechange = function() {
					if(this.readyState != 4) return;
					if(this.status == 200) {
						window.location.reload();
					} else {
						alert(this.responseText);
					}
				};
			}
		</script>
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

				<form method='GET' action='task.php'>
					<button id='btnLink'><img src='img/add.svg'>Neue Aufgabe</button>
				</form>
				<form method='GET' action='task.php' class='reservation' style='clear:both'>
					<div class='scroll-h'>
					<table id='tblTasks' class='fullwidth hover'>
						<thead>
							<tr>
								<th>Titel</th>
								<th>Person</th>
								<th>Fällig am</th>
								<th>Erledigt am</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($tasks as $task) { ?>
							<tr>
								<td>
									<?php echo htmlspecialchars($task['title']); ?>
									<?php if($task['description']) { ?>
										<img src='img/message.svg' title='<?php echo htmlspecialchars($task['description'],ENT_QUOTES); ?>' onclick='alert(this.title)'>
									<?php } ?>
								</td>
								<td>
									<?php if($task['assignee_email']) echo htmlspecialchars($task['assignee_email']); else { ?>
										<a href='#' onclick='takeTask(<?php echo $task['id']; ?>)'>übernehmen</a>
									<?php } ?>
								</td>
								<td class='<?php if($task['due'] && strtotime($task['due']) < time() && !$task['done']) echo 'red'; ?>'>
									<?php if($task['due']) echo date(DATE_FORMAT, strtotime($task['due'])); ?>
								</td>
								<td>
									<?php if($task['done']) echo date(DATE_FORMAT, strtotime($task['done'])); ?>
									<?php if($task['done_note']) { ?>
										<img src='img/message.svg' title='<?php echo htmlspecialchars($task['done_note'],ENT_QUOTES); ?>' onclick='alert(this.title)'>
									<?php } ?>
								</td>
								<td class='actions'><button name='edit' value='<?php echo $task['id']; ?>'><img src='img/edit.svg'></button></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					</div>
				</form>

			</div>
			<?php require('foot.inc.php'); ?>
		</div>
	</body>
</html>
