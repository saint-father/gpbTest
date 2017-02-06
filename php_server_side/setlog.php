<?php
/**
 * Read and parse 2 files: urls.log and browsers.log .
 * Save all data from files into DB tables.
 *
 * Created by PhpStorm.
 * User: Aleksey Fyodorov <saintfather1@yandex.ru>
 * Date: 02.02.2017
 * Time: 17:45
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// @TODO - this code should be implemented as Model pattern with table fields mapping (connection parameters should be in the config-file)
$connect_string = "host=192.168.22.10 port=5432 dbname=gpb user=vagrant password=vagrant";
$dbconnect = pg_connect($connect_string) or die('Could not connect: ' . pg_last_error());
// Patterns for IP and URL checking
$urlPattern = '/^(http:\/\/|https:\/\/)?([^\.\/]+\.)*([a-zA-Z0-9])([a-zA-Z0-9-]*)\.([a-zA-Z]{2,4})(\/.*)?$/i';
$ipPattern = '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(\.[0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]){3}$/';

// Read files to arrays
// @TODO - this code should be implemented as Repository pattern
$urls_log = file('../public_data/urls.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$browsers_log = file('../public_data/browsers.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

try {
    // Parse all rows from urls.log file
    // @TODO - this code should be implemented as Service pattern, which can be independently tested by Unit tests
    if ($urls_log !== false) {
        foreach ($urls_log as $row) {
            $logRow = [];
            $log = explode('|', $row);
            if (count($log) == 5 && preg_match($ipPattern, $log[2]) && preg_match($urlPattern, $log[3]) && preg_match($urlPattern, $log[4])) {
                // Fields lists for mapping file data with table fields
                $logRow['ip'] = $log[2];
                $logRow['url_from'] = $log[3];
                $logRow['url_to'] = $log[4];
                // @todo - It make sense to move it to some Helper library
                $logRow['created_at'] = DateTime::createFromFormat('Y-m-d H:i:s', $log[0] . ' ' . $log[1])->format('Y-m-d H:i:s');
                // This is safe, since $logRow is converted above
                // @todo - Better to use the Model with something like "firstOrCreate" functionality
                $res = pg_insert($dbconnect, 'url_log', $logRow);
                if ($res === false) {
                    throw new Exception('DB saving error. ' . pg_last_error());
                }

            } else {
                throw new Exception('Wrong data in the "urls.log" file.');
            }
        }
    } else {
        throw new Exception('Reading "urls.log" file error.');
    }

    // Parse all rows from browsers.log file
    // @TODO - this code should be implemented as Service pattern, which can be independently tested by Unit tests
    if($browsers_log !== false) {
        foreach ($browsers_log as $row) {
            $logRow = [];
            $log = explode('|', $row);
            // Check IP
            if (count($log) == 3 && preg_match($ipPattern, $log[0])) {
                $logRow['ip'] = $log[0];
                // Is this ip present in the DB (the user uses the single browser with the single OS)
                // @todo - Better to use the Model with something like "firstOrCreate" functionality
                $res = pg_select($dbconnect, 'browsers_log', ['ip' => $logRow['ip']], PGSQL_DML_STRING);
                if (! $res) {
                    // Fields lists for mapping file data with table fields
                    $logRow['browser'] = pg_escape_string($log[1]);
                    $logRow['os'] = pg_escape_string($log[2]);
                    // This is safe, since $logRow is converted above
                    $res = pg_insert($dbconnect, 'browsers_log', $logRow);
                    if ($res === false) {
                        throw new Exception('DB saving error. ' . pg_last_error());
                    }
                }
            } else {
                throw new Exception('Wrong data in the "urls.log" file.');
            }
        }
    } else {
        throw new Exception('Reading "browsers.log" file error.');
    }


    echo "Data is successfully saved";

} catch (Exception $e) {
    // @todo - better to use the Log Service as dependency for some Controller
    echo $e->getMessage();

} finally {
    pg_close($dbconnect);
}
