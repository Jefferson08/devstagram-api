<?php
namespace Controllers;

use \Core\Controller;
use \Models\Users;

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

}