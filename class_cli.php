<?php

echo "Пожалуйста, введите название города на английском языке для получения текущей погоды: ";
$inputValue = trim(fgets(STDIN)); // Читаем ввод с консоли


while (empty($inputValue)) {
    echo "Значение не может быть пустым. Пожалуйста, введите значение: ";
    $inputValue = trim(fgets(STDIN)); // Повторно читаем ввод
 
}


$req_city = $inputValue;
$open_weather_key = '902d68ad3cbdbb0db182d3af3a6f159c';
$open_weather_url = "http://api.openweathermap.org/geo/1.0/direct?&q=$req_city&appid=$open_weather_key";
$yandex_weather_key = '3f0246c8-35a9-42a0-bef1-4cf81a7877f0';
$yandex_weather_url = 'https://api.weather.yandex.ru/v2/forecast?';
$lat = null;
$lon = null;
$coordinates = null;


class APIClient {
    private $accessKey;
    private $baseUrl;

    public function __construct($key, $url) {
        $this->accessKey = $key;
        $this->baseUrl = $url;
    }

    public function getData($coordinates) {
       
        $url = $this->baseUrl.$coordinates;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Yandex-Weather-Key: {$this->accessKey}"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Отключение проверки сертификатов (не рекомендуется для продакшена)

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        $data = json_decode($response,true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON decode error: ' . json_last_error_msg());
        }

        return $data;
        
    }
}

    try {
        $key = null;
        $url = $open_weather_url;
        $apiClient = new APIClient($key, $url); 
        $data = $apiClient->getData($coordinates);
        
        if (isset($data)) {
            $city = $data[0]["name"];
             $lat = $data[0]['lat'];
             $lon = $data[0]['lon'];
             $coordinates ="lat={$lat}&lon={$lon}";
             echo $coordinates;
        } else {
            echo 'нет такого города';
        }
    } catch (Exception $e) {
        echo 'Ошибка: ' . $e->getMessage();
    }
    try {
        $key = $yandex_weather_key;
        $url = $yandex_weather_url;
        $apiWhether = new APIClient($key, $url); 
        $wether = $apiWhether->getData($coordinates);
        
        if (isset($wether)) {
            preg_match('/\/(.+)/', $wether['info']['tzinfo']['name'],$matches);
            $city = $matches[1];
            $temperature = $wether['fact']['temp'];
            $wind = $wether['fact']['wind_speed'];
            $pressure = $wether['fact']['pressure_mm'];
            echo "Today in {$city}: \n-temperature is {$temperature} degree\n-wind is {$wind} m/s\n-pressure is {$pressure} mm";
            
           
        } else {
            echo 'нет такого города';
        }
    } catch (Exception $e) {
        echo 'Ошибка: ' . $e->getMessage();
    }