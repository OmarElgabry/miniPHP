/*
 *  Document   : main.js
 *  Author     : Omar El Gabry <omar.elgabry.93@gmail.com>
 *  Description: Main Javascript file for the application.
 *               It handles all Ajax calls, and DOM Manipulations
 *
 */


/*
 * Ajax functions
 */

/**
 * Default ajax function.
 *
 * @param  string   url             URL to send ajax call
 * @param  mixed    postData        data that will be sent to the server(PHP)
 * @param  function callback        Callback Function that will be called upon success or failure
 * @param  string   spinnerBlock    An element where the spinner will be next to it
 *
 */
function ajax(url, postData, callback, spinnerBlock){

    //create the spinner element, and add it after the spinnerBlock
    var spinnerEle = $("<i>").addClass("fa fa-spinner fa-3x fa-spin spinner").css("display", "none");
    $(spinnerBlock).after(spinnerEle);

    $.ajax({
        url: root + url,
        type: "POST",
        data: appendCsrfToken(postData),
        dataType: "json",
        beforeSend: function() {
            //Run the spinner
            runSpinner(spinnerBlock, spinnerEle);
        }
    })
        .done(function(data) {
            //stopSpinner(spinnerBlock);
            callback(data);
        })
        .fail(function(jqXHR) {
            //stopSpinner(spinnerBlock);
            switch (jqXHR.status){
                case 302:
                    redirectTo(root);
                    break;
                default:
                    errorPage(jqXHR);
            }
        })
        .always(function() {
           stopSpinner(spinnerBlock, spinnerEle);
        });

    function runSpinner(spinnerBlock, spinnerEle){
        if(!empty(spinnerBlock)) {
            //var spinner = $(spinnerBlock).nextAll(".spinner:eq(0)");
            $(spinnerEle).show();
            $(spinnerBlock).css("opacity","0.6");
        }
    }
    function stopSpinner(spinnerBlock, spinnerEle){
        if(!empty(spinnerBlock) ) {
            //var spinner = $(spinnerBlock).nextAll(".spinner:eq(0)");
            $(spinnerEle).remove();
            $(spinnerBlock).css("opacity","1");
        }
    }
}

/**
 * Ajax call - ONLY for files.
 *
 * @param  string   url             URL to send ajax call
 * @param  object   fileData        data(formData) that will be sent to the server(PHP)
 * @param  function callback        Callback Function that will be called upon success or failure
 *
 */
function ajaxFiles(url, fileData, callback){

    $.ajax({
        url: root + url,
        type: "POST",
        data: appendCsrfToken(fileData),
        dataType: "json",
        beforeSend: function () {
            //reset the progress bar
            $(".progress .progress-bar").css("width", "0%").html("0%");
        },
        xhr: function() {
            var myXhr = $.ajaxSettings.xhr();
            // check if upload property exists
            if(myXhr.upload){
                myXhr.upload.addEventListener('progress', progressHandlingFunction, false);
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
                case 302:
                    redirectTo(root);
                    break;
                default:
                    errorPage(jqXHR);
            }
        })
        .always(function() {
            $(".progress").addClass("display-none");
        });

    function progressHandlingFunction(e){
        if(e.lengthComputable){
            var meter = parseInt((e.loaded/e.total) * 100);
            $(".progress .progress-bar").css("width", meter+"%").html(meter + "%");
        }
    }
}

/*
 * Helper functions
 *
 */

/**
 * @var string  the csrf token
 * @see footer.php
 */
var csrfToken;

/**
 * append csrf token to data that will be sent in ajax
 *
 * @param  mixed  data
 *
 */
function appendCsrfToken(data){

    if(typeof (data) === "string"){
        if(data.length > 0){
            data = data + "&csrf_token=" + csrfToken;
        }else{
            data = data + "csrf_token=" + csrfToken;
        }
    }

    else if(data.constructor.name === "FormData"){
        data.append("csrf_token", csrfToken);
    }

    else if(typeof(data) === "object"){
        data.csrf_token = csrfToken;
    }

    return data;
}

/**
 * replaces the current page with error page returned from ajax
 *
 * @param  XMLHttpRequest  jqXHR
 * @see http://stackoverflow.com/questions/4387688/replace-current-page-with-ajax-content
 */
function errorPage(jqXHR) {
    console.log(jqXHR);
    document.open();
    document.write(jqXHR.responseText);
    document.close();
}

/**
 * Extract keys from JavaScript object to be used as variables
 * @param  object  data
 */
function extract(data) {
    for (var key in data) {
        window[key] = data[key];
    }
}

/**
 * Checks if an element is empty(set to null or undefined)
 *
 * @param  mixed foo
 * @return boolean
 *
 */
function empty(foo){
    return (foo === null || typeof(foo) === "undefined")? true: false;
}

/**
 * extends $().html() in jQuery
 *
 * @param   string  target
 * @param   string  str
 */
function html(target, str){
    $(target).html(str);
}

/**
 * extends $().after() in jQuery
 *
 * @param   string  target
 * @param   string  str
 */
function after(target, str){
    $(target).after(str);
}

/**
 * clears all error and success messages
 *
 * @param   string  target
 */
function clearMessages(target){

    if(empty(target)){
        $(".error").remove();
        $(".success").remove();
    } else{
        //$(target).next(".error").remove();
        //$(target).next(".success").remove();
        $(target).nextAll(".error:eq(0)").remove();
        $(target).nextAll(".success:eq(0)").remove();
    }
}

/**
 * Validate the data coming from server side(PHP)
 *
 * The data coming from PHP should be something like this:
 *      data = [error = "some html code", success = "some html code", data = "some html code", redirect = "link"];
 *
 * @param   object   PHPData        The Data that was sent from the server(PHP)
 * @param   string   targetBlock    The target block where the error or success alerts(if exists) will be inserted inside/after it
 * @param   string   errorFunc      The function that will be used to display the error, Ex: html(), after(), ..etc.
 * @param   string   errorType      specifies how the error will be displayed, default or as row
 * @param   string   returnVal      the expected value returned from the server(regardless of errors and redirections), Ex: success, data, ..etc.
 * @return  boolean
 *
 *
 */
function validatePHPData(PHPData, targetBlock, errorFunc, errorType, returnVal){

    //1. clear all existing error or success messages
    clearMessages(targetBlock);

    //2. Define and extend jQuery functions required to display the error.
    if(errorFunc === "html")  errorFunc = html;
    else if(errorFunc === "after") errorFunc = after;
    else errorFunc = html;

    //3. check if PHPData is empty
    if(empty(PHPData)){
        displayError(targetBlock);
        return false;
    }

    //If there was a redirection
    else if(!empty(PHPData.redirect)){
        redirectTo(PHPData.redirect);
        return false;
    }

    //If there was errors encountered and sent from the server, then display it
    else if(!empty(PHPData.error)){

        if(errorType === "default" || empty(errorType)){
            errorFunc(targetBlock, PHPData.error);
        } else if(errorType === "row"){
            var td = $("<td>").attr("colspan", "5");
            errorFunc(targetBlock, $(td).html(PHPData.error));
        }

        return false;
    }

    else{

        if(returnVal === "success" && empty(PHPData.success)){
            displayError(targetBlock);
            return false;
        } else if(returnVal === "data" && empty(PHPData.data)){
            displayError(targetBlock);
            return false;
        } else if(returnVal !== "data" && returnVal !== "success"){
            displayError(targetBlock);
            return false;
        }
    }

    return true;
}

/**
 * Extend the serialize() function in jQuery.
 * This function is designed to add extra data(name => value) to the form.
 *
 * @param   object  ele     Form element
 * @param   string  str     String to be appended to the form data.
 * @return  string          The serialized form data in form of: "name=value&name=value"
 *
 */
function serialize(ele, str){
	if(empty(str)){
        return $(ele).serialize();
    } else {
        return $(ele).serialize()  + "&" + str;
    }
}

/**
 * This function is used to redirect.
 *
 * @param string location
 */
function redirectTo(location){
    window.location.href = location;
}

/**
 * encode potential text
 * All encoding are done and must be done on the server side,
 * but you can use this function in case it's needed on client.
 *
 *
 * @param string  str
 *
 */
function encodeHTML(str){
    return $('<div />').text(str).html();
}


/**
 * @var integer  max file size
 * @see $vars in controller
 */
    var maxFileSize;

/**
 * validate form file size
 * It's important to validate file size on client-side to avoid overflow in $_POST & $_FILES
 *
 * @param   string form  form element
 * @param   string id    id of the file input element
 * @see   request -> dataSizeOverflow()
 */
function validateFileSize(form, id){

    var size = document.getElementById(id).files[0].size;
    if(size > maxFileSize){
        displayError(form, "File size can't exceed max limit (" + parseInt(maxFileSize/1048576) + " MB)");
        return false;
    }
    return true;
}

/**
 * display error message
 *
 * @param  string  targetBlock  The target block where the error or success alerts will be inserted
 * @param  string  message      error message
 *
 */
function displayError(targetBlock, message){

    //1. clear
    clearMessages(targetBlock);

    //2. display
    var alert    = $("<div>").addClass("alert alert-danger");
    var notation = $("<i>").addClass("fa fa-exclamation-circle");
    alert.append(notation);

    message =  empty(message)? "Sorry there was a problem": message;
    alert.append(" " + message);

    var error = $("<div>").addClass("error").html(alert);
    $(targetBlock).after(error);
}


/*
 * LogIn Page
 */

    //Register
    $("#form-register").submit(function(e){
        e.preventDefault();
        ajax("Login/register", serialize(this), registerCallBack, "#form-register");
    });

    function registerCallBack(PHPData){
        if(validatePHPData(PHPData, "#form-register", "after", "default", "success")){
            $("#form-register").after(PHPData.success);
            $("#form-register").remove();
        }
    }

    //Login
    $("#form-login").submit(function(e){
        e.preventDefault();
        ajax("Login/login", serialize(this), function (PHPData){
            if(validatePHPData(PHPData, "#form-login", "after", "default")){
                redirectTo(root);
            }
        }, "#form-login");
    });

    $("#form-login #link-forgot-password, #form-forgot-password #link-login").click(function() {

        $( "#form-login, #form-forgot-password" ).toggleClass("display-none");
        $(".error").remove();
        $(".success").remove();
    });

    $("#link-register").click(function() {

        $( "#form-login, #form-forgot-password").addClass("display-none");
        $("#form-register").removeClass("display-none");
        $(".panel-title").text("Register");
        $(".error").remove();
        $(".success").remove();
    });

    $("#form-register #link-login").click(function() {

        $(".panel-title").text("Login");
        $( "#form-register").addClass("display-none");
        $( "#form-login").removeClass("display-none");
        $(".error").remove();
        $(".success").remove();
    });

    //Forgot Password
    $("#form-forgot-password").submit(function(e){
        e.preventDefault();
        ajax("Login/forgotPassword", serialize(this), forgotPasswordCallBack, "#form-forgot-password");
    });

    function forgotPasswordCallBack(PHPData){
        if(validatePHPData(PHPData, "#form-forgot-password", "after", "default", "success")){
            $("#form-forgot-password").after(PHPData.success);
            /*$("#form-reminder").remove();*/
        }
    }

/*
 * Password update Page
 *
 */
    $("#form-update-password").submit(function(e){
        e.preventDefault();
        ajax("Login/updatePassword", serialize(this), updatePasswordCallBack, "#form-update-password");
    });

    function updatePasswordCallBack(PHPData){
        if(validatePHPData(PHPData, "#form-update-password", "after", "default", "success")){
            $("#form-update-password").after(PHPData.success);
            $("#form-update-password").remove();
        }
    }

/*
 * Profile Page
 */
    //update profile info
    $("#form-profile-info").submit(function(e){
        e.preventDefault();
        ajax("User/updateProfileInfo", serialize(this), updateProfileInfoCallBack, "#form-profile-info");
    });

    function updateProfileInfoCallBack(PHPData){
        if(validatePHPData(PHPData, "#form-profile-info", "after", "default", "success")){
            $("#form-profile-info").after(PHPData.success);
            //$("#form-profile-info").remove();
        }
    }

    //upload profile picture
    $("#form-profile-picture").submit(function(e){
        e.preventDefault();
        ajaxFiles("User/updateProfilePicture", new FormData(this), uploadProfilePictureCallBack);
    });

    function uploadProfilePictureCallBack(PHPData){

        if(validatePHPData(PHPData, "#form-profile-picture", "after", "default", "data")){
            
			//refresh image(profile and navigation) after uploading
			//@see http://stackoverflow.com/questions/1985268/possible-to-clear-cache-of-browser-with-php-code
			
			var src = PHPData.data.src;
			$("img[class*='profile-pic']").attr( 'src', src + '?last_update=' + (+new Date()) );

            //$("#form-profile-picture").after(PHPData.success);
            //$("#form-profile-picture").remove();
        }
    }


/*
 * Courses & Labels Search Form & Pagination
 */

    /**
     * @var string  determines the current page: newsfeed, posts, files, ...etc.
     * @see layout/footer.php
     * @see $vars in controller
     */
    var globalPage;

    /**
     * @var string  the id of the current page, usually encrypted
     * @see layout/footer.php
     * @see $vars in controller
     */
    var globalPageId;

    //initialize
    function initializePageEvents(){

        initializePaginationEvents();

        if(globalPage === "newsfeed"){ initializeNewsFeedEvents(); }
        if(globalPage === "posts" || globalPage.indexOf("posts") > -1){ initializePostEvents(); }
        if(globalPage === "comments" || globalPage.indexOf("comments") > -1){ initializeCommentsEvents(); }
        if(globalPage === "files"){ initializeFilesEvents(); }
        if(globalPage === "users"){ initializeUsersEvents(); }
    }

/*
 * Pagination
 */

    /**
     * @var integer Acts like the page number for comments
     *
     */
    var viewMoreCounter = 1;

    /**
     * @var integer Whenever there is a new comment created in-place, this will be incremented.
     */
    var commentsCreated = 0;

    function initializePaginationEvents(){

        $("ul.pagination a").click(function(e){
            e.preventDefault();

            var pageNumber;

            if(globalPage === "comments" || globalPage.indexOf("comments") > -1){
                pageNumber = ++viewMoreCounter;
            }
            else if($(this).hasClass("prev")){
                pageNumber = getSelectedPaginationLink() - 1;
            }
            else if($(this).hasClass("next")){
                pageNumber = getSelectedPaginationLink() + 1;
            }
            else{

                //index() returns 0-indexed
                pageNumber = $(this).index("ul.pagination a:not(.prev):not(.next)") + 1;
            }

            if(globalPage === "newsfeed"){ getNewsFeed(pageNumber); }
            else if(globalPage === "posts"){ getPosts(pageNumber); }
            else if(globalPage === "comments" || globalPage.indexOf("comments") > -1){ getComments(pageNumber, commentsCreated); }
            else if(globalPage === "files"){ getFiles(pageNumber); }
            else if(globalPage === "users"){ getUsers(pageNumber); }

        });
    }

    function getSelectedPaginationLink(){
        var link = 1;

        //using index Vs the page number(text) inside the pagination
        $("ul.pagination a:not(.prev):not(.next)").each(function(index){
            if($(this).parent().hasClass("active")){
                link = index + 1;
                return false;
            }
        });
        return parseInt(link);
    }


/*
 * News Feed Page
 */

    //get newsfeed

    function getNewsFeed(pageNumber){
        if(empty(pageNumber)) pageNumber = 1;
        ajax("NewsFeed/getAll", {page_number: pageNumber}, getNewsFeedCallBack, "#list-newsfeed");
    }

    function getNewsFeedCallBack(PHPData){
        if(validatePHPData(PHPData, "#list-newsfeed", "html", "default", "data")){
            $("#list-newsfeed").html(PHPData.data.newsfeed);
            initializeNewsFeedEvents();
            $("ul.pagination").html(PHPData.data.pagination);
            initializePaginationEvents();
        }else{
            $("ul.pagination").html("");
        }
    }

    //create newfeed
    $("#form-create-newsfeed").submit(function(e){
        e.preventDefault();
        ajax("NewsFeed/create", serialize(this), createNewsFeedCallBack, "#form-create-newsfeed");
    });
    function createNewsFeedCallBack(PHPData){
        if(validatePHPData(PHPData, "#form-create-newsfeed", "after", "default", "data")){
            $("#list-newsfeed .no-data").remove();
            $(PHPData.data).hide().prependTo("#list-newsfeed").fadeIn();
            $("#form-create-newsfeed textarea").val('');
            initializeNewsFeedEvents();
        }
    }

    //It's important to have the events encapsulated inside a function,
    //so you can call the function after ajax calls to re-initialize them
    function initializeNewsFeedEvents(){
        updateNewsFeedEvents();
        deleteNewsFeed();
    }

    //update newsfeed
    function updateNewsFeedEvents(){

        $("#list-newsfeed .header .edit").click(function(){

            var newsfeedBody = $(this).parent().parent().parent().parent();
            var newsfeedId   = newsfeedBody.attr("id");
            getNewsFeedUpdateForm();


            //1. get the update form merged with the current newsfeed data
            function getNewsFeedUpdateForm(){
                ajax("NewsFeed/getUpdateForm", {newsfeed_id: newsfeedId}, getNewsFeedUpdateFormCallBack);

                function getNewsFeedUpdateFormCallBack(PHPData){
                    if(validatePHPData(PHPData, newsfeedBody, "html", "default", "data")){
                        newsfeedBody.html(PHPData.data);
                        activateCancelNewsFeedEvent();
                        activateUpdateNewsFeedEvent();
                    }
                }
            }

            //2. if cancel, then go and get the current newsfeed(regardless of any changes)
            function activateCancelNewsFeedEvent(){

                $("#form-update-"+newsfeedId+" button[name='cancel']").click(function(e){
                    e.preventDefault();
                    ajax("NewsFeed/getById", {newsfeed_id: newsfeedId}, getNewsFeedByIdCallBack);

                    function getNewsFeedByIdCallBack(PHPData){
                        if(validatePHPData(PHPData, newsfeedBody, "html", "default", "data")){
                            $(newsfeedBody).after(PHPData.data);
                            $(newsfeedBody).remove();
                            initializeNewsFeedEvents();
                        }
                    }
                });
            }

            //3. if update, then update the current newsfeed and get back the updated one
            function activateUpdateNewsFeedEvent(){

                $("#form-update-"+newsfeedId).submit(function(e){
                    e.preventDefault();
                    ajax("NewsFeed/update", serialize("#form-update-"+newsfeedId, "newsfeed_id="+newsfeedId), updateNewsFeedCallBack);

                    function updateNewsFeedCallBack(PHPData){
                        if(validatePHPData(PHPData, newsfeedBody, "after", "default", "data")){
                            $(newsfeedBody).after(PHPData.data);
                            $(newsfeedBody).remove();
                            initializeNewsFeedEvents();
                        }
                    }
                });
            }

        });
    }

    //delete newsfeed
    function deleteNewsFeed(){
        $("#list-newsfeed .header .delete").click(function(e){
            e.preventDefault();
            if (!confirm("Are you sure?")) { return; }

            var newsfeedBody = $(this).parent().parent().parent().parent();
            var newsfeedId   = newsfeedBody.attr("id");

            ajax("NewsFeed/delete", {newsfeed_id: newsfeedId}, deleteNewsFeedCallBack);
            function deleteNewsFeedCallBack(PHPData){
                if(validatePHPData(PHPData, newsfeedBody, "html", "default", "success")){
                    $(newsfeedBody).remove();
                }
            }
        });
    }

/*
 * Posts page
 */
    //get posts

    function getPosts(pageNumber){
        if(typeof(pageNumber) === "undefined") pageNumber = 1;
        ajax("Posts/getAll", {page_number: pageNumber}, getPostsCallBack, "#list-posts");
    }
    function getPostsCallBack(PHPData){
        if(validatePHPData(PHPData, "#list-posts", "html", "default", "data")){
            $("#list-posts tbody").html(PHPData.data.posts);
            $("ul.pagination").html(PHPData.data.pagination);
            initializePaginationEvents();
        }else{
            $("ul.pagination").html("");
        }
    }

    //create post
    $("#form-create-post").submit(function(e){
        e.preventDefault();
        ajax("Posts/create", serialize(this), createPostCallBack, "#form-create-post");
    });
    function createPostCallBack(PHPData){
        if(validatePHPData(PHPData, "#form-create-post", "after", "default", "success")){
            $("#form-create-post").after(PHPData.success);
            $("#form-create-post").remove();
        }
    }

    function initializePostEvents(){
        updatePostEvents();
        deletePost();
    }

    //update post
    function updatePostEvents(){
        $("#view-post .panel-heading .edit").click(function(){

            var postBody = $(this).parent().parent().parent();
            getPostUpdateForm();

            //1. get the update form
            function getPostUpdateForm(){
                ajax("Posts/getUpdateForm", {post_id: globalPageId}, getPostUpdateFormCallBack);
                function getPostUpdateFormCallBack(PHPData){
                    if(validatePHPData(PHPData, postBody, "html", "default", "data")){
                        postBody.html(PHPData.data);
                        activateCancelPostEvent();
                        activateUpdatePostEvent();
                    }
                }
            }

            //2.
            function activateCancelPostEvent(){
                $("#form-update-post button[name='cancel']").click(function(e){
                    e.preventDefault();
                    ajax("Posts/getById", {post_id: globalPageId}, getPostByIdCallBack);
                    function getPostByIdCallBack(PHPData){
                        if(validatePHPData(PHPData, postBody, "html", "default", "data")){
                            $(postBody).html(PHPData.data);
                            initializePostEvents();
                        }
                    }
                });
            }

            //3.
            function activateUpdatePostEvent(){
                $("#form-update-post").submit(function(e){
                    e.preventDefault();
                    ajax("Posts/update", serialize("#form-update-post", "post_id="+globalPageId), updatePostCallBack, "#view-post-"+globalPageId);
                    function updatePostCallBack(PHPData){
                        if(validatePHPData(PHPData, "#form-update-post", "after", "default", "data")){
                            $(postBody).html(PHPData.data);
                            initializePostEvents();
                        }
                    }
                });
            }

        });
    }

    //delete post
    function deletePost(){

        $("#view-post .panel-heading .delete").click(function(e){
            e.preventDefault();
            //optional confirmation
            if (!confirm("Are you sure?")) { return; }

            var postBody = $(this).parent().parent().parent();

            ajax("Posts/delete", {post_id: globalPageId}, deletePostCallBack);
            function deletePostCallBack(PHPData){
                if(validatePHPData(PHPData, postBody, "html", "default", "success")){
                    //here we remove all the two columns of post and that of comments as well!.
                    $(".row:eq(1)").children().eq(1).html(PHPData.success);
                }
            }
        });
    }


/*
 * Comments
 */
    //get comments
    function getComments(pageNumber, commentsCreated){

        if(empty(pageNumber)) pageNumber = 1;
        if(empty(commentsCreated)) commentsCreated = 0;

        ajax("Comments/getAll", {post_id: globalPageId, page_number: pageNumber, comments_created: commentsCreated}, getCommentsCallBack, "#list-comments");
    }
    function getCommentsCallBack(PHPData){
        if(validatePHPData(PHPData, "#list-comments", "html", "default", "data")){
            $("#list-comments").append(PHPData.data.comments);
            initializeCommentsEvents();
            $("ul.pagination").html(PHPData.data.pagination);
            initializePaginationEvents();
        }else{
            $("ul.pagination").html("");
        }
    }

    //create comment
    $("#form-create-comment").submit(function(e){
        e.preventDefault();
        ajax("Comments/create", serialize(this, "post_id="+globalPageId), createCommentCallBack, "#form-create-comment");
    });
    function createCommentCallBack(PHPData){
        if(validatePHPData(PHPData, "#form-create-comment", "after", "default", "data")){
            $("#list-comments .no-data").remove();
            $("#list-comments").append(PHPData.data);

            $("#form-create-comment textarea").val('');

            //increment number of comments created in-place
            commentsCreated++;
            initializeCommentsEvents();
        }
    }

    function initializeCommentsEvents(){
        updateCommentEvents();
        deleteComment();
    }

    //update comment
    function updateCommentEvents(){
        $("#list-comments .header .edit").click(function(){

            var commentBody = $(this).parent().parent().parent().parent();
            var commentId = commentBody.attr("id");
            getCommentUpdateForm();

            //1. get the update form
            function getCommentUpdateForm(){
                ajax("Comments/getUpdateForm", {comment_id: commentId}, getCommentUpdateFormCallBack);
                function getCommentUpdateFormCallBack(PHPData){
                    if(validatePHPData(PHPData, commentBody, "html", "default", "data")){
                        commentBody.html(PHPData.data);
                        activateCancelCommentEvent();
                        activateUpdateCommentEvent();
                    }
                }
            }

            //2.
            function activateCancelCommentEvent(){
                $("#form-update-"+commentId+" button[name='cancel']").click(function(e){
                    e.preventDefault();
                    ajax("Comments/getById", {comment_id: commentId}, getCommentByIdCallBack);
                    function getCommentByIdCallBack(PHPData){
                        if(validatePHPData(PHPData, commentBody, "html", "default", "data")){
                            $(commentBody).after(PHPData.data);
                            $(commentBody).remove();
                            initializeCommentsEvents();
                        }
                    }
                });
            }

            //3.
            function activateUpdateCommentEvent(){
                $("#form-update-"+commentId).submit(function(e){
                    e.preventDefault();
                    ajax("Comments/update", serialize("#form-update-"+commentId, "comment_id="+commentId), updateCommentCallBack);
                    function updateCommentCallBack(PHPData){
                        if(validatePHPData(PHPData, commentBody, "after", "default", "data")){
                            $(commentBody).after(PHPData.data);
                            $(commentBody).remove();
                            initializeCommentsEvents();
                        }
                    }
                });
            }

        });
    }

    //delete comment
    function deleteComment(){
        $("#list-comments .header .delete").click(function(e){
            e.preventDefault();
            if (!confirm("Are you sure?")) { return; }

            var commentBody = $(this).parent().parent().parent().parent();
            var commentId = commentBody.attr("id");

            ajax("Comments/delete", {comment_id: commentId}, deleteCommentCallBack);
            function deleteCommentCallBack(PHPData){
                if(validatePHPData(PHPData, commentBody, "html", "default", "success")){
                    $(commentBody).remove();
                }
            }
        });
    }


/*
 * Files
 */

    //get files
    function getFiles(pageNumber){
        if(typeof(pageNumber) === "undefined") pageNumber = 1;
        ajax("Files/getAll", {page_number: pageNumber}, getFilesCallBack, "#list-files");
    }

    function getFilesCallBack(PHPData){
        if(validatePHPData(PHPData, "#list-files", "html", "default", "data")){
            $("#list-files tbody").html(PHPData.data.files);
            initializeFilesEvents();
            $("ul.pagination").html(PHPData.data.pagination);
            initializePaginationEvents();
        }else{
            $("ul.pagination").html("");
        }
    }

    //create file
    $("#form-upload-file").submit(function(e){
        e.preventDefault();
        if(validateFileSize("#form-upload-file", 'file')){
            ajaxFiles("Files/create", new FormData(this), createFileCallBack);
        }
    });
    function createFileCallBack(PHPData){
        if(validatePHPData(PHPData, "#form-upload-file", "after", "default", "data")){

            $("#list-files .no-data").remove();

            //How to insert/append an element by fadeIn()?
            //@see http://stackoverflow.com/questions/4687579/append-an-element-with-fade-in-effect-jquery
            $(PHPData.data).hide().prependTo("#list-files tbody").fadeIn();

            initializeFilesEvents();
        }
    }

    //initialize
    function initializeFilesEvents(){
        deleteFile();
    }

    //delete file
    function deleteFile(){
        $("#list-files tr td .delete").click(function(e){
            e.preventDefault();
            if (!confirm("Are you sure?")) { return; }

            var row = $(this).parent().parent();
            var fileId = row.attr("id");

            ajax("Files/delete", {file_id: fileId}, deleteFileCallBack);
            function deleteFileCallBack(PHPData){
                if(validatePHPData(PHPData, row, "after", "row", "success")){
                    $(row).remove();
                }
            }
        });
    }


/*
 * Users
 */

    //get users
    function getUsers(pageNumber){
        if(empty(pageNumber)) pageNumber = 1;

        var name    = $("#form-search-users input[name='name']").val();
        var email   = $("#form-search-users input[name='email']").val();
        var role    = $("#form-search-users select[name='role']").val();

        ajax("Admin/getUsers", {name: name, email: email, role: role, page_number: pageNumber}, getUsersCallBack, "#list-users");
    }

    //get users
    $("#form-search-users").submit(function(e){
        e.preventDefault();
        ajax("Admin/getUsers", serialize(this, "page_number=1"), getUsersCallBack, "#list-users");
    });

    function getUsersCallBack(PHPData){
        if(validatePHPData(PHPData, "#form-search-users", "after", "default", "data")){
            $("#list-users tbody").html(PHPData.data.users);
            initializeUsersEvents();
            $("ul.pagination").html(PHPData.data.pagination);
            initializePaginationEvents();
        }else{
            $("ul.pagination").html("");
        }
    }

    //update user info
    $("#form-update-user-info").submit(function(e){
        e.preventDefault();
        ajax("Admin/updateUserInfo", serialize(this, "user_id="+globalPageId), updateUserInfoCallBack, "#form-update-user-info");
    });

    function updateUserInfoCallBack(PHPData){
        if(validatePHPData(PHPData, "#form-update-user-info", "after", "default", "success")){
            $("#form-update-user-info").after(PHPData.success);
        }
    }

    //initialize
    function initializeUsersEvents(){
        deleteUser();
    }

    //delete
    function deleteUser(){
        $("#list-users tr td .delete").click(function(e){
            e.preventDefault();
            if (!confirm("Are you sure?")) { return; }

            var row     = $(this).parent().parent().parent();
            var userId  = row.attr("id");

            ajax("Admin/deleteUser", {user_id: userId}, deleteUserCallBack);
            function deleteUserCallBack(PHPData){
                if(validatePHPData(PHPData, row, "after", "row", "success")){
                    $(row).remove();
                }
            }
        });
    }

/*
 * Bug, Feature or Enhancement
 */

    $("#form-bug").submit(function(e){
        e.preventDefault();
        ajax("User/reportBug", serialize(this), reportBugCallBack, "#form-bug");
    });
    function reportBugCallBack(PHPData){
        if(validatePHPData(PHPData, "#form-bug", "after", "default", "success")){
            $("#form-bug").after(PHPData.success);
            $("#form-bug").remove();
        }
    }

/*
 * Backups
 */

    $("table#backups .update-backup").click(function(e){
        e.preventDefault();
        ajax("Admin/updateBackup", {}, updateBackupCallBack, "table#backups");
        function updateBackupCallBack(PHPData){
            if(validatePHPData(PHPData, "table#backups", "after", "default", "success")){
                $("table#backups").after(PHPData.success);
            }
        }
    });
    $("table#backups .restore-backup").click(function(e){
        e.preventDefault();
        ajax("Admin/restoreBackup", {}, restoreBackupCallBack, "table#backups");
        function restoreBackupCallBack(PHPData){
            if(validatePHPData(PHPData, "table#backups", "after", "default", "success")){
                $("table#backups").after(PHPData.success);
            }
        }
    });
