<?php

namespace mhs\tools\http;

class Response
{
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    private $response;
    /**
     * @var string $error
     */
    private $error = '';

    /**
     * @return array
     */
    public function jsonContent()
    {
        if ($this->error) {
            return false;
        }
        $body = $this->response->getBody();
        $content = $body->getContents();
        $content = json_decode($content, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            $this->error = 'api response data parse error: ' . json_last_error_msg();
            return false;
        }

        return $content;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(): \Psr\Http\Message\ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function setResponse(\Psr\Http\Message\ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return empty($this->error);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->response, $name], $arguments);
    }
}