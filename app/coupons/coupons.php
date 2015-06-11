<?php

define('DEG_TO_RAD', M_PI / 180);
define('RAD_TO_DEG', 180 / M_PI);

define('EARTH_RADIUS', 3960); // In miles
define('MIN_LAT', -90);
define('MAX_LAT', 90);
define('MIN_LNG', -180);
define('MAX_LNG', 180);


function getGoogleStaticMap ($lat, $long, $couponID) {

    if ( empty($lat) || empty($long) || empty($couponID) ) return;


    $app = \Slim\Slim::getInstance();

    $coupon_static_map_url      = $app->config('coupon_static_map_url');
    $coupon_static_map_folder   = $app->config('coupon_static_map_folder');

    if (file_exists($coupon_static_map_folder.$couponID.'.jpg')) {
        return $coupon_static_map_url.$couponID.'.jpg';
    }

    $privateKey     = 'AIzaSyD8Vq74l4tZ06z1QECtNIyV9VQqPgugHV4';

    $url            = "https://maps.googleapis.com/maps/api/staticmap?center=".$lat.",".$long."&zoom=14&size=640x300&maptype=roadmap
&markers=color:red%7Clabel:S%7C".$lat.",".$long."&format=jpg&scale=2&key=".$privateKey;
    
    $imageData      = file_get_contents($url);
    /*
    $imageData64    = base64_encode($imageData);
    $imageSrc       = 'data: image/jpeg;base64,'.$imageData64;
    */

    //save to disk
    file_put_contents($coupon_static_map_folder.$couponID.'.jpg', $imageData);

    return $coupon_static_map_url.$couponID.'.jpg';
}
  

function getGoogleLocationFromZip ($zip_code = '', $latlong = '') {
   
    $client             = 'gme-registertapesunlimited'; 
    $privateKey         = 'gWBC7DS2i6FmcnhjVsVrcKNGbSE=';
    
    $address            = urlencode($zip_code);   
    $myUrlToSign        = 'http://maps.googleapis.com/maps/api/geocode/json?&address='.$address;          

    if ( !empty($latlong) ) {
        $myUrlToSign        = 'http://maps.googleapis.com/maps/api/geocode/json?&latlng='.$latlong;                  
    }

    $myUrlToSign        .= '&client='.$client;  
    $url                = parse_url($myUrlToSign);
    $urlPartToSign      = $url['path'] . "?" . $url['query'];
    $decodedKey         = base64_decode(str_replace(array('-', '_'), array('+', '/'), $privateKey));
    $signature          = hash_hmac("sha1",$urlPartToSign, $decodedKey,  true);
    $encodedSignature   = str_replace(array('+', '/'), array('-', '_'), base64_encode($signature));

    $mysignUrl          = $myUrlToSign."&signature=".$encodedSignature;

     
    $ch             = curl_init();    
    curl_setopt($ch, CURLOPT_URL, $mysignUrl); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);   
    curl_setopt($ch, CURLOPT_HEADER,0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');          
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);

    $data = curl_exec($ch);     
    curl_close($ch);  

    if ( empty($data) ) return false;

    $data = json_decode($data);
   
    if ( !isset($data->results) || count($data->results) <1 ) return false;

    foreach ($data->results as $dresult) {

        if (!isset($dresult->geometry->location)) continue;

        $return = array();
        $return['zip'] = $zip_code;
        //$return['lat'] = $dresult->geometry->location->lat;
        //$return['lon'] = $dresult->geometry->location->lng;

        foreach ($dresult->address_components as $key => $value) {

            if ($dresult->address_components[$key]->types[0] == 'postal_code') {
               $return['zip'] = $dresult->address_components[$key]->long_name; 
            }
            if ($dresult->address_components[$key]->types[0] == 'neighborhood') {
               $return['city'] = $dresult->address_components[$key]->long_name; 
            }
            if ($dresult->address_components[$key]->types[0] == 'locality') {
               $return['city'] = $dresult->address_components[$key]->long_name; 
            }
            if ($dresult->address_components[$key]->types[0] == 'administrative_area_level_1') {
               $return['state'] = $dresult->address_components[$key]->short_name; 
            }
        }


        //$return['formatted_address'] = $dresult->formatted_address;

        return $return;  
    }
    
    return false;             
}  


function _boundingCoordinates($coords, $radius) {
    // Coordinates in radians
    $lat = $coords['lat'] * DEG_TO_RAD;
    $lng = $coords['lng'] * DEG_TO_RAD;

    // Area radius in radians
    $radDist = $radius / EARTH_RADIUS;

    $minLat = $lat - $radDist;
    $maxLat = $lat + $radDist;

    if ($minLat > MIN_LAT && $maxLat < MAX_LAT) {
        $deltaLng = asin(sin($radDist) / cos($lat));
        $minLng = $lng - $deltaLng;
        if ($minLng < MIN_LNG) {
            $minLng += 2 * M_PI;
        }
        $maxLng = $lng + $deltaLng;
        if ($maxLng > MAX_LNG) {
            $maxLng -= 2 * M_PI;
        }
    } else {
        // a pole is within the distance
        $minLat = max($minLat, MIN_LAT);
        $maxLat = min($maxLat, MAX_LAT);
        $minLng = MIN_LNG;
        $maxLng = MAX_LNG;
    }

    return array(
        'minlat' => $minLat * RAD_TO_DEG,
        'maxlat' => $maxLat * RAD_TO_DEG,
        'minlng' => $minLng * RAD_TO_DEG,
        'maxlng' => $maxLng * RAD_TO_DEG
    );
}

function _genSlug($name) {
    $slug = preg_replace('~[^\\pL\d]+~u', '-', $name);
    $slug = trim($slug, '-');
    $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
    $slug = strtolower($slug);
    return preg_replace('~[^-\w]+~', '', $slug);
}

function _coupImageManager($params) {
    $app = \Slim\Slim::getInstance();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $app->config('coupon_image_manager_url'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($ret === false) {
        return array(false, $err);
    }

    return array($ret, null);
}

$type_names = array( 'local', 'national', 'filter', 'splash');



function mailToMerchant() {

  $app = \Slim\Slim::getInstance();
  $qb  = $app->db->createQueryBuilder();
  $api = $app->api;
  $req = $app->request;

  $req_fields = array('merchant_email', 'customer_email', 'customer_subject', 'customer_message');  
  $params     = $req->params();

  foreach ($req_fields as $field) {
      if (array_key_exists($field, $params) ) continue;
      $api->error(400, AE_PARAM_MISSING, 'Missing parameter: ' . $field);
  }

  foreach ($params as $key => $param) {
      if ( !empty($param) ) continue;      
      $api->error(400, AE_PARAM_INVALID, 'Empty parameter: ' . $key);
  }

  $merchant_email       = $req->params('merchant_email');
  $customer_email       = $req->params('customer_email');
  $customer_subject     = $req->params('customer_subject');
  $customer_message     = $req->params('customer_message');

  $message = 'Thank you for contacting us';

  $emailer = $app->email;
  $emailer->addAddress($merchant_email);
  $emailer->Subject = 'Message from '.$customer_email;
  $emailer->isHTML(true);

  // Render mail template
  $emailer->Body = $app->template->render(
      'email/mailtomerchant',
      array('customer_email' => $customer_email, 'customer_subject' => $customer_subject, 'customer_message' => $customer_message)
  );

  if (!$emailer->send()) {
      $app->log->critical('error sending email: ' . $emailer->ErrorInfo);
      $api->error(500, -1, 'something bad happend');
  }

  $api->respond(200, array(
      'message'       => $message
  ));

}



function setPunchCard() {

  $app = \Slim\Slim::getInstance();
  $qb  = $app->db->createQueryBuilder();
  $api = $app->api;
  $req = $app->request;

  $req_fields = array('account_id', 'user_email', 'punchcard_id', 'punch_code');  
  $params     = $req->params();

  foreach ($req_fields as $field) {
      if (array_key_exists($field, $params) ) continue;
      $api->error(400, AE_PARAM_MISSING, 'Missing parameter: ' . $field);
  }

  foreach ($params as $key => $param) {
      if ( !empty($param) ) continue;      
      $api->error(400, AE_PARAM_INVALID, 'Empty parameter: ' . $key);
  }

  $account_id       = $req->params('account_id');
  $user_email       = $req->params('user_email');
  $punchcard_id     = $req->params('punchcard_id');
  $punch_code       = $req->params('punch_code');
  $usage_date       = date("Y-m-d H:i:s");

  if ( !empty($punch_code) && strlen($punch_code) < 4 )   {
     $api->error(400, AE_PARAM_INVALID, 'Punch Code length is less than 4 characters.');
  }

  //check punch code 
  $qb->select('pc.*')
  ->from('rtui_crm_accounts_punchcards', 'pc')
  ->where(
      $qb->expr()->eq('pc.id', ':punchcard_id'),
      $qb->expr()->eq('pc.punch_code', ':punch_code')
  )
  ->setParameter('punchcard_id', $punchcard_id)
  ->setParameter('punch_code', $punch_code)
  ->setMaxResults(1);
  $valid_punch_code = $qb->execute()->fetch();

  if (!$valid_punch_code) {
     $api->error(400, AE_PARAM_INVALID, 'Invalid Punch Code: ' . $punch_code);
  }

  $punch_limit = $valid_punch_code['punch_limit'];
  
  //add punch to user
  $app->db->insert('rtui_crm_accounts_punchcards_usage', array(
      'account_id'      => $account_id,
      'user_email'      => $user_email,
      'punchcard_id'    => $punchcard_id,
      'usage_date'      => $usage_date
  ));
  $message = "Punch Card was successfully punched.";
  $app->log->info(sprintf('setPunchCard: Punch Card ID %d was punched by %d', $punchcard_id, $user_email));

  //check punch limit 
  $qb->select('count(pl.id) as total')
  ->from('rtui_crm_accounts_punchcards_usage', 'pl')
  ->where(
      $qb->expr()->eq('pl.punchcard_id', ':punchcard_id'),
      $qb->expr()->eq('pl.user_email', ':user_email'),
      $qb->expr()->eq('pl.used', '0')
  )
  ->setParameter('punchcard_id', $punchcard_id)
  ->setParameter('user_email', $user_email)
  ->setMaxResults(1);
  $punch_usage = $qb->execute()->fetch();
  $punch_usage = $punch_usage['total'];  
  
  //limit was reached
  if ($punch_usage >= $punch_limit) {

      $app->db->update('rtui_crm_accounts_punchcards_usage', 
        array('used' => 1), 
        array('user_email' => $user_email, 'punchcard_id' => $punchcard_id)
      );

      $message = "Punch Card was successfully punched and you're gonna be rewarded.";
  }
  
  $api->respond(200, array(
      'message'       => $message,
      'punch_limit'   => $punch_limit,
      'punch_usage'   => $punch_usage
  ));

}



function GetCoupons() {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;
    $req = $app->request;

    $ids        = $req->params('ids');
    $coords     = $req->params('coords');
    $zip        = $req->params('zip');
    
    $radius     = $req->params('radius') ?: 5;
    $radius     = $req->params('dst') ?: 5;

    $phone      = $req->params('phone');
    $merchant   = $req->params('merchant');
    $search     = $req->params('search');
    
    $categories = $req->params('cat');
    $categories = $req->params('ctg');

    $types      = $req->params('types');
    $status     = $req->params('status') ? intval($req->params('status')) : 1;
    $since      = $req->params('since') ? intval($req->params('since')) : 0;
    $start      = $req->params('start') ? intval($req->params('start')) : 0;
    $limit      = $req->params('limit') ? intval($req->params('limit')) : 100;

    $debug      = $req->params('debug');

    $zip_location = array();
    $zip_list 	 = '';

    // Parse ids
    if (!empty($ids)) {
        $cids = array();
        $ids = explode(',', $ids);
        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;
            $cids[] = $id;
        }
        $ids = array_unique($cids);
    }

    // Ignore filters when getting by ids
    if (empty($ids)) {
        if ($coords) {
            list($lat, $lng) = sscanf($coords, "%g,%g");
            if (! $lat || ! $lng) {
                $api->error(400, 202, 'invalid coordinates');
            }

            $zip_location = getGoogleLocationFromZip('',$coords);

            $coords = array(
                'lat' => $lat,
                'lng' => $lng
            );
        }

        if ( !$coords && $zip) {

            $zipFormatted = $zip;
            $zipFormatted = str_replace(" ", "", $zip);
            
            $qb = $app->db->createQueryBuilder();
            $qb->select('z.zc_lat AS lat', 'z.zc_lon AS lng')
                ->from('rtui_coupons_zipcodes2', 'z')
                ->where('idx_zip = :zip')
                ->setParameter('zip', $zipFormatted, 'string')
                ->setMaxResults(1);

            $coords = $qb->execute()->fetch();
            if (! $coords) {
                $api->error(400, 203, 'Invalid Zip Code! <br/>Please correct and try again.');
            }

            $zip_location = getGoogleLocationFromZip($zip);

            
	        	$lat = $coords['lat'];
	        	$lng = $coords['lng'];

	        	$c   = pi()/180.0;
		        $lat = $c*$lat;
		        $lng = $c*$lng;

	        	  $qb = $app->db->createQueryBuilder();
	            $qb->select('distinct(z.zc_zip)')
	                ->from('rtui_coupons_zipcodes2', 'z')
	                ->where('6371 * acos( sin('.$lat.') * sin( z.zc_lat * pi()/180 ) + cos('.$lat.') * cos( z.zc_lat * pi()/180 ) * cos('.$lng.' - z.zc_lon * pi()/180)) < ' . $radius);

	            // $zips  = $qb->execute()->fetch();	
	            // $zip_list = $zip . ', ' . implode(',', $zips);

              //if ( $debug == 2 ) { 
              $zipList = '';
              $zips = $qb->execute()->fetchAll();
              foreach ($zips as $zipx) {
                  $zipList .=  "'".$zipx['zc_zip']."',";              
              }  
              $zip_list = trim($zipList."'".$zip."'", ",");
              //}
        }

        if ($coords) {
            if ($radius < 1 || $radius > 25) {
                $api->error(400, 204, 'radius must be between 1 and 25');
            }
        }

        if ($phone) {
            $newPhone = preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '($1) $2-$3', $phone);

            if ($newPhone == $phone) {
                $api->error(400, 216, 'invalid phone filter');
            }

            $phone = $newPhone;
        }

        if ($categories) {
            $categories = explode(',', $categories);
            foreach($categories as $cat) {
                if (!is_numeric($cat)){
                    $api->error(400, 205, 'invalid category ids');
                }
            }
        }

        if ($status < 0 || $status > 2) {
            $api->error(400, 222, 'invalid status');
        }
    }

    $qb = $app->db->createQueryBuilder();
    $qb->select(
        'cp.id', 'cp.merchant_id', 'cp.name', 'cp.description', 'cp.filename',
        'cp.title', 'cp.title2', 'cp.title3', 'cp.title4', 'cp.title5',
        'cp.alias', 'cp.status', 'cp.number', 'cp.website', 'cp.phone',
        'cr.rating_sum/cr.rating_count AS rating', 'cp.catid', 'cp.contract',
        'cp.businessemail as business_email', 'ct.title AS category_title',
        'cp.store', 'cp.address', 'cp.city', 'cp.zip', 'cp.state', 'cp.lat',
        'cp.lng', 'cp.cycle', 'cp.keywords', 'cp.business_type AS type',
        'UNIX_TIMESTAMP(cp.created_date) as created_date',
        'UNIX_TIMESTAMP(cp.modified_date) as modified_date',
        'UNIX_TIMESTAMP(cp.expire_date) as expire_date', 'cp.width', 'cp.height', 'cp.image_timestamp')
        ->from('rtui_coupons', 'cp')
        ->leftJoin('cp', 'rtui_coupons_vote', 'cr', 'cr.content_id = cp.id')
        ->leftJoin('cp', 'rtui_categories', 'ct', 'ct.id = cp.catid');

    // Ignore filters when getting by ids
    if (empty($ids)) {
        $qb->where($qb->expr()->andX(
                $qb->expr()->isNotNull('cp.filename'),
                $qb->expr()->gte('cp.expire_date', 'NOW()')
            ))
            ->setFirstResult($start)
            ->setMaxResults($limit);

        if (!$types) { $types = 'local,national'; }

        if ($types) {
            global $type_names;

            $type_filter = array();
            $types = explode(',', $types);
            foreach($types as $type) {
                $type_idx = array_search($type, $type_names);
                if ($type_idx === false){
                    $api->error(400, 217, 'invalid type');
                }
                $type_filter[] = $type_idx;
            }
            $qb->andWhere(
                $qb->expr()->in('cp.business_type', $type_filter)
            );
        }

        if ($phone) {
            $qb->andWhere(
                    $qb->expr()->eq('cp.phone', ':phone')
                )
                ->setParameter('phone', $phone);
        }

        if ($merchant) {
            $qb->andWhere(
                    $qb->expr()->eq('cp.merchant_id', ':merchant')
                )
                ->setParameter('merchant', $merchant);
        }

        if ($categories) {
            $qb->andWhere(
                    $qb->expr()->in('cp.catid', $categories)
                );
        }

        if ($search) {
            $qb->andWhere(
                    'MATCH (cp.metadatakey) AGAINST (:search IN BOOLEAN MODE)'
                )
                ->setParameter('search', $search);
        }

        if ($since) {
            $qb->andWhere(
                    $qb->expr()->gte('cp.created_date', ':since')
            )
            ->setParameter('since', date('Y-m-d', $since));
        }
        
        if ( $zip) { 
          $qb->andWhere(' cp.zip IN ( ' . $zip_list . ' ) ');
        }
         if ($coords) {	
        	

        	
	            $bounds = _boundingCoordinates($coords, $radius);
	            $distanceFormula  = 'acos(sin(:radlat) * sin(cp.lat * pi()/180) + '.
	                                'cos(:radlat) * cos(cp.lat * pi()/180) * '.
	                                'cos((:radlng) - cp.lng * pi()/180 ))';	
								 // 'cos(cp.lng * pi()/180 - (:radlng)))';

	            $qb->andWhere($qb->expr()->andX(
	                    $qb->expr()->gt('cp.lat', $bounds['minlat']),
	                    $qb->expr()->lt('cp.lat', $bounds['maxlat']),
	                    $qb->expr()->gt('cp.lng', $bounds['minlng']),
	                    $qb->expr()->lt('cp.lng', $bounds['maxlng']),
	                    $qb->expr()->lte($distanceFormula, ':raddist')
	                ))
	                ->setParameter('radlat', $coords['lat'] * DEG_TO_RAD)
	                ->setParameter('radlng', $coords['lng'] * DEG_TO_RAD)
	                ->setParameter('raddist', $radius / EARTH_RADIUS)
	                ->orderBy($distanceFormula, 'ASC');
	        
        }

        switch($status) {
        case 0:
        case 1:
            $qb->andWhere(
                    $qb->expr()->eq('cp.status', ':status')
                )
                ->setParameter('status', $status);
            break;
        case 2:
            $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('cp.status', 0),
                        $qb->expr()->eq('cp.status', 1)
                    )
                );
            break;
        }
    } else {
        $qb->andWhere(
                $qb->expr()->in('cp.id', $ids)
            );
    }

    // Execute the query
    $res = $qb->execute();

    // Count total number of results
    $count = $res->rowCount();
    if ($start > 0 || $count == $limit) {
        $qb->select('count(cp.id) as count')
           ->setFirstResult(0)
           ->setMaxResults(1);
        $cntRes = $qb->execute()->fetch();
        $count = $cntRes['count'];
    }

    $coupons = $res->fetchAll();


    array_walk($coupons, function (&$coupon) {
        global $type_names;
        $coupon['type']             = $type_names[intval($coupon['type'])];        
        $coupon['merchant_image']   = '';
        $coupon['static_map']       = '';        
    });

    //get first coupons location
    /*
    foreach ($coupons as $coupon) {
        if ( !empty($coupon['zip']) && !empty($coupon['city']) && !empty($coupon['state']) ) {
            $zip_location['zip']    = $coupon['zip']; 
            $zip_location['city']   = $coupon['city']; 
            $zip_location['state']  = $coupon['state'];   
            break; 
        }
    }
    */

    //start duplicates
    $duplicates = array();

    //start debug / duplicates
    if ( $debug == 1 ) { 
    
        $merchants = array();
        $new_arr = array();

        foreach($coupons as $key => $val) {
            $found = false;
            $val['title'] = trim($val['title']);
            $val['name'] = utf8_encode($val['name']);

            $foundItems = array();
            if(array_key_exists($val['merchant_id'], $merchants)) {            
                $foundItems = $merchants[$val['merchant_id']];
            }

            if (!empty($foundItems)) {
                foreach($foundItems as $foundItem) {
                    $lowerTitle = strtolower($val['title']);

                    foreach($foundItem['titles'] as $title) {
                        if ($title == $lowerTitle) {
                            $found = true;
                            break;            
                        }
                    }

                    if($found) {
                        $new_arr[$foundItem['index']]['locations'][] = array(
                            'id'                => $val['id'], 
                            'filename'          => $val['filename'],
                            'title'             => $val['title'],
                            'title2'            => $val['title2'],
                            'title3'            => $val['title3'],
                            'title4'            => $val['title4'],
                            'title5'            => $val['title5'],
                            'alias'             => $val['alias'],
                            'phone'             => $val['phone'],
                            'category_title'    => $val['category_title'],
                            'store'             => $val['store'], 
                            'number'            => $val['number'], 
                            'name'              => $val['name'], 
                            'address'           => $val['address'],
                            'city'              => $val['city'],
                            'state'             => $val['state'],
                            'zip'               => $val['zip'],
                            'lat'               => $val['lat'], 
                            'lng'               => $val['lng'],
                            'expire_date'       => $val['expire_date']
                        );
                        break;
                    }
                }
            }

            if(!$found) {
                $val['locations'] = array();
                $new_arr[] = $val;

                if (!array_key_exists($val['merchant_id'], $merchants)) {
                    $merchants[$val['merchant_id']] = array();
                }

                $merchants[$val['merchant_id']][] = array(  
                    'index' => count($new_arr) - 1,
                    'titles' => array(
                        strtolower($val['title']),
                        strtolower($val['title2']),
                        strtolower($val['title3']),
                        strtolower($val['title4']),
                        strtolower($val['title5'])
                    )
                );
            }
        }

        $coupons = $new_arr;
    }//end debug    
    //end duplicates

    $app->log->info(sprintf('GetCoupons: returned %d coupons out of %d',  $res->rowCount(), $count));

    $message = '';
    if ($count == 0) $message = 'There are no coupons based on your criteria.';

    return $api->respond(200, array(
        'count' => $count,
        'zip_location' => $zip_location,
        'coupon_image_url' => $app->config('coupon_image_url_list'),
        'coupons' => $coupons,
        'message' => $message,
        'duplicates' => $duplicates
    ));
}

function GetCouponsLatest() {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;
    $req = $app->request;

    $since = $req->params('since');
    $categories = $req->params('cat');
    $start = $req->params('start') ? intval($req->params('start')) : 0;
    $limit = $req->params('limit') ? intval($req->params('limit')) : 20;

    if (! $since) {
        $api->error(400, 213, 'missing since parameter');
    }

    $since = intval($since);
    if (! $since) {
        $api->error(400, 214, 'invalid since parameter');
    }

    if ($categories) {
        $categories = explode(',', $categories);
        foreach($categories as $cat) {
            if (!is_numeric($cat)){
                $api->error(400, 205, 'invalid category ids');
            }
        }
    }

    $qb = $app->db->createQueryBuilder();
    $qb->select(
        'cp.id', 'cp.merchant_id', 'cp.name', 'cp.description', 'cp.filename',
        'cp.title', 'cp.title2', 'cp.title3', 'cp.title4', 'cp.title5',
        'cp.alias', 'cp.status', 'cp.number', 'cp.website', 'cp.phone',
        'cr.rating_sum/cr.rating_count AS rating', 'cp.catid', 'cp.contract',
        'cp.businessemail as business_email', 'ct.title AS category_title',
        'cp.store', 'cp.address', 'cp.city', 'cp.zip', 'cp.state', 'cp.lat',
        'cp.lng', 'cp.cycle', 'cp.keywords', 'cp.business_type AS type',
        'UNIX_TIMESTAMP(cp.created_date) as created_date',
        'UNIX_TIMESTAMP(cp.modified_date) as modified_date',
        'UNIX_TIMESTAMP(cp.expire_date) as expire_date')
        ->from('rtui_coupons', 'cp')
        ->leftJoin('cp', 'rtui_coupons_vote', 'cr', 'cr.content_id = cp.id')
        ->leftJoin('cp', 'rtui_categories', 'ct', 'ct.id = cp.catid')
        ->where($qb->expr()->andX(
            //$qb->expr()->eq('cp.business_type', 0),
            $qb->expr()->isNotNull('cp.filename'),
            $qb->expr()->gte('cp.expire_date', 'NOW()'),
            $since ? $qb->expr()->orX(
                $qb->expr()->gte('cp.created_date', ':since'),
                $qb->expr()->gte('cp.modified_date', ':since')
            ) : null,
            $categories ? $qb->expr()->in('cp.catid', $categories) : null
        ))
        ->setFirstResult($start)
        ->setMaxResults($limit)
        ->setParameter('since', date('Y-m-d H:i:s', $since));
    $res = $qb->execute();

    // Count total number of results
    $count = $res->rowCount();
    if ($start > 0 || $count == $limit) {
        $qb->select('count(cp.id) as count')
           ->setFirstResult(0)
           ->setMaxResults(1);
        $cntRes = $qb->execute()->fetch();
        $count = $cntRes['count'];
    }

    $coupons = $res->fetchAll();
    array_walk($coupons, function (&$coupon) {
        global $type_names;
        $coupon['type'] = $type_names[intval($coupon['type'])];
    });

    $app->log->info(sprintf('GetCouponsLatest: returned %d coupons out of %d '.
                            'since %d', $res->rowCount(), $count, $since));
    return $api->respond(200, array(
        'count' => $count,
        'coupons' => $coupons
    ));
}


function LiveSearch() {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;
    $req = $app->request;

    $search     = $req->params('search');
    $coords     = $req->params('coords');

    if (! $search) {
        $api->error(400, 213, 'missing search parameter');
    }

    if (strlen($search) < 3) {
        $api->error(400, 213, 'search parameter must be at least 3 chars long');
    }

    list($lat, $lng) = sscanf($coords, "%g,%g");
    if (! $lat || ! $lng) {
        $api->error(400, 202, 'invalid coordinates');
    }

    $qb = $app->db->createQueryBuilder();
    $qb->select(
        'cp.id', 'cp.name', 'cp.title', 'cp.title2', 'cp.title3', 'cp.title4', 'cp.title5', 'cp.merchant_accountname', 'cp.merchant_id',
        '(6371 * acos(cos(radians('.$lat.')) * cos(radians(lat)) * cos( radians(lng) - radians('.$lng.')) + 
          sin(radians('.$lat.')) * sin(radians(lat)))) AS distance'
        )
        ->from('rtui_coupons', 'cp')
        ->where($qb->expr()->andX(
                $qb->expr()->like('cp.title', ':search'),
                $qb->expr()->gte('cp.expire_date', 'NOW()')
            )           
          )
        ->having('distance <= 25')
        ->orderBy('distance', 'ASC')
        ->setMaxResults(25)
        ->setParameter('search', '%'.$search.'%');
    $res = $qb->execute();   
    $coupons = $res->fetchAll();   

    $results = array();
    $addedMerchants = array();
    foreach ($coupons as $rawCoupon) {
        $merchant_id = $rawCoupon['merchant_id'];   
        $merchant_name = $rawCoupon['merchant_accountname'];

        $coupon = array(
            'id'       => $rawCoupon['id'],
            //'name'     => $rawCoupon['name'],
            'title'    => $rawCoupon['title'],
            'title2'   => $rawCoupon['title2'],
            'title3'   => $rawCoupon['title3'],
            'title4'   => $rawCoupon['title4'],
            'title5'   => $rawCoupon['title5'],
            'distance' => number_format($rawCoupon['distance'], 2, '.', ' ').' miles'
        );

        if (array_key_exists($merchant_id, $addedMerchants)) {
            $results[$addedMerchants[$merchant_id]]['coupons'][] = $coupon;
        } else {
            $results[] = array(
                'merchant_id' => $merchant_id,
                'merchant_accountname' => $merchant_name,
                'coupons' => array($coupon)
            );

            $addedMerchants[$merchant_id] = count($results) - 1;
        }
    }

    return $api->respond(200, array(     
        'count'   => $res->rowCount(),
        'results' => $results
    ));
}

function GetCouponsRedeems() {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;
    $req = $app->request;
    $log = $app->log;

    $user_id = $req->params('user_id') ? intval($req->params('user_id')) : 0;
    $merchant_id = $req->params('merchant_id');
    $coupon_id = $req->params('coupon_id') ? intval($req->params('coupon_id')) : 0;
    $phone_id = $req->params('phone_id') ? intval($req->params('phone_id'))  : 0;
    $email = $req->params('email');
    $since = $req->params('since') ? intval($req->params('since')) : (time() - 60 * 60 * 24);
    $until = $req->params('until') ? intval($req->params('until')) : 0;

    $rqb = $app->db->createQueryBuilder();
    $rqb->select('u.*, usr.name as user_name')
        ->from('rtui_coupons_app_usage', 'u')
        ->leftJoin('u', 'rtui_users', 'usr', 'usr.id = u.user_id')
        ->where(
            $rqb->expr()->gte('u.usage_date', ':since')
        )
        ->setParameter('since', date('Y-m-d H:i:s', $since));

    if ($user_id) {
        $rqb->andWhere(
                $rqb->expr()->gte('u.user_id', ':user_id')
            )
            ->setParameter('user_id', $user_id);
    }
    if ($merchant_id) {
        $rqb->leftJoin('u', 'rtui_coupons', 'c', 'c.id = u.coupon_id')
            ->andWhere(
                $rqb->expr()->gte('c.merchant_id', ':merchant_id')
            )
            ->setParameter('merchant_id', $merchant_id);
    }
    if ($coupon_id) {
        $rqb->andWhere(
                $rqb->expr()->gte('u.coupon_id', ':coupon_id')
            )
            ->setParameter('coupon_id', $coupon_id);
    }
    if ($phone_id) {
        $rqb->andWhere(
                $rqb->expr()->gte('u.phone_id', ':phone_id')
            )
            ->setParameter('phone_id', $phone_id);
    }
    if ($email) {
        $rqb->andWhere(
                $rqb->expr()->gte('u.email', ':email')
            )
            ->setParameter('email', $email);
    }
    if ($until) {
        $rqb->andWhere(
            $rqb->expr()->lte('u.usage_date', ':until')
            )
            ->setParameter('until', date('Y-m-d H:i:s', $until));
    }
    $redeems = $rqb->execute()->fetchAll();

    $app->log->info('GetCouponsRedeems: found ' + count($redeems) + 'redeems');
    $api->respond(200, array(
        'redeems' => $redeems
    ));
}


function GetAds() {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;
    
    $qb = $app->db->createQueryBuilder();
    $qb->select(
        'cp.filename AS image', 'cp.website AS url', 'cp.business_type AS type')
        ->from('rtui_coupons', 'cp')                
        ->where($qb->expr()->orX(
       				$qb->expr()->eq('cp.business_type', 3),
       				$qb->expr()->eq('cp.business_type', 4)
   				));
    $ads = $qb->execute()->fetchAll();

    if (! $ads) {
        $api->error(404, 207, 'no as not found');
    }

    $app->log->info('GetAds: requested ads');
    $api->respond(200, array(
        'ads' => $ads
    ));
}


function GetCoupon($id) {

    $app = \Slim\Slim::getInstance();
    $api = $app->api;
    $req = $app->request;

    $email = $req->params('email');


    $id = intval($id);
    if (! $id) {
        $api->error(400, 206, 'invalid coupon id');
    }

    $qb = $app->db->createQueryBuilder();
    $qb->select(
        'cp.id', 'cp.merchant_id', 'cp.name', 'cp.description', 'cp.filename',
        'cp.title', 'cp.title2', 'cp.title3', 'cp.title4', 'cp.title5',
        'cp.alias', 'cp.status', 'cp.number', 'cp.website', 'cp.phone',
        'cr.rating_sum/cr.rating_count AS rating', 'cp.catid', 'cp.contract',
        'cp.businessemail as business_email', 'ct.title AS category_title', 'cp.catid AS category_id',
        'cp.store', 'cp.address', 'cp.city', 'cp.zip', 'cp.state', 'cp.lat',
        'cp.lng', 'cp.cycle', 'cp.keywords', 'cp.business_type AS type',
        'UNIX_TIMESTAMP(cp.created_date) as created_date',
        'UNIX_TIMESTAMP(cp.modified_date) as modified_date',
        'UNIX_TIMESTAMP(cp.expire_date) as expire_date',
        'cp.pid', 'cp.width', 'cp.height')
        ->from('rtui_coupons', 'cp')
        ->leftJoin('cp', 'rtui_coupons_vote', 'cr', 'cr.content_id = cp.id')
        ->leftJoin('cp', 'rtui_categories', 'ct', 'ct.id = cp.catid')
        ->where(
            $qb->expr()->eq('cp.id', ':id')
        )
        ->setParameter('id', $id)
        ->setMaxResults(1);
    $coupon = $qb->execute()->fetch();

    if (!$coupon) {
        $api->error(404, 207, 'coupon not found');
    }

    // if ($_SERVER['REMOTE_ADDR'] == '46.102.175.121') {    
    //}

    //get Coupon Merchant Place Information

    $qb = $app->db->createQueryBuilder();    
    $qb->select('place.id', 'place.name', 'place.types', 'place.url', 'place.website', 'place.vicinity', 'place.address', 'place.city', 
            'place.state','place.zip','place.formatted_address','place.phone_number','place.international_phone_number',
            'place.lat','place.lng','place.opening_hours','place.images','place.events','place.rating',
            'place.rating_reference','place.reviews')
        ->from('rtui_coupons_places', 'place')
        ->where(
            $qb->expr()->eq('place.id', ':id')
        )
        ->setParameter('id',  $coupon['pid'])       
        ->setMaxResults(1);
    $place = $qb->execute()->fetch();     

    if ($place) {       
        if (isset($place['opening_hours']) && !empty($place['opening_hours'])) {            
            $opening_hours = json_decode($place['opening_hours']);
            if (isset($opening_hours->weekday_text)) {        
                $place['opening_hours'] = $opening_hours->weekday_text;        
            }
        }
    }
    
    //if ($_SERVER['REMOTE_ADDR'] == '46.102.175.121') {    }

    //get Merchant Active Campaigns
    $qb = $app->db->createQueryBuilder();    
    $qb->select('campaigns.*')
        ->from('rtui_crm_accounts_campaigns', 'campaigns')
        ->where(
            $qb->expr()->eq('campaigns.account_id', ':id'),
            $qb->expr()->gte('campaigns.end_date', 'NOW()')
        )
        ->setParameter('id',  $coupon['merchant_id']);
    $campaigns = $qb->execute()->fetchAll(); 

    //get Merchant Active PunchCard
    //if ($_SERVER['REMOTE_ADDR'] == '46.102.175.121') { }
    $punchcards = array();

    if (empty($email)) {
      $qb = $app->db->createQueryBuilder();    
      $qb->select('punchcards.id', 'punchcards.account_id', 'punchcards.punch_name', 'punchcards.punch_offer', 
                  'punchcards.punch_image', 'punchcards.punch_limit')
          ->from('rtui_crm_accounts_punchcards', 'punchcards')
          ->where(
              $qb->expr()->eq('punchcards.account_id', ':id')
          )
          ->setParameter('id',  $coupon['merchant_id']);
        $dbpunchcards = $qb->execute()->fetchAll(); 
        
        if ($dbpunchcards) {
            $punchcards = $dbpunchcards;
        }
    }
    else
    {    

      $qb = $app->db->createQueryBuilder();    
      $qb->select('punchcards.id', 'punchcards.account_id', 'punchcards.punch_name', 'punchcards.punch_offer', 
                  'punchcards.punch_image', 'punchcards.punch_limit')
          ->from('rtui_crm_accounts_punchcards', 'punchcards')
          ->where(
              $qb->expr()->eq('punchcards.account_id', ':id')
          )
          ->setParameter('id',  $coupon['merchant_id']);
      $dbpunchcards = $qb->execute()->fetchAll(); 
      
      if ($dbpunchcards) {
          $dbpunchcards[0]['punch_usage'] = "0";
          $punchcards = $dbpunchcards;
      }

      $qb = $app->db->createQueryBuilder();    
      $qb->select('p.id', 'p.account_id', 'p.punch_name', 'p.punch_offer', 'p.punch_image', 'p.punch_limit', 'count(pu.id) as punch_usage')
          ->from('rtui_crm_accounts_punchcards', 'p')
          ->leftJoin('p', 'rtui_crm_accounts_punchcards_usage', 'pu', 'p.id = pu.punchcard_id')
          ->where(
              $qb->expr()->eq('p.account_id', ':id'),
              $qb->expr()->eq('pu.user_email', ':email')
          )
          ->setParameter('id',  $coupon['merchant_id'])
          ->setParameter('email',  $email);
        $dbpunchcards = $qb->execute()->fetchAll(); 
        
        if ($dbpunchcards && !empty($dbpunchcards[0]['id'])) {
           foreach ($dbpunchcards as $key => $value) {
            $pu = (float) $dbpunchcards[$key]['punch_usage'];
            $pl = (float) $dbpunchcards[$key]['punch_limit'];
            $dbpunchcards[$key]['punch_usage'] = (string) fmod($pu, $pl);
          }
          $punchcards = $dbpunchcards;
        } 

    }
    //$punchcards[0]['punch_usage'] = 0;
    //get GoogleStaticMap
    $googleStaticMap        = getGoogleStaticMap($coupon['lat'], $coupon['lng'], $coupon['id']);

    //get Merchant Image or Category Image
    $merchant_image = '';

    if ( !empty($place['images']) ) {       
        $merchanLogo = json_decode($place['images']);
        $merchanLogo = $merchanLogo[0];
        $merchant_image =  $app->config('merchant_default_image_url').$merchanLogo;
    } else {
        $merchant_default_image_path = $app->config('merchant_default_image_path');
        if (file_exists($merchant_default_image_path.$coupon['category_id'].'.jpg')) {
            $merchant_image  =  $app->config('merchant_default_image').$coupon['category_id'].'.jpg';
        } else {
            $merchant_image  = $app->config('merchant_default_image').'noimage.jpg';
        }
    }

   // $punchcards = var_dump(getallheaders());

    $app->log->info('GetCoupon: requested coupon with id ' . $id);
    $api->respond(200, array(
        'coupon_image_url'              => $app->config('coupon_image_url'),        
        'coupon'                        => $coupon,        
        'merchant_image'                => $merchant_image,        
        'merchant_default_image_url'    => $app->config('merchant_default_image_url'),
        'place'                         => $place,
        'merchant_campaigns_card_url'   => $app->config('merchant_campaigns_card_url'),    
        'campaigns'                     => $campaigns,
        'merchant_punchcard_url'        => $app->config('merchant_punchcard_url'), 
        'punchcards'                    => $punchcards,
        'googleStaticMap'               => $googleStaticMap,
    ));    
}

function UpdateCoupon($id) {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;
    $req = $app->request;

    // Check if coupon exists
    $cqb = $app->db->createQueryBuilder();
    $cqb->select('cp.*')
        ->from('rtui_coupons', 'cp')
        ->where(
            $cqb->expr()->eq('cp.id', ':id')
        )
        ->setParameter('id', $id)
        ->setMaxResults(1);

    $ccp = $cqb->execute()->fetch();
    if (! $ccp) {
        $api->error(404, 207, 'coupon not found');
    }

    // Update each field
    $coupon = array('modified_date' => date( 'Y-m-d H:i:s'));
    foreach ($req->params() as $k => $v) {
        switch ($k) {
        // Fields that have the same name in the database
        case 'contract':
        case 'title':
        case 'store':
        case 'city':
        case 'state':
        case 'zip':
        case 'address':
        case 'description':
        case 'keywords':
        case 'phone':
            $coupon[$k] = $v;
            break;

        // Fields with different names
        case 'category':
            $coupon['catid'] = $v;
            break;
        case 'business':
            $coupon['name'] = $v;
            break;
        case 'business_email':
            $coupon['businessemail'] = $v;
            break;
        case 'store_number':
            $coupon['number'] = $v;
            break;
        case 'expire':
            $coupon['expire_date'] = $v;
            break;
        case 'note':
            $coupon['special_note'] = $k;
        case 'website':
            $coupon['website'] = $v;
            $coupon['merchant_websiteurl'] = $v;
            break;
        case 'cycle':
            $coupon[$k] = $v;
            $coupon['merchant_cyclename'] = $v;
            break;

        // Fields that need special logic
        case 'business':
            $coupon['name'] = $v;
            $coupon['filename'] = $id . '-' . _genSlug($v) . '.jpg';
            break;

        case 'merchant':
            $mqb = $app->db->createQueryBuilder();
            $mqb->select('m.*')
                ->from('rtui_crm_accounts', 'm')
                ->where(
                    $mqb->expr()->eq('m.accountid', ':id')
                )
                ->setParameter('id', $v)
                ->setMaxResults(1);
            $merchant = $mqb->execute()->fetch();
            if (! $merchant) {
                $api->error(404, AE_PARAM_INVALID, 'merchant not found');
            }

            $coupon = array_merge($coupon, array(
                'merchant_id' => $merchant['accountid'],
                'merchant_accountname' => $merchant['name'],
                'merchant_address' => $merchant['address1_line1'],
                'merchant_city' => $merchant['address1_city'],
                'merchant_state' => $merchant['address1_stateorprovince'],
                'merchant_zip' => $merchant['address1_postalcode'],
                'merchant_phone' => $merchant['telephone1'],
                'merchant_contactemail' => $merchant['emailaddress1'],
            ));
            break;
        default:
            $api->error(404, AE_PARAM_INVALID, 'invalid parameter: ' . $k);
            break;
        }
    }

    // Rename coupon images if filename has changed
    if (array_key_exists('filename', $coupon)) {
        list($ren, $err) = _coupImageManager(array(
            'action' => 'rename',
            'old_filename' => $ccp['filename'],
            'filename' => $coupon['filename'],
        ));

        if ($err != null) {
            $app->log->critical('error renaming coupon images ' .
                                $coupon['id'] . ': ' . $err);
            $api->error(500, -1, 'something bad happend');
        }
    }

    $newcp = array_merge($ccp, $coupon);

    // Upload new images
    $fw = array('action' => 'upload', 'filename' => $newcp['filename']);
    foreach (array('list', 'details') as $type) {
        $k = $type . '_img';
        if (! array_key_exists($k, $_FILES)) continue;

        $f = $_FILES[$k];
        $fw[$type] = new CurlFile($f['tmp_name'], $f['type'], $f['name']);
    }
    if (count($fw) > 2) {
        list($upl, $err) = _coupImageManager($fw);
        if ($err != null) {
            $app->log->critical('error uploading coupon images ' .
                                $coupon['id'] . ': ' . $err);
            $api->error(500, -1, 'something bad happend');

        }
    }

    $app->db->update('rtui_coupons', $coupon, array('id' => $id));

    $app->log->info('UpdateCoupon: modified coupon ' . $id);
    $api->respond(200, array(
        'message' => 'success'
    ));
}


function PostCoupon() {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;
    $req = $app->request;


    $req_fields = array(
        'merchant', 'contract', 'title', 'category', 'business', 'store',
        'store_number', 'cycle', 'expire', 'address', 'city', 'state', 'zip'
    );
    $params = $req->params();
    foreach ($req_fields as $field) {
        if (array_key_exists($field, $params)) continue;
        $api->error(400, AE_PARAM_MISSING, 'missing parameter: ' . $field);
    }
    foreach (array('list_img', 'details_img') as $file) {
        if (!array_key_exists($file, $_FILES)) {
            $api->error(400, AE_PARAM_MISSING, 'missing file: ' . $file);
        }
    }

    $lext = pathinfo($_FILES['list_img']['name'], PATHINFO_EXTENSION);
    $dext = pathinfo($_FILES['details_img']['name'], PATHINFO_EXTENSION);

    if (($lext != 'jpg' && $lext != 'jpeg') ||
        ($dext != 'jpg' && $dext != 'jpeg')) {
            $api->error(400, AE_PARAM_INVALID, 'JPEG images required');
    }

    $mid = $req->params('merchant');
    $mqb = $app->db->createQueryBuilder();
    $mqb->select('m.*')
        ->from('rtui_crm_accounts', 'm')
        ->where(
            $mqb->expr()->eq('m.accountid', ':id')
        )
        ->setParameter('id', $mid)
        ->setMaxResults(1);

    $merchant = $mqb->execute()->fetch();
    if (! $merchant) {
        $api->error(404, AE_PARAM_INVALID, 'merchant not found');
    }

    $zqb = $app->db->createQueryBuilder();
    $zqb->select('z.zc_lat AS lat', 'z.zc_lon AS lng')
        ->from('rtui_coupons_zipcodes2', 'z')
        ->where('zc_zip = :zip')
        ->setParameter('zip', $req->params('zip'), 'string')
        ->setMaxResults(1);

    $coords = $zqb->execute()->fetch();
    if (! $coords) {
        $api->error(400, AE_PARAM_INVALID, 'Invalid Zip Code! Please correct and try again.');
    }

    $coupon = array(
        'contract' => $req->params('contract'),
        'title' => $req->params('title'),
        'name' => $req->params('business'),
        'store' => $req->params('store'),
        'number' => $req->params('store_number'),
        'cycle' => $req->params('cycle'),
        'created_date' => date('Y-m-d H:i:s'),
        'expire_date' => date('Y-m-d H:i:s', intval($req->params('expire'))),
        'address' => $req->params('address'),
        'city' => $req->params('city'),
        'state' => $req->params('state'),
        'zip' => $req->params('zip'),
        'lat' => $coords['lat'],
        'lng' => $coords['lng'],
        'catid' => $req->params('category'),
        'source' => 1,

        'merchant_id' => $merchant['accountid'],
        //TODO: 'merchant_chainstoreid' => $merchant[''],
        //TODO: 'merchant_chainidname' => $merchant[''],
        'merchant_accountname' => $merchant['name'],
        'merchant_cyclename' => $req->params('cycle'),
        'merchant_address' => $merchant['address1_line1'],
        'merchant_city' => $merchant['address1_city'],
        'merchant_state' => $merchant['address1_stateorprovince'],
        'merchant_zip' => $merchant['address1_postalcode'],
        'merchant_phone' => $merchant['telephone1'],
        'merchant_contactemail' => $merchant['emailaddress1'],

        'modified_date' => date( 'Y-m-d H:i:s')
    );

    $business_email = $req->params('business_email');
    if ($business_email) {
        $coupon['businessemail'] = $business_email;
    }

    $website = $req->params('website');
    if ($website) {
        $coupon['website'] = $website;
        $coupon['merchant_websiteurl'] = $website;
    }

    $description = $req->params('description');
    if ($description) {
        $coupon['description'] = $description;
    }

    $notes = $req->params('note');
    if ($notes) {
        $coupon['special_note'] = $notes;
    }

    $keywords = $req->params('keywords');
    if ($keywords) {
        $coupon['keywords'] = $keywords;
    }

    $phone = $req->params('phone');
    if ($phone) {
        $coupon['phone'] = $phone;
    }

    $type = $req->params('type');
    if ($type) {
        $type = array_search($type, $type_names);
        if ($type === false) {
            $api->error(400, 417, 'invalid type');
        }
        $coupon['type'] = $type;
    }

    // Generate a slug for the coupon
    $slug = _genSlug($coupon['name']);

    $cid = intval($req->params('id'));
    if ($cid) {
        $coupon['id'] = $cid;
        $coupon['filename'] = $coupon['id'] . '-' . $slug . '.jpg';
        $app->db->update('rtui_coupons', $coupon, array('id' => $cid));
    } else {
        $app->db->insert('rtui_coupons', $coupon);
        $coupon['id'] = $app->db->lastInsertId();
        $coupon['filename'] = $coupon['id'] . '-' . $slug . '.jpg';
        $app->db->update('rtui_coupons', array(
            'filename' => $coupon['filename']
        ), array('id' => $coupon['id']));
    }


    $fw = array('action' => 'upload', 'filename' => $coupon['filename']);
    foreach (array('list', 'details') as $type) {
        $k = $type . '_img';
        if (! array_key_exists($k, $_FILES)) continue;

        $f = $_FILES[$k];
        $fw[$type] = new CurlFile($f['tmp_name'], $f['type'], $f['name']);
    }
    if (count($fw) > 2) {
        list($upl, $err) = _coupImageManager($fw);
        if ($err != null) {
            $app->log->critical('error uploading coupon images ' .
                                $coupon['id'] . ': ' . $err);
            $api->error(500, -1, 'something bad happend');

        }
    }

    $app->log->info('PostCoupon: added coupon ' . $coupon['id']);
    $api->respond(200, array(
        'message' => 'success',
        'id' => $coupon['id']
    ));
}

function DeleteCoupon($id) {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;

    $id = intval($id);
    if (! $id) {
        $api->error(400, AE_PARAM_INVALID, 'invalid parameter: id');
    }

    $qb = $app->db->createQueryBuilder();
    $qb->select('cp.filename')
        ->from('rtui_coupons', 'cp')
        ->where(
            $qb->expr()->eq('cp.id', ':id')
        )
        ->setParameter('id', $id, PDO::PARAM_INT)
        ->setMaxResults(1);
    $coupon = $qb->execute()->fetch();

    if (! $coupon) {
        $api->error(404, 207, 'coupon not found');
    }

    $qb = $app->db->createQueryBuilder();
    $qb->delete('rtui_coupons')
        ->where(
            $qb->expr()->eq('id', ':id')
        )
        ->setParameter('id', $id, PDO::PARAM_INT);
    $qb->execute();

    list($upl, $err) = _coupImageManager(array(
        'action' => 'remove',
        'filename' => $coupon['filename'],
    ));
    if ($err != null) {
        $app->log->critical('error deleting coupon images ' .
                            $coupon['id'] . ': ' . $err);
        $api->error(500, -1, 'something bad happend');

    }

    $app->log->info('DeleteCoupon: deleted coupon ' . $id);
    $api->respond(200, array(
        'message' => 'success'
    ));
}
function RedeemCoupon($id) {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;
    $req = $app->request;
    $log = $app->log;

    $email 		= $req->params('email');
    $username 	= $req->params('username');
    $coords 	= $req->params('coord');
    $phoneID 	= $req->params('phone_id') ? $req->params('phone_id') : 'NA';
    $receipt 	= !! $req->params('receipt');
    $user_id    = $req->params('user_id');

    $id = intval($id);
    if (! $id) {
        $api->error(400, 206, 'invalid coupon id');
    }
    if (! $email) {
        $api->error(400, 208, 'email is required');
    }
    if ($coords) {
        if ($coords) {
            list($lat, $lng) = sscanf($coords, "%g,%g");
            if (! $lat || ! $lng) {
                $api->error(400, 202, 'invalid coordinates');
            }

            $coords = array(
                'lat' => $lat,
                'lng' => $lng
            );
        }
    }

    $cqb = $app->db->createQueryBuilder();
    $cqb->select('c.*')
        ->from('rtui_coupons', 'c')
        ->where(
            $cqb->expr()->eq('c.id', ':id')
        )
        ->setParameter('id', $id)
        ->setMaxResults(1);
    $coupon = $cqb->execute()->fetch();

    if (! $coupon) {
        $api->error(404, 207, 'coupon not found');
    }

    $merchant_id = $coupon['merchant_id'];
    $catid       = $coupon['catid'];

    //check user pref cat
    $upc = $app->db->createQueryBuilder();
    $upc->select('u.*')
        ->from('rtui_users_app_preferences', 'u')
        ->where(
            $upc->expr()->eq('u.email', ':email')
        )
        ->setParameter('email', $email)
        ->setMaxResults(1);
    $upcat = $upc->execute()->fetch();
    $categories = $upcat['categories'];
    //

    $redeem = array(
        'coupon_id' => $id,
        'emails' 	=> $email,
        'username' 	=> $username,
        'phone_id' 	=> $phoneID,
        'user_id'   => $user_id
    );

    if ($coords) {
      $redeem['lat'] = $coords['lat'];
      $redeem['lng'] = $coords['lng'];
    } else {
      $coords['lat'] = $coupon['lat'];
      $coords['lng'] = $coupon['lng'];
      $redeem['lat'] = $coupon['lat'];
      $redeem['lng'] = $coupon['lng'];
    }

    if ($receipt) {
        $cqb = $app->db->createQueryBuilder();
        $cqb->select(
            'cp.id', 'cp.merchant_id', 'cp.name', 'cp.description', 'cp.filename',
            'cp.title', 'cp.title2', 'cp.title3', 'cp.title4', 'cp.title5',
            'cp.alias', 'cp.status', 'cp.number', 'cp.website', 'cp.phone',
            'cp.catid', 'cp.contract', 'cp.businessemail as business_email',
            'cp.store', 'cp.address', 'cp.city', 'cp.zip', 'cp.state', 'cp.lat',
            'cp.lng', 'cp.cycle', 'cp.keywords', 'cp.business_type AS type', 'cp.expire_date', 
            'UNIX_TIMESTAMP(cp.created_date) as created_date',
            'UNIX_TIMESTAMP(cp.modified_date) as modified_date',
            'UNIX_TIMESTAMP(cp.expire_date) as expire_date')
            ->from('rtui_coupons', 'cp')
            ->where(                
                $cqb->expr()->neq('cp.merchant_id', ':merchant_id'),
                $cqb->expr()->neq('cp.catid', ':catid'),
                $cqb->expr()->in('cp.catid', array($categories)),
                $cqb->expr()->eq('cp.business_type', 0),
                $cqb->expr()->isNotNull('cp.filename'),
                $cqb->expr()->gte('cp.expire_date', 'NOW()')
            )
            ->setParameter('merchant_id', $merchant_id)
            ->setParameter('catid', $catid)
            ->setMaxResults(3);

        $bounds = _boundingCoordinates($coords, 5);
        $distanceFormula = 'acos(sin(:radlat) * sin(cp.lat * pi()/180) + '.
                            'cos(:radlat) * cos(cp.lat * pi()/180) * '.
                            'cos(cp.lng * pi()/180 - (:radlng)))';

        $cqb->andWhere($cqb->expr()->andX(
                $cqb->expr()->eq('cp.status', 1),
                $cqb->expr()->isNotNull('cp.lat'),
                $cqb->expr()->isNotNull('cp.lng'),
                $cqb->expr()->gt('cp.lat', $bounds['minlat']),
                $cqb->expr()->lt('cp.lat', $bounds['maxlat']),
                $cqb->expr()->gt('cp.lng', $bounds['minlng']),
                $cqb->expr()->lt('cp.lng', $bounds['maxlng']),
                $cqb->expr()->lte($distanceFormula, ':raddist')
            ))
            ->setParameter('radlat', $coords['lat'] * DEG_TO_RAD)
            ->setParameter('radlng', $coords['lng'] * DEG_TO_RAD)
            ->setParameter('raddist', 5 / EARTH_RADIUS)
            ->orderBy($distanceFormula, 'ASC')
            ->groupBy('cp.merchant_id');
        
        $emailer = $app->email;
        $emailer->addAddress($email);
        $emailer->Subject = $coupon['name'];
        $emailer->isHTML(true);

        // Render mail template
        $emailer->Body = $app->template->render(
            'email/coupon_redeem_receipt',
            array('coupon' => $coupon, 'nearby' => $cqb->execute()->fetchAll())
        );

        if (!$emailer->send()) {
            $app->log->critical('error sending email: ' . $emailer->ErrorInfo);
            $api->error(500, -1, 'something bad happend');
        }
    }

    // update redeem table
    $app->db->insert('rtui_coupons_app_usage', $redeem);

    //$app->log->info('RedeemCoupon: coupon with id ' .$id. ' redeemed by '.$email);
    
    $api->respond(200, array(
        'message' => 'success'
    ));
}

function RateCoupon($id) {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;
    $req = $app->request;

    $rating = $req->params('rating');

    $id = intval($id);
    if (! $id) {
        $api->error(400, 206, 'invalid coupon id');
    }

    if (! $rating) {
        $api->error(400, 211, 'rating is required');
    }

    if (!is_numeric($rating)) {
        $api->error(400, 212, 'invalid rating of ' . $rating);
    }

    $rating = intval($rating);
    if ($rating < 0 || $rating > 5) {
        $api->error(400, 212, 'invalid rating of ' . $rating);
    }

    $cqb = $app->db->createQueryBuilder();
    $cqb->select('c.id')
        ->from('rtui_coupons', 'c')
        ->where(
            $cqb->expr()->eq('c.id', ':id')
        )
        ->setParameter('id', $id)
        ->setMaxResults(1);
    $coupon = $cqb->execute()->fetch();

    if (! $coupon) {
        $api->error(404, 207, 'coupon not found');
    }

    $crqb = $app->db->createQueryBuilder();
    $crqb->select('cr.content_id', 'cr.rating_sum', 'cr.rating_count')
        ->from('rtui_coupons_vote', 'cr')
        ->where(
            $crqb->expr()->eq('cr.content_id', ':id')
        )
        ->setParameter('id', $id)
        ->setMaxResults(1);
    $currentRating = $crqb->execute()->fetch();

    if ($currentRating) {
        $app->db->update('rtui_coupons_vote', array(
            'rating_sum' => $currentRating['rating_sum'] + $rating,
            'rating_count' => $currentRating['rating_count'] + 1
        ), array(
            'content_id' => $id
        ));
    } else {
        $app->db->insert('rtui_coupons_vote', array(
            'content_id' => $id,
            'rating_sum' => $rating,
            'rating_count' => 1
        ));
    }

    $app->log->info('RateCoupon: cast a vote of ' .$rating. ' on coupon '.$id);
    $api->respond(200, array(
        'message' => 'success'
    ));
}

function GetMerchantCoupons($id) {
    $app = \Slim\Slim::getInstance();
    $api = $app->api;
    $req = $app->request;

    $phone = $req->params('phone');
    $coords = $req->params('coords');
    $zip = $req->params('zip');
    $radius = $req->params('radius') ?: 5;
    $types = $req->params('types');
    $start = $req->params('start') ? intval($req->params('start')) : 0;
    $limit = $req->params('limit') ? intval($req->params('limit')) : 20;

    if ($coords) {
        list($lat, $lng) = sscanf($coords, "%g,%g");
        if (! $lat || ! $lng) {
            $api->error(400, 202, 'invalid coordinates');
        }

        $coords = array(
            'lat' => $lat,
            'lng' => $lng
        );
    }

    if (! $coords && $zip) {
        $qb = $app->db->createQueryBuilder();
        $qb->select('z.zc_lat AS lat', 'z.zc_lon AS lng')
            ->from('rtui_coupons_zipcodes2', 'z')
            ->where('zc_zip = :zip')
            ->setParameter('zip', $zip, 'string')
            ->setMaxResults(1);

        $coords = $qb->execute()->fetch();
        if (! $coords) {
            $api->error(400, 203, 'Invalid Zip Code! Please correct and try again.');
        }
    }

    if ($coords) {
        if ($radius < 1 || $radius > 25) {
            $api->error(400, 204, 'radius must be between 1 and 25');
        }
    }
    if ($phone) {
        $newPhone = preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '($1) $2-$3',
            $phone);

        if ($newPhone == $phone) {
            $api->error(400, 216, 'invalid phone filter');
        }

        $phone = $newPhone;
    }

    $qb = $app->db->createQueryBuilder();
    $qb->select('m.id')
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

    $qb = $app->db->createQueryBuilder();
    $qb->select(
        'cp.id', 'cp.merchant_id', 'cp.name', 'cp.description', 'cp.filename',
        'cp.title', 'cp.title2', 'cp.title3', 'cp.title4', 'cp.title5',
        'cp.alias', 'cp.status', 'cp.number', 'cp.website', 'cp.phone',
        'cr.rating_sum/cr.rating_count AS rating', 'cp.catid', 'cp.source',
        'ct.title AS category_title', 'cp.store', 'cp.address', 'cp.city',
        'cp.zip', 'cp.state',  'cp.lat', 'cp.lng', 'cp.cycle', 'cp.business_type AS type',
        'UNIX_TIMESTAMP(cp.created_date) as created_date',
        'UNIX_TIMESTAMP(cp.modified_date) as modified_date',
        'UNIX_TIMESTAMP(cp.expire_date) as expire_date')
        ->from('rtui_coupons', 'cp')
        ->leftJoin('cp', 'rtui_coupons_vote', 'cr', 'cr.content_id = cp.id')
        ->leftJoin('cp', 'rtui_categories', 'ct', 'ct.id = cp.catid')
        ->where($qb->expr()->andX(
            //$qb->expr()->eq('cp.business_type', 0),
            $qb->expr()->isNotNull('cp.filename'),
            $qb->expr()->gte('cp.expire_date', 'NOW()'),
            $qb->expr()->eq('cp.merchant_id', ':id'),
            $phone ? $qb->expr()->eq('cp.phone', ':phone') : null
        ))
        ->setFirstResult($start)
        ->setMaxResults($limit)
        ->setParameter('id', $id)
        ->setParameter('phone', $phone);

    if ($coords) {
        $bounds = _boundingCoordinates($coords, $radius);
        $distanceFormula = 'acos(sin(:radlat) * sin(cp.lat * pi()/180) + '.
                            'cos(:radlat) * cos(cp.lat * pi()/180) * '.
                            'cos(cp.lng * pi()/180 - (:radlng)))';

        $qb->andWhere($qb->expr()->andX(
                $qb->expr()->gt('cp.lat', $bounds['minlat']),
                $qb->expr()->lt('cp.lat', $bounds['maxlat']),
                $qb->expr()->gt('cp.lng', $bounds['minlng']),
                $qb->expr()->lt('cp.lng', $bounds['maxlng']),
                $qb->expr()->lte($distanceFormula, ':raddist')
            ))
            ->setParameter('radlat', $coords['lat'] * DEG_TO_RAD)
            ->setParameter('radlng', $coords['lng'] * DEG_TO_RAD)
            ->setParameter('raddist', $radius / EARTH_RADIUS)
            ->orderBy($distanceFormula, 'ASC');
    }

    if ($types) {
        global $type_names;

        $type_filter = array();
        $types = explode(',', $types);
        foreach($types as $type) {
            $type_idx = array_search($type, $type_names);
            if ($type_idx === false){
                $api->error(400, 217, 'invalid type');
            }
            $type_filter[] = $type_idx;
        }
        $qb->andWhere(
            $qb->expr()->in('cp.business_type', $type_filter)
        );
    };

    $res = $qb->execute();

    // Count total number of results
    $count = $res->rowCount();
    if ($start > 0 || $count == $limit) {
        $qb->select('count(cp.id) as count')
           ->setFirstResult(0)
           ->setMaxResults(1);
        $cntRes = $qb->execute()->fetch();
        $count = $cntRes['count'];
    }

    $coupons = $res->fetchAll();
    array_walk($coupons, function (&$coupon) {
        global $type_names;
        $coupon['type'] = $type_names[intval($coupon['type'])];
    });

    $app->log->info(sprintf('GetMerchantCoupons: returned %d coupons out of %d'.
                            ' for merchant %s', $res->rowCount(), $count, $id));
    return $api->respond(200, array(
        'count' => $count,
        'coupons' => $coupons
    ));
}
