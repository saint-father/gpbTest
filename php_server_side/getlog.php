<?php
/**
 * Server-side functionality for "Log Grid" functionality.
 * Query DB tables and send json response to ExtJS application.
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
$dbconnect = pg_connect($connect_string)or die('Could not connect: ' . pg_last_error());
// Fields list for mapping query results and checking request parameters
$queriedFields = ["user_ip", "urls_from", "urls_to", "browser", "os"];
// Patterns for IP and URL checking
$ipPattern = '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(\.[0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]){0,3}$/';

// Get and decode "sort" and "filter" parameters.
// @TODO - this code should be moved to the some Controller
$request = $_REQUEST;
/** @var array $sorts */
$sorts = (! empty($request['filter'])) ? json_decode($request['sort'],true) : [];
/** @var array $filters */
$filters = (! empty($request['filter'])) ? json_decode($request['filter'],true) : [];

$whereStr = '';
//Check the filter and prepare the "where" sql condition
//filter format is:[{"operator":"like","value":"127.0.1","property":"name"}]
// @TODO - this code should be implemented as Service pattern
foreach ($filters as $filter)
{
    try {
        // Filtered field should be present in the DB table (query result)
        if (in_array($filter['property'], $queriedFields)) {
            // First of all need to Escape parameters for query
            $filter['value'] = pg_escape_string($filter['value']);
            $filter['property'] = pg_escape_string($filter['property']);
            // Prepare ip parameter if it's necessary
            switch ($filter['property']) {
                case "user_ip":
                    // check the ip value
                    if (preg_match($ipPattern, $filter['value']) !== 1)
                        throw new Exception('Wrong IP data.');
                    $filter['property'] = 'ul.ip';
                    break;
            }
            // Currently I use only 2 operators in the SQL
            switch ($filter['operator']) {
                case "like":
                    $filter['operator'] = "like";
                    $filter['value'] = '\'%' . $filter['value'] . '%\'';
                    break;
                default:
                    $filter['operator'] = "=";
            }

            $whereStr .= (!empty($whereStr)) ? ' AND ' : ' WHERE ';
            $whereStr .= $filter['property'] . ' ' . $filter['operator'] . ' ' . $filter['value'];
        } else {
            throw new Exception('Filtered field name is wrong.');
        }
    } catch (Exception $e) {
        // We can save log or implement any other behavior there.
        die;
    }
}

$sortStr = '';
// Check the sort and prepare the "sort by" sql condition
// sort format:[{"property":"name","direction":"ASC"},{"property":"email","direction":"DESC"}]
// @TODO - this code should be implemented as Service pattern
foreach ($sorts as $sort)
{
    // Ordered fields should be in the table or SQL result
    // additionally the "direction" should contain "asc" or "desc" only.
    if (in_array($sort['property'], $queriedFields) && in_array(strtolower($sort['direction']), ["asc","desc"]))
    {
        $sortStr .= (! empty($sortStr)) ? ' , ' : ' ORDER BY ';
        // Need to Escape parameters for query
        $sortStr .= pg_escape_string($sort['property']) . ' ' . pg_escape_string($sort['direction']);
    }
}

// Prepare the SQL query string.
// I use aggregate funtions there
// @TODO - this code should be implemented as Repository pattern
$query = "select " . implode(', ',$queriedFields) .
" from (
    select user_ip, json_agg(url_from) as urls_from, json_agg(url_to) as urls_to
    from (
        select ul.ip as user_ip, ul.created_at, ul.url_from, ul.url_to
        from url_log as ul
        " . $whereStr . "
        order by ul.created_at DESC
    ) as logs
    group by user_ip
) as log
join browsers_log as bl on (bl.ip = user_ip)
" . $sortStr;

// Get the logs.
$result = pg_query($dbconnect, $query);
$result = pg_fetch_all($result);

$items = [];
// Prepare the response to the ExtJS Grid
// @TODO - this code should be implemented as independent Transformer object for Controller
foreach ($result as $row) {
    $item['user_ip'] = $row['user_ip'];
    $item['browser'] = $row['browser'];
    $item['os'] = $row['os'];
    $row['urls_from'] = json_decode($row['urls_from'], true);
    $row['urls_to'] = json_decode($row['urls_to'], true);
    // Not the best solution : the Log may contain a lot of URLs by robot
    // need to solve this issue by PostgreSql .. may be View
    $urls_count = count(array_unique($row['urls_to']));
    $item['url_from'] = array_shift($row['urls_from']);
    $item['url_to'] = array_pop($row['urls_to']);
    $item['urls_count'] = $urls_count;
    $items[] = $item;
}
pg_close($dbconnect);

header('Content-Type: application/json');
echo json_encode([ 'items' => $items]);