<?php

function PostLead() {
  $app = \Slim\Slim::getInstance();
  $api = $app->api;
  $req = $app->request;

  $name = $req->params('name');
  $business = $req->params('business');
  $email = $req->params('email');
  $address = $req->params('address');
  $city = $req->params('city');
  $state = $req->params('state');
  $zip = $req->params('zip');
  $phone = $req->params('phone');
  $country = $req->params('country');
  $message = $req->params('message');
  $coords = $req->params('coords');

  // Check required fields
  if (!$name) {
    $api->error(400, AE_PARAM_MISSING, 'name required');
  }
  if (!$email) {
    $api->error(400, AE_PARAM_MISSING, 'email required');
  }
  if (!$country) {
    $api->error(400, AE_PARAM_MISSING, 'country required');
  }
  if (!$address) {
    $api->error(400, AE_PARAM_MISSING, 'address required');
  }
  if (!$city) {
    $api->error(400, AE_PARAM_MISSING, 'city required');
  }
  if (!$state) {
    $api->error(400, AE_PARAM_MISSING, 'state required');
  }
  if (!$zip) {
    $api->error(400, AE_PARAM_MISSING, 'zip required');
  }
  if (!$phone) {
    $api->error(400, AE_PARAM_MISSING, 'phone required');
  }
  if (!$message) {
    $api->error(400, AE_PARAM_MISSING, 'message required');
  }

  if ($coords) {
    if ($coords) {
      list($lat, $lng) = sscanf($coords, "%g,%g");
      if (! $lat || ! $lng) {
        $api->error(400, AE_PARAM_INVALID, 'invalid coordinates');
      }

      $coords = array(
        'lat' => $lat,
        'lng' => $lng
      );
    }
  }

  $country = strtoupper($country);
  if ($country != 'US' && $country != 'CANADA') {
    $api->error(400, AE_PARAM_INVALID, 'country must be US or CANADA');
  }

  $lead = array(
    'zone' => 'ZONE_00',
    'created' => date('Y-m-d H:i:s'),
    'published' => 1,
    'source' => 1
  );

  $lead = array(
    'contact_name' =>	$name,
    'business_name' => $business,
    'business_email' => $email,
    'business_address' => $address,
    'business_city' => $city,
    'business_state' => $state,
    'business_zip' => $zip,
    'business_phone' => $phone,
    'business_country' => $country,
    'comments' => $message,
    'lat' => $coords['lat'],
    'lng' => $coords['lng'],
    'zone' => 'ZONE_00',
    'created' => date('Y-m-d H:i:s'),
    'published' => 1,
    'source' => 1,
    'source_about' => 14
  );


  $app->db->insert('rtui_coupons_callingleads', $lead);
  $lead['id'] = $app->db->lastInsertId();

  $app->log->info('PostLead: added lead ' . $lead['id']);
  $api->respond(200, array(
      'message' => 'success',
      'id' => $lead['id']
  ));
}
