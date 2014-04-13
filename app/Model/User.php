<?php
/**
 * Created by IntelliJ IDEA.
 * User: arik-so
 * Date: 4/12/14
 * Time: 4:51 PM
 */

class User extends AppModel{

    public $hasMany = ['UserInterest'];

} 