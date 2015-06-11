<?php

function GetContract($id) {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;


    $cqb = $app->db->createQueryBuilder();
    $cqb->select('c.trg_accountid')
        ->from('rtui_crm_contracts', 'c')
        ->where(
            $cqb->expr()->eq('c.trg_name', ':id')
        )
        ->setParameter('id', $id)
        ->setMaxResults(1);

    $coupon = $cqb->execute()->fetch();
    if (! $coupon) {
        $api->error(404, 401, 'contract not found');
    }

    $qb = $app->db->createQueryBuilder();
    $qb->select(
        'm.accountid AS id', 'm.trg_contact', 'm.name',
        'm.address1_line1 AS address', 'm.address1_city AS city',
        'm.address1_stateorprovince AS state', 'm.address1_postalcode AS zip',
        'm.emailaddress1 AS email', 'm.fax', 'm.telephone1 AS telephone',
        'm.trg_categoryidname', 'm.websiteurl', 'm.contract', 'm.active')
        ->from('rtui_crm_accounts', 'm')
        ->where(
            $qb->expr()->eq('m.accountid', ':id')
        )
        ->setParameter('id', $coupon['trg_accountid'])
        ->setMaxResults(1);
    $merchant = $qb->execute()->fetch();

    $app->log->info('GetContract: requested contract with id ' . $id);
    $api->respond(200, array(
        'id' => $id,
        'merchant' => $merchant ? $merchant : null,
    ));
}
