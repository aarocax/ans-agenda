<?php

$hostname = 'hostname';
$database = 'databasename';
$username = 'username';
$password = 'password';
$max_min_past = 10;

try {
  $conn = new PDO('mysql:host='.$hostname.';dbname='.$database, $username, $password, 
      array(PDO::ATTR_PERSISTENT => true));
  echo "Conectado\n";
} catch (Exception $e) {
  die("No se pudo conectar: " . $e->getMessage());
}

try {
	$paid = false;
	$sql = "select * from wp_ans_agenda WHERE paid = :paid";
	$results = $conn->query($sql);
	$statement = $conn->prepare($sql);
	$statement->bindValue(":paid", $paid);
	$statement->execute();
	$results = $statement->fetchAll();
	$actual_date = new DateTime('now', new DateTimeZone('Europe/Madrid'));
	foreach ($results as $key => $value) {
		$date = new \DateTime($value['creation_date'], new DateTimeZone('Europe/Madrid'));
		$since_start = $actual_date->diff($date);
		if ($since_start->days > 0 || $since_start->y > 0 || $since_start->m > 0 || $since_start->d > 0 || $since_start->h > 0 || $since_start->i > $max_min_past) {
			// remove
			$statement = $conn->prepare( "DELETE FROM wp_ans_agenda WHERE id =:id" );
			$statement->bindParam(":id", $value['id'] );
			$statement->execute();
			if( ! $statement->rowCount() ) echo "Deletion failed";
		}
	}
} catch (Exception $e) {
	
}