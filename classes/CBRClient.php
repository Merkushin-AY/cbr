<?php
/**
 * Class CBRClient
 *
 * Класс для получения данных с cbr.ru на основе SOAP
 * Чтобы работало кэширование SOAP, следует установить соответствующие настройки в php.ini
 */

class CBRClient
{

    private $client;
    private $apiUrl = 'http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL';


    public function __construct () {
        $this->client = new SoapClient($this->apiUrl);
    }


    /**
     * Получить данные о курсе рубля за день и за предыдущий день.
     * Если не хватает данных, то возвращается false.
     *
     * @param string $date
     * @param string $code
     * @return array|false
     */
    public function getExchangeDayDynamic (string $date, string $code)
    {
        $fromDate = date('c', strtotime($date . " - 1 day"));
        $toDate = date('c', strtotime($date));

        $response = $this->client->GetCursDynamic(array(
            'FromDate' => $fromDate,
            'ToDate' => $toDate,
            'ValutaCode' => $code,
        ));

        $response = simplexml_load_string($response->GetCursDynamicResult->any);

        if (!$response) return false;

        $result = array();

        foreach ($response->ValuteData->ValuteCursDynamic as $rate) {
            $arRate = (array)$rate;
            $nom = $arRate['Vnom'] ?: 1;
            if ($nom > 1) $arRate['Vcurs'] = $arRate['Vcurs'] / $arRate['Vnom'];
            $result[] = $arRate;
        }

        return $result;
    }


    /**
     * @param string $date
     * @param string $code
     * @param string $baseCode
     * @return array|false
     *
     * Получить данные ою обменном курсе валют за день и за предыдущий день
     *
     */
    public function getExchangeRate (string $date, string $code, string $baseCode = '')
    {
        $result = $this->getExchangeDayDynamic($date, $code);

        if (!$baseCode) return $result;
        if (!$result || count($result) < 2) return false;

        if ($code == $baseCode) {
            $result2 = $result;
        } else {
            $result2 = $this->getExchangeDayDynamic($date, $baseCode);
        }

        if (!$result2 || count($result2) < 2) return false;

        $result[0]['Vcurs'] = $result[0]['Vcurs'] / $result2[0]['Vcurs'];
        $result[1]['Vcurs'] = $result[1]['Vcurs'] / $result2[1]['Vcurs'];

        return $result;
    }


    /**
     * Получить список валют
     * @return array
     */
    public function getEnumDayValutes (): array
    {
        $response = $this->client->EnumValutes(array('Seld' => false));
        $response = simplexml_load_string($response->EnumValutesResult->any);

        $result = array();

        foreach ($response->ValuteData->EnumValutes as $currency) {
            $result[] = (array)$currency;
        }

        return $result;
    }

    /**
     * Получить последнюю дату, для которой есть данные в cbr.ru
     * @return string
     */
    public function getLatestDate(): string
    {
        $response = $this->client->GetLatestDate();
        return date('Y-m-d', strtotime($response->GetLatestDateResult));
    }
}