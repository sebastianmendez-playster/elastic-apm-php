<?php

namespace PhilKra\Serializers;

use PhilKra\Exception\Serializers\UnsupportedApmVersionException;
use PhilKra\Stores\ErrorsStore;
use PhilKra\Helper\Config;

/**
 *
 * Convert the Registered Errors to JSON Schema
 *
 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
 *
 */
class Errors extends Entity implements \JsonSerializable
{
    /**
     * @var \PhilKra\Stores\ErrorsStore
     */
    private $store;

    /**
     * @param ErrorsStore $store
     */
    public function __construct(Config $config, ErrorsStore $store)
    {
        parent::__construct($config);
        $this->store = $store;
    }

    /**
     * Serialize Error Data to JSON "ready" Array
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if($this->config->useVersion1()){
                    return $this->getSkeleton() + [
            'errors' => $this->store
        ];
        }

        if ($this->config->useVersion2()) {
            return $this->makeVersion2Json();
        }

        throw new UnsupportedApmVersionException($this->config->apmVersion());
    }

    private function makeVersion2Json(): array
    {
        if ($this->store->isEmpty()) {
            return $this->getSkeleton();
        }

        $transactionData = json_decode(json_encode($this->store), true);

        $encodedTransactions =  json_encode($this->getSkeletonV2()).PHP_EOL;

        foreach ($transactionData as $transaction) {
            $encodedTransactions .= json_encode(['error' => $transaction]).PHP_EOL;
        }

        return [$encodedTransactions];
    }
}
