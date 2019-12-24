<?php
namespace Controllers;

use \Core\Controller;
use \Models\Users;
use \Models\Photos;

class UsersController extends Controller {

	public function index() {}

	public function login() {
		
		$array = array('error' => '');

		$method = $this->getMethod();
		$data = $this->getRequestData();

		if($method == 'POST') {

			if (!empty($data['email']) && !empty($data['pass'])) {
				
				$users = new Users();

				$data['pass'] = addslashes($data['pass']);

				if($users->checkCredentials($data['email'], $data['pass'])) {

					$array['jwt'] = $users->createJwt();
					
				} else {
					$array['error'] = 'Acesso Negado!!!';
				}
			} else {
				$array['error'] = 'Preencha os campos email e senha!!!';
			}

		} else {
			$array['error'] = 'Método de requisição inválido!!!';
		}

		$this->returnJson($array);
	}

	public function view($id_user) {

		$array = array('error' => '');

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();

		if (!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
			
			$array['logged'] = true;
			$array['is_me'] = false;

			if ($id_user === $users->getId()) {
				$array['is_me'] = true;
			}

			switch ($method) {
				case 'GET':
					
					$array['data'] = $users->getInfo($id_user);

					if (count($array['data']) === 0) {
						$array['error'] = 'Usuário não encontrado!!!';
					}

					break;
				case 'PUT':
					
					$array['error'] = $users->editInfo($id_user, $data);

					break;
				case 'DELETE':

					$array['data'] = $users->getInfo($id_user);

					if (count($array['data']) === 0) {
						$array['error'] = 'Usuário não encontrado!!!';
					} else {
						$array['error'] = $users->deleteUser($id_user);
					}

					$array['data'] = '';

					break;
				default:
					$array['error'] = 'Invalid request '.$method.' method';
					break;
			}


		} else {
			$array['error'] = 'Acesso negado!!!';
		}

		return $this->returnJson($array);
	}

	public function feed() {

		$array = array('error' => '');

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();

		if (!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
			
			$array['logged'] = true;

			if ($method == 'GET') {
				
				$offset = 0;

				if (isset($data['offset']) && !empty($data['offset'])) {
					$offset = intval($data ['offset']);
				}

				$per_page = 10;

				if (isset($data['per_page']) && !empty($data['per_page'])) {
					$per_page = intval( $data['per_page']);
				}

				$array['data'] = $users->getFeed($offset, $per_page);
				
			} else {
				$array['error'] = 'Método indisponível';
			}


		} else {
			$array['error'] = 'Acesso negado!!!';
		}

		return $this->returnJson($array);
	}

	public function photos($id_user){

		$array = array('error' => '');
		$photos = new Photos();

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();

		if (!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
			
			$array['logged'] = true;
			$array['is_me'] = false;

			if ($id_user === $users->getId()) {
				$array['is_me'] = true;
			}

			if ($method == 'GET') {
				
				$offset = 0;

				if (isset($data['offset']) && !empty($data['offset'])) {
					$offset = intval($data ['offset']);
				}

				$per_page = 10;

				if (isset($data['per_page']) && !empty($data['per_page'])) {
					$per_page = intval( $data['per_page']);
				}

				$array['data'] = $photos->getPhotos($id_user, $offset, $per_page);
				
			} else {
				$array['error'] = 'Método indisponível';
			}


		} else {
			$array['error'] = 'Acesso negado!!!';
		}

		return $this->returnJson($array);
	}	

	public function new_record() {

		$array = array('error' => '');

		$method = $this->getMethod();
		$data = $this->getRequestData();

		if ($method == 'POST') {
			
			$users = new Users();

			if (!empty($data['name']) && !empty($data['email']) && !empty($data['pass'])) {
				
				if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
					
					if ($users->create($data['name'], $data['email'], $data['pass'])) {
						
						$array['jwt'] = $users->createJwt();

					} else {
						$array['error'] = 'Email já cadastrado!!!';
					}

				} else {
					$array['error'] = 'Insira um email válido!!!';
				}

			} else {
				$array['error'] = 'Preencha todos os dados!!';
			}

		} else {
			$array['error'] = 'Método de requisição inválido';
		}


		return $this->returnJson($array);
	}

	public function follow($id_user) {

		$array = array('error' => '');

		$users = new Users();
		
		$method = $this->getMethod();
		$data = $this->getRequestData();

		if (!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {

			if ($method == 'POST') {

				if (!$users->follow($id_user)) {
					$array['error'] = 'Não foi possível seguir este usuário';
				} 
				
			} else if ($method == 'DELETE') {
				
				$users->unfollow($id_user);

			} else {

				$array['error'] = 'Método de requisição inválido';

			}


		} else {
			$array['error'] = 'Acesso negado!!!';
		}

		return $this->returnJson($array);		
	}

}