<?php
function getWeather($city) {
    // Set timezone to Pakistan timezone
    date_default_timezone_set('Asia/Karachi');
      $apiKey = "47c052966fd54808a0975417251306"; // Your WeatherAPI.com API key
    $url = "http://api.weatherapi.com/v1/current.json?key=" . $apiKey . "&q=" . urlencode($city) . "&aqi=no";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
        curl_close($ch);
        return array('error' => 'Failed to fetch weather data');
    }
    
    curl_close($ch);
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return array('error' => 'Invalid response from weather API');
    }
    
    return $data;
}
?>
