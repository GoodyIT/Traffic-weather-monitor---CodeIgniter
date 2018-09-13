<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Causeway Live</title>
    <meta name="description" content="A React.js Boilerplate application homepage" />
    
    <!-- Compiled and minified CSS -->
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.js" ></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.css"  type="text/css">

    <link rel="stylesheet" href="<?php echo base_url('assets/css/home.css'); ?>" type="text/css">
</head>
</head>
<body>
<div class="se-pre-con">
    <img id="loading-image" src="<?php echo base_url('assets/images/load_process.gif'); ?>" alt="Loading..." />
</div>
<div class="container" style="display:none;">
    <div class="home-page">
        <div class="main-frame">
            <div class="top-div">
                <div class="top-title">Causeway Live Traffic | Weather</div>
                <div class="traffic">
                <div class="image-wrapper"><img src="<?=$traffic_sign?>" alt="traffic light" /></div>
                <div class="traffic-text">
                    <div class="src-dst"><?=$src['title']?> -> <?=$dst['title']?></div>
                    <div class="est"><div class="est-text" style="display:inline-block;"><?= $est ?></div>&nbsp;<div class="traffic-type" style="display:inline-block; color:<?=$traffic_color?>;"> <?=$traffic_status?></div></div>
                </div>
                </div>
                <div class="weather">
                <div class="weather-text">
                    <div class="time"><?=$today?></div>
                    <div class="summary" style="display:inline-block;"><?= $weather_summary ?></div>&nbsp;
                    <div style="display:inline-block;" class="temperature">&nbsp;<?=$temperature?></div>
                </div>
                <div class="weather-icon"><img src="<?php echo base_url('assets/images/icon_weather.png')?>" alt="weather icon" /></div>
                </div>
            </div>

            <div class="currency">
                <div><img src="<?php echo base_url('/assets/images/icon_currency.png')?>" alt="Currency Icon" /></div>
                <div class="currency-text">1 SGD&nbsp;&nbsp;-&nbsp;&nbsp;MYR <span class="my"><?=$MYR?></span>&nbsp;&nbsp;/&nbsp;&nbsp;USD <span class="us"><?=$USD?></span></div>
            </div>
        </div>

        <div class="bottom-frame">
            <div class="destination">
                <div class="destination-text">Destination</div>
                <div class="flags">
                    <span class="src flag-icon flag-icon-<?=$src['code']?>"></span>
                        &nbsp;&nbsp;<strong>></strong>&nbsp;&nbsp;
                    <span class="dst flag-icon flag-icon-<?=$dst['code']?>"></span>
                </div>
            </div>

            <div class="from-to">
                <div class="from-to-group">
                    <div style="text-align: right;">
                        <p>from:</p>
                        <h3 class="from-place"><?=$src['title']?></h3>
                    </div>
                    <div class="switch-wrapper">
                        <a class="waves-effect waves-light btn btn-small white-color "><i class="small material-icons">repeat</i></a>
                    </div>
                    <div style="text-align: left;">
                        <p>to:</p>
                        <h3 class="to-place"><?=$dst['title']?></h3>
                    </div>
                </div>
                <div class="button-group">
                    <a href="<?=$map_url?>" class="map-link waves-effect waves-light btn btn-small primary-color"><i class="material-icons left">pin_drop</i>View Map</a>
                    <a href="<?php echo base_url('index.php/welcome/camera')?>" class="waves-effect waves-light btn btn-small primary-color"><i class="material-icons left">videocam</i>Live Cam</a>
                </div>
            </div>

            <div class="adsense">
                <div>ADSENSE DIV</div>
            </div>
        </div>
    </div>
</div>

<script>
var state; 

function switchLocation() {
    var src_code = state.src.code;
    var dst_code = state.dst.code;
    $.ajax({
        type: 'GET',
        url:  "<?php echo base_url(); ?>index.php/welcome/index/" + src_code + "/" + dst_code + "/1",
        success: function(data) {
            data = JSON.parse(data);
            $('.image-wrapper img').attr('src', data.traffic_sign);
            $('.src-dst').text(data.src.title + '->' + data.dst.title);
            $('.est-text').text(data.est);
            $('.traffic-type').text(data.traffic_status).css('color', data.traffic_color);
            $('.weather-text .summary').text(data.weather_summary);
            $('.weather-text .time').text(data.today);
            $('.weather-text .temperature').text(data.temperature);
            $('.currency-text my').text(data.MYR);
            $('.currency-text us').text(data.USD);
            $('.flags .src').attr('class', '').addClass('src flag-icon flag-icon-' + data.src.code);
            $('.flags .dst').attr('class', '').addClass('dst flag-icon flag-icon-' + data.dst.code);
            $('.from-place').text(data.src.title);
            $('.to-place').text(data.dst.title);
            $('.map-link').attr('href', data.map_url);
        }, 
        error: function(err) {
            console.log(err);
        },
    });
}

var routeInfo = localStorage.getItem('route');
if (routeInfo != null) {
    state = JSON.parse(routeInfo);
    switchLocation();
} else {
    routeInfo = {
        src: {
            code: 'sg',
            title: 'Singapore',
            path: 'Woodlands+Checkpoint,+21+Woodlands+Crossing,+738203',
            place_id: 'ChIJcax8Ev0S2jER7fTRxrPHz2w',
        },
        dst: {
            code: 'my',
            title: 'Malaysia',
            path: 'Sultan+Iskandar+Complex+Customs,+Jalan+Jim+Quee,+Bukit+Chagar,+80300+Johor+Bahru,+Johor,+Malaysia',
            place_id: 'ChIJ4-MEgNwS2jERPDLDNgWnENA',
        }
    }
    state = routeInfo;
    localStorage.setItem('route', JSON.stringify(routeInfo));
}

jQuery(document).ready(function ($) {
    $(window).load(function () {
        setTimeout(function(){
            $('.se-pre-con').fadeOut('slow', function () {
                $('.container').show();
            });
           
        },1000); // set the time here

        $( ".switch-wrapper a" ).click(function(event) {
            event.preventDefault();
            var temp = state.src;
            var src = state.dst;
            var dst = temp;
            state = {src: src, dst: dst};
            localStorage.setItem('route', JSON.stringify(state ));
            switchLocation();
        });

        
    });  
});

</script>
</body>