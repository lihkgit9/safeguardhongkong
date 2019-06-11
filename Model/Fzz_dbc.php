<?php
include_once 'Cdb.class.php';
include_once 'db.php';

class  Fzz_dbc extends Cdb
{
    private $fzz_table = 'user_signs';

    public function __construct()
    {
        $this->db_connect();
    }

    public function db_connect()
    {
        $db_host = DB_HOST4;
        $db_user = DB_UNAME4;
        $db_pwd = DB_PWD4;
        $db_dbname = DB_DNAME4;

        $this->InitDatebase($db_host, $db_user, $db_pwd, $db_dbname);
    }

    public function GetHKCodeNum($hkcode)
    {
        return $this->GetNum($this->fzz_table, " AND hkcode='$hkcode'");
    }

    public function getTotalRecord($time = null)
    {
        $where = '';
        if (!is_null($time)) {
            $where = ' AND dtime<=' . $time;
        }
        return $this->GetNum($this->fzz_table, $where);
    }

    public function getCountBySector($time = null)
    {
        $where = '';
        if (!is_null($time)) {
            $where = ' WHERE dtime<=' . $time;
        }

        $sql = " select COUNT(id) as total, sector from `" . $this->fzz_table . '` ' . $where;
        $query = $this->Query($sql);
        $rt = array();
        while ($row = mysqli_fetch_assoc($query)) {
            $rt['sector_' . $row['sector']] = $row['total'];
        }
        return $rt;
    }

    public function getIpLastTime($ip, $user_agent)
    {
        return $this->GetLine($this->fzz_table, " AND ip='$ip' AND user_agent='$user_agent' ORDER BY id desc LIMIT 1 ");
    }

    public function InsertFzz($dataArray)
    {
        // print_r($dataArray);
        return $this->Insert($this->fzz_table, $dataArray);
    }
}
