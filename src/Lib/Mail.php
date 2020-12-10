<?php
namespace Sy\Bootstrap\Lib;

class Mail extends \Sy\Mail {

	public function __construct($to = '', $from = '', $subject = '', $body = '') {
		parent::__construct($to, $subject, $body);
		$this->setFrom($from);

		$config = SMTP_CONFIG;
		$this->setSmtp($config['host'], $config['username'], $config['password'], $config['encryption'], $config['port']);
	}

}