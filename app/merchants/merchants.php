<?php

function GetMerchants() {
    $app = \Slim\Slim::getInstance();
    $req = $app->request;
    $api = $app->api;

    $ids = $req->params('ids');
    $search = $req->params('search');
    $email = $req->params('email');
    $phone = $req->params('phone');
    $start = $req->params('start') ? intval($req->params('start')) : 0;
    $limit = $req->params('limit') ? intval($req->params('limit')) : 20;

    // Parse ids
    if (!empty($ids)) {
        $mids = array();
        $ids = explode(',', $ids);
        foreach ($ids as $id) {
            if (!$id) continue;
            $mids[] = '\'' . $id . '\'';
        }
        $ids = array_unique($mids);
    }

    $qb = $app->db->createQueryBuilder();
    $qb->select(
        'm.accountid AS id', 'm.trg_contact', 'm.name',
        'm.address1_line1 AS address', 'm.address1_city AS city',
        'm.address1_stateorprovince AS state', 'm.address1_postalcode AS zip',
        'm.emailaddress1 AS email', 'm.fax', 'm.telephone1 AS telephone',
        'm.trg_categoryidname', 'm.websiteurl', 'm.contract', 'm.active')
        ->from('rtui_crm_accounts', 'm');

    if (empty($ids)) {
        if ($search) {
            $qb->andWhere(
                    'MATCH (m.name) AGAINST (:search IN BOOLEAN MODE)'
                )
                ->setParameter('search', $search);
        }

        if ($email) {
            $qb->andWhere(
                    $qb->expr()->eq('m.emailaddress1', ':email')
                )
                ->setParameter('email', $email);
        }

        if ($phone) {
            $qb->andWhere(
                    $qb->expr()->eq('m.telephone1', ':phone')
                )
                ->setParameter('phone', $phone);
        }

        $qb->setFirstResult($start)
            ->setMaxResults($limit);
    } else {
        $qb->andWhere(
                $qb->expr()->in('m.accountid', $ids)
            );
    }
    $res = $qb->execute();

    // Count total number of results
    $count = $res->rowCount();
    if ($start > 0 || $count == $limit) {
        $qb->select('count(m.id) as count')
           ->setFirstResult(0)
           ->setMaxResults(1);
        $cntRes = $qb->execute()->fetch();
        $count = $cntRes['count'];
    }

    $app->log->info(sprintf('GetMerchants: returned %d merchants out of %d',
                            $res->rowCount(), $count));
    return $api->respond(200, array(
        'count' => $count,
        'merchants' => $res->fetchAll()
    ));
}

function GetMerchant($id) {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;

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
        ->setParameter('id', $id)
        ->setMaxResults(1);
    $merchant = $qb->execute()->fetch();

    if (! $merchant) {
        $api->error(404, 301, 'merchant not found');
    }

    $app->log->info('GetMerchant: requested merchant with id ' . $id);
    $api->respond(200, array(
        'merchant' => $merchant
    ));
}
