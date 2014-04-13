<script type="text/javascript">

    <?php if(!$hasLocation){ ?>

    function getLocation(){
        if (navigator.geolocation){
            navigator.geolocation.getCurrentPosition(showPosition);
        }
    }
    function showPosition(position){

        location.href = '?lat='+position.coords.latitude+'&lon='+position.coords.longitude;
    }

    getLocation();

    <?php } ?>

</script>

<pre>

<?php

// $relevantMatches;

print_r($relevantMatches);

foreach($relevantMatches as $currentMatch){

    $currentUser = $currentMatch['User'];
    $currentUserPhotoLink = 'https://graph.facebook.com/'.$currentUser['fbid'].'/picture';
    $interests = $currentMatch['UserInterest'];

    ?>

        <div style="background-color: green;">

            <img src="<?php echo $currentUserPhotoLink; ?>" />

            Common Interests: <br/>

            <?php

                foreach($interests as $currentInterest){

                    $currentInterestObject = $currentInterest['Interest'];

                    $interestFBPhotoLink = 'https://graph.facebook.com/'.$currentInterestObject['fbid'].'/picture';

                    ?>

                        <img src="<?php echo $interestFBPhotoLink; ?>" />

                    <?php

                }

            ?>

        </div>


    <?php

}

?>

</pre>