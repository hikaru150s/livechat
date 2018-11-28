<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rsa {
	public $config;
	
	public function __construct() {
		$this->config = array(
			"digest_alg" => "sha256",
			"private_key_bits" => 2048,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
			"config" => FCPATH . "securedassets/openssl.cnf"
		);
	}
	
	public function generate() {
		$res = openssl_pkey_new($this->config);
		openssl_pkey_export($res, $privKey, '', $this->config);
		$pubKey = openssl_pkey_get_details($res);
		$pubKey = $pubKey["key"];
		return array('private' => $privKey, 'public' => $pubKey);
	}
	
	public function encrypt($data, $pubKey) {
		openssl_public_encrypt($data, $encrypted, $pubKey);
		return $encrypted;
	}
	
	public function decrypt($data, $privKey) {
		openssl_private_decrypt($data, $decrypted, $privKey);
		return $decrypted;
	}
}