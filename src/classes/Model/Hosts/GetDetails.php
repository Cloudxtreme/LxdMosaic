<?php
namespace dhope0000\LXDClient\Model\Hosts;

use dhope0000\LXDClient\Model\Database\Database;

class GetDetails
{
    public function __construct(Database $database)
    {
        $this->database = $database->dbObject;
    }

    public function getAll($hostId)
    {
        $sql = "SELECT
                    `Host_ID`,
                    `Host_Url_And_Port`,
                    `Host_Cert_Path`
                FROM
                    `Hosts`
                WHERE
                    `Host_ID` = :hostId
                ORDER BY
                    `Host_ID` DESC
                ";
        $do = $this->database->prepare($sql);
        $do->execute([
            ":hostId"=>$hostId
        ]);
        return $do->fetch(\PDO::FETCH_ASSOC);
    }

    public function getIdByUrlMatch($hostUrl)
    {
        $sql = "SELECT
                    `Host_ID`
                FROM
                    `Hosts`
                WHERE
                    `Host_Url_And_Port` LIKE ?
                ORDER BY
                    `Host_ID` DESC
                ";
        $do = $this->database->prepare($sql);
        $do->execute(
            ["%$hostUrl%"]
        );
        return $do->fetchColumn();
    }

    public function getCertificate($hostId)
    {
        $sql = "SELECT
                    `Host_Cert_Path`
                FROM
                    `Hosts`
                WHERE
                    `Host_ID` = :hostId
                ORDER BY
                    `Host_ID` DESC
                ";
        $do = $this->database->prepare($sql);
        $do->execute([
            ":hostId"=>$hostId
        ]);
        return $do->fetchColumn();
    }
}
