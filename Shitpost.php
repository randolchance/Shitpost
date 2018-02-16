<?php
/**
 * Created by PhpStorm.
 * User: dco
 * Date: 2/12/18
 * Time: 10:19 AM
 */
session_start();

require_once 'class.db.php';
require_once 'class.viewer.php';

$template_dir = './templates/';

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

function view_render($view,$filename) {
    try {
        $view->render($filename);
    }
    catch (Exception $E) {
        echo $E -> getMessage();
    }
}

$ShitpostHeaderTemplate = 'ShitpostHeader.phtml';
$ShitpostLoginTemplate = 'ShitpostLogin.phtml';
$ShitpostNewEntryTemplate = 'ShitpostNewEntry.phtml';
$ShitpostEntryTemplate = 'ShitpostEntry.phtml';
$ShitpostFooterTemplate = 'ShitpostFooter.phtml';

$dbhost = "localhost";
$dbname = "shitdb";
$dbuser = "root";
$dbpasswd = "abcdefgh";

$shittable = "shit_table";
$logintable = "login_table";
$shitdb = new db("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpasswd);

$ShitView = new viewer($template_dir);

view_render($ShitView,$ShitpostHeaderTemplate);

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    session_unset();
    $loginErr = $shtErr = '';
    $usr = $pwd = $shitpost = '';

    view_render($ShitView,$ShitpostLoginTemplate);

} else {
    if (!isset($_SESSION["usr"])) {
        if (empty($_POST["usr"])) {
            $loginErr = "User name is required";
        } else {
            // Check User database
            $checkUser = 'User = \'' . $_POST["usr"] . '\'';
            $goodLogin = $shitdb->select($logintable,$checkUser);
            if (!empty($goodLogin)) {
                $usr = $_POST["usr"];
            } else {
                $loginErr = "Login is not valid";
            }
        }
    } else {
        $usr = $_SESSION["usr"];
    }

    if (!isset($_SESSION["pwd"])) {
        if (empty($_POST["pwd"])) {
            $loginErr .= ($loginErr != '') ? " and password is required" : "Password is required";
        } else {
            if ($_POST["pwd"] != $goodLogin["0"]["Password"]) {
                $loginErr = "Login is not valid";
            } else {
                $pwd = $_POST["pwd"];
            }
        }

    } else {
        $pwd = $_SESSION["pwd"];
    }

    if (($usr == '' || $pwd == '') && (!isset($_SESSION["usr"]))) {
        $ShitView->loginVars = array(
            "usr"=>$usr,
            "loginErr"=>$loginErr
        );
        view_render($ShitView,$ShitpostLoginTemplate);
    } else {
        $_SESSION["usr"] = $usr;
        $_SESSION["pwd"] = $pwd;
        // Process new shitpost to db if one exists
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

        $ShitView->newEntryVars = array(
            "usr"=>$usr,
            "shtErr"=>$shtErr
        );
        view_render($ShitView,$ShitpostNewEntryTemplate);

        // List db entries here
        $currentPostDate = '';
        echo "<div class=\"flexbody\">";
        foreach(array_reverse($shitdb->select($shittable)) as $shite) {
            $dateArray = explode(" ", $shite["Date"]);
            $shitDate = ($currentPostDate == $dateArray[0]) ? "" : $currentPostDate = $dateArray[0];
            $shitDate .= (date('Y-m-d') == $shitDate) ? " (Today)" : "";

            $shitTime = "@" . $dateArray[1];

            $shitUser = ($shite["User"] != $usr) ? $shite["User"] : "You";

            $shitPost = str_replace("\n","<br>",$shite["Shit"]);
            $shitPost = make_clickable($shitPost);

            $ShitView->entryVars = array(
                "shitDate"=>$shitDate,
                "shitTime"=>$shitTime,
                "shitUser"=>$shitUser,
                "shitPost"=>$shitPost
            );
            view_render($ShitView,$ShitpostEntryTemplate);
        }
        echo '</div>';
    }
}

require_once $template_dir.'ShitpostFooter.phtml';
?>