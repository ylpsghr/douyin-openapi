<?php


namespace Peimengc\DouyinOpenapi\Tests;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Peimengc\DouyinOpenapi\Api;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    protected function getApi()
    {
        $key = 'key';
        $sercet = 'secret';
        return new Api($key, $sercet);
    }

    public function testGetHttpClient()
    {
        $this->assertInstanceOf(Client::class, $this->getApi()->getHttpClient());
    }

    public function testGetGuzzleOptions()
    {
        $this->assertIsArray($this->getApi()->getGuzzleOptions());
    }

    public function testSetGuzzleOptions()
    {
        $api = $this->getApi();
        $this->assertNull($api->getHttpClient()->getConfig('timeout'));
        $api->setGuzzleOptions(['timeout' => 10]);
        $this->assertSame(10, $api->getHttpClient()->getConfig('timeout'));
    }

    // 获取授权地址
    public function testGetAuthUrl()
    {
        $redirectUri = 'https://www.baidu.com';
        $state = 'test';
        $scope = 'scope';
        $url = $this->getApi()->getAuthUrl($redirectUri, $scope, $state);
        parse_str($url, $query);
        $this->assertEquals($state, $query['state']);
        $this->assertEquals($scope, $query['scope']);
        $this->assertEquals($redirectUri, $query['redirect_uri']);
    }

    // 获取access_token
    public function testGetAccessToken()
    {
        $key = 'key';
        $secret = 'secret';
        $code = 'code';
        $response = new Response(200, [], '{"data": {"error_code": 0}}');
        $client = \Mockery::mock(Client::class);
        $client->allows()
            ->post('/oauth/access_token/', [
                'form_params' => [
                    'client_secret' => $secret,
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'client_key' => $key,
                ]
            ])->andReturn($response);
        $api = \Mockery::mock(Api::class, [$key, $secret])->makePartial();
        $api->allows()->getHttpClient()->andReturn($client);
        $this->assertSame(['data' => ['error_code' => 0]], $api->getAccessToken($code));
    }

    // 获取access_token
    public function testRefreshToken()
    {
        $key = 'key';
        $secret = 'secret';
        $refreshToken = 'refresh_token';
        $response = new Response(200, [], '{"data": {"error_code": 0}}');
        $client = \Mockery::mock(Client::class);
        $client->allows()
            ->post('/oauth/renew_refresh_token/', [
                'form_params' => [
                    'refresh_token' => $refreshToken,
                    'client_key' => $key,
                ]
            ])->andReturn($response);
        $api = \Mockery::mock(Api::class, [$key, $secret])->makePartial();
        $api->allows()->getHttpClient()->andReturn($client);
        $this->assertSame(['data' => ['error_code' => 0]], $api->refreshToken($refreshToken));
    }

    // 获取用户公开信息
    public function testGetUser()
    {
        $key = 'key';
        $secret = 'secret';
        $openId = 'open_id';
        $accessToken = 'access_token';
        $response = new Response(200, [], '{"data": {"error_code": 0}}');
        $client = \Mockery::mock(Client::class);
        $client->allows()
            ->get('/oauth/userinfo/', [
                'query' => [
                    'open_id' => $openId,
                    'access_token' => $accessToken,
                ]
            ])->andReturn($response);
        $api = \Mockery::mock(Api::class, [$key, $secret])->makePartial();
        $api->allows()->getHttpClient()->andReturn($client);
        $this->assertSame(['data' => ['error_code' => 0]], $api->setOpenId($openId)->setAccessToken($accessToken)->getUser());
    }

}