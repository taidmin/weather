<?php
namespace Taidmin\Weather;


use GuzzleHttp\Client;
use Taidmin\Weather\Exceptions\HttpException;
use Taidmin\Weather\Exceptions\InvalidArgumentException;

class Weather
{
    protected $key;
    protected $guzzleOptions = [];

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    // 获取天气
    public function getWeather($city, $type = 'live', $format = 'json')
    {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        $types = [
            'live' => 'base',
            'forecast' => 'all',
        ];

        // 1、对 $format 与 $type 参数进行检查，不在范围内抛出异常

        if(!in_array(strtolower($format),['json', 'xml'])){
            throw new InvalidArgumentException('Invalid response format:' . $format);
        }

        if(!in_array(strtolower($type), $types)){
            throw new InvalidArgumentException('Invalid type value(live/forecast):' . $type);
        }

        // 2、封装 query 参数，并对空值进行过滤
        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => $format,
            'extensions' => $type
        ]);

        try{
            // 3、调用 getHttpClient 获取实例，并调用该实例的 get 方法
            // 传递参数为两个: $url, ['query' => $query]
            $response = $this->getHttpClient()->get($url,[
                'query' => $query,
            ])->getBody()->getContents();


            // 4、返回值根据 $format 返回不同的格式
            // 当 $format 为 json 时，返回数组格式，否则返回 xml 格式
            return 'json' === $format ? json_decode($response, true) : $response;
        }catch (\Exception $e){
            // 5、当调用出现异常时捕获并抛出，消息为捕获到的异常消息
            // 并将调用异常作为 $previousException 传入
            throw new HttpException($e->getMessage(),$e->getCode(),$e);
        }


    }

    // 获取实时天气
    public function getLiveWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'base', $format);
    }

    // 获取天气预报
    public function getForecastsWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'all', $format);
    }
}



















