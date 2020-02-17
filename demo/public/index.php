<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name index.php
 * @Author King
 * @Version Beta 1.0
 * @Date 2019年11月18日上午11:18:04
 * @Description
 * @Class List 1.
 * @Function List 1.
 * @History King 2019年11月18日上午11:18:04 第一次建立该文件
 *                 King 2019年11月18日上午11:18:04 修改
 *
 */
define('APPLICATION_PATH', dirname(__DIR__) . '/application/');
include_once (dirname(dirname(__DIR__)) . '/lib/tiny/src/Tiny.php');

\Tiny\Tiny::setENV([
    'RUNTIME_CACHE_ENABLE'=> TRUE,
    'RUNTIME_CACHE_SIZE' => '10000000'
]);

Tiny\Tiny::createApplication(APPLICATION_PATH, APPLICATION_PATH . 'config/profile.php')->run();
?>