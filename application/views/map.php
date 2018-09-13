<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Live Camera</title>
    <meta name="description" content="Live traffic map for the location" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-material-design@4.1.1/dist/css/bootstrap-material-design.min.css" integrity="sha384-wXznGJNEXNG1NFsbm0ugrLFMQPWswR3lds2VeinahP8N0zJw9VWSopbjv2x7WCvX" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/home.css'); ?>" type="text/css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.js" ></script>
</head>

<body>
<div class="se-pre-con">
    <img id="loading-image" src="<?php echo base_url('assets/images/load_process.gif'); ?>" alt="Loading..." />
</div>
<div class="container">
    <div className="home-page">
        <nav class="navbar navbar-light app-bar" >
            <a href="<?php echo base_url('')?>" class="navbar-brand " href="#">
                <i class="small material-icons">keyboard_backspace</i>
                Map
            </a>
        </nav>
        <iframe
            width="100%"
            height="800"
            frameBorder="0" 
            src=<?=$map_link?> allowFullScreen ></iframe>
    </div>
    </div>
<script>
jQuery(document).ready(function ($) {
    $(window).load(function () {
        $('.se-pre-con').fadeOut('slow', function () {
            $('.container').show();
        });
    });  
});
</script>
</body>