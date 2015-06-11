<?php

# Common API Errors
define("AE_NOT_FOUND", 1);
define("AE_PARAM_MISSING", 2);
define("AE_PARAM_INVALID", 3);

function _StripInvalidUTF8($str){
    if(is_array($str)) return array_map('_StripInvalidUTF8', $str);
    return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
}

class ApiMiddleware extends \Slim\Middleware
{
    protected $app;

    public function __construct() {
        $this->app = \Slim\Slim::getInstance();
        $this->app->api = $this;

        $api = $this;
        $this->app->notFound(function() use ($api) {
            $api->error(404, 1, 'invalid resource');
        });
    }

    public function renderResponse($data=null) {
        if ($data === null) {
            $data = array();
        }

        $body = json_encode($data);
        // Check if the data contained invalid UTF-8 sequences, fix, and retry
        if (!$body && json_last_error() == JSON_ERROR_UTF8) {
            $body = json_encode(_StripInvalidUTF8($data));
        }
        // Throw exception if we didn't manage to fix
        if (!$body) throw new Exception(json_last_error_msg());

        return array('application/json', $body);
    }

    public function respond($status, $data=null, $stop=true) {
        $app = $this->app;

        list($contentType, $body) = $this->renderResponse($data);
        $response = $app->response();
        $response->status($status);
        $response->header('Content-Type', $contentType);
        $response->body($body);

        if ($stop) {
            $app->stop();
        }
    }

    public function error($status, $code, $message, $stop=true) {
        $error = array(
            'code' => $code,
            'message' => $message
        );

        $this->app->applyHook('api.error', $error);
        $this->respond($status, array('error' => $error), $stop);
    }

    public function call() {
        $app = \Slim\Slim::getInstance();

        if (! $app->config('api.noauth')){
            list($error, $message) = $this->checkAuthorization();
            if ($error >= 0) {
                $app->applyHook('api.unauthorized', array(
                    'code' => $error + 100,
                    'message' => $message
                ));
                $this->error(403, $error + 100, 'Unauthorized: ' .
                             $message, false);
                return;
            }
        }

        $this->next->call();
    }

    public function checkAuthorization() {
        $app = \Slim\Slim::getInstance();
        $req = $app->request;

        $authHdr = $req->headers->get('HTTP_AUTHORIZATION');
        if (! $authHdr) {
            return array(0, 'missing Authorization header');
        }

        // Header should have a value of 'Token APIKEY:SIG'
        $auth = explode(' ', $authHdr);
        if (count($auth) != 2 || $auth[0] != 'Token') {
            return array(1, 'invalid Authorization header');
        }

        $authParts = explode(':', $auth[1]);
        if (count($authParts) != 2) {
            return array(2, 'invalid Authorization header');
        }
        $this->session_id = $authParts[0];

        $qb = $app->db->creqteQueryBuilder();
        $qb->select('u.id', 'u.name', 'u.username', 'u.email', 'u.block')
            ->from('rtui_session', 's')
            ->leftJoin('s', 'rtui_users', 'u', 's.userid = u.id')
            ->where('s.session_id = :session_id')
            ->setParameter('session_id', $this->session_id)
            ->setMaxResults(1);

        $user = $qb->execute()->fetch();
        if (!$user) {
            $api->error(400, 403, 'invalid login or password');
        }

        if ($user['block']) {
            $api->error(400, 404, 'user is blocked');
        }

        $this->user = $user;

        return array(-1, 'Session ok');
    }
}
