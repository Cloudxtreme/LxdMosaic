<?php

namespace dhope0000\LXDClient\Tools\Hosts;

use dhope0000\LXDClient\Model\Hosts\AddHost as AddHostModel;
use dhope0000\LXDClient\Tools\Hosts\GenerateCert;
use dhope0000\LXDClient\Model\Hosts\GetDetails;
use dhope0000\LXDClient\Tools\Node\Hosts;

class AddHosts
{
    public function __construct(
        AddHostModel $addHost,
        GenerateCert $generateCert,
        GetDetails $getDetails,
        Hosts $hosts
    ) {
        $this->generateCert = $generateCert;
        $this->addHost = $addHost;
        $this->getDetails = $getDetails;
        $this->hosts = $hosts;
    }

    public function add(array $hostsDetails)
    {
        foreach ($hostsDetails as $hostsDetail) {
            $this->validateDetails($hostsDetail);

            $hostName = $this->addSchemeAndDefaultPort($hostsDetail["name"]);

            if (!empty($this->getDetails->getIdByUrlMatch($hostName))) {
                throw new \Exception("Already have host under " . $hostName, 1);
            }

            try {
                $result = $this->generateCert->createCertAndPushToServer(
                    $hostName,
                    $hostsDetail["trustPassword"]
                );

                $alias = null;

                if (isset($hostsDetail["alias"]) && !empty($hostsDetail["alias"])) {
                    $alias = $hostsDetail["alias"];
                }

                $this->addHost->addHost(
                    $hostName,
                    $result["shortPaths"]["key"],
                    $result["shortPaths"]["cert"],
                    $result["shortPaths"]["combined"],
                    $alias
                );

                $this->hosts->reloadHosts();
            } catch (\Http\Client\Exception\NetworkException $e) {
                throw new \Exception("Can't connect to host, is lxd running and the port open?", 1);
            }
        }
        return true;
    }

    private function addSchemeAndDefaultPort($name)
    {
        $parts = parse_url($name);

        if (!isset($parts["scheme"])) {
            $parts["scheme"] = "https://";
        }

        if (!isset($parts["port"])) {
            $parts["port"] = 8443;
        }

        return $parts["scheme"] . $parts["path"] . ":" . $parts["port"];
    }

    private function validateDetails($host)
    {
        if (!isset($host["name"]) || empty($host["name"])) {
            throw new \Exception("Please provide name", 1);
        } elseif (!isset($host["trustPassword"]) || empty($host["trustPassword"])) {
            throw new \Exception("Please provide trust password", 1);
        }
        return true;
    }
}
