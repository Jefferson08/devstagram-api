<?php

namespace Models;

use \Core\Model;

class Photos extends Model {

	public function getPhotosCount($id_user) {

		$sql = "SELECT COUNT(*) AS c FROM photos WHERE id_user = :id";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_user);
		$sql->execute();

		$info = $sql->fetch();

		return $info['c'];
	} 

	public function deleteAll($id_user) {

		$sql = "DELETE FROM photos WHERE id_user = :id";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_user);
		$sql->execute();

		$sql = "DELETE FROM comments WHERE id_user = :id";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_user);
		$sql->execute();

		$sql = "DELETE FROM photos_likes WHERE id_user = :id";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_user);
		$sql->execute();
	}


}