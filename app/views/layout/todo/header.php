<!DOCTYPE html>
<html lang="en">

<head>
		
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="mini PHP">
    <meta name="author" content="mini PHP">

    <title>mini PHP</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= PUBLIC_ROOT;?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= PUBLIC_ROOT;?>css/sb-admin-2.css">
    <link rel="stylesheet" href="<?= PUBLIC_ROOT;?>css/font-awesome.min.css" rel="stylesheet" type="text/css">
    
    <!-- Styles for ToDo Application -->
    <style>
        .todo_container{
            width:80%; 
            margin: 0 auto; 
            margin-top: 5%
        }
        #todo-list li{ 
            list-style-type: none; 
            border: 1px solid #e7e7e7;
            padding: 3px;
            margin: 3px;
        }
        #todo-list li:hover{
            background-color: #eee;
        }
        form button{
            float:right;
            margin: 3px;
        }
        form:after{
            content: '';
            display: block;
            clear: both;
        }
    </style>
</head>
<body>