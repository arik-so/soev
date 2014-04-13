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

    public $uses = ['User', 'UserInterest'];

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

        echo $user_id;
        $userObject = $this->User->findByFbid($user_id);

        if(!$userObject){

            $userObject = $this->User->create();
            $userObject['User']['fbid'] = $user_id;
            $userObject = $this->User->save($userObject);

        }


        echo '<pre>';
        $fbLikes = $facebook->api('/me/likes');
        print_r($fbLikes);

        $searchOptions = [];
        $searchOptions['conditions']['User.id'] = $userObject['User']['id'];
        $localLikes = $this->UserInterest->fetch('all', $searchOptions);

        print_r($localLikes);

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
            'scope' => 'basic_info,user_birthday,user_interests,user_likes',
            'redirect_uri' => $redirectURL
        ];

        $this->set('fblogin', $facebook->getLoginUrl($params));

    }

} 