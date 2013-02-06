<?php
abstract class Zend_Test_PHPUnit_AbstractApiTestCase extends Zend_Test_PHPUnit_BaseControllerTestCase {

    const MULTIPLICATOR = 1000000;
    const HTTP_CODE_OK = 200;
    const HTTP_CODE_NOT_MODIFIED = 304;
	const HTTP_CODE_BAD_REQUEST = 400;
	const HTTP_CODE_FORBIDDEN = 403;
	const HTTP_CODE_METHOD_NOT_ALLOWED = 405;
	const HTTP_CODE_INTERNAL_SERVER_ERROR = 500;
	const HTTP_CODE_CONFLICT = 409;
    const SECRET_KEY_PARAM = "secretKey";

        /**
         * Generate environment for user invitation
         * and some places that are inside and outside given area
         *
         * @return array 
         */
        public function createMockInvitationWithPlaces(){

            list($boy, $girl, $invitation) = $this->createClosestInviteKit();
            $distanceCalc = new DistanceCalculator($boy);
            $areaAroundBoy = $distanceCalc->getSearchAreaAroundUser();
            $placesAroundBoy = $this->createPlacesSetInsideArea($areaAroundBoy);
            return array($boy, $girl, $invitation, $placesAroundBoy);
        }
        
    public function createPointInsideArea($area){
        $output = array();

        $output[] =
        $this->demultiplicate(
                rand(
                $this->multiplicate($area['bottom_left']['lat']),
                $this->multiplicate($area['top_left']['lat']))
            );

        $output[] =
        $this->demultiplicate(
                rand(
                $this->multiplicate($area['top_left']['long']),
                $this->multiplicate($area['top_right']['long']))
            );

        return $output;
    }

    private function multiplicate($coordinate) {
        return round($coordinate * self::MULTIPLICATOR,0);
    }

    private function demultiplicate($coordinate) {
        return $coordinate / self::MULTIPLICATOR;
    }

    public function createPlacesSetInsideArea($area){
            $j = rand(2, 10);
            $places = array();
            for($i = 0; $i <= $j; $i++){
                $place = new Place();
                $place->city_id = 1;
                $place->name = 'Китайский летчик Джао Да clone:'.$i;
                $place->address = 'Малая бронная 12/'.$i;
                list($lat, $long) = $this->createPointInsideArea($area);
                $place->latitude = $lat;
                $place->longitude = $long;                
                $place->save();
                $places[] = $place;
            }            
            return $places;
    }

    protected function createNewBoy($name, $age, $latitude, $longitude,
                $secretKeyBase=null) {
   	   $user = new User();
           $user->secretKey = md5($secretKeyBase == null ? rand(1000,10000) : $secretKeyBase);
           $user->name=$name;
           $user->age = $age;
           $user->sex = "M";
           $user->lookingFor = "F";
           $user->country = "UA";
           $user->provider = "MTS";
           $user->phoneId = "12345";
           $user->photoFileName = "12345.jpg";
           $user->latitude = $latitude;
           $user->longitude = $longitude; 
           $user->lastRequestTimeStamp = time();
           $user->save();
           return $user;
    }
       
    protected function createNewGirl($name, $age, $latitude, $longitude,
                $secretKeyBase=null){
           $user = new User();
           $user->secretKey = md5($secretKeyBase == null ? rand(1000,10000) : $secretKeyBase);
           $user->name=$name;
           $user->age = $age;
           $user->sex = "F";
           $user->lookingFor = "M";
           $user->country = "UA";
           $user->provider = "MTS";
           $user->phoneId = "12345";
           $user->photoFileName = "12345.jpg";
           $user->latitude = $latitude;
           $user->longitude = $longitude; 
           $user->lastRequestTimeStamp = time();
           $user->save();
           return $user;
       }
        
    protected function createClosestInviteKit(){
            $boy = $this->createNewBoy('Anton', '24', '55.911275', '37.396301');
            $girl = $this->createNewGirl('Inna', '29', '55.911275', '37.396301');
            $invite = $this->createInvitationWithBoyAndGirl($boy, $girl);
            return array($boy, $girl, $invite);
    }


    protected function createInvitationKit(){
            list($boy, $girl) = $this->createBoyAndGirl();
            $invite = $this->createInvitationWithBoyAndGirl($boy, $girl);
            Zend_Registry::set('currentUser', $boy);
            $this->setTestPOSTFields(array('id' => $boy->id, 'secretKey'=>$boy->secretKey));            
            return array($boy, $girl, $invite);
    }


    protected function createTestUserpicAndSetFiles($width, $height) {
	   	$im = @imagecreate($width, $height);
		$background_color = imagecolorallocate($im, 0, 0, 0);
		$text_color = imagecolorallocate($im, 233, 14, 91);
		imagestring($im, 1, 5, 5,  "It is me", $text_color);
		$temp_image = tempnam(sys_get_temp_dir(), 'TestImage') . ".jpg";
		imagejpeg($im, $temp_image);
		imagedestroy($im);		
		$_FILES = array(
            'userpic' => array(
                'name' => $temp_image,
                'type' => 'image/jpeg',
                'size' => filesize($temp_image),
                'tmp_name' => $temp_image,
                'error' => 0));
   }
 
   
   protected  function setTestPOSTFields($override = array()) {
   	$this->request
           ->setMethod('POST')
           ->setPost(array_merge($this->getRequestData(), $override));
   }
   
   protected function setTestGETFields($override = array()) {
	    $this->request
   	   	->setMethod('GET')
   	   	->setQuery(array_merge($this->getRequestData(), $override));   	
   }
   
   protected function getRequestData() {
   	return array('name' => 'Билли',
 				 'secretKey' => md5("test"),
                 'sex' => 'M',
           		 'lookingFor' => 'F',
           		 'age' => '32',
           		 'country' => 'UA',
           		 'provider' => 'MTS',
            	 'phoneId' => '4455677',
           		 'userpic' => 'test.jpeg',
           		 'coordinates' => '87.12367867887,-45.234677123255'   	   	
   	   	);
   }
   
   protected function createInvitationWithBoyAndGirl(User $inviter, User $invitee){
        $invite = $this->createInvitation($inviter->id, $invitee->id);
        $inviter->invitationId = $invite->id;
        $invitee->invitationId = $invite->id;
        $invitee->save();
        $inviter->save();
        return $invite;
   }

   /**
    * Creates a two test users 
    *
    * @return array
    */
   protected function createBoyAndGirl(){
       $girl = $this->createNewUser('Marry-'.rand(1,100), 24, "test");  	   
       $boy = $this->createNewUser('John-'.rand(1,100), 30);        
       return array($boy, $girl);
   }

   protected function getUserAbuses($user){
       $inv = new Invitation();
       return $inv->getCountAbusedInvitationsByUser($user);
   }
   
   protected function createInvitation($inviter, $invitee){
              
       $place = $this->createPlace();
       $inv = new Invitation();
       $inv->inviterId = $inviter;
       $inv->inviteeId = $invitee;
       $inv->placeId = 1;
       $inv->timeshift = 12;
       $inv->finalTime = time();
       $inv->userIdProposedTimeplace;
       $inv->accepted = 1;
       $inv->agreed = 1;
       $inv->abused = null; 
       $inv->placeId = $place->id;
       $inv->save();
       return $inv;
   }
   
   protected function createPlace(){
       $place = new Place();
       $place->latitude = 53.434232;
       $place->longitude = 54.343354;
       $place->name = 'Бар-ресторан "Вечерние зори Рязани"';
       $place->address = 'б. Васильковская 6';
       $place->save();
       return $place;
   }

   protected function getCurrentUser() {
        if (Zend_Registry::isRegistered('currentUser')) {
                $currentUser = Zend_Registry::get('currentUser');
                return $currentUser;
        }else{
            return new User();
        }		
    }

	protected function createNewUser($name, $age, $secretKeyBase=null) {
   	   $user = new User();
           $user->secretKey = md5($secretKeyBase == null ? rand(1000,10000) : $secretKeyBase);
           $user->name=$name;
           $user->age = $age;
           $user->sex = "F";
           $user->lookingFor = "M";
           $user->country = "UA";
           $user->provider = "MTS";
           $user->phoneId = "12345";
           $user->photoFileName = "12345.jpg";
           $user->latitude = rand(-180,180);
           $user->longitude = rand(-90,90); 
           $user->lastRequestTimeStamp = time();
           $user->save();
       return $user;
   }

    protected function linkWithInvitation($inviter, $invitee) {
        $invitation = new Invitation();
        $invitation->timestampCreated = time();
        $invitation->inviteeId = $invitee->id;
        $invitation->inviterId = $inviter->id;

        $inviter->Invitation = $invitation;
        $invitee->Invitation = $invitation;
        $inviter->save();
        $invitee->save();
        return $invitation;
    }

    protected function getHTTPHeaderValueByName($headers, $headerName) {
        $result = "";
        foreach($headers as $header) {
            if ($header['name'] == $headerName) {
                $result = $header['value'];
                break;
            }
        }
        return $result;
    }
}