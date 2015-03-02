<?php
require 'Slim/Slim.php';
require_once('../classes/config.php');
require_once('../classes/Dbf2.class.php');

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$dbf2 = new Dbf2(Dbf2::MSSQLSERVER, $username, $password, $dbname, $csvPath);

// Root
$app->get(
    '/',
    function () use ($app) {
        $app->redirect('./dbf2');
    }
);

// Import Dbf
$app->post(
    '/import',
    function () use ($app, $dbf2) {
        $jsonDbf = $app->request->post('dbf');
        $jsonDbf = str_replace("/", "\\", $jsonDbf);

        $dbfFile = $jsonDbf;
        
        if ($dbf2->hasError()){
            echo $dbf2->getErrorCode();
        }
        else{
            if (!$dbf2->generateFiles($dbfFile)){
                if ($dbf2->hasError()){
                    echo $dbf2->getErrorCode();
                }

                $dbf2->dropTable();
                $dbf2->generateFiles($dbfFile);
            }
        }
            
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
        $app->view()->setData(array('dbfdir' => $dbfPath));
        
        $app->render('dbf2.php');
    }
);

$app->run();
