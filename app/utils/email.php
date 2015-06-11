<?php

$app->email = function () use ($app) {
    $log = $app->log;

    $emailConfig = $app->config('email');
    if (! $emailConfig) {
        $log->critical('No email configuration found');
    }

    if (!array_key_exists('smtp', $emailConfig)) {
        $log->critical('No email SMTP configuration found');
    }

    $smtpConfig = $emailConfig['smtp'];

    if (!array_key_exists('host', $smtpConfig)) {
        $log->critical('No email SMTP host found');
    }


    $emailer = new PHPMailer;
    $emailer->isSMTP();
    $emailer->Host = $smtpConfig['host'];
    if (array_key_exists('port', $smtpConfig)) {
        $emailer->Port = $smtpConfig['port'];
    }
    if (array_key_exists('username', $smtpConfig)) {
        $emailer->SMTPAuth = true;
        $emailer->Username = $smtpConfig['username'];
        $emailer->Password = $smtpConfig['password'];
    }
    if (array_key_exists('security', $smtpConfig)) {
        $emailer->SMTPSecure = $smtpConfig['security'];
    }

    if (array_key_exists('from', $emailConfig)) {
        $emailer->From = $emailConfig['from'];
    }

    if (array_key_exists('from_name', $emailConfig)) {
        $emailer->FromName = $emailConfig['from_name'];
    }

    return $emailer;
};
