<?php

class Admin_IndexController extends Zend_Controller_Action
{

    public function init()
    {
       	session_start();
       	session_id($_COOKIE['PHPSESSID']);
       	$this->_helper->layout()->setLayout('index');
    	if ($this->getRequest()->getActionName() != 'login' and $_SESSION['pass'] != 'kdrr7BNMfkdrr') $this->_helper->redirector("login");
    	if ($this->getRequest()->getActionName() == 'login' and $_SESSION['pass'] == 'kdrr7BNMfkdrr') $this->_helper->redirector("get-places");
    }

    public function loginAction()
    {
    	if ($this->getRequest()->isPost()) {
    		$pass = $this->getRequest()->getParam('password');
    		if ($pass == 'kdrr7BNMfkdrr') {
    			$_SESSION['pass'] = $pass;
    			$this->_helper->redirector("get-places");
    		} else $this->view->message = 'Пароль не верный';
    	}
    }

    public function indexAction()
    {
    	if ($this->getRequest()->isPost()) {
    		if ($_FILES["file"]["error"] > 0) {
				$this->view->message = "Error: " . $_FILES["file"]["error"] . "<br />";
			} else {
				if (strpos($_FILES["file"]["type"], '/csv') === FALSE) {
					$this->view->message = "Please import only CSV files<br />";
				} else {
					// Get types of places
					$placeTypes = Placetype::getAll();
					foreach ($placeTypes as $type)
						$types[$type['name']] = $type['id'];

					$row = 1;
					if (($handle = fopen($_FILES["file"]["tmp_name"], "r")) !== FALSE) {
						// Delete all places
						Place::deleteAll();
					    while (($data = fgetcsv($handle)) !== FALSE) {
					        // If this row itsn't header
					        if ($data[0] == (string)(int)$data[0]) {
					        	try {
					        		// Create place
							        $place = new Place();
							        // Place ID
							        $place->id = $data[0];
							        // Place type
							        $typeName = $data[1];
							        if ($typeID = $types[$typeName]) $place->type_id = $typeID;
							        else {
							        	// Add new place type
							        	$placeType = new Placetype();
							        	$placeType->name = $typeName;
							        	$placeType->save();
							        	echo "новый тип мест для свиданий($typeName) добавлен успешно<br />\n";
							        	// Get new list of place types
										$placeTypes = Placetype::getAll();
										foreach ($placeTypes as $type)
											$types[$type['name']] = $type['id'];
										// Set place type
										$place->type_id = $types[$typeName];
							        }
							        // Place city
							        $place->city_id = 1;
							        // Place name
							        $place->name = $data[2];
							        // Place latitude and longitude
							        $coord = explode(',', $data[3]);
							        $place->longitude = $coord[0];
							        $place->latitude = $coord[1];
							        // Place address
							        $place->address = $data[4];
							        // Place priority
							        $place->priority = $data[5];
							        // Place selected (it's partner?)
							        $place->selected = $data[6];
							        // Place comments
							        $place->comments = $data[7];
							        $place->save();
							        echo "$row строка добавлена успешно<br />\n";
						        } catch (Exception $e) {
									echo "$row строка с ошибкой: ",  $e->getMessage(), "<br />\n";
								}
						    }
					        $row++;
					    }
					    fclose($handle);
					}
					die('Импорт завершен');
				}
			}
    	}
    }

    public function getPlacesAction() {
    	$this->view->places = Place::getAll();

    	// Get list of place types
    	$placeTypes = Placetype::getAll();
		foreach ($placeTypes as $type)
			$types[$type['id']] = $type['name'];
		$this->view->placeTypes = $types;

		// Get list of cites
    	$placeCites = City::getAll();
		foreach ($placeCites as $city)
			$cites[$city['id']] = $city['name'];
		$this->view->placeCites = $cites;
    }

    public function botSettingsAction() {
    	if ($this->getRequest()->isPost()) {
    		$bot_on = $this->getRequest()->getParam('bot_on');
    		Setting::updateByName('bot_on', $bot_on);

    		$bot_online_count = $this->getRequest()->getParam('bot_online_count');
    		Setting::updateByName('bot_online_count', $bot_online_count);
		}
    	$this->view->bot_on = Setting::getByName('bot_on');
    	$this->view->bot_online_count = Setting::getByName('bot_online_count');
    }

}

