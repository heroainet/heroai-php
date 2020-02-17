<?php

namespace App\Model\Main;

use Tiny\Mvc\Model\Db;

class UserInfo extends Db
{
    //指定dataid
    protected $_dataId = "default";
    
    
    public $a = ["aaaa"];
    
    public function main()
    {
        $a = $this->exec('SELECT * FROM :0t WHERE :1', 'user', "user='root'");
        return [$a];
    }
}
?>