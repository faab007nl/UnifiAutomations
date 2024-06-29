<?php

namespace Fabian\CloudflareUnifiIpImport\enums;

enum FirewallGroupType: string
{
    case IP_V4_ADDRESS_GROUP = 'address-group';
    case IP_V6_ADDRESS_GROUP = 'ipv6-address-group';
    case PORT_GROUP = 'port-group';
}