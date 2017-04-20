<?php
/**
 * Created by PhpStorm.
 * User: YANG
 * Date: 2017/4/20
 * Time: 下午2:15
 */

namespace Controllers;


class Controller
{
    public $log = '';

    public function __construct()
    {
        $this->log = new \Monolog\Logger('controller');
        $this->log->pushHandler(new \Monolog\Handler\StreamHandler('logs/controller.log', \Monolog\Logger::WARNING));
    }
}