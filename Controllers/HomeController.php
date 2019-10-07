<?php
namespace Controllers;

use \Core\Controller;
use \Models\Usuarios;

class HomeController extends Controller {

	public function index() {

		echo "Método: ".$this->getMethod()."\n";
		
		print_r($this->getRequestData());

	}

	public function test() {
		echo "Action test";
	}

	public function testParam($param) {
		echo "Action testParam";
		echo "<br> Param: ".$param;
	}

}