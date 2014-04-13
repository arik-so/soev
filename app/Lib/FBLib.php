<?php
/**
 * Created by IntelliJ IDEA.
 * User: arik-so
 * Date: 4/12/14
 * Time: 5:19 PM
 */


class FBLib {

    const APP_ID = '1411226749145886';
    const APP_SECRET = '03c230d7b2dd5ef90d6d9de283efa26f';

    public static function getFBObject(){

        $config = array(
            'appId' => self::APP_ID,
            'secret' => self::APP_SECRET,
            'fileUpload' => false, // optional
            'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
        );

        $facebook = new Facebook($config); // this is a facebook object, which can do stuff

        return $facebook;

    }

} 