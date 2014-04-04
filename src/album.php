<?php
if( !isset($_GET['id']) )
    die("No direct access allowed!");
require_once '../php-sdk/facebook.php';

$facebook = new Facebook(array(
    'appId'  => '441093369359229',
    'secret' => '6fa913f044435ba2d67387fcfd825868',
    'cookie' => true
));

$user = $facebook->getUser();
if ($user) {
    try {
        $logoutUrl = $facebook->getLogoutUrl();

        $params = array();
        if( isset($_GET['offset']) )
            $params['offset'] = $_GET['offset'];
        if( isset($_GET['limit']) )
            $params['limit'] = $_GET['limit'];
        $params['fields'] = 'name,source,images';
        $params = http_build_query($params, null, '&');
        $album_photos = $facebook->api("/{$_GET['id']}/photos?$params");
        if( isset($album_photos['paging']) ) {
            if( isset($album_photos['paging']['next']) ) {
                $next_url = parse_url($album_photos['paging']['next'], PHP_URL_QUERY) . "&id=" . $_GET['id'];
            }
            if( isset($album_photos['paging']['previous']) ) {
                $pre_url = parse_url($album_photos['paging']['previous'], PHP_URL_QUERY) . "&id=" . $_GET['id'];
            }
        }
        $photos = array();
        if(!empty($album_photos['data'])) {
            foreach($album_photos['data'] as $photo) {
                $temp = array();
                $temp['id'] = $photo['id'];
                $temp['name'] = (isset($photo['name'])) ? $photo['name']:'';
                $temp['picture'] = $photo['images'][1]['source'];
                $temp['source'] = $photo['source'];
                $photos[] = $temp;
            }
        }
    } catch (FacebookApiException $e) {
        error_log($e);
        $user = null;
    }
} else {
    header("Location: hackathon.php");
}
?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
<head>

    <title>My Facebook Album</title>
    <link href="../css/style.css" media="screen" type="text/css" rel="stylesheet">
    <link href="../css/jquery.fancybox-1.3.4.css" media="screen" type="text/css" rel="stylesheet">
    <link href="../css/panorama_viewer.css" media="screen2" type="text/css" rel="stylesheet">
    <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
    <script type="text/javascript" src="../js/jquery.fancybox-1.3.4.pack.js"></script>
    <script type="text/javascript" src="../js/jquery.panorama_viewer.js"></script>
    <script type="text/javascript" src="../js/jquery.panorama_viewer.min.js"></script>

</head>
<body>
<div id="wrapper">
    <div id="header">
        <h1><a href="hackathon.php">My Facebook Album</a></h1>
        <div class="links">
            <?php if ($user): ?>
                <a class="login" href="<?php echo $logoutUrl; ?>">Logout</a>
            <?php endif ?>
        </div>
    </div>
    <div id="content">
        <?php if(!empty($photos)) { ?>
            <table id="album">
                <tr>
                    <?php
                    $count = 0;
                    foreach($photos as $photo) {
                        $lastChild = "";
                        if( $count%5 == 0 && $count != 0 )
                            echo "</tr><tr>";
                        $count++;
                        list($width, $height, $type, $attr) = getimagesize("{$photo['picture']}");
                        $aspect = $width / $height;
                        if ($aspect > 4 )
                        {
                            // it is a 360 panaroma
                          ?>
                            <script>
                                $(".panorama").css("repeat",true);
                            </script>
                            <?php

                            echo	"<td>" .
                                " <div class=\"panorama\" rel=\"pic_gallery\">" .
                                "<img src=\"{$photo['source']}\"> " .
                                "</div>" .
                                "</td>";

                        }
                        else if ($aspect > 2 && $aspect < 4)
                        {
                            //it is a 180 panaroma
                            echo	"<td>" .
                                " <div class=\"panorama\" rel=\"pic_gallery\">" .
                                "<img src=\"{$photo['source']}\"> " .
                                "</div>" .
                                "</td>";
                        }
                        else
                        {
                            //normal image - aspect ratio < 2
                            echo	"<td>" .
                                "<a href=\"{$photo['source']}\" title=\"{$photo['name']}\" rel=\"pic_gallery\">" .
                                "<div class=\"thumb\" style=\"background-image: url({$photo['picture']})\"></div>" .
                                "</a></td>";
                        }
                    }
                    ?>
                </tr>
            </table>
            <?php if(isset($album_photos['paging'])) { ?>
                <div class="paging">
                    <?php if(isset($next_url)) { echo "<a class='next' href='album.php?$next_url'>Next</a>"; } ?>
                    <?php if(isset($pre_url)) { echo "<a class='prev' href='album.php?$pre_url'>Previous</a>"; } ?>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>

<script>
    $(function() {
        $("a[rel=pic_gallery]").fancybox({
            'titlePosition' 	: 'over',
            'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
                return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
            }
        });
        $("div[rel=pic_gallery]").fancybox({
            'titlePosition' 	: 'over',
            'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
                return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
            }
        });
    });
</script>
<script>
    $(document).ready(function () {

        $(".panorama").panorama_viewer({
            repeat: false,              // The image will repeat when the user scroll reach the bounding box. The default value is false.
            direction: "horizontal",    // Let you define the direction of the scroll. Acceptable values are "horizontal" and "vertical". The default value is horizontal
            animationTime: 700,         // This allows you to set the easing time when the image is being dragged. Set this to 0 to make it instant. The default value is 700.
            easing: "ease-out",         // You can define the easing options here. This option accepts CSS easing options. Available options are "ease", "linear", "ease-in", "ease-out", "ease-in-out", and "cubic-bezier(...))". The default value is "ease-out".
            overlay: true               // Toggle this to false to hide the initial instruction overlay
        });
    });

</script>
</body>
</html>
