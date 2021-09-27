<?php

namespace DevOceanLT\CamundaQueue;

use DevOceanLT\CamundaQueue\CamundaQueue;
use Illuminate\Queue\Connectors\ConnectorInterface;

class CamundaConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return CamundaQueue
     */
    public function connect(array $config)
    {
        return new CamundaQueue;
    }
}