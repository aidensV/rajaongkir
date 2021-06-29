<?php

namespace ArsoftModules\RajaOngkir;

use Exception;

class RajaOngkir
{
	protected $endpoint;
	protected $key;
	private $status = 'success', $data, $errorMessage;
	public function getStatus()
    {
        return $this->status;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

	public function __construct()
	{
		$this->endpoint = config('rajaongkir.end_point_api', 'https://api.rajaongkir.com/starter');
		$this->key = config('rajaongkir.api_key');
		// $this->city = json_decode(file_get_contents(__DIR__ . '/config/city.json'));
		// $this->province = json_decode(file_get_contents(__DIR__ . '/config/province.json'));
	}

	private function _request($path, $options = null)
	{
		$url = $this->endpoint . "/" . $path;

		$curl = curl_init();
		$config = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"key: " . $this->key
			),
		);

		$config = array_replace($config, $options);
		curl_setopt_array($curl, $config);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			 
		}

		$rajaongkir =json_decode($response)->rajaongkir;
		
		if ($rajaongkir->status->code == 400) {
			$this->status = 'error';
            $this->errorMessage = $rajaongkir->status->description;
            return $this;
		}

		if ($rajaongkir->status->code == 200) {
			$this->data =  $rajaongkir->results;
			return $this;
		}
	}

	public function province($id = null)
	{
		if ($id = null) {
			return empty($this->province) ? self::_request('/province') : $this->province;
		}

		if (empty($this->province)) {
			return self::_request('/province?id=' . $id);
		}

		foreach ($this->province as $key => $value) {
			if ($value->province_id == $id) {
				return $value;
			}
		}

		return null;
	}

	public function city($id = null)
	{
		if ($id == null) {
			return empty($this->city) ? self::_request('/city') : $this->city;
		}

		if (empty($this->city)) {
			return self::_request('/city?id=' . $id);
		}

		foreach ($this->city as $key => $value) {
			if ($value->city_id == $id) {
				return $value;
			}
		}

		return null;
	}

	public function cost($origin, $destination, $weight, $courier)
	{
		$options = [
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "origin=" . $origin . "&destination=" . $destination . "&weight=" . $weight . "&courier=" . $courier,
			CURLOPT_HTTPHEADER => array(
				"content-type: application/x-www-form-urlencoded",
				"key: " . $this->key
			),
		];
		return self::_request('/cost', $options);
	}
}
