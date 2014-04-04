<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require_once '../php-sdk/facebook.php';

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
    'appId'  => '441093369359229',
    'secret' => '6fa913f044435ba2d67387fcfd825868',
    'cookie' => true
));

// Get User ID
$user = $facebook->getUser();

// We may or may not have this data based on whether the user is logged in.
//
// If we have a $user id here, it means we know the user is logged into
// Facebook, but we don't know if the access token is valid. An access
// token is invalid if the user logged out of Facebook.

if ($user) {
    try {
        // Proceed knowing you have a logged in user who's authenticated.

        $user_albums = $facebook->api('/me/albums');
        $albums = array();
        if(!empty($user_albums['data'])) {
            foreach($user_albums['data'] as $album) {
                $temp = array();
                $temp['id'] = $album['id'];
                $temp['name'] = $album['name'];
                $temp['thumb'] = "https://graph.facebook.com/{$album['id']}/picture?type=album&access_token={$facebook->getAccessToken()}";
                $temp['count'] = (!empty($album['count'])) ? $album['count']:0;
                if($temp['count']>1 || $temp['count'] == 0)
                    $temp['count'] = $temp['count'] . " photos";
                else
                    $temp['count'] = $temp['count'] . " photo";
                $albums[] = $temp;
            }
        }
    } catch (FacebookApiException $e) {
        error_log($e);
        $user = null;
    }
}

// Login or logout url will be needed depending on current user state.
if ($user) {
    $logoutUrl = $facebook->getLogoutUrl();
} else {
    $statusUrl = $facebook->getLoginStatusUrl();
    $loginUrl = $facebook->getLoginUrl(array(
        'scope' => 'user_photos'
    ));
}

// This call will always work since we are fetching public data.
$aravind = $facebook->api('/aravindanB');

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
    <title>php-sdk</title>

    <link href="../css/style.css" media="screen" type="text/css" rel="stylesheet">

    <script type="text/javascript" src="../js/jquery-1.6.1.min.js "></script>
    <script type="text/javascript" src="../js/jquery.fancybox-1.3.4.pack.js"></script>

</head>
<body>



<h1>php-sdk</h1>

<?php if ($user): ?>
    <a href="<?php echo $logoutUrl; ?>">Logout</a>
<?php else: ?>
    <div>
        Check the login status using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $statusUrl; ?>">Check the login status</a>
    </div>
    <div>
        Login using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $loginUrl; ?>">Login with Facebook</a>
    </div>
<?php endif ?>

<?php if ($user): ?>
    <h3>Public profile of Aravindan Balan</h3>
    <img src="https://graph.facebook.com/<?php echo $user; ?>/picture">

<?php else: ?>
    <strong><em>You are not Connected.</em></strong>
<?php endif ?>

<?php echo $aravind['name']; ?>

<h3>My Facebook Albums</h3>

<div id="wrapper">
    <div id="header">
        <h1><a href="hackathon.php">My Facebook Albums Browser</a></h1>
        <div class="links">
            <?php if ($user): ?>
                <a class="login" href="<?php echo $logoutUrl; ?>">Logout</a>
            <?php else: ?>
                <a class="login" href="<?php echo $loginUrl; ?>">Login with Facebook</a>
            <?php endif ?>
        </div>
    </div>
    <div id="content">
        <?php if(!empty($albums)) { ?>
            <table id="albums">
                <tr>
                    <?php
                    $count = 1;
                    foreach($albums as $album) {
                        if( $count%6 == 0 )
                            echo "</tr><tr>";
                        echo	"<td>" .
                            "<a href=\"album.php?id={$album['id']}\">" .
                            "<div class=\"thumb\" style=\"background: url({$album['thumb']}) no-repeat 50% 50%\"></div>" .
                            "<p>{$album['name']}</p>" .
                            "<p>{$album['count']}</p>" .
                            "</a></td>";
                        $count++;
                    }
                    ?>
                </tr>

            </table>
        <?php } ?>
    </div>

</div>

</body>
</html>


<?php
/**
 * for creating new images with additional metadata - use this for panaroma
 * https://developers.facebook.com/docs/opengraph/using-objects/
 *
 * http://phpflickr.com/ - for connecting to flickr
 *
 * picasa - https://developers.google.com/picasa-web/docs/1.0/developers_guide_php
 */
?>