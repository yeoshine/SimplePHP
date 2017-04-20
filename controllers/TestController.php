<?php
/**
 * Created by PhpStorm.
 * User: YANG
 * Date: 2017/4/20
 * Time: 下午2:41
 */

namespace Controllers;


class TestController extends Controller
{

    public function index()
    {
        $this->log->addWarning('warning');
    }

}