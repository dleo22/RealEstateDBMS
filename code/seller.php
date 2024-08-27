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
$config["dbuser"] = "ora_smande01";			
$config["dbpassword"] = "a38316287";	
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;	// login credentials are used in connectToDB()

$success = true;	// keep track of errors so page redirects only if there are no errors
$failedBefore = false;
$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP
?>

<!-- Above is pasted -->
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Seller Info</title>
</head>
<body>

    <nav>
        <a href="propertyList.php"><button>Property List</button></a>
        <a href="agent.php"><button>Agent Info</button></a>
        <a href="seller.php"><button>Seller Info</button></a>
        <a href="buyer.php"><button>Buyer Info</button></a>
        <a href="lawyer.php"><button>Lawyer Info</button></a>
        <a href="login.php"><button id="signout">Sign Out</button></a>
      </nav>

    <div class="container">

        <h1 style="font-family:helvetica;">Seller Information</h1>
        
        <div>

            <h2 style="font-family:helvetica">Current Sellers</h2>
            <!-- Borrowed the below from Shane -->
            <?php
            if (connectToDB()) {
                $result = executePlainSQL("SELECT * FROM Seller");
                printResult($result);
            }
            disconnectFromDB();
            ?>
            <!-- Borrowed the above from Shane -->

            <hr width="100%" size="2" color="black">

            <h2 style="font-family:helvetica">Add New Seller</h2>
            <form method="POST" action="seller.php">
                <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
                Email: <input type="text" name="insEmail"> <br><br>
                Name: <input type="text" name="insName"> <br><br>
                Lawyer: <input type="text" name="insLawyer"> <br><br>
                <input type="submit" class="generalbtn" value="Add" name="insertSubmit">
            </form>

            <hr width="100%" size="2" color="black">

            <h2 style="font-family:helvetica">Update Seller</h2>
            <form method="POST" action="seller.php">
                <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
                Seller Email: 
                <!-- Borrowed the below from Shane -->
                <select name="email_to_update" id="email_to_update">
                <?php
                if (connectToDB()) {
                    $result = executePlainSQL("SELECT * FROM Seller");
                    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
                        echo "<option>" . $row["EMAIL"] ."</option>"; //or just use "echo $row[0]"
                    }
                }
                disconnectFromDB();
                ?>
                </select> <br><br>
                <!-- Borrowed the above from Shane -->
                <!-- New Email: <input type="text" name="new_email"> <br><br> -->
                New Name: <input type="text" name="new_name"> <br><br>
                New Lawyer: <select name="new_lawyer">
                <?php
                if (connectToDB()) {
                    $result = executePlainSQL("SELECT * FROM Lawyer");
                    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
                        echo "<option>" . $row["EMAIL"] ."</option>"; //or just use "echo $row[0]"
                    }
                }
                disconnectFromDB();
                ?>
                </select> <br><br>
                <input type="submit" class="generalbtn" value="Update" name="updateSubmit">
            </form>

            <!-- <hr width="100%" size="2" color="black">

            <h2 style="font-family:helvetica">Delete Seller</h2>
            <form method="POST" action="seller.php">
                <input type="hidden" id="deleteQueryRequest" name="deleteQueryRequest">
                Seller Email:   
                <select name='email_to_delete' id='email_to_delete'>
               
                </select> <br><br>
                <input type="submit" class="generalbtn" value="Delete" name="deleteSubmit">
            </form> -->

        </div>

    </div>

<!-- Bellow is pasted -->

<?php

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

function executeBoundSQL($cmdstr, $list)
{
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
            //echo $val;
            //echo "<br>".$bind."<br>";
            oci_bind_by_name($statement, $bind, $val);
            unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
        }

        $r = oci_execute($statement, OCI_DEFAULT);
        if (!$r) {
            // echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            // $e = OCI_Error($statement); // For oci_execute errors, pass the statementhandle
            // echo htmlentities($e['message']);
            // echo "<br>";
            $success = False;
        }
    }
}

function printResult($result)
{ //prints results from a select statement
    echo "<table>";
    echo "<tr><th>Email</th><th>Name</th><th>Lawyer</th></tr>";

    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
        if (array_key_exists("NAME", $row) && array_key_exists("L_EMAIL", $row)) {
            echo "<tr><td>" . $row["EMAIL"] . "</td><td>" . $row["NAME"] . "</td><td>" . $row["L_EMAIL"] . "</td></tr>";
        } else if (array_key_exists("NAME", $row)) {
            echo "<tr><td>" . $row["EMAIL"] . "</td><td>" . $row["NAME"] . "</td><td> N/A </td></tr>";
        } else if (array_key_exists("L_EMAIL", $row)) {
            echo "<tr><td>" . $row["EMAIL"] . "</td><td> N/A </td><td>" . $row["L_EMAIL"] . "</td></tr>";
        } else {
            echo "<tr><td>" . $row["EMAIL"] . "</td><td> N/A </td><td> N/A </td></tr>";
        }
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
    executePlainSQL("DROP TABLE Seller cascade constraints");

    // Create new table
    echo "<br> creating new table <br>";
    executePlainSQL("CREATE TABLE Seller (email VARCHAR(40) PRIMARY KEY, name VARCHAR(40), L_email VARCHAR(40))");
    oci_commit($db_conn);
}

function handleInsertRequest()
{
    global $db_conn;
    global $success;
    global $failedBefore;

    //Getting the values from user and insert data into the table
    $tuple = array(
        ":bind1" => $_POST['insEmail'],
        ":bind2" => $_POST['insName'],
        ":bind3" => $_POST['insLawyer']
    );

    $alltuples = array(
        $tuple
    );

    executeBoundSQL("insert into Seller values (:bind1, :bind2, :bind3)", $alltuples);

    if($success) {
        oci_commit($db_conn);
        header('Location: seller.php');
      } else if (!$failedBefore) {
          echo "<p>Please enter valid seller information!</p>";
          $failedBefore = true;
      }
      $success = true;
    
}

function handleUpdateRequest()
{
    global $db_conn;
    global $success;
    global $failedBefore;

    $email_to_update = $_POST['email_to_update'];
    $new_name = $_POST['new_name'];
    $new_lawyer = $_POST['new_lawyer'];

    $tuple = array(":bind1" => $new_name);
    $all = array($tuple);

    if ($new_name != '') {
        executeBoundSQL("UPDATE Seller SET name=:bind1 WHERE email='" . $email_to_update . "'", $all);
        oci_commit($db_conn);
    }
    if ($new_lawyer != '') {
        executePlainSQL("UPDATE Seller SET L_email='" . $new_lawyer . "' WHERE email='" . $email_to_update . "'");
        oci_commit($db_conn);
    }
    if($success) {
        echo "<p>Update successful! Refresh to see changes";
      } else if (!$failedBefore) {
          echo "<p>Update failed... refresh and try again!</p>";
          $failedBefore = true;
      }
      $success = true;
}

// Borrowed the below from Shane
function handleDeleteRequest()
{
    global $db_conn;

    $seller = $_POST['email_to_delete'];

    executePlainSQL("DELETE FROM Seller WHERE email='" . $seller . "'");
    oci_commit($db_conn);

    echo "<br> Deleted " . $seller . " <br>";
    header('Location: seller.php');
}
// Borrowed the above from Shane

function handleDisplayRequest()
{
    global $db_conn;
    
    $result = executePlainSQL("SELECT * FROM Seller");

    
    
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
        } else if (array_key_exists('updateQueryRequest', $_POST)) {
            handleUpdateRequest();
        } else if (array_key_exists('deleteQueryRequest', $_POST)) {
            handleDeleteRequest();
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

// Above is pasted

?>

</body>

</html>
