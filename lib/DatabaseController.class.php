<?php

class DatabaseController {

	protected $dbh;
	private $stmt;

	function __construct() {
		try {
			$this->dbh = new PDO(
				DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME.';',
				DB_USER, DB_PASS,
				array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4')
			);
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(Exception $e) {
			error_log($e->getMessage());
			throw new Exception('Failed to establish database connection to ›'.DB_HOST.'‹. Gentle panic.');
		}
	}

	public function getTasks() {
		$this->stmt = $this->dbh->prepare('SELECT * FROM task ORDER BY due ASC');
		$this->stmt->execute();
		$tasks = [];
		foreach($this->stmt->fetchAll() as $row) {
			$tasks[$row['id']] = $row;
		}
		return $tasks;
	}
	public function getTask($id) {
		$this->stmt = $this->dbh->prepare('SELECT * FROM task WHERE id=:id');
		$this->stmt->execute([':id'=>$id]);
		foreach($this->stmt->fetchAll() as $row) {
			return $row;
		}
	}
	public function insertTask($title, $description, $assignee_email, $due, $reminder, $done, $done_note, $token) {
		$this->stmt = $this->dbh->prepare(
			'INSERT INTO task (title, description, assignee_email, due, reminder, done, done_note, token)
			VALUES (:title, :description, :assignee_email, :due, :reminder, :done, :done_note, :token)'
		);
		$this->stmt->execute([
			':title' => $title, ':description' => $description,
			':assignee_email' => $assignee_email, ':due' => $due, ':reminder' => $reminder,
			':done' => $done, ':done_note' => $done_note, ':token' => $token,
		]);
		return $this->dbh->lastInsertId();
	}
	public function updateTask($id, $title, $description, $assignee_email, $due, $reminder, $done, $done_note, $token) {
		$this->stmt = $this->dbh->prepare(
			'UPDATE task SET title=:title, description=:description, assignee_email=:assignee_email, due=:due, reminder=:reminder, done=:done, done_note=:done_note, token=:token WHERE id=:id'
		);
		$this->stmt->execute([
			':id' => $id, ':title' => $title, ':description' => $description,
			':assignee_email' => $assignee_email, ':due' => $due, ':reminder' => $reminder,
			':done' => $done, ':done_note' => $done_note, ':token' => $token,
		]);
		return $this->dbh->lastInsertId();
	}
	public function updateTaskReminderSent($id) {
		$this->stmt = $this->dbh->prepare(
			'UPDATE task SET reminder_sent=current_timestamp() WHERE id=:id'
		);
		$this->stmt->execute([':id' => $id]);
		return $this->dbh->lastInsertId();
	}
	public function deleteTask($id) {
		$this->stmt = $this->dbh->prepare('DELETE FROM task WHERE id=:id');
		return $this->stmt->execute([':id' => $id]);
	}

	public function getSetting($key) {
		$this->stmt = $this->dbh->prepare('SELECT * FROM setting WHERE `key` = :key');
		$this->stmt->execute([':key' => $key]);
		foreach($this->stmt->fetchAll() as $row) {
			return $row['value'];
		}
	}
	public function updateSetting($key, $value) {
		$this->stmt = $this->dbh->prepare('REPLACE INTO setting (`key`, value) VALUES (:key, :value)');
		return $this->stmt->execute([':key' => $key, ':value' => $value]);
	}

}
