<?php
namespace Controllers;

use \Core\Controller;

class NotfoundController extends Controller {

	public function index() {
		echo $this->returnJson(array());
	}

}