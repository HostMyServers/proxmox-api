<?php

namespace ProxmoxApi;


/**
 * Class ProxmoxNode
 * @package ProxmoxApi
 */
class ProxmoxNode
{
    use ProxmoxMethodsTrait;

    /**
     * @var ProxmoxClient
     */
    protected ProxmoxClient $client;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var \stdClass|null
     */
    private ?\stdClass $config = null;

    /**
     * ProxmoxNode constructor.
     * @param ProxmoxClient $client
     * @param string $name
     */
    public function __construct(ProxmoxClient $client, $name)
    {
        $this->client = $client;
        $this->name = $name;
    }

    /**
     * @param int $vmid
     * @return ProxmoxVM
     */
    public function vm($vmid)
    {
        return new ProxmoxVM($this, $vmid);
    }

    /**
     * @return ProxmoxClient
     */
    protected function client(): ProxmoxClient
    {
        return $this->client;
    }

    /**
     * @return string
     */
    protected function path(): string
    {
        return "/nodes/{$this->name}";
    }

    /**
     * @return \stdClass
     * @throws ProxmoxApiException
     */
    public function config(): \stdClass
    {
        if ($this->config === null) {
            $this->config = $this->get('config');
        }
        return $this->config;
    }
}
