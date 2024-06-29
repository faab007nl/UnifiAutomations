<?php

namespace Fabian\CloudflareUnifiIpImport\models;

use Fabian\CloudflareUnifiIpImport\enums\FirewallGroupType;

class UnifiFirewallGroup
{

    public string $id;
    public string $name;
    public FirewallGroupType $type;
    public array $group_members;

    public function __construct(string $id, string $name, string $type, array $group_members)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = FirewallGroupType::from($type);
        $this->group_members = $group_members;
    }

}