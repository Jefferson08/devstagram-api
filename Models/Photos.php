<?php

namespace Models;

use \Core\Model;

class Photos extends Model {

	public function getPhotos($id_user, $offset, $per_page){

		$array = array();

		$sql = "SELECT * FROM photos WHERE id_user = :id ORDER BY id DESC LIMIT ".$offset." ,".$per_page;
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_user);
		$sql->execute();

		if ($sql->rowCount() > 0) {

			$array = $sql->fetchAll(\PDO::FETCH_ASSOC);

			foreach ($array as $key => $photo) {
				$array[$key]['likes_count'] = $this->getLikesCount($photo['id']);
				$array[$key]['comments'] = $this->getComments($photo['id']);
				$array[$key]['url'] = BASE_URL.'media/photos/'.$photo['url'];
			}
		}

		return $array;
	}

	public function getPhoto($id_photo) {

		$array = array();

		$users = new Users();

		$sql = "SELECT * FROM photos WHERE id = :id_photo";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id_photo', $id_photo);
		$sql->execute();

		if ($sql->rowCount() > 0) {

			$array = $sql->fetch(\PDO::FETCH_ASSOC);

			$user_info = $users->getInfo($array['id_user']);

			$array['name'] = $user_info['name'];
			$array['avatar'] = $user_info['avatar'];
			$array['likes_count'] = $this->getLikesCount($array['id']);
			$array['comments'] = $this->getComments($array['id']);
			$array['url'] = BASE_URL.'media/photos/'.$array['url'];
	
		}

		return $array;

	}

	public function deletePhoto($id_photo, $id_user) {

		$sql = "SELECT id FROM photos WHERE id = :id_photo AND id_user = :id_user";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id_user', $id_user);
		$sql->bindValue(':id_photo', $id_photo);
		$sql->execute();

		if ($sql->rowCount() > 0) {
			
			$sql = "DELETE FROM photos WHERE id = :id_photo";
			$sql = $this->db->prepare($sql);
			$sql->bindValue(':id_photo', $id_photo);
			$sql->execute();

			$sql = "DELETE FROM comments WHERE id_photo = :id_photo";
			$sql = $this->db->prepare($sql);
			$sql->bindValue(':id_photo', $id_photo);
			$sql->execute();

			$sql = "DELETE FROM photos_likes WHERE id_photo = :id_photo";
			$sql = $this->db->prepare($sql);
			$sql->bindValue(':id_photo', $id_photo);
			$sql->execute();

			return '';

		} else {
			return 'Esta foto não é sua ou não existe';
		}
	}

	public function getRamdomPhotos($per_page, $excludes) {

		$array = array();

		if (!empty($excludes)) {
			
			foreach ($excludes as $key => $value) {
				$excludes[$key] = intval($value);
			}

			$sql = "SELECT * FROM photos WHERE id NOT IN (".implode(', ', $excludes).") ORDER BY rand() LIMIT ".$per_page;
		} else {
			$sql = "SELECT * FROM photos ORDER BY rand() LIMIT ".$per_page;
		}

		$sql = $this->db->query($sql);

		if ($sql->rowCount() > 0) {
			
			$array = $sql->fetchAll(\PDO::FETCH_ASSOC);

		}

		return $array;
	}

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

	public function addComment($id_photo, $id_user, $txt) {

		$sql = "INSERT INTO comments (id_user, id_photo, date_comment, txt_comment) VALUES (:id_user, :id_photo, now(), :txt)";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id_user', $id_user);
		$sql->bindValue(':id_photo', $id_photo);
		$sql->bindValue(':txt', $txt);

		$sql->execute();

		return '';
	}

	public function deleteComment($id_comment, $id_user) {

		$sql = "SELECT id_photo, id_user FROM comments WHERE id = :id_comment";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id_comment', $id_comment);
		$sql->execute();

		if ($sql->rowCount() > 0) {
			
			$sql = $sql->fetch(\PDO::FETCH_ASSOC);

			$photo = $this->getPhoto($sql['id_photo']);

			if ($photo['id_user'] == $id_user || $sql['id_user'] == $id_user) {
				
				$sql = "DELETE FROM comments WHERE id = :id_comment";
				$sql = $this->db->prepare($sql);
				$sql->bindValue(':id_comment', $id_comment);
				$sql->execute();

				return '';

			} else {
				return 'Este comentário não é seu!!';
			}
		} else {
			return 'Este comentário não existe!!!';
		}

	}

	public function like($id_photo, $id_user) {

		$sql = "SELECT * FROM photos_likes WHERE id_photo = :id AND id_user = :id_user";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_photo);
		$sql->bindValue(':id_user', $id_user);
		$sql->execute();

		if ($sql->rowCount() == 0) {
			
			$sql = "INSERT INTO photos_likes (id_photo, id_user) VALUES (:id, :id_user)";
			$sql = $this->db->prepare($sql);
			$sql->bindValue(':id', $id_photo);
			$sql->bindValue(':id_user', $id_user);
			$sql->execute();

			return '';

		} else {
			return 'Você já curtiu essa foto!!!';
		}

	}

	public function unlike($id_photo, $id_user) {

		$sql = "DELETE FROM photos_likes WHERE id_photo = :id AND id_user = :id_user";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(':id', $id_photo);
		$sql->bindValue(':id_user', $id_user);
		$sql->execute();

		return '';
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