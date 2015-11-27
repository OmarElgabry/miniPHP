<!DOCTYPE html>
<html lang="en">

<head>
		
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="mini PHP">
    <meta name="author" content="mini PHP">

    <title>mini PHP</title>

	<!-- Icons -->
        <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
        <link rel="shortcut icon" href="<?= PUBLIC_ROOT;?>img/icons/favicon.ico">
        <link rel="apple-touch-icon" href="<?= PUBLIC_ROOT;?>img/icons/icon57.png" sizes="57x57">
        <link rel="apple-touch-icon" href="<?= PUBLIC_ROOT;?>img/icons/icon72.png" sizes="72x72">
        <link rel="apple-touch-icon" href="<?= PUBLIC_ROOT;?>img/icons/icon76.png" sizes="76x76">
        <link rel="apple-touch-icon" href="<?= PUBLIC_ROOT;?>img/icons/icon114.png" sizes="114x114">
        <link rel="apple-touch-icon" href="<?= PUBLIC_ROOT;?>img/icons/icon120.png" sizes="120x120">
        <link rel="apple-touch-icon" href="<?= PUBLIC_ROOT;?>img/icons/icon144.png" sizes="144x144">
        <link rel="apple-touch-icon" href="<?= PUBLIC_ROOT;?>img/icons/icon152.png" sizes="152x152">
    <!-- END Icons -->
	
	
	<!-- Stylesheets -->
        <link rel="stylesheet" href="<?= PUBLIC_ROOT;?>css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= PUBLIC_ROOT;?>css/sb-admin-2.css">
        <link rel="stylesheet" href="<?= PUBLIC_ROOT;?>css/font-awesome.min.css" rel="stylesheet" type="text/css">
		
</head>

<body>

    <div id="wrapper">
		<?php require_once(Config::get('VIEWS_PATH') . "layout/default/navigation.php");?>