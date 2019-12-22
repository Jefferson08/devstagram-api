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

	public function getFeedCollection($followingUsers, $offset, $per_page){

		$array = array();
		$users = new Users();

		if (count($followingUsers) > 0) {
			
			$sql = "SELECT * FROM photos WHERE id_user IN (".implode(", ", $followingUsers).") ORDER BY id DESC LIMIT ".$offset." ,".$per_page;

			$sql = $this->db->query($sql);

			if ($sql->rowCount() > 0) {

				$array = $sql->fetchAll(\PDO::FETCH_ASSOC);

				foreach ($array as $key => $photo) {

					$user_info = $users->getInfo($photo['id_user']);

					$array[$key]['name'] = $user_info['name'];
					$array[$key]['avatar'] = $user_info['avatar'];
					$array[$key]['likes_count'] = $this->getLikesCount($photo['id']);
					$array[$key]['comments'] = $this->getComments($photo['id']);
					$array[$key]['url'] = BASE_URL.'media/photos/'.$photo['url'];
				}
			}
		}

		

		return $array;
	}

	public function getComments($id_photo){

		$array = array();

		$sql = "SELECT comments.*, users.name FROM comments, users WHERE comments.id_photo = :id AND users.id_user = comments.id_user";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_photo);

		$sql->execute();

		if ($sql->rowCount() > 0) {
			$array = $sql->fetchAll(\PDO::FETCH_ASSOC);
		}

		return $array;
	}

	public function getLikesCount($id_photo){
		$sql = "SELECT COUNT(*) AS c FROM photos_likes WHERE id_photo = :id";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_photo);
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