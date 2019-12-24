<?php

namespace Models;

use \Core\Model;
use \Models\Jwt;
use \Models\Photos;

class Users extends Model {

	private $id_user;

	public function getId() {
		return $this->id_user;
	}

	public function create($name, $email, $pass) {

		if (!$this->emailExists($email)) {

			$hash = password_hash($pass, PASSWORD_DEFAULT);
			
			$sql = 	"INSERT INTO users (name, email, pass) VALUES (:name, :email, :pass)";
			$sql = $this->db->prepare($sql);
			$sql->bindValue(':name', $name);
			$sql->bindValue(':email', $email);
			$sql->bindValue(':pass', $hash);

			$sql->execute();

			$this->id_user = $this->db->lastInsertId();

			return true;
		} else {
			return false;
		}
	}

	public function editInfo($id, $data) {

		if ($id === $this->getId()) {
			
			$changes = array();

			if(!empty($data['name'])){
				$changes['name'] = $data['name'];
			}

			if (!empty($data['email'])) {
				if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) && !$this->emailExists($data['email'])) {
					$changes['email'] = $data['email'];
				} else {
					return 'Email inválido ou já existente!';
				}
			}

			if (!empty($data['pass'])) {
				$changes['pass'] = password_hash($data['pass'], PASSWORD_DEFAULT);
			}

			if (count($changes) > 0) {

				$fields = array();

				foreach ($changes as $key => $value) {
					array_push($fields, $key.' = :'.$key);
				}
				
				$sql = "UPDATE users SET ".implode(', ', $fields)." WHERE id_user = :id";

				$sql = $this->db->prepare($sql);
				$sql->bindValue(':id', $id);

				foreach ($changes as $key => $value) {
					$sql->bindValue(':'.$key, $value);
				}

				$sql->execute();

				return '';

			} else {
				return 'Preencha os dados corretamente!';
			}

		} else {
			return 'Você não pode alterar outro usuário!';
		}

	} 

	public function deleteUser($id_user) {

		if ($id_user === $this->getId()) {
			
			$p = new Photos();

			$p->deleteAll($id_user);

			$sql = "DELETE FROM users_following WHERE id_user_active = :id OR id_user_passive = :id";
			$sql = $this->db->prepare($sql);
			$sql->bindValue(':id', $id_user);
			$sql->execute();

			$sql = "DELETE FROM users WHERE id_user = :id";
			$sql = $this->db->prepare($sql);
			$sql->bindValue(':id', $id_user);
			$sql->execute();

			return '';

		} else {
			return 'Você não pode excluir outro usuário!';
		}

	}

	public function getInfo($id_user) {

		$array = array();

		$sql = "SELECT id_user, name, email, avatar FROM users WHERE id_user = :id ";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_user);
		$sql->execute();

		if ($sql->rowCount() > 0) {
			$array = $sql->fetch(\PDO::FETCH_ASSOC);

			if (!empty($array['avatar'])) {
				$array['avatar'] = BASE_URL.'media/avatar/'.$array['avatar'];
			} else {
				$array['avatar'] = BASE_URL.'media/avatar/default.png';
			}

			$photos = new Photos();

			$array['following'] = $this->getFollowingCount($id_user);
			$array['followers'] = $this->getFollowersCount($id_user);
			$array['photos_count'] = $photos->getPhotosCount($id_user);
		}

		return $array;
	}

	public function getFeed($offset = 0, $per_page = 10){

		$followingUsers = $this->getFollowingIds($this->getId());

		$photos = new Photos();

		return $photos->getFeedCollection($followingUsers, $offset, $per_page);

	}

	public function getFollowingIds($id_user){

		$array = array();

		$sql = "SELECT id_user_passive FROM users_following WHERE id_user_active = :id";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_user);
		$sql->execute();

		if ($sql->rowCount() > 0) {
			
			$data = $sql->fetchAll();

			foreach ($data as $id) {
				$array[] = intval( $id['id_user_passive']);
			}
		}

		return $array;
	}

	public function getFollowingCount($id_user) {

		$sql = "SELECT COUNT(*) AS c FROM users_following WHERE id_user_active = :id";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_user);
		$sql->execute();

		$info = $sql->fetch();

		return $info['c'];
	}

	public function getFollowersCount($id_user) {

		$sql = "SELECT COUNT(*) AS c FROM users_following WHERE id_user_passive = :id";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_user);
		$sql->execute();

		$info = $sql->fetch();

		return $info['c'];
	}

	public function checkCredentials($email, $pass) {

		$sql = "SELECT id_user, pass FROM users WHERE email = :email";

		$sql = $this->db->prepare($sql);

		$sql->bindValue(':email', $email);

		$sql->execute();

		if($sql->rowCount() > 0){

			$info = $sql->fetch();

			if (password_verify($pass, $info['pass'])) {
				
				$this->id_user = $info['id_user'];
				return true;
			} else {
				return false;
			}

		} else {
			return false;
		}
	}

	public function follow($id_user) {

		$sql = "SELECT * FROM users_following WHERE id_user_active = :id_user_active AND id_user_passive= :id_user_passive";
		$sql = $this->db->prepare($sql);
		$sql->bindValue('id_user_active', $this->getId());
		$sql->bindValue('id_user_passive', $id_user);
		$sql->execute();

		if ($sql->rowCount() === 0) {

			$sql = "INSERT INTO users_following (id_user_active, id_user_passive) VALUES (:id_user_active, :id_user_passive)";
			$sql = $this->db->prepare($sql);
			$sql->bindValue(':id_user_active', $this->getId());
			$sql->bindValue(':id_user_passive', $id_user);
			$sql->execute();

			return true;
		} else {

			return false;
			
		}

	}

	public function unfollow($id_user) {

		$sql = "DELETE FROM users_following WHERE id_user_passive = :id_user_passive AND id_user_passive = :id_user_passive";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id_user_active', $this->getId());
		$sql->bindValue(':id_user_passive', $id_user);
		$sql->execute();
	}

	public function emailExists($email) {

		$sql = "SELECT id_user FROM users WHERE email = :email";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':email', $email);
		$sql->execute();

		if ($sql->rowCount() > 0) {
			return true;	
		} else {
			return false;
		}
	}

	public function createJwt(){
		$jwt = new Jwt();
		return $jwt->create(array("id_user" => $this->id_user));
	}

	public function validateJwt($token) {
		$jwt = new Jwt();
		
		$info = $jwt->validate($token);

		if (isset($info->id_user)) {
			$this->id_user = $info->id_user;
			return true;
		} else {
			return false;
		}
	}
}