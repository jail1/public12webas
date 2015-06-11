<?php

function getAPPConfig() {

  $app = \Slim\Slim::getInstance();
  $qb = $app->db->createQueryBuilder();

  $qb->select('app_update_new_android', 
              'app_update_force_android', 
              'app_update_new_ios', 
              'app_update_force_ios', 
              'app_update_url_android', 
              'app_update_url_ios', 
              'app_update_info_android',
              'app_update_info_ios')
     ->from('rtui_coupons_app_config', 'c');

  $res = $qb->execute();
  
  $app->api->respond(200, array(
    'config' => $res->fetchAll()
  ));

}

function getUserPreferences() {

  $app = \Slim\Slim::getInstance();
  $qb  = $app->db->createQueryBuilder();
  $req = $app->request;

  $email = $req->params('email');
  if (!$email) {
    $app->api->error(200, 203, 'Missing or Invalid email');
  }

  $qb->select('u.*')
     ->from('rtui_users_app_preferences', 'u')
     ->where(
          $qb->expr()->eq('u.email', ':email')
      )
     ->setParameter('email', $email)
     ->setMaxResults(1);

  $res = $qb->execute()->fetch();
  if (!$res) {
    //$app->api->error(404, 207, 'User not found');
    $res = array("id"=>0,
        "user_id"=>0,
        "email"=>$email,
        "distance"=>"25",
        "categories"=>"22,21,20,19,18,17,30,29,28,27,26,39,38,37,36,35,34,33,32,31,43,42,41,40,50,49,48,47,46,45,44,54,53,52,51,62,61,60,59,58,57,56,55,70,69,68,67,66,65,64,63,77,76,75,74,73,72,71,78",
        "ereceipt"=>"1",
        "notifications"=>"3",
        "notifications_silent"=>"1",
        "wallet_code"=>null,
        "wallet_coupons"=>"",
        "view_zip"=>"1",
        "birthday"=>"0000-00-00",
        "lastupdate"=>"0000-00-00 00:00:00",
        "sex"=>"0",
        "emails"=>"",
        "emailreceipt"=>"");
  }
  

  $app->api->respond(200, array(
    'usersPreferences' => $res
  ));

}

function setUserPreferences() {

  $app = \Slim\Slim::getInstance();
  $qb  = $app->db->createQueryBuilder();
  $api = $app->api;
  $req = $app->request;

  $req_fields = array('email', 'distance', 'categories', 'ereceipt', 'notifications', 'notifications_silent', 'view_zip');  
  $params     = $req->params();

  foreach ($req_fields as $field) {
      if (array_key_exists($field, $params) ) continue;
      $api->error(400, AE_PARAM_MISSING, 'Missing parameter: ' . $field);
  }

  foreach ($params as $key => $param) {    
      if ( !empty($param) || $param == 0) continue;      
      $api->error(400, AE_PARAM_INVALID, 'Empty parameter: ' . $key);
  }

  $email                = $req->params('email');
  $distance             = $req->params('distance');
  $categories           = $req->params('categories');
  $ereceipt             = $req->params('ereceipt');
  $notifications        = $req->params('notifications');
  $notifications_silent = $req->params('notifications_silent');
  $wallet_code          = $req->params('wallet_code');
  $wallet_coupons       = $req->params('wallet_coupons') ? $req->params('wallet_coupons') : '';
  $view_zip             = $req->params('view_zip');
  $birthday             = $req->params('birthday') ? $req->params('birthday') : '';
  $sex                  = $req->params('sex') ? $req->params('sex') : '0';
  $emails               = $req->params('emails') ? $req->params('emails') : '';
  $emailreceipt         = $req->params('emailreceipt') ? $req->params('emailreceipt') : '0';


  if ( !empty($wallet_code) && strlen($wallet_code) < 4 )   {
     $api->error(400, AE_PARAM_INVALID, 'Wallet Code length is less than 4 characters.');
  }

  $qb->select('u.*')
     ->from('rtui_users_app_preferences', 'u')
     ->where(
          $qb->expr()->eq('u.email', ':email')
      )
     ->setParameter('email', $email)
     ->setMaxResults(1);
  $action = $qb->execute()->fetch();

  if ($action) {

      $app->db->update('rtui_users_app_preferences', array(          
          'distance'              => $distance,
          'categories'            => $categories,
          'ereceipt'              => $ereceipt,
          'notifications'         => $notifications,
          'notifications_silent'  => $notifications_silent,
          'wallet_code'           => $wallet_code,
          'wallet_coupons'        => $wallet_coupons,
          'view_zip'              => $view_zip,
          'birthday'              => $birthday,
          'sex'                   => $sex,
          'emails'                => $emails,
          'emailreceipt'          => $emailreceipt
      ), array(
          'email' => $email
      ));	
  } else {

      $app->db->insert('rtui_users_app_preferences', array(
          'email'                 => $email,
          'distance'              => $distance,
          'categories'            => $categories,
          'ereceipt'              => $ereceipt,
          'notifications'         => $notifications,
          'notifications_silent'  => $notifications_silent,
          'wallet_code'           => $wallet_code,
          'wallet_coupons'        => $wallet_coupons,
          'view_zip'              => $view_zip,
          'birthday'              => $birthday,
          'sex'                   => $sex,
          'emails'                => $emails,
          'emailreceipt'          => $emailreceipt
      ), array(
      ));
  }

  $api->respond(200, array(
      'message' => 'success'
  ));

}


function checkJoomlaUser($login, $password, $joomlaPassword) {
  list($hash, $salt) = explode(':', $joomlaPassword);
  if (!$salt) {
    return;
  }

  return md5($password.$salt) == $hash;
}

function getJoomlaUserGroups($uid) {
    $qb = \Slim\Slim::getInstance()->db->createQueryBuilder();

    $qb->select('g.title')
       ->from('rtui_usergroups', 'g')
       ->leftJoin('g', 'rtui_user_usergroup_map', 'gm', 'gm.group_id = g.id')
       ->where($qb->expr()->eq('gm.user_id', ':uid'))
       ->setParameter('uid', $uid);

    $dbGroups = $qb->execute();
    $groups = [];
    foreach ($dbGroups as $group) {
        $groups[] = strtolower(trim($group['title']));
    }

    return $groups;
}

function GetUserCheck() {
  $app = \Slim\Slim::getInstance();
  $api = $app->api;
  $req = $app->request;

  $login = $req->params('login');
  $password = $req->params('password');

  if (!$login) {
    $api->error(400, 401, 'missing login parameter');
  }

  if (!$password) {
    $api->error(400, 402, 'missing password parameter');
  }

  $qb = $app->db->createQueryBuilder();
  $qb->select('u.id', 'u.name', 'u.username', 'u.email', 'u.block', 'u.password',
              'u.accountid as merchant_id')
     ->from('rtui_users', 'u')
     ->where('u.username = :login')
     ->setParameter('login', $login)
     ->setMaxResults(1);

  $user = $qb->execute()->fetch();
  if (!$user) {
    $api->error(400, 403, 'invalid login or password');
  }

  if ($user['block']) {
    $api->error(400, 404, 'user is blocked');
  }

  if (!checkJoomlaUser($login, $password, $user['password'])) {
    $api->error(400, 403, 'invalid login or password');
  }


  $groups = getJoomlaUserGroups($user['id']);
  $merchant = in_array('merchants', $groups);
  if (!$merchant && !in_array('registered', $groups)) {
    $api->error(400, 405, 'unprivileged user');
  }

  unset($user['password']);
  unset($user['block']);
  if (!$merchant) {
      unset($user['merchant_id']);
  }

  $app->log->info('GetUserCheck: found user ' . $user['id']);
  return $api->respond(200, array(
      'user' => $user
  ));
}
