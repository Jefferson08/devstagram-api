<?php
namespace Controllers;

use \Core\Controller;
use \Models\Users;
use \Models\Photos;

class PhotosController extends Controller { 

	public function random() {
		$array = array('error' => '');

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();
		$photos = new Photos();

		if (!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
			
			$array['logged'] = true;


			if ($method == 'GET') {

				$per_page = 10;
				if (!empty($data['per_page'])) {
					$per_page = $data['per_page'];
				}

				$excludes = array();
				if (!empty($data['excludes'])) {
					$excludes = explode(',', $data['excludes']);
				}
				
				$array['data'] = $photos->getRamdomPhotos($per_page, $excludes);

			} else {
				$array['error'] = 'Invalid request '.$method.' method';
			}
					
		} else {
			$array['error'] = 'Acesso negado!!!';
		}

		return $this->returnJson($array);
	}

	public function view($id_photo) {

		$array = array('error' => '');

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();
		$photos = new Photos();

		if (!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
			
			$array['logged'] = true;

			if ($method == 'GET') {
				
				$array['data'] = $photos->getPhoto($id_photo);

			} else if ($method == 'DELETE') {


			} else {
				$array['error'] = 'Invalid request '.$method.' method';
			}
					
		} else {
			$array['error'] = 'Acesso negado!!!';
		}

		return $this->returnJson($array);

	}
}

?>