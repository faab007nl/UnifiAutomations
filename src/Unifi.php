<?php

namespace Fabian\CloudflareUnifiIpImport;

use Exception;
use Fabian\CloudflareUnifiIpImport\enums\FirewallGroupType;
use Fabian\CloudflareUnifiIpImport\models\UnifiFirewallGroup;
use Fabian\CloudflareUnifiIpImport\models\UnifiNetwork;
use UniFi_API\Client;

class Unifi
{

    private string $siteId;

    private Client $unifiConnection;

    private array $firewallGroups;
    private array $networks;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        // Verify env vars
        if (!isset($_ENV['UNIFI_USER']) || !isset($_ENV['UNIFI_PASSWORD']) || !isset($_ENV['UNIFI_URL']) || !isset($_ENV['UNIFI_SITE_ID']) || !isset($_ENV['UNIFI_CONTROLLER_VERSION'])) {
            throw new Exception('Please set the UNIFI_USER, UNIFI_PASSWORD, UNIFI_URL, UNIFI_SITE_ID and UNIFI_CONTROLLER_VERSION environment variables.');
        }

        $username = trim($_ENV['UNIFI_USER']);
        $password = trim($_ENV['UNIFI_PASSWORD']);
        $url = trim($_ENV['UNIFI_URL']);
        $this->siteId = trim($_ENV['UNIFI_SITE_ID']);
        $controllerVersion = trim($_ENV['UNIFI_CONTROLLER_VERSION']);

        $this->unifiConnection = new Client(
            $username,
            $password,
            $url,
            $this->siteId,
            $controllerVersion,
            false
        );
        $this->unifiConnection->login();

        $this->fetchFirewallGroups();
        $this->fetchNetworks();
    }

    protected function fetchFirewallGroups(): void
    {
        $firewallGroups = $this->unifiConnection->list_firewallgroups();
        foreach ($firewallGroups as $firewallGroup) {
            $this->firewallGroups[] = new UnifiFirewallGroup(
                $firewallGroup->_id,
                $firewallGroup->name,
                $firewallGroup->group_type,
                $firewallGroup->group_members
            );
        }
    }

    public function firewallGroupExists(string $name): bool
    {
        foreach ($this->firewallGroups as $firewallGroup) {
            if ($firewallGroup->name === $name) {
                return true;
            }
        }
        return false;
    }

    public function findFirewallGroupByName(string $name): UnifiFirewallGroup|null
    {
        foreach ($this->firewallGroups as $firewallGroup) {
            if ($firewallGroup->name === $name) {
                return $firewallGroup;
            }
        }
        return null;
    }

    public function createFirewallGroup(string $name, FirewallGroupType $type, array $ips): bool
    {
        $group = $this->unifiConnection->create_firewallgroup(
            $name,
            $type->value,
            $ips
        );
        if ($group) {
            $this->fetchFirewallGroups();
        }
        return !empty($group);
    }

    public function updateFirewallGroup(string $id, string $name, FirewallGroupType $type, array $ips): bool
    {
        $group = $this->unifiConnection->edit_firewallgroup(
            $id,
            $this->siteId,
            $name,
            $type->value,
            $ips
        );
        if ($group) {
            $this->fetchFirewallGroups();
        }
        return !empty($group);
    }

    public function fetchNetworks(): void
    {
        $networks = $this->unifiConnection->list_networkconf();
        foreach ($networks as $network) {
            $this->networks[] = new UnifiNetwork(
                $network->_id,
                $network->name,
                $network->dhcpd_dns_1 ?? "",
                $network->dhcpd_dns_2 ?? "",
                $network->dhcpd_dns_3 ?? "",
                $network->dhcpd_dns_4 ?? ""
            );
        }
    }

    public function findNetworkByName(string $name): UnifiNetwork|null
    {
        foreach ($this->networks as $network) {
            if ($network->name === $name) {
                return $network;
            }
        }
        return null;
    }

    public function updateNetworkDns(
        string $networkId,
        string|null $dns1,
        string|null $dns2,
        string|null $dns3,
        string|null $dns4
    ): void
    {
        $data = $this->unifiConnection->set_networksettings_base(
            $networkId,
            [
                'dhcpd_dns_1' => $dns1 ?? "",
                'dhcpd_dns_2' => $dns2 ?? "",
                'dhcpd_dns_3' => $dns3 ?? "",
                'dhcpd_dns_4' => $dns4 ?? ""
            ]
        );
        echo json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
    }

}