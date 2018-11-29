<?php
require_once FCPATH . '/vendor/autoload.php';

defined('BASEPATH') OR exit('No direct script access allowed');

use phpseclib\Crypt\Rijndael;

class CryptoChat {
	public function __construct() {
		
	}
	
	private function linearSpace($a, $b, $n) {
		$step = ($b - $a) / ($n - 1);
		return range($a, $b, $step);
	}
	
	private function logisticMap(&$debug = null) {
		$n = 24;
		$a = 2.0;
		$b = 4;
		$rs = $this->linearSpace($a, $b, $n);
		$m = 100;
		$result = array();
		$debug = array(); // Just for debug
		for ($j = 0; $j < count($rs); $j++) {
			$r = $rs[$j];
			$x = array_fill(0, $m, 0);
			$x[0] = 0.5;
			for ($i = 1; $i < $m; $i++) {
				$x[$i] = $r * $x[$i - 1] * (1 - $x[$i - 1]);
			}
			$randomvalue = random_int(0, 255);
			$debug[] = $randomvalue;
			$result[$j] = round($x[$m - 1] * $randomvalue); // Multiply with random data to make it differ from another
		}
		return $result;
	}
	
	private function hanonMap(&$debug = null) {
		$a = 1.4;
		$b = 0.3;
		$n = 24;
		$iterations = 100;
		$x = array_fill(0, $iterations + 1, 0);
		$y = array_fill(0, $iterations + 1, 0);
		$result = array();
		$debug = array(); // Just for debug
		for ($j = 0; $j < $n; $j++) {
			for ($i = 0; $i < $iterations; $i++) {
			//py: return x = y + 1.0 - a *x *x, y = b * x
			$x[$i + 1] = 1 - ($a * $x[$i] ** 2) + $y[$i]; // ** similar to ^ in matlab
				$y[$i + 1] = $b * $x[$i];
			}
			$randomvalue = random_int(0, 255);
			$debug[] = $randomvalue;
			$result[$j] = round($x[$iterations - 1] * $randomvalue); // Multiply with random data to make it differ from another
		}
		return $result;
	}
	
	public function encrypt($plaintext, $pubKey) {
		// Hash the plaintext first
		$h256 = hash('sha256', $plaintext);
		
		// Determine which map should be used
		$probability = (rand() * microtime(true)) % 2; // Get modulo of (random number * current time) by 2
		// Generate key
		
		/*
			This is production block, it will not show the generated random array in both of logistic map and hanon map
		*/
		// ----- Begin Block -----
		$key = null;
		if ($probability == 0) {
			$key = $this->logisticMap();
		} else {
			$key = $this->hanonMap();
		}
		// ----- End Block -----
		
		/*
			This is development block, it will show the generated random array in both of logistic map and hanon map. Uncomment this block and var_dump to see the generated array
		*/
		// ----- Begin Block -----
		/*$key = null;
		$generatedrandom = array();
		if ($probability == 0) {
			$key = $this->logisticMap($generatedrandom);
		} else {
			$key = $this->hanonMap($generatedrandom);
		}*/
		// var_dump($generatedrandom);
		// ----- End Block -----
		
		// Convert key (array[24]) into string by performing base64 encode: resulting a string with length 32.
		$key = array_map('chr', $key); // Convert to ascii first
		$key = implode($key); // Join them as single string
		$key = base64_encode($key); // Encode to Base64
		
		// Generate ciphertext using Rijndael algorithm
		$ct = '';
		$cipher = new Rijndael(Rijndael::MODE_ECB); // ECB has no Initialization Vector (IV)
		$cipher->setBlockLength(256);
		$cipher->setKey($key);
		$ct = $cipher->encrypt($plaintext); // Result in raw binary format
		$ct = base64_encode($ct); // Make them insert-able (Human readable)
		
		// Encrypt the key using pubKey
		$enck = '';
		openssl_public_encrypt($key, $enck, $pubKey); // Result in raw binary format
		$enck = base64_encode($enck); // Make them insert-able (Human readable)
		
		// Pack encrypted messages
		$result = new stdClass();
		$result->ct = $ct;
		$result->enck = $enck;
		$result->h256 = $h256;
		
		// Return as json
		return json_encode($result);
	}
	
	public function decrypt($data, $privKey) {
		// Unpack the data first
		$unpacked = json_decode($data);
		
		// Rebuild key from enck
		$key = '';
		openssl_private_decrypt(base64_decode($unpacked->enck), $key, $privKey);
		
		// Decrypt ct using key
		$plaintext = '';
		$cipher = new Rijndael(Rijndael::MODE_ECB); // ECB has no Initialization Vector (IV)
		$cipher->setBlockLength(256);
		$cipher->setKey($key);
		$plaintext = $cipher->decrypt(base64_decode($unpacked->ct));
		
		// Calculate hash of message
		$hash = hash('sha256', $plaintext);
		
		// Compare hash, return result
		if ($hash == $unpacked->h256) {
			return $plaintext;
		} else {
			return "unable to decrypt message: Hash mismatch.";
		}
		
	}
}
