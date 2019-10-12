<?php

namespace Models;

use \Core\Model;

class Users extends Model {

	private $id_user;

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
}