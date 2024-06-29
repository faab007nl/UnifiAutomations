<?php

namespace Fabian\CloudflareUnifiIpImport\models;

class CloudflareIpResponse
{

    public bool $success = false;
    public array $ipV4Addresses = [];
    public array $ipV6Addresses = [];

    public function __construct(bool $success, array $ipV4Addresses, array $ipV6Addresses)
    {
        $this->success = $success;
        $this->ipV4Addresses = $ipV4Addresses;
        $this->ipV6Addresses = $ipV6Addresses;
    }

    public function toJson(): string
    {
        return json_encode($this);
    }

}