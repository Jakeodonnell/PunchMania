<?php
// Author Marcus Nordström
header('Access-Control-Allow-Origin: *');
session_start();
$servername = "ddwap.mah.se:3306/ah7115";
$username = "ah7115";
$password = "Grupp1";
$dbname = "ah7115";
$GLOBALS["conn"] = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$GLOBALS["conn"]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
function index($site) {
	switch ($site) {
		case null:
		templateBody(null, null, null);
		break;
		case "login":
		templateBody("login", "0", "0");
		break;
		case "logout":
		templateBody("logout", "0", "0");
		break;
		case "line":
		templateBody("line", null, null);
		break;
		case "register":
		templateBody("register", "0", "0");
		break;
		case "user":
		if (isset($_GET["user"])) {
			templateBody("user", $_GET["user"], "0");
		}
		default:
		# code...
		break;
	}
}

function templateBody($info, $highscore, $queue) {
	echo '<div class="container-fluid">
	<div class="row header">
	<div class="col-lg">
	<h2 class="title"><a href="index.php">PunchMania</a></h2>
	</div>
	</div>
	<div class="row info">
	<div class="col-lg info">
	<h2 class="title"><a href="index.php">PunchMania</a></h2>';
	echo '<img src="images/logo512.png" class="bgImg" alt="Boxingglove">';
	getInfo($info);
	echo '</div>
	</div>
	<div class="row list">';
	getHSList($highscore);
	getQueue($queue);
	echo '</div>
	</div>';
}
function getHSList($name) {
	if ($name == null) {
		echo '<div class="col-lg hs"><h2 class="title"><a href="index.php">PunchMania</a></h2><h2 class="hsh2">HighScore</h2>';
		echo '<button class="btn" onclick="';
		echo "$('#hsF').css('display', 'block');$('#hs').css('display', 'none');$('.hs .hsh2').text('FastPunch');";
		echo '">Fast</button><button class="btn" onclick="';
		echo "$('#hsF').css('display', 'none');$('#hs').css('display', 'block');$('.hs .hsh2').text('HardPunch');";
		echo '">Hard</button>';
		echo '<img src="images/hs512.png" class="bgImg" alt="Trophy">';
		echo '<div id="hs"></div>
		<script type="text/javascript">
		setInterval(function(){
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var tbodyPosition = $("#hs tbody").scrollTop();
					document.getElementById("hs").innerHTML = this.responseText;
					$("#hs tbody").scrollTop(tbodyPosition);
				}
			};
			xmlhttp.open("GET", "https://ddwap.mah.se/ah7115/PunchMania/main.php?js=hs", true);
			xmlhttp.send();
		}, 500);
		</script>';
		echo '<div id="hsF"></div>
		<script type="text/javascript">
		setInterval(function(){
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var tbodyPosition = $("#hsF tbody").scrollTop();
					document.getElementById("hsF").innerHTML = this.responseText;
					$("#hsF tbody").scrollTop(tbodyPosition);
				}
			};
			xmlhttp.open("GET", "https://ddwap.mah.se/ah7115/PunchMania/main.php?js=hsFast", true);
			xmlhttp.send();
		}, 500);
		</script>';
		echo '</div>';
	} elseif ($name == "0") {
		echo '';
	} else {
		echo '<div class="col-lg hs"><h2 class="title"><a href="index.php">PunchMania</a></h2><h2 class="hsh2">HighScore</h2>';
		echo '<button class="btn" onclick="';
		echo "$('#hsF').css('display', 'block');$('#hs').css('display', 'none');$('.hs .hsh2').text('FastPunch');";
		echo '">Fast</button><button class="btn" onclick="';
		echo "$('#hsF').css('display', 'none');$('#hs').css('display', 'block');$('.hs .hsh2').text('HardPunch');";
		echo '">Hard</button>';
		echo '<img src="images/hs512.png" class="bgImg" alt="Trophy">';
		echo '<div id="hs"></div>
		<script type="text/javascript">
		setInterval(function(){
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var tbodyPosition = $("#hs tbody").scrollTop();
					document.getElementById("hs").innerHTML = this.responseText;
					$("#hs tbody").scrollTop(tbodyPosition);
				}
			};
			xmlhttp.open("GET", "https://ddwap.mah.se/ah7115/PunchMania/main.php?js=hs&user='.$name.'", true);
			xmlhttp.send();
		}, 500);
		</script>';
		echo '<div id="hsF"></div>
		<script type="text/javascript">
		setInterval(function(){
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var tbodyPosition = $("#hsF tbody").scrollTop();
					document.getElementById("hsF").innerHTML = this.responseText;
					$("#hsF tbody").scrollTop(tbodyPosition);
				}
			};
			xmlhttp.open("GET", "https://ddwap.mah.se/ah7115/PunchMania/main.php?js=hsFast&user='.$name.'", true);
			xmlhttp.send();
		}, 500);
		</script>';
		echo '</div>';
	}

}
function getInfo($info){
	switch ($info) {
		case null:
		echo '<div id="info"></div>
		<script type="text/javascript">
		setInterval(function(){
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var previousState = document.getElementById("info").innerHTML;
					var state = this.responseText;
					if(previousState != state) {
						document.getElementById("info").innerHTML = state;
						recentColor();
					}
				}
			};
			xmlhttp.open("GET", "https://ddwap.mah.se/ah7115/PunchMania/main.php?js=info", true);
			xmlhttp.send();
		}, 1000);
		</script>';
		break;
		case "login":
		if (isset($_GET["error"])) {
			echo "<p>Wrong username or password, try again</p>";
		}
		echo '<form action="index.php?site=login" method="POST">
		<label><abbr title="3-15 characters">Username</abbr>:</label><br>
		<input type="text" name="uname" pattern=".{3,15}" required autocomplete="punchmania username"><br>
		<label><abbr title="3-15 characters">Password</abbr>:</label><br>
		<input type="password" name="pw" required autocomplete="punchmania password"><br><br>
		<input type="submit" value="Login" class="btn">
		</form>';
		if (isset($_POST["uname"]) && isset($_POST["pw"])) {
			$query = $GLOBALS["conn"]->prepare("SELECT * FROM user WHERE Uname=:uname");
			$query->bindParam(':uname', $_POST["uname"]);
			$query->execute();
			$result = $query->fetch();
			if (password_verify($_POST["pw"], $result["PW"])) {
				$_SESSION["uname"] = $result["Uname"];
        login($result["Uname"]);
				redirect("index.php?");
			} else {
				redirect("index.php?site=login&error=1");
			}
		}
		break;
		case "logout":
    if (isset($_COOKIE["uuid"])) {
    	$_COOKIE["uuid"] = "NaN";
    }
    unset ($_SESSION['uname']);
		session_destroy();
		redirect("index.php?");
		break;
		case "register":
		if (isset($_GET["error"])) {
			switch ($_GET["error"]) {
				case "1":
					echo "<p>Username already exists!</p>";
					break;
				case "2":
					echo "<p>You have to accept our terms of service</p>";
					break;
				default:
					// code...
					break;
			}
		}
		echo '<form action="index.php?site=register" method="POST">
		<label><abbr title="3-15 characters">Username</abbr>:</label><br>
		<input type="text" name="uname" pattern=".{3,15}" required autocomplete="punchmania username"><br>
		<label><abbr title="3-15 characters">Password</abbr>:</label><br>
		<input type="password" name="pw" required autocomplete="punchmania password"><br>
		<p><input type="checkbox" name="tos"/> I agree to the <a data-toggle="modal" data-target="#tos-modal" id="tos">terms of service</a></p>
		<input type="submit" value="Register" class="btn"></form>
		<div class="modal fade" id="tos-modal">
    <div class="modal-dialog modal-dialog-centered">
		<div class="modal-body">
			 <p>Group1, the creators of PunchMania&trade; <br> Does not take responsability for obscene and inapropiate usernames and reserves the right to remove any account from our platform.</p>
			 <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
		 </div>
		</div></div>';
		if (isset($_POST["uname"]) && isset($_POST["pw"])) {
			if (!isset($_POST["tos"])) {
				redirect("index.php?site=register&error=2");
			}
			$hashpw = password_hash($_POST["pw"], PASSWORD_DEFAULT);
			$query = $GLOBALS["conn"]->prepare("SELECT Uname FROM user WHERE Uname=:uname");			//check if user exists
			$query->bindParam(':uname', $_POST["uname"]);
			$query->execute();
			$checkuname = $query->fetch();
			if ($checkuname != false) {
				redirect("index.php?site=register&error=1");
			}
			$ins = $GLOBALS["conn"]->prepare("INSERT INTO user (Uname, PW) VALUES (:uname, :hash)");
			$ins->bindParam(':uname', $_POST["uname"]);
			$ins->bindParam(':hash', $hashpw);
			$ins->execute();
			$_SESSION["uname"] = $_POST["uname"];
      login($_POST["uname"]);
			redirect("index.php?");
		}
		break;
		case "user":
		echo '<div id="info"></div>
		<script type="text/javascript">
		setInterval(function(){
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var previousState = document.getElementById("info").innerHTML;
					var state = this.responseText;
					if(previousState != state) {
						document.getElementById("info").innerHTML = state;
					}
				}
			};
			xmlhttp.open("GET", "https://ddwap.mah.se/ah7115/PunchMania/main.php?js=user&user='.$_GET["user"].'", true);
			xmlhttp.send();
		}, 1000);
		</script>';
		break;
		default:
		# code...
		break;
	}
}
function getLastPunch() {
	$query = $GLOBALS["conn"]->prepare("SELECT fastpunch.Name, fastpunch.Score, fastpunch.Time AS ti, 'fp' FROM fastpunch UNION SELECT hslist.Name, hslist.Score, hslist.Time as ti, 'hp' FROM hslist ORDER by ti DESC LIMIT 1");
	$query->execute();
	$query = $query->fetch();
	if ($query["fp"] == "fp") {
		$queryfp = $GLOBALS["conn"]->prepare("SELECT * FROM `fastpunch` ORDER BY `Score` DESC LIMIT 1");
		$queryfp->execute();
		$queryfp = $queryfp->fetch();
		if ($query["ti"] == $queryfp["Time"]) {
			echo "<p class='recent' id='recentText' data-highScore='global' data-unix-time='". $query["ti"] ."'><a href='index.php?site=user&user=" .$query["Name"] . "'>". $query["Name"] ."</a> hit a new highscore " . $query["Score"] . " in FastPunch ". getLastPunchTime($query["ti"]) ." ago</p>";
		} else {
			$queryfpp = $GLOBALS["conn"]->prepare("SELECT * FROM `fastpunch` WHERE `Name`=:uname ORDER BY `Score` DESC LIMIT 1");
			$queryfpp->bindParam(":uname", $query["Name"]);
			$queryfpp->execute();
			$queryfpp = $queryfpp->fetch();
			if ($query["ti"] == $queryfpp["Time"]) {
				echo "<p class='recent' id='recentText' data-highScore='personal' data-unix-time='". $query["ti"] ."'><a href='index.php?site=user&user=" .$query["Name"] . "'>". $query["Name"] ."</a> hit a new personal highscore " . $query["Score"] . " in FastPunch ". getLastPunchTime($query["ti"]) ." ago</p>";
			} else {
				echo "<p class='recent' id='recentText' data-highScore='no' data-unix-time='". $query["ti"] ."'><a href='index.php?site=user&user=" .$query["Name"] . "'>". $query["Name"] ."</a> hit " . $query["Score"] . " in FastPunch ". getLastPunchTime($query["ti"]) ." ago</p>";
			}
		}
	} elseif ($query["fp"] == "hp") {
		$queryhp = $GLOBALS["conn"]->prepare("SELECT * FROM `hslist` ORDER BY `Score` DESC LIMIT 1");
		$queryhp->execute();
		$queryhp = $queryhp->fetch();
		if ($query["ti"] == $queryhp["Time"]) {
			echo "<p class='recent' id='recentText' data-highScore='global' data-unix-time='". $query["ti"] ."'><a href='index.php?site=user&user=" .$query["Name"] . "'>". $query["Name"] ."</a> hit a new highscore " . $query["Score"] . " in HardPunch ". getLastPunchTime($query["ti"]) ." ago</p>";
		} else {
			$queryhpp = $GLOBALS["conn"]->prepare("SELECT * FROM `hslist` WHERE `Name`=:uname ORDER BY `Score` DESC LIMIT 1");
			$queryhpp->bindParam(":uname", $query["Name"]);
			$queryhpp->execute();
			$queryhpp = $queryhpp->fetch();
			if ($query["ti"] == $queryhpp["Time"]) {
				echo "<p class='recent' id='recentText' data-highScore='personal' data-unix-time='". $query["ti"] ."'><a href='index.php?site=user&user=" .$query["Name"] . "'>". $query["Name"] ."</a> hit a new personal highscore " . $query["Score"] . " in HardPunch ". getLastPunchTime($query["ti"]) ." ago</p>";
			} else {
				echo "<p class='recent' id='recentText' data-highScore='no' data-unix-time='". $query["ti"] ."'><a href='index.php?site=user&user=" .$query["Name"] . "'>". $query["Name"] ."</a> hit " . $query["Score"] . " in HardPunch ". getLastPunchTime($query["ti"]) ." ago</p>";
			}
		}

	}
}
function getLastPunchTime($time) {
	$LastPunchTime = strtotime($time);
	$CurrentTime = time();
	$diffTime = $CurrentTime - $LastPunchTime;
	$hour = floor($diffTime/3600);
	$diffTime %= 3600;
	$minute = floor($diffTime/60);
	$second = intval($diffTime%60);
	return $hour . ":" . $minute . ":" . $second;
}

function getQplace() {
	if (isset($_SESSION["uname"])) {
		$query = $GLOBALS["conn"]->prepare("SELECT count(*) as num FROM queue WHERE ID < ( SELECT ID FROM queue WHERE Name = :name )+1");
		$query->bindParam(':name', $_SESSION["uname"]);
		$query->execute();
		$query = $query->fetch();
		if ($query["num"] > 0) {
			echo '<button class="btn" onclick="';
			echo "var xmlhttp = new XMLHttpRequest();xmlhttp.open('GET', 'https://ddwap.mah.se/ah7115/PunchMania/main.php?js=line', true);xmlhttp.send();";
			echo '">Remove me<br>from # '. $query["num"] .'</button>';
		} else {
			echo '<button class="btn" onclick="';
			echo "var xmlhttp = new XMLHttpRequest();xmlhttp.open('GET', 'https://ddwap.mah.se/ah7115/PunchMania/main.php?js=line', true);xmlhttp.send();";
			echo '">Put me<br> in line</button>';
		}
	}
}
function getUserStats($user) {
	$queryq = $GLOBALS["conn"]->prepare("SELECT count(*) as num FROM queue WHERE ID < ( SELECT ID FROM queue WHERE Name = :name )+1");
	$queryq->bindParam(':name', $user);
	$queryq->execute();
	$queryq = $queryq->fetch();
	if ($queryq["num"] > 0) {
		echo "<p>Queue: ". $queryq["num"]."</p>";
	} else {
		echo "<p>Queue: Not in queue</p>";
	}
	$queryhs = $GLOBALS["conn"]->prepare("SELECT * FROM `hslist` WHERE `Name` = :name AND `Score` = (SELECT MAX(Score) FROM `hslist` WHERE Name = :name1)");
	$queryhs->bindParam(':name', $user);
	$queryhs->bindParam(':name1', $user);
	$queryhs->execute();
	$queryhs = $queryhs->fetch();
	$queryhsf = $GLOBALS["conn"]->prepare("SELECT * FROM `fastpunch` WHERE `Name` = :name AND `Score` = (SELECT MAX(Score) FROM `fastpunch` WHERE Name = :name1)");
	$queryhsf->bindParam(':name', $user);
	$queryhsf->bindParam(':name1', $user);
	$queryhsf->execute();
	$queryhsf = $queryhsf->fetch();
	if(isset($queryhs["Score"]) || isset($queryhsf["Score"])) {
		if (isset($queryhs["Score"])) {
			echo '<p id="Hp">Best HardPunch: '. $queryhs["Score"] .'</p>';
		}
		if (isset($queryhsf["Score"])) {
			echo '<p id="Fp">Best FastPunch: '. $queryhsf["Score"] .'</p>';
		}
	} else {
		echo "<p>Best score: No score</p>";
	}
}
function getQueue($queue) {
	if ($queue == null) {
		echo '<div class="col-lg q">';
		echo '<h2 class="title"><a href="index.php">PunchMania</a></h2>';
		echo '<img src="images/q512.png" class="bgImg" alt="Clock">';
		echo '<div id="q"></div>
		<script type="text/javascript">
		setInterval(function(){
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var tbodyPosition = $("#q tbody").scrollTop();
					document.getElementById("q").innerHTML = this.responseText;
					$("#q tbody").scrollTop(tbodyPosition);
				}
			};
			xmlhttp.open("GET", "https://ddwap.mah.se/ah7115/PunchMania/main.php?js=q", true);
			xmlhttp.send();
		}, 500);
		</script>';
		echo '</div>';
	}
}
function tableStart() {
	echo "<table><tbody>";
}
function tableEnd() {
	echo '</tbody></table>';
}
function redirect($extra) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra .= "&token=" . generateUUID(6);
	header("Location: https://$host$uri/$extra");
	exit();
}
function generateUUID($length = 25){
  $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charlength = strlen($char);
  $randstr = '';
  for ($i = 0; $i < $length; $i++) {
    $randstr .= $char[rand(0, $charlength - 1)];
  }
  return $randstr;
}
function login($uname) {
  $uuid = generateUUID();
  $ins = $GLOBALS["conn"]->prepare("INSERT INTO `loggedin`(`Uname`, `UUID`) VALUES (:uname, :uuid)");
  $ins->bindParam(":uname", $uname);
  $ins->bindParam(":uuid", $uuid);
  $ins->execute();
  setcookie("uuid", $uuid);
}
function loggedIn() {
  if (isset($_SESSION["uname"])) {
    return true;
  } else if (isset($_COOKIE["uuid"])) {
    setSession($_COOKIE["uuid"]);
    return true;
  }
  return false;
}
function setSession($uuid) {
  $sel = $GLOBALS["conn"]->prepare("SELECT `Uname` AS name FROM `loggedin` WHERE `UUID` = :uuid");
  $sel->bindParam(":uuid", $uuid);
  $sel->execute();
  $fetch = $sel->fetch();
  $_SESSION["uname"] = $fetch["name"];
}
if (isset($_GET["js"])) {
	switch ($_GET["js"]) {
		case "hs":
		if (isset($_GET["user"])) {
			$query = $GLOBALS["conn"]->prepare("SELECT * FROM hslist WHERE Name = :name ORDER BY Score DESC LIMIT 100");
			$name = $_GET["user"];
			$query->bindParam(':name',  $name);
			$query->execute();
			$query = $query->fetchAll();
			$place = 1;
			if (!empty($query)) {
				echo "<table class='hardhs'><tbody>";
				foreach ($query as $row) {
					echo '<tr><td><a href="index.php?site=user&user='.$row["Name"].'">'.$place.'</a></td><td><a href="index.php?site=user&user='.$row["Name"].'">'.$row["Name"].'</a></td><td><a href="index.php?site=user&user='.$row["Name"].'">'.$row["Score"].'</a></td></tr>';
					$place++;
				}
			}
			tableEnd();
		} else {
			$query = $GLOBALS["conn"]->prepare("SELECT * FROM hslist ORDER BY Score DESC LIMIT 100");
			$query->execute();
			$query = $query->fetchAll();
			$place = 1;
			echo "<table class='hardhs'><tbody>";
			foreach ($query as $row) {
				echo '<tr><td><a href="index.php?site=user&user='.$row["Name"].'">'.$place.'</a></td><td><a href="index.php?site=user&user='.$row["Name"].'">'.$row["Name"].'</a></td><td><a href="index.php?site=user&user='.$row["Name"].'">'.$row["Score"].'</a></td></tr>';
				$place++;
			}
			tableEnd();
		}
		break;
		case "hsFast":
		if (isset($_GET["user"])) {
			$query = $GLOBALS["conn"]->prepare("SELECT * FROM fastpunch WHERE Name = :name ORDER BY Score DESC LIMIT 100");
			$name = $_GET["user"];
			$query->bindParam(':name',  $name);
			$query->execute();
			$query = $query->fetchAll();
			$place = 1;
			if (!empty($query)) {
				echo "<table class='fasths'><tbody>";
				foreach ($query as $row) {
					echo '<tr><td><a href="index.php?site=user&user='.$row["Name"].'">'.$place.'</a></td><td><a href="index.php?site=user&user='.$row["Name"].'">'.$row["Name"].'</a></td><td><a href="index.php?site=user&user='.$row["Name"].'">'.$row["Score"].'</a></td></tr>';
					$place++;
				}
			}
			tableEnd();
		} else {
			$query = $GLOBALS["conn"]->prepare("SELECT * FROM fastpunch ORDER BY Score DESC LIMIT 100");
			$query->execute();
			$query = $query->fetchAll();
			$place = 1;
			echo "<table class='fasths'><tbody>";
			foreach ($query as $row) {
				echo '<tr><td><a href="index.php?site=user&user='.$row["Name"].'">'.$place.'</a></td><td><a href="index.php?site=user&user='.$row["Name"].'">'.$row["Name"].'</a></td><td><a href="index.php?site=user&user='.$row["Name"].'">'.$row["Score"].'</a></td></tr>';
				$place++;
			}
			tableEnd();
		}
		break;
		case "q":
		$query = $GLOBALS["conn"]->prepare("SELECT * FROM queue ORDER BY ID ASC LIMIT 100");
		$query->execute();
		$query = $query->fetchAll();
		$place = 1;
		echo ' <h2>Queue</h2>';
		tableStart();
		foreach ($query as $row) {
			echo '<tr><td><a href="index.php?site=user&user='.$row["Name"].'">'.$place.'</a></td><td><a href="index.php?site=user&user='.$row["Name"].'">'.$row["Name"].'</a></td></tr>';
			$place++;
		}
		tableEnd();
		break;
		case "info":
		if (loggedIn()) {
			echo '<h3>Welcome <a href="index.php?site=user&user='.$_SESSION["uname"].'">'.$_SESSION["uname"].'</a></h3>';
			getQplace();
			echo '<a href="index.php?site=logout"><button class="btn">Logout</button></a>';
		} else {
			echo '<a href="index.php?site=login"><button class="btn">Login</button></a>
			<a href="index.php?site=register"><button class="btn">Register</button></a>';
		}
		getLastPunch();
		break;
		case 'line':
		$query = $GLOBALS["conn"]->prepare("SELECT count(*) as num FROM queue WHERE ID < ( SELECT ID FROM queue WHERE Name = :name )+1");
		$query->bindParam(':name', $_SESSION["uname"]);
		$query->execute();
		$query = $query->fetch();
		if($query["num"] == 0) {
			$ins = $GLOBALS["conn"]->prepare("INSERT INTO queue (Name) VALUES (:name)");
			$ins->bindParam(':name', $_SESSION["uname"]);
			$ins->execute();
		} else {
			$del = $GLOBALS["conn"]->prepare("DELETE FROM `queue` WHERE `Name` = :name");
			$del->bindParam(':name', $_SESSION["uname"]);
			$del->execute();
		}
		break;
		case 'user':
		echo '<a href="index.php?site=user&user='.$_GET["user"].'"><h2 class="name">'.$_GET["user"].'</h2></a>';
		getUserStats($_GET["user"]);
		break;
		default:
		# code...
		break;
	}
}
?>
