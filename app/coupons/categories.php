<?php

function GetCategories() {
    $app = \Slim\Slim::getInstance();
    
    $qb = $app->db->createQueryBuilder();
    $qb->select('c.id', 'c.parent_id', 'c.title')
        ->from('rtui_coupons_categories', 'c');
    
    $res = $qb->execute();
    
    $app->log->info('GetCategories: got ' .$res->rowCount(). ' categories');
    $app->api->respond(200, array(
        'categories' => $res->fetchAll()
    ));
}
