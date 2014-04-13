<?php
/**
 * Created by IntelliJ IDEA.
 * User: arik-so
 * Date: 4/12/14
 * Time: 5:00 PM
 */

App::import('Vendor', 'facebook/facebook');
App::import('Lib', 'FBLib');

class HomeController extends AppController{

    public $uses = ['User', 'UserInterest', 'Interest'];

    public function index(){


        // show FB login option

        // if not logged in, redirect to login page

        // otherwise, get the client's location

        $facebook = FBLib::getFBObject();

        $user_id = $facebook->getUser();

        if(!$user_id){

            // the user is not signed in, so let's redirect to the FB login page

            $path = Router::url(['controller' => 'home', 'action' => 'signup']);
            header('Location: '.$path);

            die();

        }

        $userObject = $this->User->findByFbid($user_id);

        if(!$userObject){

            $userObject = $this->User->create();

            $userObject['User']['fbid'] = $user_id;

            $myInfo = $facebook->api('/me');
            $userObject['User']['birthday'] = date('Y-m-d H:i:s', strtotime($myInfo['birthday']));

            $userObject = $this->User->save($userObject);

        }


        // echo '<pre>';
        $fbLikes = $facebook->api('/me/likes');

        $orderedFBLikes = [];
        $allFBLikeIDs = [];
        foreach($fbLikes['data'] as $currentLike){

            $currentFBLikeID = $currentLike['id'];
            $orderedFBLikes[$currentFBLikeID] = $currentLike;
            $allFBLikeIDs[] = $currentFBLikeID;

        }

        // print_r($fbLikes);
        // print_r($orderedFBLikes);
        // print_r($allFBLikeIDs);

        // let's find the local likes that are not within the facebook likes

        $userInterestObject = $this->UserInterest;
        /* @var UserInterest $userInterestObject; */


        // so first of all, we delete all the local likes that are not within the facebook likes

        $deletionConditions = [];
        $deletionConditions['User.id'] = $userObject['User']['id'];
        $deletionConditions['Interest.fbid NOT IN '] = $allFBLikeIDs;
        $userInterestObject->deleteAll($deletionConditions);
        // $deletionConditions['']



        // now, we need to iterate through the facebook interests and add those which are not there yet



        foreach($orderedFBLikes as $currentFBLikeID => $currentLike){

            $searchOptions = [];
            $searchOptions['conditions']['User.id'] = $userObject['User']['id'];
            $searchOptions['conditions']['Interest.fbid'] = $currentFBLikeID;
            $duplicate = $userInterestObject->find('first', $searchOptions);
            if($duplicate){ continue; } // we don't need to resave duplicate

            $interestObject = $this->Interest->findByFbid($currentFBLikeID);
            if(!$interestObject){
                $interestObject = $this->Interest->create();
                $interestObject['Interest']['fbid'] = $currentFBLikeID;
                $interestObject['Interest']['type'] = 'like';
                $interestObject['Interest']['name'] = $currentLike['name'];
                $interestObject['Interest']['imagePath'] = 'https://graph.facebook.com/'.$currentFBLikeID.'/picture';
                $interestObject = $this->Interest->save($interestObject);
            }

            $userInterest = $userInterestObject->create();
            $userInterest['UserInterest']['user_id'] = $userObject['User']['id'];
            $userInterest['UserInterest']['interest_id'] = $interestObject['Interest']['id'];
            $userInterest = $userInterestObject->save($userInterest);

        }

        /*

        $searchOptions = [];
        $searchOptions['conditions']['User.id'] = $userObject['User']['id'];
        $searchOptions['conditions']['Interest.fbid IN '] = $allFBLikeIDs;
        $localLikes = $this->UserInterest->find('all', $searchOptions);

        // print_r($localLikes);

        */



        if($_GET['lat']&& $_GET['lon']){

            $location = [$_GET['lat'], $_GET['lon']];

            $_SESSION['location'] = $location;



        }



        $location = $_SESSION['location'];

        $hasLocation = !!$location;

        $this->set('hasLocation', $hasLocation);

        // found good matches

        if($hasLocation){

            $userObject['User']['latitude'] = $location[0];
            $userObject['User']['longtitude'] = $location[1];
            $this->User->save($userObject);

            // $relevantMatches = ['asdasd' => 'asdasd']; // is an array
            // $this->set('relevantMatches', $relevantMatches);





            //





        }else{



        }


        $userLocalID = $userObject['User']['id'];

        $relevantMatches = [];


        mysql_connect('localhost', 'soev', 'soev');
        mysql_select_db('soev');

        $queryString = 'SELECT *,COUNT(`interest_id`) AS `commonity` FROM `user_interests` WHERE `interest_id` IN ( SELECT `interest_id` FROM `user_interests` WHERE `user_id` = '.$userLocalID.' ) AND `user_id` != '.$userLocalID.' GROUP BY `user_id` ORDER BY `commonity` DESC';
        $query = mysql_query($queryString);

        while($row = mysql_fetch_assoc($query)){

            $currentUser = $this->User->findById($row['user_id']);
            $currentInterests = $currentUser['UserInterest'];

            foreach($currentInterests as $interestIndex => &$currentInterest){

                $currentInterest = $this->Interest->findById($currentInterest['interest_id']);

                $currentInterestID = $currentInterest['Interest']['fbid'];
                if(in_array($currentInterestID, $allFBLikeIDs)){

                    $currentInterest['common'] = true;

                }else{

                    // unset($currentInterest); // sorry
                    unset($currentInterests[$interestIndex]);

                }


            }

            $currentUser['UserInterest'] = $currentInterests;

            $relevantMatches[] = $currentUser;

        }

        $this->set('relevantMatches', $relevantMatches);

    }

    public function signup(){

        // the user is not currently logged in but needs to do that

        $facebook = FBLib::getFBObject();

        $user_id = $facebook->getUser();

        if($user_id){

            // WAIT, I actually AM signed in? This should not be the case

            $path = Router::url(['controller' => 'home', 'action' => 'index']);
            header('Location: '.$path);

            die();

        }


        $redirectURL = Router::url(['controller' => 'home', 'action' => 'index'], true);

        echo $redirectURL;

        $params = [
            // 'scope' => 'basic_info,user_birthday,user_interests,user_likes',
            'scope' => 'basic_info,user_birthday,user_interests',
            'redirect_uri' => $redirectURL
        ];

        $this->set('fblogin', $facebook->getLoginUrl($params));

    }

} 