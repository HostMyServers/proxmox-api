<?php

namespace ProxmoxApi;

/**
 * Class ProxmoxVM
 * @package ProxmoxApi
 */
class ProxmoxVM
{
    use ProxmoxMethodsTrait;

    /**
     * @var ProxmoxNode
     */
    protected ProxmoxNode $node;

    /**
     * @var int
     */
    protected int $id;

    /**
     * @var \stdClass|null
     */
    private ?\stdClass $config = null;

    /**
     * ProxmoxVM constructor.
     * @param ProxmoxNode $node
     * @param int $vmid
     */
    public function __construct(ProxmoxNode $node, int $vmid)
    {
        $this->node = $node;
        $this->id = $vmid;
    }

    /**
     * @return ProxmoxClient
     */
    protected function client(): ProxmoxClient
    {
        return $this->node->client();
    }

    /**
     * @return string
     */
    protected function path(): string
    {
        return "{$this->node->path()}/qemu/{$this->id}";
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
