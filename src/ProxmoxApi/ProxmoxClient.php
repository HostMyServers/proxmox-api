<?php

namespace ProxmoxApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class ProxmoxClient
 * @package ProxmoxApi
 */
class ProxmoxClient
{
    use ProxmoxMethodsTrait;

    // Constantes plus lisibles et corrigées (MENTHOD -> METHOD)
    private const REQUEST_METHODS = [
        'DELETE' => 'DELETE',
        'POST'   => 'POST',
        'PUT'    => 'PUT',
        'GET'    => 'GET'
    ];

    // Default configuration
    private const DEFAULT_CONFIG = [
        'realm'     => 'pam',
        'sslverify' => true,
        'useproxy'  => '',
        'proxyauth' => '',
        'timeout'   => 4.0  // Global timeout in seconds
    ];

    // Regrouper les propriétés par type/usage
    private Client $client;

    // Authentification
    private string $username = '';
    private string $ticket = '';
    private string $CSRFPreventionToken = '';

    // Configuration
    private string $host = '';
    private bool $sslverify = true;
    private string $useproxy = '';
    private string $proxyauth = '';
    private float $timeout;

    /**
     * ProxmoxApi constructor.
     * @param $host
     * @param $user
     * @param $password
     * @param string $realm
     * @param bool $sslverify
     * @param string $useproxy
     * @param string $proxyauth Format: 'username:password'
     * @param float $timeout Global timeout in seconds
     * @throws ProxmoxApiException
     */
    public function __construct(
        string $host,
        string $user,
        string $password,
        string $realm = self::DEFAULT_CONFIG['realm'],
        bool $sslverify = self::DEFAULT_CONFIG['sslverify'],
        string $useproxy = self::DEFAULT_CONFIG['useproxy'],
        string $proxyauth = self::DEFAULT_CONFIG['proxyauth'],
        float $timeout = self::DEFAULT_CONFIG['timeout']
    ) {
        $this->initializeConfiguration($host, $sslverify, $useproxy, $proxyauth, $timeout);
        $this->initializeClient();
        $this->authenticate($user, $password, $realm);
    }

    private function initializeConfiguration(
        string $host,
        bool $sslverify,
        string $useproxy,
        string $proxyauth,
        float $timeout
    ): void {
        $this->host = $host;
        $this->sslverify = $sslverify;
        $this->useproxy = $useproxy;
        $this->proxyauth = $proxyauth;
        $this->timeout = $timeout;
    }

    private function initializeClient(): void
    {
        $this->client = new Client([
            'base_uri' => "https://{$this->host}/api2/json/",
            'verify' => $this->sslverify,
            'proxy' => $this->useproxy ?: null,
            'proxy_auth' => $this->proxyauth ?: null,
            'timeout' => $this->timeout,         // Response timeout
            'connect_timeout' => $this->timeout  // Connection timeout
        ]);
    }

    private function authenticate(string $user, string $password, string $realm): void
    {
        $resp = $this->create('/access/ticket', [
            'username' => $user,
            'password' => $password,
            'realm'    => $realm
        ]);

        $this->username = $resp->username;
        $this->ticket = $resp->ticket;
        $this->CSRFPreventionToken = $resp->CSRFPreventionToken;
    }

    /**
     * @return ProxmoxClient
     */
    protected function client(): ProxmoxClient
    {
        return $this;
    }

    /**
     * @return string
     */
    protected function path(): string
    {
        return '';
    }

    /**
     * @param string $name
     * @return ProxmoxNode
     */
    public function node($name)
    {
        return new ProxmoxNode($this, $name);
    }

    /**
     * @param string $method
     * @param string $action
     * @param array $params
     * @return mixed
     * @throws ProxmoxApiException
     */
    public function request(string $method, string $action, array $params = []): mixed
    {
        $options = $this->prepareRequestOptions($method, $params);

        try {
            $response = $this->client->request(
                $method,
                ltrim($action, '/'),
                $options
            );

            return $this->parseResponse($response);
        } catch (GuzzleException $e) {
            if (strpos($e->getMessage(), 'timed out') !== false) {
                throw new ProxmoxApiException("Proxmox API request timed out after 5 seconds", 408);
            }
            throw new ProxmoxApiException($e->getMessage(), $e->getCode());
        }
    }

    private function prepareRequestOptions(string $method, array $params): array
    {
        $options = [
            'headers' => $this->getHeaders(),
        ];

        $method = strtoupper($method);
        if (in_array($method, [self::REQUEST_METHODS['POST'], self::REQUEST_METHODS['PUT']])) {
            $options['form_params'] = $params;
        } elseif ($method === self::REQUEST_METHODS['GET']) {
            $options['query'] = $params;
        }

        return $options;
    }

    private function getHeaders(): array
    {
        $headers = [];

        if ($this->ticket) {
            $headers['Cookie'] = "PVEAuthCookie={$this->ticket}";
        }

        if ($this->CSRFPreventionToken) {
            $headers['CSRFPreventionToken'] = $this->CSRFPreventionToken;
        }

        return $headers;
    }

    private function parseResponse($response): mixed
    {
        $body = $response->getBody()->getContents();
        $data = json_decode($body);
        return $data->data;
    }
}
