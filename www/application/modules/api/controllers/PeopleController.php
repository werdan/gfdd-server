<?php 
require_once 'controllers/AbstractApiController.php';
require_once 'DistanceCalculator.php';

class Api_PeopleController extends AbstractApiController {

	protected function getAllowedHTTPMethod() {
		return self::HTTP_METHOD_GET;
	}

	public function listAction() {
        $currentUser = $this->getCurrentUser();
		$people = $currentUser->getPeopleAroundLookedFor();
		$this->view->assign("people", $people);
	}

    public function listnoauthAction() {
        $this->setNewCoordinatesFromRequest();
        $currentUser = $this->getCurrentUser();
        $people = $currentUser->getPeopleAroundLookedFor();
        $this->view->assign("people", $people);
        $this->_helper->viewRenderer->setRender('list');
    }

}
