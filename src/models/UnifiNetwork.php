<?php

namespace Fabian\CloudflareUnifiIpImport\models;

class UnifiNetwork
{

    public string $id;
    public string $name;
    public string|null $dns_1;
    public string|null $dns_2;
    public string|null $dns_3;
    public string|null $dns_4;

    public function __construct(string $id, string $name, string|null $dns_1, string|null $dns_2, string|null $dns_3, string|null $dns_4)
    {
        $this->id = $id;
        $this->name = $name;
        $this->dns_1 = $dns_1;
        $this->dns_2 = $dns_2;
        $this->dns_3 = $dns_3;
        $this->dns_4 = $dns_4;
    }

}