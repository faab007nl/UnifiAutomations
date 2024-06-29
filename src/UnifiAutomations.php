<?php

namespace Fabian\CloudflareUnifiIpImport;

use Dotenv\Dotenv;
use Exception;
use Fabian\CloudflareUnifiIpImport\enums\FirewallGroupType;

class UnifiAutomations
{

    private Scheduler $scheduler;
    private CloudFlare $cloudflare;
    private Unifi $unifi;
    private PiHole $piHole;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        // Check if the .env file exists
        if (!file_exists(ROOT_DIR . '/.env')) {
            throw new Exception('Please create a .env file in the root directory.');
        }

        try{
            // Load environment variables
            $dotenv = Dotenv::createImmutable(ROOT_DIR);
            $dotenv->load();

            // Initialize the classes
            $this->scheduler = new Scheduler();
            $this->cloudflare = new CloudFlare();
            $this->unifi = new Unifi();
            $this->piHole = new PiHole();

            // Update the Cloudflare firewall groups
            $this->scheduler->registerTask('updateCloudflareFirewallGroups', 3600, function () {
                $this->updateCloudflareFirewallGroups();
            });
            // Update the DNS records
            $this->scheduler->registerTask('verifyPiHoleStatus', 1, function () {
                $this->verifyPiHoleStatus();
            });

            $this->scheduler->run();

            echo "success";
        }catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @return void
     *
     * Update the Cloudflare firewall groups with the latest Cloudflare IP addresses
     */
    private function updateCloudflareFirewallGroups(): void
    {
        $cloudflareIps = $this->cloudflare->fetchIps();

        $ipv4GroupName = trim($_ENV['IPV4_GROUP_NAME']);
        $ipv6GroupName = trim($_ENV['IPV6_GROUP_NAME']);

        $ipv4GroupExists = $this->unifi->firewallGroupExists($ipv4GroupName);
        if($ipv4GroupExists) {
            $group = $this->unifi->findFirewallGroupByName($ipv4GroupName);
            $this->unifi->updateFirewallGroup(
                $group->id,
                $group->name,
                $group->type,
                $cloudflareIps->ipV4Addresses
            );
        }else{
            $this->unifi->createFirewallGroup(
                $ipv4GroupName,
                FirewallGroupType::IP_V4_ADDRESS_GROUP,
                $cloudflareIps->ipV4Addresses
            );
        }

        $ipv6GroupExists = $this->unifi->firewallGroupExists($ipv6GroupName);
        if($ipv6GroupExists) {
            $group = $this->unifi->findFirewallGroupByName($ipv6GroupName);
            $this->unifi->updateFirewallGroup(
                $group->id,
                $group->name,
                $group->type,
                $cloudflareIps->ipV6Addresses
            );
        }else{
            $this->unifi->createFirewallGroup(
                $ipv6GroupName,
                FirewallGroupType::IP_V6_ADDRESS_GROUP,
                $cloudflareIps->ipV6Addresses
            );
        }
    }

    /**
     * @return void
     *
     * Check if PiHole is online
     * If online set the dns records to the PiHole
     * If offline set the dns records to cloudflare
     *
     * @throws Exception
     */
    private function verifyPiHoleStatus(): void
    {
        $piHoleOnline = $this->piHole->isOnline();
        $networksToUpdate = explode(',', $_ENV['UNIFI_NETWORKS_TO_UPDATE']);
        $piholeOnlineIps = explode(',', $_ENV['PIHOLE_ONLINE_DNS_IPS']);
        $piholeOfflineIps = explode(',', $_ENV['PIHOLE_OFFLINE_DNS_IPS']);

        $newIps = $piHoleOnline ? $piholeOnlineIps : $piholeOfflineIps;

        // Set the DNS records to the PiHole
        foreach ($networksToUpdate as $networkName) {
            $network = $this->unifi->findNetworkByName($networkName);
            if ($network === null) {
                throw new Exception('Network not found: ' . $networkName);
            }

            $this->unifi->updateNetworkDns(
                $network->id,
                $newIps[0] ?? "",
                $newIps[1] ?? "",
                $newIps[2] ?? "",
                $newIps[3] ?? ""
            );
        }

    }

}