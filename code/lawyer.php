<?php
// The preceding tag tells the web server to parse the following text as PHP
// rather than HTML (the default)

// The following 3 lines allow PHP errors to be displayed along with the page
// content. Delete or comment out this block when it's no longer needed.
// ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set some parameters

// Database access configuration
$config["dbuser"] = "ora_smande01";			// change "cwl" to your own CWL
$config["dbpassword"] = "a38316287";	// change to 'a' + your student number
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;	// login credentials are used in connectToDB()

$success = true;	// keep track of errors so page redirects only if there are no errors
$failedBefore=false;

$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Lawyer Page</title>
</head>
<body>
    <nav>   
        <a href="propertyList.php"><button>Property List</button></a>
        <a><button>Agent Info</button></a>
        <a href="seller.php"><button>Seller Info</button></a>
        <a href="buyer.php"><button>Buyer Info</button></a>
        <a href="lawyer.php"><button>Lawyer Info</button></a>
        <a href="login.php"><button id="signout">Sign Out</button></a>
      </nav>
	<!--<h2>Reset</h2>
	<p>Please don't press this unless you have to 🙏</p>

	<form method="POST" action="lawyer.php">
		 "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file.
		<input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
		<p><input type="submit" value="Reset" name="reset"></p>
	</form>


    <hr />
    -->
    <h1>Lawyer Information</h1>
    <h2>Current Lawyers</h2>
    <?php
    if (connectToDB()) {
          $result = executePlainSQL("SELECT * FROM Lawyer");
          printResult($result);
      }
      disconnectFromDB();
     ?>
    <hr>
    <h2>Add New Lawyer</h2>
	<form method="POST" action="lawyer.php">
		<input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
		Email: <input type="text" name="insEmail"> <br /><br />
		Name: <input type="text" name="insName"> <br /><br />
        Firm: <input type="text" name="insFirm"> <br /><br />
		<input type="submit" value="Insert" name="insertSubmit" class="generalbtn"></p>
	</form>

	<hr />

    <h2>Update Lawyer Info</h2>
	<p>The values are case sensitive and if you enter in the wrong case, the update statement will not do anything.</p>

	<form method="POST" action="lawyer.php">
		<input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
		Lawyer Email:
        <select name="updateLawyerEmail" id="updateLawyerSelect">
        <?php
        if (connectToDB()) {
            $result = executePlainSQL("SELECT * FROM Lawyer");

            while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
                echo "<option>" . $row["EMAIL"] ."</option>"; //or just use "echo $row[0]"
            }
        }
        disconnectFromDB();

        ?>

		</select>

		<br /><br />
		New Name: <input type="text" name="newName"> <br /><br />
        New Firm: <input type="text" name="newFirm"> <br /><br />
        
		<input type="submit" value="Update" name="updateSubmit" class="generalbtn"></p>
	</form>

	<hr />

    <h2>DELETE Lawyer</h2>
	<p>This action is irreversible!!</p>

	<form method="POST" action="lawyer.php">
		<input type="hidden" id="deleteQueryRequest" name="deleteQueryRequest">
		Lawyer Email:
        <select name="L_email" id="agentSelect">
        <?php
        if (connectToDB()) {
            $result = executePlainSQL("SELECT * FROM Lawyer");

            while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
                echo "<option>" . $row["EMAIL"] ."</option>"; //or just use "echo $row[0]"
            }
        }
        disconnectFromDB();
        ?>
		</select>

		<input type="submit" value="DELETE" name="deleteSubmit" class="generalbtn"></p>
	</form>

  <?php



  // The following code will be parsed as PHP
  function debugAlertMessage($message)
  {
      global $show_debug_alert_messages;

      if ($show_debug_alert_messages) {
          echo "<script type='text/javascript'>alert('" . $message . "');</script>";
      }
  }

  function executePlainSQL($cmdstr)
  { //takes a plain (no bound variables) SQL command and executes it
      //echo "<br>running ".$cmdstr."<br>";
      global $db_conn, $success;

      $statement = oci_parse($db_conn, $cmdstr);
      //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

      if (!$statement) {
          echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
          $e = OCI_Error($db_conn); // For oci_parse errors pass the connection handle
          echo htmlentities($e['message']);
          $success = False;
      }

      $r = oci_execute($statement, OCI_DEFAULT);
      if (!$r) {
          echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
          $e = oci_error($statement); // For oci_execute errors pass the statementhandle
          echo htmlentities($e['message']);
          $success = False;
      }

      return $statement;
  }

  function executeBoundSQL($cmdstr, $list) {
      /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
      In this case you don't need to create the statement several times. Bound variables cause a statement to only be
      parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
      See the sample code below for how this function is used */

      global $db_conn, $success;
      $statement = oci_parse($db_conn, $cmdstr);

      if (!$statement) {
        //   echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        //   $e = OCI_Error($db_conn);
        //   echo htmlentities($e['message']);
          $success = False;
      }

      foreach ($list as $tuple) {
          foreach ($tuple as $bind => $val) {
            //   echo $val;
            //   echo "<br>".$bind."<br>";
              oci_bind_by_name($statement, $bind, $val);
              unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
          }

          $r = oci_execute($statement, OCI_DEFAULT);
          if (!$r) {
            //   echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            //   $e = OCI_Error($statement); // For oci_execute errors, pass the statementhandle
            //   echo htmlentities($e['message']);
            //   echo "<br>";
              $success = False;
          }
      }}

  function printResult($result)
    { //prints results from a select statement
     // echo "<br>Retrieved data from table Lawyer:<br>";
      echo "<table>";
      echo "<tr><th>Name</th><th>Firm</th><th>Email</th></tr>";

      while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
        echo "<tr><td>" . $row["NAME"] . "</td><td>" . $row["FIRM"] . "</td><td>" . $row["EMAIL"] . "</td></tr>"; //or just use "echo $row[0]"
      }

      echo "</table>";
    }

  function connectToDB()
    {
		global $db_conn;
		global $config;

		// Your username is ora_(CWL_ID) and the password is a(student number). For example,
		// ora_platypus is the username and a12345678 is the password.
		// $db_conn = oci_connect("ora_cwl", "a12345678", "dbhost.students.cs.ubc.ca:1522/stu");
		$db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);

		if ($db_conn) {
			debugAlertMessage("Database is Connected");
			return true;
		} else {
			debugAlertMessage("Cannot connect to Database");
			$e = OCI_Error(); // For oci_connect errors pass no handle
			echo htmlentities($e['message']);
			return false;
		}
    }

	function disconnectFromDB()
	{
		global $db_conn;

		debugAlertMessage("Disconnect from Database");
		oci_close($db_conn);
	}

	function handleResetRequest()
	{
		global $db_conn;
		// Drop old table
		executePlainSQL("DROP TABLE Lawyer cascade constraints");

		// Create new table
		echo "<br> creating new table <br>";
		executePlainSQL("CREATE TABLE Lawyer (email VARCHAR(40) PRIMARY KEY, name VARCHAR(40), firm VARCHAR(40))");
		oci_commit($db_conn);
	}

    function handleInsertRequest()
	{
		global $db_conn, $success, $failedBefore;

		//Getting the values from user and insert data into the table
		$tuple = array(
			":bind1" => $_POST['insName'],
			":bind2" => $_POST['insFirm'],
            ":bind3" => $_POST["insEmail"]
		);

		$alltuples = array(
			$tuple
		);

        executeBoundSQL("insert into Lawyer values (:bind1, :bind2, :bind3)", $alltuples);

        if($success) {
            oci_commit($db_conn);
            header('Location: lawyer.php');
          } else if (!$failedBefore) {
              echo "<p>Please enter unique lawyer email!</p>";
              $failedBefore = true;
        }
        $success = true;


		//header('Location: lawyer.php');
	}

    function handleDeleteRequest()
    {
        global $db_conn;

        $lawyer = $_POST['L_email'];

		executePlainSQL("DELETE FROM Lawyer WHERE email='" . $lawyer . "'");
		oci_commit($db_conn);

        echo "<br> Deleted " . $lawyer . " <br>";
        header('Location: lawyer.php');
    }

    function handleUpdateRequest()
    {
        global $db_conn;
        $email = $_POST['updateLawyerEmail'];
        $name = $_POST['newName'];
        $firm = $_POST['newFirm'];

        $tuple = array (
            ":bind1" => $name,
            ":bind2" => $firm
        );
        $all = array($tuple);
        
        if ($name != '') {
            executeBoundSQL("UPDATE Lawyer SET name=:bind1 WHERE email='" . $email . "'", $all);
            oci_commit($db_conn);
        }

        if ($firm != '') {
            executeBoundSQL("UPDATE Lawyer SET firm=:bind2 WHERE email='" . $email . "'", $all);
            oci_commit($db_conn);
        }
        header('Location: lawyer.php');
    }

	function handleDisplayRequest()
	{
		global $db_conn;
		$result = executePlainSQL("SELECT * FROM Lawyer");
		printResult($result);
	}

    function handleDisplayOption() {
        global $db_conn;
		$result = executePlainSQL("SELECT * FROM Lawyer");

        while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
          echo "<option>" . $row["EMAIL"] . "</option>";}
    }

  // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handlePOSTRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('resetTablesRequest', $_POST)) {
				handleResetRequest();
			} else if (array_key_exists('insertQueryRequest', $_POST)) {
				handleInsertRequest();
			} else if (array_key_exists('deleteQueryRequest', $_POST)) {
                handleDeleteRequest();
            } else if (array_key_exists('updateQueryRequest', $_POST)) {
                handleUpdateRequest();
            }

			disconnectFromDB();
		}
	}
	// HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handleGETRequest()
	{
		if (connectToDB()) {
            if (array_key_exists('displayTuples', $_GET)) {
				handleDisplayRequest();
			}

			disconnectFromDB();
		}
	}

	if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit']) || isset($_POST['deleteSubmit'])) {
		handlePOSTRequest();
	} else if (isset($_GET['displayTuplesRequest'])) {
		handleGETRequest();
	}

  ?>
</body>
</html>