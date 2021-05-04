<?php
/**
 * Ленивое API c файловым кэширование результата
 */

$hashRequest = md5(implode('', $_GET));

try {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/autoload.php');

    $cacheId = implode('', $_GET);


    // Try to get cache
    $cache = new FileCache();
    $result = $user = $cache->get($cacheId);
    if ($result) {
        echo $result;
        die();
    }

    $result = array ('status' => true);

    //Get cbr data
    $cbr = new CBRClient();

    if ($_GET['action'] === 'getLastDate') {
        $result['data'] = $cbr->getLatestDate();

    } else if ($_GET['action'] === 'getCurrencies') {
        $result['data'] = $cbr->getEnumDayValutes();

    } else if ($_GET['action'] === 'getExchangeRate') {
        $result['data'] = $cbr->getExchangeRate($_GET['date'], $_GET['code'], $_GET['baseCode']);
        $result['params'] = $_GET;
        if ($result['data'] === false || count($result['data']) < 2)
            throw new Exception('Нет данных при заданных параметрах', 404);

    } else {
        throw new Exception('Неправильный метод или action', 405);
    }


} catch (Throwable $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    $result = array (
        'status' => false,
        'error' => $e->getMessage(),
    );
}

$result = json_encode($result, JSON_UNESCAPED_UNICODE);

$lifetime = 3600;
if ($cache) $cache->save($cacheId, $result, $lifetime);

echo $result;
die();