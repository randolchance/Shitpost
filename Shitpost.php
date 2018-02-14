<?php
/**
 * Created by PhpStorm.
 * User: dco
 * Date: 2/12/18
 * Time: 10:19 AM
 */
session_start();

include 'class.db.php';

include 'ShitpostHeader.php';

function make_clickable($text) {
    $regex = '#(^|\s)https?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';
    return preg_replace_callback($regex, function ($matches) {
        $returnURL = $matches[0];
        $addSpace = "";
        if ($returnURL[0] == " ") {
            $returnURL = substr($returnURL, 1);
            $addSpace = " ";
        }
        return $addSpace . "<a href=\'{$returnURL}\'>{$returnURL}</a>";
    }, $text);
}

$dbhost = "localhost";
$dbname = "shitdb";
$dbuser = "root";
$dbpasswd = "abcdefgh";

$shittable = "shit_table";
$logintable = "login_table";
$shitdb = new db("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpasswd);

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $usrErr = $pwdErr = $shtErr = '';
    $usr = $pwd = $shitpost = '';
    include 'ShitpostLogin.php';

} else {
    if (!isset($_SESSION["usr"])) {
        if (empty($_POST["usr"])) {
            $usrErr = "User name is required";
        } else {
            // Check User database
            $checkUser = 'User = \'' . $_POST["usr"] . '\'';
            $goodLogin = $shitdb->select($logintable,$checkUser);
            if (!empty($goodLogin)) {
                $usr = $_POST["usr"];
            } else {
                $usrErr = "User is not valid";
            }
        }
    } else {
        $usr = $_SESSION["usr"];
    }

    if (!isset($_SESSION["pwd"])) {
        if (empty($_POST["pwd"])) {
            $pwdErr = "Password is required";
        } else {
            if ($_POST["pwd"] != $goodLogin["0"]["Password"]) {
                $pwdErr = "Password is not valid";
            } else {
                $pwd = $_POST["pwd"];
            }
        }

    } else {
        $pwd = $_SESSION["pwd"];
    }

    if (($usr == '' || $pwd == '') && (!isset($_SESSION["usr"]))) {
        include 'ShitpostLogin.php';
    } else {
        $_SESSION["usr"] = $usr;
        $_SESSION["pwd"] = $pwd;
        if (isset($_POST["shitpost"]) && ($_POST["shitpost"] != '')) {
            // Insert shitpost into the DB under usr
            $shitdb->insert($shittable, array(
                "Date" => date('Y-m-d H:i:s'),
                "User" => $_SESSION["usr"],
                "Shit" => $_POST["shitpost"]
            ));
        } elseif (isset($_POST["shitpost"]) && ($_POST["shitpost"] == '')) {
            $shtErr = "Empty shitposts not allowed!";
        }
        include 'ShitpostDB.php';
        // List db entries here
        $currentPostDate = '';
        foreach(array_reverse($shitdb->select($shittable)) as $shite) {
            $dateArray = explode(" ", $shite["Date"]);
            $shitDate = ($currentPostDate == $dateArray[0]) ? "" : $currentPostDate = $dateArray[0];
            $shitDate .= (date('Y-m-d') == $shitDate) ? " (Today)" : "";
            $shitTime = "@" . $dateArray[1];
            $shitUser = ($shite["User"] != $usr) ? $shite["User"] : "You";
            $shitPost = str_replace("\n","<br>",$shite["Shit"]);

            $shitPost = make_clickable($shitPost);

            include 'ShitEntry.php';
        }
    }
}

include 'ShitpostFooter.php';
?>