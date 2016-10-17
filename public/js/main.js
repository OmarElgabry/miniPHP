/*
 *  Document   : main.js
 *  Author     : Omar El Gabry <omar.elgabry.93@gmail.com>
 *  Description: Main Javascript file for the application.
 *               It handles all Ajax calls, Events, and DOM Manipulations
 *
 */


/*
 * Configuration Variables
 * An Object with key-value paris assigned in footer.php
 *
 * @see footer.php
 * @see core/Controller.php
 */

var config = {};

/*
 * Ajax
 */

var ajax = {

    /**
     * Default ajax function.
     *
     * @param  string   url             URL to send ajax call
     * @param  mixed    postData        data that will be sent to the server(PHP)
     * @param  function callback        Callback Function that will be called upon success or failure
     * @param  string   spinnerBlock    An element where the spinner will be next to it
     *
     */
    send: function(url, postData, callback, spinnerBlock){

        var spinnerEle = null;

        $.ajax({
            url: config.root + url,
            type: "POST",
            data: helpers.appendCsrfToken(postData),
            dataType: "json",
            beforeSend: function() {

                // create the spinner element, and add it after the spinnerBlock
                spinnerEle = $("<i>").addClass("fa fa-spinner fa-3x fa-spin spinner").css("display", "none");
                $(spinnerBlock).after(spinnerEle);

                // run the spinner
                ajax.runSpinner(spinnerBlock, spinnerEle);
            }
        })
            .done(function(data) {
                // stopSpinner(spinnerBlock);
                callback(data);
            })
            .fail(function(jqXHR) {
                // stopSpinner(spinnerBlock);
                switch (jqXHR.status){
                    case 0:
                        callback(null);
                    case 302:
                        helpers.redirectTo(config.root);
                        break;
                    default:
                        helpers.displayErrorPage(jqXHR);
                }
            })
            .always(function() {
                ajax.stopSpinner(spinnerBlock, spinnerEle);
            });
    },

    /**
     * Ajax call - ONLY for files.
     *
     * @param  string   url             URL to send ajax call
     * @param  object   fileData        data(formData) that will be sent to the server(PHP)
     * @param  function callback        Callback Function that will be called upon success or failure
     *
     */
    upload: function(url, fileData, callback){

        $.ajax({
            url: config.root + url,
            type: "POST",
            data: helpers.appendCsrfToken(fileData),
            dataType: "json",
            beforeSend: function () {
                // reset the progress bar
                $(".progress .progress-bar").css("width", "0%").html("0%");
            },
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                // check if upload property exists
                if(myXhr.upload){
                    myXhr.upload.addEventListener('progress', ajax.progressbar, false);
                    $(".progress").removeClass("display-none");
                }
                return myXhr;
            },
            contentType: false,
            cache: false,
            processData:false
        })
            .done(function(data) {
                callback(data);
            })
            .fail(function(jqXHR) {
                switch (jqXHR.status){
                    case 0:
                        callback(null);
                    case 302:
                        helpers.redirectTo(config.root);
                        break;
                    default:
                        helpers.displayErrorPage(jqXHR);
                }
            })
            .always(function() {
                $(".progress").addClass("display-none");
            });
    },
    progressbar: function(e){
        if(e.lengthComputable){
            var meter = parseInt((e.loaded/e.total) * 100);
            $(".progress .progress-bar").css("width", meter+"%").html(meter + "%");
        }
    },
    runSpinner: function(spinnerBlock, spinnerEle){

        if(!helpers.empty(spinnerBlock)) {
            // var spinner = $(spinnerBlock).nextAll(".spinner:eq(0)");
            $(spinnerEle).show();
            $(spinnerBlock).css("opacity","0.6");
        }
    },
    stopSpinner: function(spinnerBlock, spinnerEle){
        if(!helpers.empty(spinnerBlock) ) {
            // var spinner = $(spinnerBlock).nextAll(".spinner:eq(0)");
            $(spinnerEle).remove();
            $(spinnerBlock).css("opacity","1");
        }
    }
};

/*
 * Helpers
 *
 */

var helpers = {

    /**
     * append csrf token to data that will be sent in ajax
     *
     * @param  mixed  data
     *
     */
    appendCsrfToken: function (data){

        if(typeof (data) === "string"){
            if(data.length > 0){
                data = data + "&csrf_token=" + config.csrfToken;
            }else{
                data = data + "csrf_token=" + config.csrfToken;
            }
        }

        else if(data.constructor.name === "FormData"){
            data.append("csrf_token", config.csrfToken);
        }

        else if(typeof(data) === "object"){
            data.csrf_token = config.csrfToken;
        }

        return data;
    },

    /**
     * replaces the current page with error page returned from ajax
     *
     * @param  XMLHttpRequest  jqXHR
     * @see http://stackoverflow.com/questions/4387688/replace-current-page-with-ajax-content
     */
    displayErrorPage: function (jqXHR) {
        document.open();
        document.write(jqXHR.responseText);
        document.close();
    },

    /**
     * Extract keys from JavaScript object to be used as variables
     * @param  object  data
     */
    extract: function (data) {
        for (var key in data) {
            window[key] = data[key];
        }
    },

    /**
     * Checks if an element is empty(set to null or undefined)
     *
     * @param  mixed foo
     * @return boolean
     *
     */
    empty: function (foo){
        return (foo === null || typeof(foo) === "undefined")? true: false;
    },

    /**
     * extends $().html() in jQuery
     *
     * @param   string  target
     * @param   string  str
     */
    html: function (target, str){
        $(target).html(str);
    },

    /**
     * extends $().after() in jQuery
     *
     * @param   string  target
     * @param   string  str
     */
    after: function (target, str){
        $(target).after(str);
    },

    /**
     * clears all error and success messages
     *
     * @param   string  target
     */
    clearMessages: function (target){

        if(helpers.empty(target)){
            $(".error").remove();
            $(".success").remove();
        } else{
            // $(target).next(".error").remove();
            // $(target).next(".success").remove();
            $(target).nextAll(".error:eq(0)").remove();
            $(target).nextAll(".success:eq(0)").remove();
        }
    },

    /**
     * Extend the serialize() function in jQuery.
     * This function is designed to add extra data(name => value) to the form.
     *
     * @param   object  ele     Form element
     * @param   string  str     String to be appended to the form data.
     * @return  string          The serialized form data in form of: "name=value&name=value"
     *
     */
    serialize: function (ele, str){
        if(helpers.empty(str)){
            return $(ele).serialize();
        } else {
            return $(ele).serialize()  + "&" + str;
        }
    },

    /**
     * This function is used to redirect.
     *
     * @param string location
     */
    redirectTo: function (location){
        window.location.href = location;
    },

    /**
     * encode potential text
     * All encoding are done and must be done on the server side,
     * but you can use this function in case it's needed on client.
     *
     * @param string  str
     */
    encodeHTML: function (str){
        return $('<div />').text(str).html();
    },

    /**
     * validate form file size
     * It's important to validate file size on client-side to avoid overflow in $_POST & $_FILES
     *
     * @param   string form  form element
     * @param   string id    id of the file input element
     * @see     app/core/Request/dataSizeOverflow()
     */
    validateFileSize: function (fileId){

        var size = document.getElementById(fileId).files[0].size;
        return size < config.fileSizeOverflow;
    },

    /**
     * display error message
     *
     * @param  string  targetBlock  The target block where the error or success alerts will be inserted
     * @param  string  message      error message
     *
     */
    displayError: function (targetBlock, message){

        // 1. clear
        helpers.clearMessages(targetBlock);

        // 2. display
        var alert    = $("<div>").addClass("alert alert-danger");
        var notation = $("<i>").addClass("fa fa-exclamation-circle");
        alert.append(notation);

        message =  helpers.empty(message)? "Sorry there was a problem": message;
        alert.append(" " + message);

        var error = $("<div>").addClass("error").html(alert);
        $(targetBlock).after(error);
    },

    /**
     * Validate the data coming from server side(PHP)
     *
     * The data coming from PHP should be something like this:
     *      data = [error = "some html code", success = "some html code", data = "some html code", redirect = "link"];
     *
     * @param   object   result        The Data that was sent from the server(PHP)
     * @param   string   targetBlock    The target block where the error or success alerts(if exists) will be inserted inside/after it
     * @param   string   errorFunc      The function that will be used to display the error, Ex: html(), after(), ..etc.
     * @param   string   errorType      specifies how the error will be displayed, default or as row
     * @param   string   returnVal      the expected value returned from the server(regardless of errors and redirections), Ex: success, data, ..etc.
     * @return  boolean
     */
    validateData: function (result, targetBlock, errorFunc, errorType, returnVal){

        // 1. clear all existing error or success messages
        helpers.clearMessages(targetBlock);

        // 2. Define and extend jQuery functions required to display the error.
        if(errorFunc === "html")        errorFunc = helpers.html;
        else if(errorFunc === "after")  errorFunc = helpers.after;
        else                            errorFunc = helpers.html;

        // 3. check if result is empty
        if(helpers.empty(result)){
            helpers.displayError(targetBlock);
            return false;
        }

        // If there was a redirection
        else if(!helpers.empty(result.redirect)){
            helpers.redirectTo(result.redirect);
            return false;
        }

        // If there was errors encountered and sent from the server, then display it
        else if(!helpers.empty(result.error)){

            if(errorType === "default" || helpers.empty(errorType)){
                errorFunc(targetBlock, result.error);
            } else if(errorType === "row"){
                var td = $("<td>").attr("colspan", "5");
                errorFunc(targetBlock, $(td).html(result.error));
            }

            return false;
        }

        else{

            if(returnVal === "success" && helpers.empty(result.success)){
                helpers.displayError(targetBlock);
                return false;
            } else if(returnVal === "data" && helpers.empty(result.data)){
                helpers.displayError(targetBlock);
                return false;
            } else if(returnVal !== "data" && returnVal !== "success"){
                helpers.displayError(targetBlock);
                return false;
            }
        }

        return true;
    }

};

/*
 * App
 *
 */
var app = {
    init: function (){

        // initialize todo application event
        events.todo.init();
        
        if(!helpers.empty(config.curPage)){

            // pagination
            events.pagination.init();

            // events of current page
            if(config.curPage.constructor === Array){

                config.curPage.forEach(function(sub) {
                
                    // add 'active' class to current navigation list
                    $(".sidebar-nav #"+ sub +" a").addClass("active");
                    events[sub].init();
                });

            }else{

                $(".sidebar-nav #"+ config.curPage +" a").addClass("active");
                if(!helpers.empty(events[config.curPage])){ 
                    events[config.curPage].init();   
                }
            }
        }
    }
};

/*
 * Events
 *
 */
var events = {

   /*
    * Pagination
    */
    pagination: {

        /**
         * @var integer Acts like the page number for comments
         */
        viewMoreCounter: 1,

        /**
         * @var integer Whenever there is a new comment created in-place, this will be incremented.
         */
        commentsCreated: 0,


        init: function(){

            $("ul.pagination a").click(function(e){

                var pageNumber;

                if(config.curPage === "comments" || config.curPage.indexOf("comments") > -1){
                    pageNumber = ++events.pagination.viewMoreCounter;
                }
                else if($(this).hasClass("prev")){
                    pageNumber = events.pagination.getSelectedPaginationLink() - 1;
                }
                else if($(this).hasClass("next")){
                    pageNumber = events.pagination.getSelectedPaginationLink() + 1;
                }
                else{

                    // index() returns 0-indexed
                    pageNumber = $(this).index("ul.pagination a:not(.prev):not(.next)") + 1;
                }

                if(config.curPage === "comments" || config.curPage.indexOf("comments") != -1) {
                    e.preventDefault();
                    events.comments.get(pageNumber, events.pagination.commentsCreated); 
                }
                else if(config.curPage === "users") { 
                    e.preventDefault();
                    events.users.get(pageNumber);
                }

            });
        },
        getSelectedPaginationLink: function(){
            var link = 1;

            // using index Vs the page number(text) inside the pagination
            $("ul.pagination a:not(.prev):not(.next)").each(function(index){
                if($(this).parent().hasClass("active")){
                    link = index + 1;
                    return false;
                }
            });
            return parseInt(link);
        }
    },

    /*
     * LogIn Page
     */
    login: {
        init: function(){
            events.login.tabs();
        },
        tabs: function(){
            $("#form-login #link-forgot-password, #form-forgot-password #link-login").click(function() {
                
                $("#form-login, #form-forgot-password" ).toggleClass("display-none");
                $(".error, .success").remove();
            });

            $("#link-register").click(function() {
                
                $("#form-login, #form-forgot-password").addClass("display-none");
                $("#form-register").removeClass("display-none");
                $(".panel-title").text("Register");
                $(".error, .success").remove();
            });

            $("#form-register #link-login").click(function() {
                
                $(".panel-title").text("Login");
                $("#form-register").addClass("display-none");
                $("#form-login").removeClass("display-none");
                $(".error, .success").remove();
            });
        }
    },

    /*
     * Profile
     */
    profile: {
        init: function(){
        }
    },

    /*
     * News Feed
     */
    newsfeed:{
        init: function(){
            events.newsfeed.update();
            events.newsfeed.delete();
        },
        reInit: function(){

            // It's important to have the update & delete events encapsulated inside a function,
            // so you can call the function after ajax calls to re-initialize them
            events.newsfeed.update();
            events.newsfeed.delete();
        },
        update: function(){
            $("#list-newsfeed .header .edit").off('click').on('click', function() {

                var newsfeedBody = $(this).parent().parent().parent().parent();
                var newsfeedId   = newsfeedBody.attr("id");
                getNewsFeedUpdateForm();


                // 1. get the update form merged with the current newsfeed data
                function getNewsFeedUpdateForm(){
                    ajax.send("NewsFeed/getUpdateForm", {newsfeed_id: newsfeedId}, getNewsFeedUpdateFormCallBack);

                    function getNewsFeedUpdateFormCallBack(result){
                        if(helpers.validateData(result, newsfeedBody, "html", "default", "data")){
                            newsfeedBody.html(result.data);
                            activateCancelNewsFeedEvent();
                            activateUpdateNewsFeedEvent();
                        }
                    }
                }

                // 2. if cancel, then go and get the current newsfeed(regardless of any changes)
                function activateCancelNewsFeedEvent(){

                    $("#form-update-"+newsfeedId+" button[name='cancel']").click(function(e){
                        e.preventDefault();
                        ajax.send("NewsFeed/getById", {newsfeed_id: newsfeedId}, getNewsFeedByIdCallBack);

                        function getNewsFeedByIdCallBack(result){
                            if(helpers.validateData(result, newsfeedBody, "html", "default", "data")){
                                $(newsfeedBody).after(result.data);
                                $(newsfeedBody).remove();
                                events.newsfeed.reInit();
                            }
                        }
                    });
                }

                // 3. if update, then update the current newsfeed and get back the updated one
                function activateUpdateNewsFeedEvent(){

                    $("#form-update-"+newsfeedId).submit(function(e){
                        e.preventDefault();
                        ajax.send("NewsFeed/update", helpers.serialize("#form-update-"+newsfeedId, "newsfeed_id="+newsfeedId), updateNewsFeedCallBack);

                        function updateNewsFeedCallBack(result){
                            if(helpers.validateData(result, newsfeedBody, "after", "default", "data")){
                                $(newsfeedBody).after(result.data);
                                $(newsfeedBody).remove();
                                events.newsfeed.reInit();
                            }
                        }
                    });
                }

            });
        },
        delete: function(){

            $("#list-newsfeed .header .delete").off('click').on('click', function(e) {
                e.preventDefault();
                if (!confirm("Are you sure?")) { return; }

                var newsfeedBody = $(this).parent().parent().parent().parent();
                var newsfeedId   = newsfeedBody.attr("id");

                ajax.send("NewsFeed/delete", {newsfeed_id: newsfeedId}, deleteNewsFeedCallBack);
                function deleteNewsFeedCallBack(result){
                    if(helpers.validateData(result, newsfeedBody, "html", "default", "success")){
                        $(newsfeedBody).remove();
                    }
                }
            });
        }
    },

    /*
     * Posts
     */
    posts: {
        init: function(){
        }
    },

    /*
     * Comments
     */
    comments: {
        init: function(){
            events.comments.create();
            events.comments.update();
            events.comments.delete();
        },
        reInit: function(){
            events.comments.update();
            events.comments.delete();
        },
        get: function(pageNumber, commentsCreated){

            if(helpers.empty(pageNumber)) pageNumber = 1;
            if(helpers.empty(commentsCreated)) commentsCreated = 0;

            ajax.send("Comments/getAll", {post_id: config.postId, page: pageNumber,
                comments_created: commentsCreated}, getCommentsCallBack, "#list-comments");

            function getCommentsCallBack(result){
                if(helpers.validateData(result, "#list-comments", "html", "default", "data")){
                    $("#list-comments").append(result.data.comments);
                    events.comments.reInit();

                    $("ul.pagination").html(result.data.pagination);
                    events.pagination.init();
                }else{
                    $("ul.pagination").html("");
                }
            }

        },
        create: function(){
            $("#form-create-comment").submit(function(e){
                e.preventDefault();
                ajax.send("Comments/create", helpers.serialize(this, "post_id="+config.postId), createCommentCallBack, "#form-create-comment");
            });
            function createCommentCallBack(result){
                if(helpers.validateData(result, "#form-create-comment", "after", "default", "data")){
                    $("#list-comments .no-data").remove();
                    $("#list-comments").append(result.data);

                    $("#form-create-comment textarea").val('');

                    // increment number of comments created in-place
                    events.pagination.commentsCreated++;
                    events.comments.reInit();
                }
            }
        },
        update: function(){

            $("#list-comments .header .edit").off('click').on('click', function() {

                var commentBody = $(this).parent().parent().parent().parent();
                var commentId   = commentBody.attr("id");
                getCommentUpdateForm();

                // 1. get the update form
                function getCommentUpdateForm(){
                    ajax.send("Comments/getUpdateForm", {comment_id: commentId}, getCommentUpdateFormCallBack);

                    function getCommentUpdateFormCallBack(result){
                        if(helpers.validateData(result, commentBody, "html", "default", "data")){
                            commentBody.html(result.data);
                            activateCancelCommentEvent();
                            activateUpdateCommentEvent();
                        }
                    }
                }

                // 2.
                function activateCancelCommentEvent(){
                    $("#form-update-"+commentId+" button[name='cancel']").click(function(e){
                        e.preventDefault();
                        ajax.send("Comments/getById", {comment_id: commentId}, getCommentByIdCallBack);
                        function getCommentByIdCallBack(result){
                            if(helpers.validateData(result, commentBody, "html", "default", "data")){
                                $(commentBody).after(result.data);
                                $(commentBody).remove();
                                events.comments.reInit();
                            }
                        }
                    });
                }

                // 3.
                function activateUpdateCommentEvent(){
                    $("#form-update-"+commentId).submit(function(e){
                        e.preventDefault();
                        ajax.send("Comments/update", helpers.serialize("#form-update-"+commentId, "comment_id="+commentId), updateCommentCallBack);
                        function updateCommentCallBack(result){
                            if(helpers.validateData(result, commentBody, "after", "default", "data")){
                                $(commentBody).after(result.data);
                                $(commentBody).remove();
                                events.comments.reInit();
                            }
                        }
                    });
                }

            });
        },
        delete: function(){

            $("#list-comments .header .delete").off('click').on('click', function(e) {
                e.preventDefault();
                if (!confirm("Are you sure?")) { return; }

                var commentBody = $(this).parent().parent().parent().parent();
                var commentId = commentBody.attr("id");

                ajax.send("Comments/delete", {comment_id: commentId}, deleteCommentCallBack);
                function deleteCommentCallBack(result){
                    if(helpers.validateData(result, commentBody, "html", "default", "success")){
                        $(commentBody).remove();
                    }
                }
            });
        }
    },

    /*
     * Files
     */
    files: {
        init: function(){
            events.files.create();
            events.files.delete();
        },
        reInit: function(){
            events.files.delete();
        },
        create: function(){

            $("#form-upload-file").submit(function(e){
                e.preventDefault();

                if(helpers.validateFileSize("file")){
                    ajax.upload("Files/create", new FormData(this), createFileCallBack);
                }else{
                    helpers.displayError("#form-upload-file", "File size can't exceed max limit");
                }
            });

            function createFileCallBack(result){
                if(helpers.validateData(result, "#form-upload-file", "after", "default", "data")){

                    $("#list-files .no-data").remove();

                    //How to insert/append an element by fadeIn()?
                    //@see http://stackoverflow.com/questions/4687579/append-an-element-with-fade-in-effect-jquery
                    $(result.data).hide().prependTo("#list-files tbody").fadeIn();

                    events.files.reInit();
                }
            }
        },
        delete: function(){

            $("#list-files tr td .delete").off('click').on('click', function(e) {
                e.preventDefault();
                if (!confirm("Are you sure?")) { return; }

                var row     = $(this).parent().parent();
                var fileId  = row.attr("id");

                ajax.send("Files/delete", {file_id: fileId}, deleteFileCallBack);
                function deleteFileCallBack(result){
                    if(helpers.validateData(result, row, "after", "row", "success")){
                        $(row).remove();
                    }
                }
            });
        }
    },

    /*
     * Users
     */
    users: {
        init: function(){
            events.users.search();
            events.users.update();
            events.users.delete();
        },
        reInit: function(){
            events.users.delete();
        },
        get: function(pageNumber){

            if(helpers.empty(pageNumber)) pageNumber = 1;

            var name    = $("#form-search-users input[name='name']").val();
            var email   = $("#form-search-users input[name='email']").val();
            var role    = $("#form-search-users select[name='role']").val();

            ajax.send("Admin/getUsers", {name: name, email: email, role: role, page: pageNumber},
                events.users.get_search_callback, "#list-users");
        },
        search: function(){

            $("#form-search-users").submit(function(e){
                e.preventDefault();
                ajax.send("Admin/getUsers", helpers.serialize(this, "page=1"), events.users.get_search_callback, "#list-users");
            });
        },
        get_search_callback: function(result){
            if(helpers.validateData(result, "#form-search-users", "after", "default", "data")){
                $("#list-users tbody").html(result.data.users);
                events.users.reInit();

                $("ul.pagination").html(result.data.pagination);
                events.pagination.init();
            }else{
                $("ul.pagination").html("");
            }
        },
        update: function(){

            $("#form-update-user-info").submit(function(e){
                e.preventDefault();
                ajax.send("Admin/updateUserInfo", helpers.serialize(this, "user_id="+config.userId), updateUserInfoCallBack, "#form-update-user-info");
            });

            function updateUserInfoCallBack(result){
                if(helpers.validateData(result, "#form-update-user-info", "after", "default", "success")){
                    $("#form-update-user-info").after(result.success);
                }
            }
        },
        delete: function(){

            $("#list-users tr td .delete").click(function(e){
                e.preventDefault();
                if (!confirm("Are you sure?")) { return; }

                var row     = $(this).parent().parent().parent();
                var userId  = row.attr("id");

                ajax.send("Admin/deleteUser", {user_id: userId}, deleteUserCallBack);
                function deleteUserCallBack(result){
                    if(helpers.validateData(result, row, "after", "row", "success")){
                        $(row).remove();
                    }
                }
            });
        }
    },

    /*
     * Bug, Feature or Enhancement
     */
    bugs:{
        init: function(){
        }
    },

    /*
     * Backups
     */
    backups:{
        init: function(){
        }
    },
    
    /*
     * Todo application
     */
    todo:{
        init: function(){
            events.todo.create();
            events.todo.delete();
        },
        create: function(){
            $("#form-create-todo").submit(function(e){
                e.preventDefault();
                ajax.send("Todo/create", helpers.serialize(this), createTodoCallBack, "#form-create-todo");
            });

            function createTodoCallBack(result){
                if(helpers.validateData(result, "#form-create-todo", "after", "default", "success")){
                    alert(result.success + " refresh the page to see the results");
                }
            }
        },
        delete: function(){
            $("#todo-list form.form-delete-todo").submit(function(e){
                e.preventDefault();
                if (!confirm("Are you sure?")) { return; }
                
                var cur_todo = $(this).parent();
                ajax.send("Todo/delete", helpers.serialize(this), deleteTodoCallBack, cur_todo);

                function deleteTodoCallBack(result){
                    if(helpers.validateData(result, cur_todo, "after", "default", "success")){
                        $(cur_todo).remove();
                        alert(result.success);
                    }
                }
            });
        }
    }
};









