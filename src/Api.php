<?php


namespace Peimengc\DouyinOpenapi;


use GuzzleHttp\Client;

class Api
{
    protected $key;
    protected $secret;
    protected $guzzleOptions = [];
    protected $baseUri = 'https://open.douyin.com';
    protected $openId;
    protected $accessToken;

    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    public function getHttpClient()
    {
        return new Client($this->getGuzzleOptions());
    }

    public function getGuzzleOptions()
    {
        return array_merge([
            'base_uri' => $this->baseUri,
        ], $this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
        return $this;
    }

    // 设置openId
    public function setOpenId($openId)
    {
        $this->openId = $openId;
        return $this;
    }

    // 设置AccessToken
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    // 获取授权地址
    public function getAuthUrl($redirectUri, $scope, $state = '')
    {
        return $this->baseUri . "/platform/oauth/connect/?" . http_build_query([
                'client_key' => $this->key,
                'response_type' => 'code',
                'scope' => $scope,
                'redirect_uri' => $redirectUri,
                'state' => $state
            ]);
    }

    // 获取access_token
    public function getAccessToken($code)
    {
        $contents = $this->getHttpClient()
            ->post('/oauth/access_token/', [
                'form_params' => [
                    'client_secret' => $this->secret,
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'client_key' => $this->key,
                ]
            ])
            ->getBody()
            ->getContents();

        return json_decode($contents, true);
    }

    // 获取access_token
    public function refreshToken($refreshToken)
    {
        $contents = $this->getHttpClient()
            ->post('/oauth/refresh_token/', [
                'form_params' => [
                    'refresh_token' => $refreshToken,
                    'client_key' => $this->key,
                    'grant_type' => 'refresh_token'
                ]
            ])
            ->getBody()
            ->getContents();

        return json_decode($contents, true);
    }

    // 刷新refresh_token
    public function renewRefreshToken($refreshToken)
    {
        $contents = $this->getHttpClient()
            ->post('/oauth/renew_refresh_token/', [
                'form_params' => [
                    'refresh_token' => $refreshToken,
                    'client_key' => $this->key,
                ]
            ])
            ->getBody()
            ->getContents();

        return json_decode($contents, true);
    }

    // 获取用户公开信息
    public function getUser()
    {
        $contents = $this->getHttpClient()
            ->get('/oauth/userinfo/', [
                'query' => [
                    'open_id' => $this->openId,
                    'access_token' => $this->accessToken,
                ]
            ])
            ->getBody()
            ->getContents();

        return json_decode($contents, true);
    }

    //解密手机号
    public function decryptMobile($encryptedMobild)
    {
        $iv = substr($this->secret, 0, 16);
        return openssl_decrypt($encryptedMobild, 'aes-256-cbc', $this->secret, 0, $iv);
    }
}