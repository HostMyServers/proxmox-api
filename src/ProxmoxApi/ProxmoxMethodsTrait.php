<?php

namespace ProxmoxApi;


/**
 * Trait ProxmoxMethodsTrait
 * @package ProxmoxApi
 */
trait ProxmoxMethodsTrait
{
    /**
     * @return ProxmoxClient
     */
    abstract protected function client(): ProxmoxClient;

    /**
     * @return string
     */
    abstract protected function path(): string;

    /**
     * @param string $action
     * @param array $params
     * @return mixed
     * @throws ProxmoxApiException
     */
    public function get(string $action, array $params = []): mixed
    {
        return $this->client()->request(
            'GET',
            $this->pathNormalize($action),
            $params
        );
    }

    /**
     * @param string $action
     * @param array $params
     * @return mixed
     * @throws ProxmoxApiException
     */
    public function create(string $action, array $params = []): mixed
    {
        return $this->client()->request(
            'POST',
            $this->pathNormalize($action),
            $params
        );
    }

    /**
     * @param string $action
     * @param array $params
     * @return mixed
     * @throws ProxmoxApiException
     */
    public function set(string $action, array $params = []): mixed
    {
        return $this->client()->request(
            'PUT',
            $this->pathNormalize($action),
            $params
        );
    }

    /**
     * @param string $action
     * @return mixed
     * @throws ProxmoxApiException
     */
    public function delete(string $action): mixed
    {
        return $this->client()->request(
            'DELETE',
            $this->pathNormalize($action)
        );
    }

    /**
     * Normalise le chemin de l'action en s'assurant qu'il y a un seul slash entre les segments
     */
    private function pathNormalize(string $action): string
    {
        return rtrim($this->path(), '/') . '/' . ltrim($action, '/');
    }
}
