<?php

namespace mhs\tools\http;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class Client
{
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var string $error
     */
    protected $error = '';
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;
    /**
     * @var array $options
     */
    private $options = [
        'timeout' => '10', // 超时时间
        'allow_redirects' => true, // 是否支持重定向
    ];
    /**
     * @var string $method
     */
    private $method = 'GET';
    /**
     * @var array $data
     */
    private $data = [];
    /**
     * @var array $header
     */
    private $header = [];
    /**
     * @var array $cookie
     */
    private $cookie = [];
    /** @var string $cookieDomain */
    private $cookieDomain = '';
    /**
     * @var array $files
     */
    private $files = [];
    /**
     * 是否异步请求
     * @var bool $async
     */
    private $async = false;
    /**
     * @var string $dataType
     */
    private $dataType = 'json';
    /**
     * @var string $baseUri
     */
    private $baseUri = '';
    /**
     * @var string $uri
     */
    private $uri = '';

    public function __construct($options = [])
    {
        $this->setOptions($options);
        $client = new \GuzzleHttp\Client($options);
        $this->setClient($client);
        $this->response = new Response();
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = array_replace_recursive($this->options, $options);
        $this->setBaseUri($options['base_uri'] ?? $this->baseUri);
        unset($options['base_uri']);

        return $this;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient(): \GuzzleHttp\Client
    {
        return $this->client;
    }

    /**
     * @param \GuzzleHttp\Client $client
     * @return self
     */
    public function setClient(\GuzzleHttp\Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->method = mb_strtoupper($method);

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return self
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @param array $header
     * @return self
     */
    public function setHeader(array $header): self
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return array
     */
    public function getCookie(): array
    {
        return $this->cookie;
    }

    /**
     * @param array $cookie
     * @return self
     */
    public function setCookie(array $cookie): self
    {
        $this->cookie = $cookie;

        return $this;
    }

    /**
     * @param string $cookieDomain
     * @return self
     */
    public function setCookieDomain(string $cookieDomain): self
    {
        $this->cookieDomain = $cookieDomain;

        return $this;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param array $files
     * @return self
     */
    public function setFiles(array $files): self
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAsync(): bool
    {
        return $this->async;
    }

    /**
     * @param bool $async
     * @return self
     */
    public function setAsync(bool $async): self
    {
        $this->async = $async;

        return $this;
    }

    /**
     * @param string $url
     * @return self
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @param string $baseUri
     * @return self
     */
    public function setBaseUri(string $baseUri): self
    {
        $this->baseUri = $baseUri;

        return $this;
    }

    /**
     * @param string $uri
     * @return self
     */
    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType
     * @return self
     */
    public function setDataType(string $dataType): self
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * @return Response
     */
    public function send()
    {
        $url = $this->getUrl();
        $options = $this->getRequestOptions();
        try {
            $response = $this->client->request($this->method, $url, $options);
            $this->response->setResponse($response);
        } catch (GuzzleException $e) {
            $this->response->setError($e->getMessage());
        } catch (\Exception $e) {
            $this->response->setError($e->getMessage());
        }

        return $this->response;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if (stripos($this->uri, 'http') === 0) {
            return $this->uri;
        }

        return rtrim($this->baseUri, '/') . '/' . ltrim($this->uri, '/');
    }

    protected function getRequestOptions()
    {
        $options = $this->options;
        if ($this->header) {
            $options['headers'] = array_merge($this->options['headers'] ?? [], $this->header);
        }
        if ($this->cookie) {
            $cookie = CookieJar::fromArray($this->cookie, $this->cookieDomain ?: '/');
            $options['cookies'] = $cookie;
        }
        if ($this->files) {
            $this->setMethod('POST')->setDataType(RequestOptions::MULTIPART);
        }
        if ($this->data || $this->files) {
            $options[$this->dataType] = $this->getRequestData();
        }

        return $options;
    }

    protected function getRequestData()
    {
        if ($this->dataType != RequestOptions::MULTIPART) {
            return $this->data;
        }
        $data = $this->buildPostData($this->data);

        return $data;
    }

    protected function buildPostData($data, $prefix = '')
    {
        $params = [];
        foreach ($data as $key => $datum) {
            $name = $prefix ? "{$prefix}[{$key}]" : $key;
            if (is_array($datum)) {
                $tmp = $this->buildPostData($datum, $name);
                $params = array_merge($params, $tmp);
            } elseif ($datum instanceof \CURLFile) {
                $params[] = [
                    'name' => $name,
                    'contents' => fopen($datum->getFilename(), 'rb'),
                ];
            } else {
                $params[] = [
                    'name' => $name,
                    'contents' => $datum
                ];
            }
        }

        return $params;
    }

    /**
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function sendAsync()
    {
        $url = $this->getUrl();
        $options = $this->getRequestOptions();
        return $this->client->requestAsync($this->method, $url, $options);
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}