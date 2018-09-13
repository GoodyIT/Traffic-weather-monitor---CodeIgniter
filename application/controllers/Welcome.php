<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */

	var $RX_RATE_URL = 'https://api.exchangeratesapi.io/latest?base=SGD&symbols=MYR,USD';

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('url'));
	
		//======== or simply ===============
	
		$this->load->helper('url');

		$this->load->database();
	}

	public function index($src = 'sg', $dst = 'my', $ajax = '0')
	{
		// get the fx rate info
		$res = json_decode(file_get_contents($this->RX_RATE_URL), true);

		// get the weather Info
		$query = $this->db->query('select * from weather order by dateAdded desc limit 1');
		$weather_summary = $query->num_rows() > 0 ? $query->row()->summary : '';
		$temperature = $query->num_rows() > 0 ? $query->row()->temperature . ' °C' : '';

		// get the traffic info
		$query = $this->db->query('select * from traffic where src="' . $src . '" and dst="' . $dst . '" order by dateAdded desc limit 1');
		$est = $query->num_rows() > 0 ? 'EST ' . $query->row()->est : '';
		$traffic_status =  $query->num_rows() > 0 ?  '(' . $query->row()->status . ')' : '';
		$traffic_color = 'red';
		$traffic_sign = base_url('./assets/images/red_light.gif');
		if (strpos($traffic_status, 'Normal') !== false) {
			$traffic_sign = base_url('./assets/images/yellow_light.gif');
			$traffic_color = 'yellow';
		} else if (strpos($traffic_status, 'Light') !== false) {
			$traffic_sign = base_url('./assets/images/green_light.gif');
			$traffic_color = 'green';
		}
		$src_array = array(
			"code" =>  "sg",
			"title" => "Singapore",
			"path" => "Woodlands+Checkpoint,+21+Woodlands+Crossing,+738203",
			"place_id" => "ChIJcax8Ev0S2jER7fTRxrPHz2w",
		);

		$dst_array = array(
			"code" => "my",
			"title" => "Malaysia",
			"path" => "Sultan+Iskandar+Complex+Customs,+Jalan+Jim+Quee,+Bukit+Chagar,+80300+Johor+Bahru,+Johor,+Malaysia",
			"place_id" => "ChIJ4-MEgNwS2jERPDLDNgWnENA",
		);

		$temp = $src_array;
		if ($src_array['code'] != $src) {
			$src_array = $dst_array;
			$dst_array = $temp;
		}

		date_default_timezone_set('UTC+8');

		$data =array(
			'est'=> $est,
			'traffic_sign'=> $traffic_sign,
			'traffic_status'=> $traffic_status,
			'traffic_color' => $traffic_color,
			'today' => date("l H:i A"),
			'temperature' => $temperature,
			'weather_summary' => $weather_summary,
			'MYR' => number_format($res['rates']['MYR'], 3),
			'USD' => number_format($res['rates']['USD'], 3),
			'src' =>  $src_array,
			'dst' => $dst_array,
			'map_url' => base_url('index.php/welcome/map/' . $src . '/' . $dst),
		);
		if ($ajax == '0') {
			$this->load->view('home', $data);
		} else {
			echo json_encode($data);
		}
	}

	public function map($src, $dst) {
		$src_path = "Woodlands+Checkpoint,+21+Woodlands+Crossing,+738203";
		$dst_path = "Sultan+Iskandar+Complex+Customs,+Jalan+Jim+Quee,+Bukit+Chagar,+80300+Johor+Bahru,+Johor,+Malaysia";
		if ($src == "my") {
			$src_path = "Sultan+Iskandar+Complex+Customs,+Jalan+Jim+Quee,+Bukit+Chagar,+80300+Johor+Bahru,+Johor,+Malaysia";
			$dst_path = "Woodlands+Checkpoint,+21+Woodlands+Crossing,+738203";
		}
		$map_link = "https://www.google.com/maps/embed/v1/directions?key=AIzaSyCYc3U2zpF5V8DiAsY9PSSq0SF_CeRbdkA&zoom=14&origin=$src_path&destination=$dst_path";
		$data = array(
			"map_link" => $map_link,
		);

		$this->load->view('map', $data);
	}

	public function camera() {
		$query = $this->db->query('select * from camera order by dateAdded desc limit 1');
		$data = array(
			"camera" => array(
				'image1' => $query->row()->image1,
				'image2' => $query->row()->image2,
			),
		);
		$this->load->view('camera', $data);
	}

	public function scheduleWeather() {
		$weather = json_decode(file_get_contents('https://api.darksky.net/forecast/b3fa3ecdf4e7167c9fee494ee87b2de0/1.452768,103.769179'), true);
		$temperature = ($weather['currently']['temperature'] - 32) * 5 / 9;
		$temperature = number_format($temperature, 0);
        $sql = 'insert into weather (summary, temperature) values("' . $weather['currently']['summary'] . '", "' . $temperature . '")';
		$query = $this->db->query($sql);
	}

	public function scheduleOnClearTables() {
		$this->db->query('delete from camera');
		$this->db->query('delete from traffic');
	}

	public function scheduleOnOnSignal() {
		$query = $this->db->query('select * from traffic order by dateAdded desc limit 1');
		if($query->num_rows() > 0 && $query->row()->notification != '') {
			$this->sendPushMessage($query->row()->notification);
		}
	}

	public function sendPushMessage($message = 'hello') {
		$content = array(
			"en" => $message
		);
		$heading = array(
			"en" => 'CauseWay Live'
		);

		$fields = array(
			'app_id' => '95e5d98e-bd41-4b70-b69c-d7eca26b01af',
			'included_segments' => array('All'),
			'contents' => $content,
			'headings' => $heading,
			'big_picture' => 'https://s6.postimg.org/x79g410ap/winner.jpg',
			'small_icon' => 'https://s6.postimg.org/sseqc2qpt/ic_launcher.png',
			'large_icon' => 'https://s6.postimg.org/sseqc2qpt/ic_launcher.png'
		);
		
		$fields = json_encode($fields);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
												   'Authorization: Basic NjNmMWM1YzctZDc5Ni00NTJjLWE4NjQtMDdiMzRhNjFkZWE3'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);

		echo $response;
	}

	public function scheduleOnCamera() {
		$cameras = json_decode(file_get_contents('https://api.data.gov.sg/v1/transport/traffic-images'), true);
		if (sizeof($cameras['items']) > 0) {
			$images = array();
			$temp = $cameras['items'][0]['cameras'];
			foreach ($temp as $row) {
				if ($row['camera_id'] === '2701' || $row['camera_id'] === '2702') {
					array_push($images, $row['image']);
				}
			}

			var_dump($images);

			$sql = 'insert into camera (image1, image2) values("' . $images[0] . '", "' . $images[1] . '")';
			$query = $this->db->query($sql);
		}
	}

	public function scheduleOnTrafficFromSGtoMY() {
		$traffic = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/directions/json?origin=place_id:ChIJcax8Ev0S2jER7fTRxrPHz2w&destination=place_id:ChIJ4-MEgNwS2jERPDLDNgWnENA&key=AIzaSyDOn9pwR-eP2cBeqMji7ERWNeRlbaw0srg&departure_time=now'), true);
		$this->addTrafficInfo($traffic, 'sg', 'my');
	}

	public function scheduleOnTrafficFromMYtoSG() {
		$traffic = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/directions/json?origin=place_id:ChIJ4-MEgNwS2jERPDLDNgWnENA&destination=place_id:ChIJcax8Ev0S2jER7fTRxrPHz2w&key=AIzaSyDOn9pwR-eP2cBeqMji7ERWNeRlbaw0srg&departure_time=now'), true);
		$this->addTrafficInfo($traffic, 'my', 'sg');
	}

	public function addTrafficInfo($traffic, $src, $dst) {
		if (sizeof($traffic['routes']) > 0) {
			$temp = $traffic['routes'][0]['legs'][0];
			$estValue = (int)$temp['duration_in_traffic']['value'];
			$duration = (int)$temp['duration']['text'];
			$est = (int)$temp['duration_in_traffic']['text'];

			$status = 'Heavy Traffic';
			if ($estValue < 10 * 60) {
				$status = 'Light Traffic';
			} else if ($estValue <= 20 * 60) {
				$status = 'Normal Traffic';
			}

			$location = array(
				'SG' => 'Singapore',
				'MY' => 'Malaysia',
			);

			$notification = '';

			if ($estValue >= 45 * 60) {
				$notification = 'Heavy traffic from ' . $location['src'] . ' to ' . $location['dst'] . ', EST 45 mins+ ';
			}

			$sql = 'insert into traffic (src, dst, duration, est, notification, status) values("' . $src . '", "' . $dst . '", "' . $duration . '", "' .$est . '", "' . $notification . '", "' . $status . '")';
			$query = $this->db->query($sql);
		}
	}
}
