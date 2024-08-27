<?php
// The preceding tag tells the web server to parse the following text as PHP
// rather than HTML (the default)

// The following 3 lines allow PHP errors to be displayed along with the page
// content. Delete or comment out this block when it's no longer needed.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set some parameters

// Database access configuration
$config["dbuser"] = "ora_smande01";			// change "cwl" to your own CWL
$config["dbpassword"] = "a38316287";	// change to 'a' + your student number
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;	// login credentials are used in connectToDB()

$success = true;	// keep track of errors so page redirects only if there are no errors
$failedBefore = false;
$failedLoginBefore = false;
$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Agent Login</title>
</head>
<body>


      <h1 id="login-heading">Welcome, please log in or create an account to continue</h1>
      <div class="login-parent">
      <form method="POST" action="login.php">
      <input type="hidden" id="checkLogin" name="checkLogin">
              <div class="login">

                  <h2>Agent Login</h2>

                  <input type="text" id="email" name="email" placeholder="Email" class="loginput" required>
                  <input type="password" id="password" name="password" placeholder="Password" class="loginput" required>
                  <input type="text" class="hidden">
                  <label for="lrd" class="hidden">License Renewal Date</label>
                  <input type="date" id="lrd" class="hidden">

                  <button type="submit" class="generalbtn" name="loginSubmit">Login</button>

              </div>
      </form>

      <form method="POST" action="login.php">
      <input type="hidden" name="signInRequest">
          <div class="login">
              <h2>Create Account</h2>
              <input type="text" id="email" name="newEmail" placeholder="Email" class="loginput" required>
              <input type="text" id="password" name="newPass" placeholder="Password" class="loginput" required>
              <input type="text" name="newName" placeholder="Name" required>
              <label for="lrd">Licence Renewal Date</label>
              <input type="date" name="date" id="lrd">
              <button type="submit" class="generalbtn" name="signInSubmit">Create Account</button>
              <?php
                function printError() {
                    echo "<p>Account with that email already exists!<p>";
                }
              ?>
          </div>
      </form>
          </div>

    <?php
    function isSanitized($dirty) {
        return !(str_contains($dirty,"\INSERT") || str_contains($dirty,"insert") || str_contains($dirty, "DROP") || str_contains($dirty, "drop") || str_contains($dirty, "\SELECT") || str_contains($dirty, "select"));
    }

    function handleLogin() {
        global $db_conn;
        global $success;
        global $failedLoginBefore;

        $email = $_POST['email'];
        $password = $_POST['password'];

        $tuple = array(":bind1" => $email);
        $all = array($tuple);
        $result = executeBoundSQL("SELECT * FROM agent WHERE EMAIL=:bind1", $all);
        // $result = executePlainSQL("SELECT * FROM agent WHERE EMAIL='$email'");


        if ($success) {
            $row = OCI_Fetch_Array($result, OCI_ASSOC);


            if ($row["PASSWORD"] === $password) {
                header('Location: propertyList.php');
                die;
            } else if (!$failedLoginBefore) {
                echo "<p>No account with that email and/or password exists!<p>";
                $failedLoginBefore = true;
            }

        }
    }

    function handleSignIn() {
        global $db_conn;
        global $success;
        global $failedBefore;

        $email = $_POST['newEmail'];
        $name = $_POST['newName'];
        $password = $_POST['newPass'];
        $date = $_POST['date'];

        $tuple = array (
            ":bind1" => $email,
            ":bind2" => $name,
            ":bind3"=> $password
        );

        $all = array($tuple);
        
        executeBoundSQL("insert into Agent values (:bind1, :bind2, :bind3, date '$date')", $all);
        
        if($success) {
            oci_commit($db_conn);
            header("Location: propertyList.php");
        } else if (!$failedBefore) {
            printError();
            $failedBefore = true;
        }
        $success = true;
    }

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
             // echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
             // $e = oci_error($statement); // For oci_execute errors pass the statementhandle
             // echo htmlentities($e['message']);
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
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($db_conn);
            echo htmlentities($e['message']);
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
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($statement); // For oci_execute errors, pass the statementhandle
                echo htmlentities($e['message']);
                echo "<br>";
                $success = False;
            }
            return $statement;
        }}

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

      // HANDLE ALL POST ROUTES
    	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    	function handlePOSTRequest()
    	{
    		if (connectToDB()) {
    			if (array_key_exists('checkLogin', $_POST)) {
    			    handleLogin();
    			} else if (array_key_exists('signInRequest', $_POST)) {
    			    handleSignIn();
    			}
    			disconnectFromDB();
    		}
    	}

    	if (isset($_POST['loginSubmit']) || isset($_POST['signInSubmit'])) {
    		handlePOSTRequest();
    	}

      ?>

</body>
</html>


