//<?php
/**
 * newPost
 *
 * Обновление и получение городов и отделений новой почты
 *
 * @category    plugin
 * @internal    @events OnWebPageInit,OnPageNotFound
 * @internal    @modx_category delivery
 * @internal    @properties 
 * @internal    @disabled 0
 * @internal    @installset base
 */

$e = $modx->event;
require_once MODX_BASE_PATH.'assets/snippets/newPost/newPost.php';
$newPost = new newPost($modx);
$start = microtime(true);
switch ($e->name){
    case 'OnWebPageInit':
        if(empty($_SESSION['mgrShortname'])){ return ;  }
        $lastUpdateDateFile = MODX_BASE_PATH.'assets/snippets/newPost/date.txt';
        $date = date('d-m-Y');
        $lastUpdateDate = file_get_contents($lastUpdateDateFile);
        if($lastUpdateDate != $date){
            $newPost->update($start);
            file_put_contents($lastUpdateDateFile,$date);
        }
        break;
    case 'OnPageNotFound':
        switch ($_GET['q']) {
            case 'newPost-update':
                $newPost->update($start);
                echo 'Список обновлено';
                die();
            case 'newPost-getDepartments':
                echo $newPost->getDepartments($_GET['city_ref'],$_GET['lang']);
                die();
                break;
            case 'newPost-getCities':
                echo $newPost->getCities($_GET['query'],$_GET['lang']);
                die();
        }
        break;
}





