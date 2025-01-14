<?php

class R4 {

	public static function retOkAPI($params=[]) {
		$params['ok'] = 1;
		echo json_encode($params);
	}


	public static function dieAPI($stat=0, $msg='', $obs='', $fields=[]) {
		if(count($fields)) $jsonfields = ', "errFields":'. json_encode($fields);
		echo '{"error": 1, "status": "'. $stat .'", "errMsg": "'. $msg .'", "errObs": "'. $obs .'"'. $jsonfields .'}';
		require 'r4iniend.php';
		die();
	}


	public static function setSession($index, $val) {
		$_SESSION[SYSTEMID][$index] = $val;
		return true;
	}


	public static function getSession($index) {
		if(!isset($_SESSION[SYSTEMID][$index])) return null;
		return $_SESSION[SYSTEMID][$index];
	}


	public static function intArray($val) {
		if(is_array($val)) {
			$arr = $val;
		} else {
			if(strpos($val, ',') !== false) {
				$arr = explode(',', $val);
			} else {
				$arr = [$val];
			}
		}

		foreach($arr as $item) {
			$ret[] = (int)$item;
		}

		return $ret;
	}


	public static function mergeNewArr($old, $new) {
		$merged  = [];
		$changed = [];

		foreach($old as $key => $val) {
			//Se o campo está informado no new
			if(array_key_exists($key, $new)) {
				//Se o valor novo é igual do old
				if($old[$key] == $new[$key]) {
					$merged[$key]  = $old[$key];
				} else {
					$merged[$key]  = $new[$key];
					$changed[$key] = $new[$key];
				}
			}
		}

		return [
			'merged'  => $merged,
			'changed' => $changed
		];
	}


	public static function zeroFill($value, $totalsize=3) {
		while(strlen($value) < $totalsize) $value = '0' . $value;
		return $value;
	}


	public static function numberMask($value, $mindec=2, $maxdec=0, $ifzero=null) {
		$mindec = (int)$mindec;
		if(($ifzero!==null) && ((float)$value==0)) return $ifzero;
		else {
			$value = (float)$value;
			if(is_numeric($value)) {
				if($maxdec > $mindec) {
					$value = number_format($value, $maxdec, ',', '.');
					$ct = $mindec+1+strpos($value, ',');
					while(strlen($value) > $ct) {
						if(substr($value, -1) == 0) {
							$value = substr($value, 0, -1);
						}
						else break;
					}
					return trim($value, ',');

				} else {
					return number_format($value, $mindec, ',', '.');
				}
			}
			else return $value;
		}
	}


	public static function numberUnmask($number) {
		$resp = str_replace('.', '', $number);
		$resp = trim(str_replace(',', '.', $resp));
		if(is_numeric($resp)) return $resp;
		else return $number;
	}


	public static function changeDate($date, $year=0, $month=0, $day=0, $hour=0, $min=0, $sec=0) {
		if(strlen($date) > 12) { //Date time (Y-m-d H:i:s)
			$temp = explode(' ', $date);
			$dat = explode('-', $temp[0]);
			$tim = explode(':', $temp[1]);
			return date('Y-m-d H:i:s', mktime($tim[0]+$hour, $tim[1]+$min, $tim[2]+$sec, $dat[1]+$month, $dat[2]+$day, $dat[0]+$year));
		} else {
			$split = explode('-', $date);
			return date('Y-m-d', mktime(0, 0, 0, (int)$split[1]+$month, (int)$split[2]+$day, (int)$split[0]+$year));
		}
	}

	public static function dateMask($date='') {
		if(($date == '')
		|| ($date == '0000-00-00')
		|| ($date == '0000-00-00 00:00:00')) {
			return '';
		}
		else {
			//If it's datetime format
			if(strlen($date) > 12) {
				$prima = explode(' ', $date);
				$hour = ' '. $prima[1];
				$date = $prima[0];
			}
			$split = explode('-', $date);
			return $split[2].'/'.$split[1].'/'.$split[0] . $hour;
		}
	}


	public static function dateUnmask($date='') {
		if($date == '') return '';
		else {
			if(strlen($date) > 12) {
				$prima = explode(' ', $date);
				$hour = ' '. $prima[1];
				$date = $prima[0];
			}
			$split = explode('/',$date);
			if(strlen($split[2]) == 2) {
				if($split[2] < 35) $split[2] = '20'. $split[2];
				else $split[2] = '19'.$split[2];
			}
			return $split[2].'-'. str_pad($split[1], 2, '0', STR_PAD_LEFT)
			     . '-'. str_pad($split[0], 2, '0', STR_PAD_LEFT) . $hour;
		}
	}


	public static function onlyNumbers($string) {
		$result = preg_replace('/[^0-9]/', '', $string);
		return $result;
	}


	public static function cepMask($cep) {
		$cep = Roda::onlyNumbers($cep);

		return substr($cep, 0, 5) .'-'. substr($cep, 5);
	}


	public static function cpfCnpjMask($x) {
		$x = Roda::onlyNumbers($x);
		if(strlen($x) == 0) return '';
		if(strlen($x) > 12) {
			if(strlen($x) == 14) {
				return substr($x, 0, 2) .'.'. substr($x, 2, 3) .'.'. substr($x, 5, 3) .'/'. substr($x, 8, 4) .'-'. substr($x, 12);
			} else {
				return substr($x, 0, 3) .'.'. substr($x, 3, 3) .'.'. substr($x, 6, 3) .'/'. substr($x, 9, 4) .'-'. substr($x, 13);
			}
		}
		else return substr($x, 0, 3) .'.'. substr($x, 3, 3) .'.'. substr($x, 6, 3) .'-'. substr($x, 9);
	}


	public static function fileSizeMask($size) {
		$i=0;
		$iec = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		while(($size/1024) > 1) {
			$size=$size/1024;
			$i++;
		}
		return Roda::numberMask(substr($size, 0, strpos($size,'.')+4)).$iec[$i];
	}


	public static function friendfyName($name) {
		$allowed = 'abcdefghijklmnopqrstuvwxyz0123456789-._';

		$x = trim($name);

		$x = stripslashes($x);

		$charout = array('\'', "\\", '"', ' ', '--', '-.', '.-',
		                 '-.-', '.-.', '..', '$quot;', '&');

		$charin  = array('',   '',   '',  '_', '-',  '.',  '.',
		                 '.',   '.',   '.',  '',       '_e_');

		$x = str_replace($charout, $charin, $x);
		$x = strtolower($x);
		$x = Roda::stripAccent($x);

		for($ii=0; $ii<strlen($x); $ii++) {
			if(stripos($allowed, $x[$ii]) !== false) {
				$resp .= $x[$ii];
			}
		}
		while(strrpos($resp, '.') == (strlen($resp)-1)) {
			$resp = substr($resp, 0, (strlen($resp)-1));
		}
		while(strpos($resp, '.')     === 0) $resp = substr($resp, 1);
		while(strpos($resp, 'www.')  === 0) $resp = substr($resp, 4);
		while(strpos($resp, 'wwww.') === 0) $resp = substr($resp, 5);
		if(empty($resp)) $resp = 'general';
		return $resp;
	}


	public static function stripAccent($string) {
		return str_replace(
			array('à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ',
			      'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ', 'À','Á','Â','Ã','Ä',
			      'Ç', 'È','É','Ê','Ë', 'Ì','Í','Î','Ï', 'Ñ', 'Ò','Ó','Ô','Õ','Ö',
			      'Ù','Ú','Û','Ü', 'Ý'),
			array('a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n',
			      'o','o','o','o','o', 'u','u','u','u', 'y','y', 'A','A','A','A','A',
			      'C', 'E','E','E','E', 'I','I','I','I', 'N', 'O','O','O','O','O',
			      'U','U','U','U', 'Y'),
			$string);
	}


}