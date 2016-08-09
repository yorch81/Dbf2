<?php
require "../vendor/autoload.php";
require_once('../classes/config.php');
require_once('../classes/Dbf2.class.php');

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

if ($dbtype == 'MSSQLSERVER')
    $dbf2 = new Dbf2(Dbf2::MSSQLSERVER, $hostname, $username, $password, $dbname, $csvPath);
else
    $dbf2 = new Dbf2(Dbf2::MYSQL, $hostname, $username, $password, $dbname, $csvPath);

if ($dbf2->hasError()){
    die("Unable load Application DBF2");
}

// Set Time Execution Limit
set_time_limit (600);

// Init Session
session_start();

// Root
$app->get(
    '/',
    function () use ($app) {
        if (isset($_SESSION["dbf2"]))
            $app->redirect('./dbf2');
        else
            $app->redirect('./login');
    }
);

// Exit
$app->get(
    '/exit',
    function () use ($app) {
        session_destroy();
        $app->redirect('./login');
    }
);

// Import Dbf
$app->post(
    '/import',
    function () use ($app, $dbf2) {
        $jsonDbf = $app->request->post('dbf');

        // Replace / for \ for Windows
        $jsonDbf = str_replace("/", "\\", $jsonDbf);

        $dbfFile = $jsonDbf;
        $result = $dbf2->generateFiles($dbfFile);

        $app->response()->status(200);

        echo $result;

        $dbf2 = null;
    }
);

// Drop Table
$app->post(
    '/drop',
    function () use ($app, $dbf2) {
        $jsonDbf = $app->request->post('dbf');

        // Replace / for \ for Windows
        $jsonDbf = str_replace("/", "\\", $jsonDbf);

        $dbfFile = $jsonDbf;
        $dbf2->dropTable($dbfFile);

        $app->response()->status(200);

        echo 'Ok';

        $dbf2 = null;
    }
);

// Get Files
$app->post(
    '/getfiles',
    function () use ($app, $dbfPath) {
        $dir = $app->request->post('dir');

        try{
            $app->response()->status(200);

            if ($dir == "./")
                $dir = $dbfPath;

            $dir = urldecode($dir);

            if( file_exists($dir) ) {
                $files = scandir($dir);
                natcasesort($files);
                if( count($files) > 2 ) { /* The 2 accounts for . and .. */
                    echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
                    // All dirs
                    foreach( $files as $file ) {
                        if( file_exists($dir . $file) && $file != '.' && $file != '..' && is_dir( $dir . $file) ) {
                            echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "/\">" . htmlentities($file) . "</a></li>";
                        }
                    }
                    // All files
                    foreach( $files as $file ) {
                        if( file_exists($dir . $file) && $file != '.' && $file != '..' && !is_dir($dir . $file) ) {
                            $ext = preg_replace('/^.*\./', '', $file);
                            // Filter DBF
                            if (strtoupper($ext) == "DBF")
                                echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "\">" . htmlentities($file) . "</a></li>";
                        }
                    }
                    echo "</ul>";   
                }
            }
        }
        catch (ResourceNotFoundException $e) {
            $app->response()->status(404);
        } 
        catch (Exception $e) {
            $app->response()->status(400);
            $app->response()->header('X-Status-Reason', $e->getMessage());
        }
    }
);

// Dbf2 View
$app->get("/dbf2", 
    function () use ($app, $dbfPath) {
        if (! isset($_SESSION["dbf2"]))
            $app->redirect('./login');

        $app->view()->setData(array('dbfdir' => $dbfPath));
        
        $app->render('dbf2.php');
    }
);

// Login View
$app->get("/login", 
    function () use ($app, $dbfPath) {  
        if (isset($_SESSION["dbf2"]))
            $app->redirect('./dbf2');

        $app->render('login.php');
    }
);

// Login Post
$app->post(
    '/login',
    function () use ($app, $appUser, $appPassword) {
        $user = $app->request->post('txtUser');
        $password = $app->request->post('txtPassword');

        if ($user == $appUser && $password == $appPassword)
            $_SESSION["dbf2"] = "DBF2";
        else
            $_SESSION["error"] = "User or Password is incorrect";

        if (isset($_SESSION["dbf2"]))
            $app->redirect('./dbf2');
        else
            $app->redirect('./login');
    }
);

// Run Slim Application
$app->run();
