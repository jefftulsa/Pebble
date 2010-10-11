<?php
class Twitter extends Pebble_Dispatcher
{
    protected $_twitterApiBaseUrl = 'http://api.twitter.com/1/';
   
    public function ___timeline()
    {
        $url = 'statuses/public_timeline.json';
        $fullUrl = $this->_twitterApiBaseUrl . $url;
        $status = file_get_contents($fullUrl);
        $decoded = json_decode($status, true);
        foreach ($decoded as $tweet) {
            echo '<p>' . $decoded['text'] . '</p>';
        }
    }
    
    public function ___trigger_error()
    {
        throw new Exception('Test Exception Message', 9999);
    }
}