![miniPHP](https://raw.githubusercontent.com/OmarElGabry/miniPHP/master/public/img/backgrounds/background.png)

# miniPHP
[![Build Status](https://scrutinizer-ci.com/g/OmarElGabry/miniPHP/badges/build.png?b=master)](https://scrutinizer-ci.com/g/OmarElGabry/miniPHP/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/OmarElGabry/miniPHP/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/OmarElGabry/miniPHP/?branch=master)
[![Code Climate](https://codeclimate.com/github/OmarElGabry/miniPHP/badges/gpa.svg)](https://codeclimate.com/github/OmarElGabry/miniPHP)
[![Dependency Status](https://www.versioneye.com/user/projects/55ae85dd3865620018000001/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55ae85dd3865620018000001)

[![Latest Stable Version](https://poser.pugx.org/omarelgabry/miniphp/v/stable)](https://packagist.org/packages/omarelgabry/miniphp)
[![License](https://poser.pugx.org/omarelgabry/miniphp/license)](https://packagist.org/packages/omarelgabry/miniphp)

A small, simple PHP MVC framework skeleton that encapsulates a lot of features surrounded with powerful security layers.

miniPHP is a very simple application, useful for small projects, helps to understand the PHP MVC skeleton, know how to authenticate and authorize, encrypt data and apply security concepts, sanitization and validation, make ajax calls and more.

It's not a full framework, nor a very basic one but it's not complicated. You can easily install, understand, and use it in any of your projects.

It's indented to remove the complexity of the frameworks. Things like routing, authentication, authorization, manage user session and cookies, and so on are not something I've invented from the scratch, however, they are aggregation of concepts already implemented in other frameworks, but, built in a much simpler way, So, you can understand it, and take it further.

If you need to build bigger application, and take the advantage of most of the features available in frameworks, you can see [CakePHP](http://cakephp.org/), [Laravel](http://laravel.com/), [Symphony](http://symfony.com/).

Either way, It's important to understand the PHP MVC skeleton, and know how to authenticate and authorize, learn about security issues and how can you defeat against, and how to build you own application using the framework.

## Documentation
Full Documentation can be also found [here](http://omarelgabry.github.io/miniPHP/) — created by GitHub automatic page generator.

## Index
+ [Demo](#live-demo)
+ [Installation](#installation)
+ [Routing](#routing)
+ [Controller](#controller)
+ [Components(Middlewares)](#components)
	+ [Authentication](#authentication)
	    - [Session](#session)
	    - [Cookies](#cookies)
	+ [Authorization](#authorization)
	+ [Security](#security)
		- [HTTP Methods](#http-method)
		- [Domain Validation](#referer)
		- [Form Tampering](#form-tampering)
		- [CSRF](#csrf)
		- [htaccess](#htaccess)
	+ [Turn on/off Components](#turn-on-off-components)
+ [Views](#views)
+ [Models](#models)
+ [Login](#login)
	- [User Verification](#user-verification)
	- [Forgotten Password](#forgotten-password)
	- [Brute-Force attack](#brute-force)
	- [Captcha](#captcha)
	- [Block IP Addresses](#block-ip)
+ [Database](#database)
+ [Encryption](#encryption)
+ [Validation](#validation)
+ [Errors & Exceptions](#errors-exceptions)
+ [Logger](#logger)
+ [Email](#email)
+ [Configurations](#configurations)
+ [JavaScript & Ajax](#js)
+ [Application(Demo)](#app-demo)
	+ [Intro](#intro-demo)
	+ [Installation](#installation-demo)
	+ [User Profile](#profile)
	+ [Files](#files)
	+ [News Feed & Posts & Comments](#newsfeed-posts-comments)
	+ [Admin](#admin)
	+ [Notifications](#notifications)
	+ [Report Bugs](#bugs)
	+ [Backups](#backups)
+ [ToDo Application(Step By Step Implementation)](#todo)
+ [Support](#support)
+ [Contribute](#contribute)
+ [Dependencies](#dependencies)
+ [License](#license)

## Demo <a name="live-demo"></a>
A live demo is available [here](https://miniphp.ga/). The live demo is for the demo application built on top of this framework in this [section](#app-demo). Thanks to [@Everterstraat](https://github.com/Everterstraat).

> Some features mighn't work in the demo.

## Installation <a name="installation"></a>
Install via [Composer](https://getcomposer.org/doc/00-intro.md)

```
	composer install
```

## Routing <a name="routing"></a>

Whenever you make a request to the application, it wil be directed to _index.php_ inside public folder. 
So, if you make a request: ```http://localhost/miniPHP/User/update/412 ```. This will be splitted and translated into 

+ Controller: User
+ Action Method: update
+ Arguemtns to action method: 412

In fact, htaccess splits everything comes after ```http://localhost/miniPHP ``` and adds it to the URL as querystring argument. So, this request will be converted to: ```http://localhost/miniPHP?url='User/update/412' ```.

Then ```App``` Class, Inside ```splitUrl()```, will split the query string ```$_GET['url']``` into controller, action method, and any passed arguments to action method.

In ```App``` Class, Inside ```run()```, it will instantiate an object from controller class, and make a call to action method, passing any arguments if exist.

## Controller <a name="controller"></a>

After the ```App``` Class intantiates controller object, It will call ```$this->controller->startupProcess()``` method, which in turn will trigger 3 consecutive events/methods:

1. ```initialize()```: Use it to load components
2. ```beforeAction()```: Perform any logic actions before calling controller's action method
3. ```triggerComponents()```: Trigger startup() method of loaded components

The constructor of ```Controller``` Class **shouldn't** be overridden, instead you can override the ```initialize()``` & ```beforeAction()``` methods in the extending classes.

After the startup process of the constrcutor finishes it's job, Then, the requested action method will be called, and arguments will be passed(if any).

## Components(Middlewares) <a name="components"></a>
Components are the middlewares. They provide reusable logic to be used as part of the controller. Authentication, Authorization, Form Tampering, and Validate CSRF Tokens are implemented inside Components. 

It's better to pull these pieces of logic out of controller class, and keep all various tasks and validations inside these Components.

Every component inherits from the base/super class called ```Component```. Each has a defined task. There are two components, one for called _Auth_ for Authentication and Authorization, and the other one called _Security_ for other Security Issues.

They are very simple to deal with, and they will be called inside controller constructor.

### Authentication <a name="authentication"></a>
Is user has right credentials?

#### Session<a name="session"></a>
The AuthComponent takes care of user session.

+ Prevent Session Concurrency
	- There can't be 2 users logged in with same user credentials.
+ Defeat against Session Hijacking & Fixation
	- HTTP Only with session cookies
	- Whenever it's possible, It's Highly Recommended to use Secured connection(SSL).
	- Regenerate session periodically and after actions like login, forgot password, ...etc.
	- Validate user's IP Address and User agent(initially will be stored in session). Although they can be faked, It's better to keep them as part of validation methods.
+ Session Expiration
	- Session will expire after certain duration(>= 1 day)
	- Session cookie in browser is also configured to be expired after (>= 1 week)
+ Session accessible only through the HTTP protocol
	- This is important so sessions won't be accessible by JS.

#### Cookies<a name="cookies"></a>

+ Remember Me Tokens
	- User can keep himself logged in using cookies
	- HTTP Only with cookies
	- Whenever it's possible, It's Highly Recommended to use Secured connection(SSL).
	- Cookies stored in browser are attached with tokens and Encrypted data
	- Cookies in browser are also configured to be expired after (>= 2 weeks)
	
### Authorization <a name="authorization"></a>
Do you have the right to access or to perform X action?. The _Auth_ Component takes care of authorization for each controller. Thus, each controller should implement ``` isAuthorized() ``` method. What you need to do is to return ``` boolean ``` value.

So, for example, in order to check if current user is admin or not, you would do something like this:
```php
    // AdminController

    public function isAuthorized(){

        $role = Session::getUserRole();
        if(isset($role) && $role === "admin"){
            return true;
        }
        return false;
    }

```

If you want to take it further and apply some permission rules, There is a powerful class called ``` Permission ``` responsible for defining permission rules. This class allows you to define "Who is allowed to perform specific action method on current controller".

So, for example, in order to allow admins to perform any action on notes, while normal users can only edit their notes:
```php
   // NotesController
   
   public function isAuthorized(){

        $action = $this->request->param('action');
        $role 	= Session::getUserRole();
        $resource = "notes";

		// only for admins
		// they are allowed to perform all actions on $resource
        Permission::allow('admin', $resource, ['*']);

		// for normal users, they can edit only if the current user is the owner
		Permission::allow('user', $resource, ['edit'], 'owner');

        $noteId = $this->request->data("note_id");
        $config = [
            "user_id" => Session::getUserId(),
            "table" => "notes",
            "id" => $noteId
        ];

		// providing the current user's role, $resource, action method, and some configuration data
		// Permission class will check based on rules defined above and return boolean value
		return Permission::check($role, $resource, $action, $config);
    }
```
Now, you can check authorization based on user's role, resource, and for each action method.

### Security <a name="security"></a>
The SecurityComponent takes care of various security tasks and validation. 

#### HTTP Method<a name="http-method"></a>

It's important to restrict the request methods. As an example, if you have an action method that accepts form values, So, ONLY POST request will be accepted. The same idea for Ajax, GET, ..etc. You can do this inside ```beforeAction() ``` method. 

```php
    // NotesController

    public function beforeAction(){

        parent::beforeAction();

        $actions = ['create', 'delete'];

        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);
    }
```

Also if you require all requests to be through secured connection, you can configure the whole controller, or specific actions to redirect all requests to HTTPS instead of HTTP.

```php
    // NotesController

    public function beforeAction(){

        parent::beforeAction();

        $actions = ['create', 'delete'];	// specific action methods	
        $actions = ['*'];		        	// all action methods

        $this->Security->requireSecure($actions);
    }
```
#### Domain Validation<a name="referer"></a>

It checks & validates if request is coming from the same domain. Although they can be faked, It's good to keep them as part of our security layers.

#### Form Tampering<a name="form-tampering"></a>

Validate submitted form coming from POST request. The pitfall of this method is you need to define the expected form fields, or data that will be sent with POST request. 

By default, the framework will validate for form tampering when POST request is made, and it will make sure the CSRF token is passed with the form fields. In this situation, if you didn't pass the CSRF token, it will be considered as a Security thread.

+ Unknown fields cannot be added to the form.
+ Fields cannot be removed from the form.

```php
    // NotesController

    public function beforeAction(){

        parent::beforeAction();

        $action = $this->request->param('action');
        $actions = ['create', 'delete'];

        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);

        switch($action){
            case "create":
                $this->Security->config("form", [ 'fields' => ['note_text']]);
                break;
            case "delete":
            	// If you want to disable validation for form tampering
            	// $this->Security->config("validateForm", false);
                $this->Security->config("form", [ 'fields' => ['note_id']]);
                break;
        }
    }
```

#### CSRF Tokens<a name="csrf"></a>
CSRF Tokens are important to validate the submitted forms, and to make sure they aren't faked. A hacker can trick the user to make a request to a website, or click on a link, and so on.

They are valid for a certain duration(>= 1 day), then it will be regenerated and stored in user's session.

CSRF validation is disabled by default. If you want to validate the CSRF token, then assign ```validateCsrfToken``` to ```true``` as shown in the example below. CSRF validation will be forced when request is POST and form tampering is enabled. 

Now, You do not need to manually verify the CSRF token on every requests. The _Security_ Component will verify token in the request versus the token stored in the session.

```php
    // NotesController

    public function beforeAction(){

        parent::beforeAction();

		$action = $this->request->param('action');
		$actions = ['index'];

        $this->Security->requireGet($actions);

        switch($action){
            case "index":
                $this->Security->config("validateCsrfToken", true);
                break;
        }
    }
```

CSRF tokens are generated per session. You can either add a hidden form field, or in the URL as query parameter.

**Form**

``` <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken(); ?>" /> ``` 

**URL**

``` <a href="<?= PUBLIC_ROOT . "?csrf_token=" . urlencode(Session::generateCsrfToken()); ?>">Link</a> ```

**JavaScript**

You can also assign the CSRF token to a javascript variable. 

```<script>config = <?= json_encode(Session::generateCsrfToken()); ?>;</script>``` 

#### htacess<a name="htaccess"></a>

+ All requests will be redirected to ```index.php``` in public root folder. 
+ Block directory traversal/browsing
+ Deny access to app directory(Althought it's not needed if you setup the application correctly)

### Turn on/off Components(Middlewares) <a name="turn-on-off-components"></a>
Sometimes you need to have a control on these components, such as when want to have a Controller without Authentication or Authorization, or a Security component is enabled. This can be done by override ```initialize()``` method inside your Controller class, and load only needed Components.

**Example 1**: Don't load any component, no authentication or authorization, or security validations.
```php
public function initialize(){

	$this->loadComponents([]);
}
```
**Example 2**: Load Security, & Auth component, but don't authenticate and authorize, just in case you want to use the Auth component inside the action methods. [LoginController](https://github.com/OmarElGabry/miniPHP/blob/master/app/controllers/LoginController.php#L60) is an example on **how to access a page without require a logged-in user**.
```php
public function initialize(){
	$this->loadComponents([ 
	    	'Auth',
	    	'Security'
	    ]);
}
 ```
**Example 3**: Load Security, & Auth component, and authenticate user & authorize for the current controller. This is the default behavior in the [core/Controller](https://github.com/OmarElGabry/miniPHP/blob/master/app/core/Controller.php#L137) Class
```php
public function initialize(){
	$this->loadComponents([
		'Auth' => [
			'authenticate' => ['User'],
			'authorize' => ['Controller']
		],
		'Security'
	    ]);
}
``` 

## Views <a name="views"></a>

Inside the action method you can make a call to model to get some data, and/or render pages inside _views_ folder

```php
  //  NotesController
  
  public function index(){
 
	// render full page with layout(header and footer)
	$this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('VIEWS_PATH') . 'notes/index.php');
	
	// render page without layout
	$this->view->render(Config::get('VIEWS_PATH') . 'notes/note.php');
	
	// get the rendered page
	$html = $this->view->render(Config::get('VIEWS_PATH') . 'notes/note.php');
	
	// render a json view
	$this->view->renderJson(array("data" => $html));
  }
```

## Models <a name="models"></a>
> In MVC, the model represents the information (the data) and the business rules; the view contains elements of the user interface such as text, form inputs; and the controller manages the communication between the model and the view.
[Source](http://www.yiiframework.com/doc/guide/1.1/en/basics.mvc)

All operations like create, delete, update, and validation are implemented in model classes.

```php
   // NotesController

    public function create(){
    
		// get content of note submitted to a form
		// then pass the content along with the current user to Note class
		$content  = $this->request->data("note_text");
		$note     = $this->note->create(Session::getUserId(), $content);
        
        if(!$note){
            $this->view->renderErrors($this->note->errors());
        }else{
            return $this->redirector->root("Notes");
        }
    }
```

**In Notes Model**

```php
   // Notes Model

    public function create($userId, $content){
    
    	// using validation class(see below)
        $validation = new Validation();
        if(!$validation->validate(['Content'   => [$content, "required|minLen(4)|maxLen(300)"]])) {
            $this->errors = $validation->errors();
            return false;
        }
        
        // using database class to insert new note
        $database = Database::openConnection();
        $query    = "INSERT INTO notes (user_id, content) VALUES (:user_id, :content)";
        $database->prepare($query);
        $database->bindValue(':user_id', $userId);
        $database->bindValue(':content', $content);
        $database->execute();
        
        if($database->countRows() !== 1){
            throw new Exception("Couldn't create note");
        }
        
        return true;
     }
```

## Login<a name="login"></a>
Using the framework, you would probably do login, register, and logout. These actions are implemented in _app/models/Login_ & _app/controllers/LoginController_. In most situations, you won't need to modify anything related to login actions, just understand the behaviour of the framework. 

**NOTE** If you don't have SSL, you would better want to encrypt data manually at Client Side, If So, read [this](http://stackoverflow.com/questions/3715920/about-password-hashing-system-on-client-side) and also [this](http://stackoverflow.com/questions/4121629/password-encryption-at-client-side?lq=1).

### User Verification<a name="user-verification"></a>
Whenever the user registers, An email will be sent with token concatenated with encrypted user id. This token will be expired after 24 hour. It's much better to expire these tokens, and re-use the registered email if they are expired.

**Passwords** are hashed using the latest algorithms in PHP v5.5
```php
$hashedPassword = password_hash($password, PASSWORD_DEFAULT, array('cost' => Config::get('HASH_COST_FACTOR')));
```

### Forgotten Password<a name="forgotten-password"></a>
If user forgot his password, he can restore it. The same idea of expired tokens goes here. 

In addition, block user for certain duration(>= 10min) if he exceeded number of forgotten passwords attempts(5) during a certain duration(>= 10min).

#### Brute Force Attack<a name="brute-force"></a>
Throttling brute-force attacks is when a hacker tries all possible input combination until he finds the correct password.

Solution:
+ Block failed logins, So, if a user exceeded number of failed logins(5) during certain duration(>= 10min), the email will be blocked for duration(>= 10min).
+ Blocking will be for emails even these emails aren't stored in our database, meaning for non-registered users.
+ Require **Strong** passwords
	- At least one lowercase character
	- At least one uppercase character
	- At least one special character
	- At least one number
	- Min Length is 8 characters

### Captcha<a name="captcha"></a>
CAPTCHAs are particularly effective in preventing automated logins. Using [Captcha](https://github.com/Gregwar/Captcha) an awesome PHP Captcha library.

### Block IP Address<a name="block-ip"></a>
Blocking IP Addresses is the last solution to think about. IP Address will be blocked if the same IP failed to login multiple times using different credentials(>=10).

## Database<a name="database"></a>
PHP Data Objects (PDO) is used for preparing and executing database queries. Inside ```Database``` Class, there are various methods that hides complexity and let's you instantiate database object, prepare, bind, and execute in few lines.

+ SQL Injection
	- Using prepared statements will prevent SQL Injection.
+ Limit Privileges
	- Don't use _root_ user, Create a new one instead.
	- Always assign limited privileges to current database user
	- ```SELECT, INSERT, UPDATE, DELETE ``` are enough for users
	- For backups, It's recommended to use another database user with more privileges. These privileges needed for [mysqldump](https://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) are mentioned in ```Admin``` Class.
+ UTF-8
	- For complete UTF-8 support, you need to use ```utf8mb4 ```on database level.
	- MySQL’s ```utf8``` charset only store UTF-8 encoded symbols that consist of one to three bytes. But, It can't for symbols with four bytes. 
	- Here, charset is ```utf8```. But, if you want to upgrade to ```utf8mb4 ``` follow these links:
		- [Link 1](https://mathiasbynens.be/notes/mysql-utf8mb4) & [Link 2](https://dev.mysql.com/doc/refman/5.5/en/charset-unicode-upgrading.html)
		- Don't forget to change **charset** in _app/config/config.php_ to ```utf8mb4 ```

## Encryption<a name="encryption"></a>
``` Encryption ``` Class is responsible for encrypting and decryption of data. Encryption is applied to things like cookies, User ID, Post ID, ..etc. Encrypted strings are authenticated and they are different every time you encrypt. 

## Validation<a name="validation"></a>
Validation is a small library for validating user inputs. All validation rules are inside ``` Validation ``` Class.

#### Usage
```php

$validation = new Validation();

// there are default error messages for each rule
// but, you still can define your custom error message
$validation->addRuleMessage("emailUnique", "The email you entered is already exists");

if(!$validation->validate([
    "User Name" => [$name, "required|alphaNumWithSpaces|minLen(4)|maxLen(30)"],
    "Email" => [$email, "required|email|emailUnique|maxLen(50)"],
    'Password' => [$password,"required|equals(".$confirmPassword.")|minLen(6)|password"],
    'Password Confirmation' => [$confirmPassword, 'required']])) {

    var_dump($validation->errors());
}
```

## Errors and Exceptions<a name="errors-exceptions"></a>
``` Handler``` Class is responsible for handling all exceptions and errors. It will use [Logger](#logger) to log errors. Error reporting is turned off by default, because every error will be logged and saved in  _app/logs/log.txt_.

If error encountered or exception was thrown, the application will show System Internal Error(500).

### Configurations(php.ini)
+ Turn Off display errors
+ Turn Off log errors if not needed

## Logger<a name="logger"></a>
A place where you can log anything and save it to _app/log/log.txt_. You can write any failures, errors, exceptions, or any other malicious actions or attacks.

```php
Logger::log("COOKIE", self::$userId . " is trying to login using invalid cookie", __FILE__, __LINE__);
```

## Email<a name="email"></a>
Emails are sent using [PHPMailer](https://github.com/PHPMailer/PHPMailer) via SMTP, another library for sending emails. You shouldn't use ```mail()``` function of PHP.

## Configurations<a name="configurations"></a>
In _app/config_, there are two files, one called _config.php_ for main application configurations, and another one for javascript called _javascript.php_. The javascript configurations will be then assigned to a javascript variable in your _footer.php_.

## JavaScript <a name="js"></a>
In order to send request and recieve a respond, you may depend on Ajax calls to do so. This framework is heavily depends on ajax requests to perform actions, but, you still can do the same thing for normal requests with just small tweaks.

#### In _public/main.js_

**config** object is assigned to key-value pairs in [footer.php](https://github.com/OmarElGabry/miniPHP/blob/master/app/views/layout/default/footer.php). These key-value pairs can be added in server-side code using ```Config::setJsConfig('key', "value");```, which will be assigned then to _config_ object.

**ajax** A namespace that has two main functions for sending ajax request. One for normal ajax calls, and another for for uploading files.

**helpers** A namespace that has variety of functions display errors, serialize, redirect, encodeHTML, and so on

**app** A namespace that's used to initalize the whole javascript events for the current page

**events** A namespace that's used to declare all of events that may occure, like when user clicks on a link to create, delete or update.

## Application(Demo) <a name="app-demo"></a>
### Intro<a name="intro-demo"></a>
In order to show how to use the framework in a real-life situation, the framework comes with implementation for features like Manage User Profile Management, Dashboard, News Feed, Upload & Download Files, Posts & Comments, Pagination, Admin panel, Manage System Backups, Notificatons, Report Bugs, ...etc.

### Installation<a name="installation-demo"></a>
Steps:

1. Edit configuration file in _app/config/config.php_ with your credentials

2. Execute SQL queries in __installation_ directory in order

3. Login
	+ Admin:
		+ Email: admin@demo.com
		+ Password: 12345
	+ Normal User:
		+ Email: user@demo.com
		+ Password: 12345

**EMAIL SETUP** 

You need to configure your SMTP account data in _app/config/config.php_. **But**, If you don't have SMTP account, then you save emails in _app/logs/log.txt_ using Logger. 

To do that, In [core/Email](https://github.com/OmarElGabry/miniPHP/blob/master/app/core/Email.php#L78), comment ```$mail->Send()``` & uncomment ```Logger::log("EMAIL", $mail->Body);``` 

### User Profile<a name="profile"></a>
Every user can change his name, email, password. Also upload profile picture (i.e. initially assigned to default.png).

#### Update & Revoke User Email<a name="update-revoke-user-email"></a>
Whenever user asks to change his email, a notification will be sent to user's old email, and the new one.

The notification sent to old email is giving the user the chance to revoke email change, while the notification sent to new email is asking for confirmation. User can still login with his old email until he confirms the change.

This is done in ```UserController```, In methods ```updateProfileInfo()```, ```revokeEmail()```, & ```updateEmail()```. In most situations, you won't need to modify the behavior of these methods.

### Files<a name="files"></a>
You can upload and download files.

#### Upload
+ All uploaded files are out of root public, so, they aren't accessible by anyone
+ Validate against HTTP POST uploads, MIME, Size, Image dimension
+ Setting file permission to avoid executable files
+ Sanitizing file names
+ Progress bar(no-plugins)

#### Download
+ Every file will have hashed version of it's name, this hashed name will be exposed to users. 
+ The hashed name = hash(original filename . extension). So, download link will look something like this: _http://miniPHP/downloads/download/b989f733f948e8a4b8b700e1_

#### Configurations(php.ini)
+ Set ```file_uploads``` to true
+ Set ```upload_max_filesize, max_file_uploads, post_max_size```
	- Check [documentation](http://php.net/manual/en/ini.core.php#ini.post-max-size) to know how to assign proper values for each.

### News Feeds, Posts & Comments <a name="newsfeed-posts-comments"></a>

Think of News Feed as tweets in twitter, and in Posts like when you open an Issue in Github.

They are implemented on the top of this framework. 
+ They are useful to show & apply some concepts like **Pagination**, 
+ How can you edit & delete in place(secured way), 
+ How can you manage permissions for who can create, edit, update and delete, and so forth.

### Admin<a name="admin"></a>
Admins can perform actions where normal users can't. They can delete, edit, create any newsfeed, post, or comment. Also they have control over all user profiles, create & restore backups.

#### Users<a name="users"></a>
Only admins have access to see all registered users. They can delete, edit their info.

#### Backups<a name="backups"></a>
In most of the situations, you will need to create backups for the system, and restore them whenever you want.

This is done by using [mysqldump](https://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) to create and restore backups. All backups will be stored in _app/backups_.

### Notifications<a name="notifications"></a>
Did you see the red notifications on facebook, or the blue one on twitter?. The same idea is here. But, It's implemented using triggers instead. Triggers are defined in __installation/triggers.sql_.

So, whenever user creates a new newsfeed, post, or upload a file, this will increment the count for all other users, and will display a red notification in navigation bar.

### Report Bugs<a name="bugs"></a>
Users can report Bugs, Features & Enhancements. Once they submitted the form, an email will be sent to ```ADMIN_EMAIL``` defined in _app/config/config.php_

## ToDo Application<a name="todo"></a>
Let's say you want to build a simple ToDo Application. Here, I will go step by step on how to create a ToDo App using the framework with & without Ajax calls.

(1) If you followed the installtion setup steps above, you shouldn't have any problem with creating initial user accounts.

(2) Create a table with id as INT, content VARCHAR, user_id as Foreign Key to ```users``` table

```sql
CREATE TABLE `todo` (
	 `id` int(11) NOT NULL AUTO_INCREMENT,
	 `user_id` int(11) NOT NULL,
	 `content` varchar(512) NOT NULL,
	 PRIMARY KEY (`id`),
	 FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

(3) Create TodoController

Create a file called ```TodoController.php``` inside _app/controllers_

```php

class TodoController extends Controller{

    // override this method to perform any logic before calling action method as explained above
    public function beforeAction(){

        parent::beforeAction();

        // define the actions in this Controller
        $action = $this->request->param('action');

        // restrict the request to action methods
        // $this->Security->requireAjax(['create', 'delete']);
        $this->Security->requirePost(['create', 'delete']);

        // define the expected form fields for every action if exist
        switch($action){
            case "create":
                // you can exclude form fields if you don't care if they were sent with form fields or not
                $this->Security->config("form", [ 'fields' => ['content']]);
                break;
            case "delete":
				// If you want to disable validation for form tampering
				// $this->Security->config("validateForm", false);
                $this->Security->config("form", [ 'fields' => ['todo_id']]);
                break;
        }
    }

    public function index(){

        $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/todo/", Config::get('VIEWS_PATH') . 'todo/index.php');
    }

    public function create(){

        $content  = $this->request->data("content");
        $todo     = $this->todo->create(Session::getUserId(), $content);

        if(!$todo){

            // in case of normal post request
            Session::set('errors', $this->todo->errors());
            return $this->redirector->root("Todo");

            // in case of ajax
            // $this->view->renderErrors($this->todo->errors());

        }else{

            // in case of normal post request
            Session::set('success', "Todo has been created");
            return $this->redirector->root("Todo");

            // in case of ajax
            // $this->view->renderJson(array("success" => "Todo has been created"));
        }
    }

    public function delete(){

        $todoId = Encryption::decryptIdWithDash($this->request->data("todo_id"));
        $this->todo->delete($todoId);

        // in case of normal post request
        Session::set('success', "Todo has been deleted");
        return $this->redirector->root("Todo");

        // in case of ajax
        // $this->view->renderJson(array("success" => "Todo has been deleted"));
    }

    public function isAuthorized(){

        $action = $this->request->param('action');
        $role = Session::getUserRole();
        $resource = "todo";

        // only for admins
        Permission::allow('admin', $resource, ['*']);

        // only for normal users
        Permission::allow('user', $resource, ['delete'], 'owner');

        $todoId = $this->request->data("todo_id");

        if(!empty($todoId)){
            $todoId = Encryption::decryptIdWithDash($todoId);
        }

        $config = [
            "user_id" => Session::getUserId(),
            "table" => "todo",
            "id" => $todoId];

        return Permission::check($role, $resource, $action, $config);
    }
}
```

(4) Create Note Model Class called ```Todo.php``` in _app/models_

```php
class Todo extends Model{

    public function getAll(){

        $database = Database::openConnection();
        $query  = "SELECT todo.id AS id, users.id AS user_id, users.name AS user_name, todo.content ";
        $query .= "FROM users, todo ";
        $query .= "WHERE users.id = todo.user_id ";

        $database->prepare($query);
        $database->execute();
        $todo = $database->fetchAllAssociative();

        return $todo;
     }

    public function create($userId, $content){
    
    	// using validation class
        $validation = new Validation();
        if(!$validation->validate(['Content'   => [$content, "required|minLen(4)|maxLen(300)"]])) {
            $this->errors = $validation->errors();
            return false;
        }
        
        // using database class to insert new todo
        $database = Database::openConnection();
        $query    = "INSERT INTO todo (user_id, content) VALUES (:user_id, :content)";
        $database->prepare($query);
        $database->bindValue(':user_id', $userId);
        $database->bindValue(':content', $content);
        $database->execute();
        
        if($database->countRows() !== 1){
            throw new Exception("Couldn't create todo");
        }
        
        return true;
     }
  
    public function delete($id){

        $database = Database::openConnection();
        $database->deleteById("todo", $id);

        if($database->countRows() !== 1){
            throw new Exception ("Couldn't delete todo");
        }
    }
 }
```

(5) Inside _views/_

(a) Create ```header.php``` & ```footer.php```  inside _views/layout/todo_

```php
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
```

```php
	<!-- footer -->

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<!--<script src="<?= PUBLIC_ROOT; ?>js/jquery.min.js"></script>-->
	<script src="<?= PUBLIC_ROOT; ?>js/bootstrap.min.js"></script>
	<script src="<?= PUBLIC_ROOT; ?>js/sb-admin-2.js"></script>
	<script src="<?= PUBLIC_ROOT; ?>js/main.js"></script>

        <!-- Assign CSRF Token to JS variable -->
		<?php Config::setJsConfig('csrfToken', Session::generateCsrfToken()); ?>
        <!-- Assign all configration variables -->
		<script>config = <?= json_encode(Config::getJsConfig()); ?>;</script>
        <!-- Run the application -->
        <script>$(document).ready(app.init());</script>
        
        <?php Database::closeConnection(); ?>
	</body>
</html>
```

(b) Inside _views/_ Create todo folder that will have ```index.php```, which will contain our todo list. 

```php
<div class="todo_container">

<h2>TODO Application</h2>

<!-- in case of normal post request  -->
<form action= "<?= PUBLIC_ROOT . "Todo/create" ?>"  method="post">
    <label>Content <span class="text-danger">*</span></label>
    <textarea name="content" class="form-control" required placeholder="What are you thinking?"></textarea>
    <input type='hidden' name = "csrf_token" value = "<?= Session::generateCsrfToken(); ?>">
    <button type="submit" name="submit" value="submit" class="btn btn-success">Create</button>
</form>


<!-- in case of ajax request  
<form action= "#" id="form-create-todo" method="post">
    <label>Content <span class="text-danger">*</span></label>
    <textarea name="content" class="form-control" required placeholder="What are you thinking?"></textarea>
    <button type="submit" name="submit" value="submit" class="btn btn-success">Create</button>
</form>
-->

<br>
<?php 

// display success or error messages in session
if(!empty(Session::get('success'))){
    echo $this->renderSuccess(Session::getAndDestroy('success'));
}else if(!empty(Session::get('errors'))){
    echo $this->renderErrors(Session::getAndDestroy('errors'));
}

?>

<br><hr><br>

<ul id="todo-list">
<?php 
    $todoData = $this->controller->todo->getAll();
    foreach($todoData as $todo){ 
?>
        <li>
            <p> <?= $this->autoLinks($this->encodeHTMLWithBR($todo["content"])); ?></p>

            <!-- in case of normal post request -->
            <form action= "<?= PUBLIC_ROOT . "Todo/delete" ?>" method="post">
                <input type='hidden' name= "todo_id" value="<?= "todo-" . Encryption::encryptId($todo["id"]);?>">
                <input type='hidden' name = "csrf_token" value = "<?= Session::generateCsrfToken(); ?>">
                <button type="submit" name="submit" value="submit" class="btn btn-xs btn-danger">Delete</button>
            </form>


            <!-- in case of ajax request 
            <form class="form-delete-todo" action= "#"  method="post">
                <input type='hidden' name= "todo_id" value="<?= "todo-" . Encryption::encryptId($todo["id"]);?>">
                <button type="submit" name="submit" value="submit" class="btn btn-xs btn-danger">Delete</button>
            </form>
             -->
        </li>
    <?php } ?>
</ul>

</div>
```

(6) JavaScript code to send ajax calls, and handle respond

```js

// first, we need to initialize the todo events whenever the application initalized
// the app.init() is called in footer.php, see views/layout/todo/footer.php

var app = {
    init: function (){
    
    	events.todo.init();
    }
};

// inside var events = {....} make a new key called "todo" 
var events = {
	// ....
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
	
	            function createTodoCallBack(PHPData){
	                if(helpers.validateData(PHPData, "#form-create-todo", "after", "default", "success")){
	                    alert(PHPData.success + " refresh the page to see the results");
	                }
	            }
	        },
	        delete: function(){
	            $("#todo-list form.form-delete-todo").submit(function(e){
	                e.preventDefault();
	                if (!confirm("Are you sure?")) { return; }
	                
	                var cur_todo = $(this).parent();
	                ajax.send("Todo/delete", helpers.serialize(this), deleteTodoCallBack, cur_todo);
	                
	                function deleteTodoCallBack(PHPData){
	                    if(helpers.validateData(PHPData, cur_todo, "after", "default", "success")){
	                        $(cur_todo).remove();
	                        alert(PHPData.success);
	                    }
	                }
	            });
		}
	}
}
```

### Support <a name="support"></a>
I've written this script in my free time during my studies. This is for free, unpaid. I am saying this because I've seen many developers acts very rude towards any software, and their behavior is really frustrating. I don't know why?! Everyone tends to complain, and saying harsh words. I do accept the feedback, but, in a good and respectful manner.

There are many other scripts online for purchase that does the same thing(if not less), and their authors are earning good money from it, but, I choose to keep it public, available for everyone.

If you learnt something, or I saved your time, please support the project by spreading the word.

### Contribute <a name="contribute"></a>

Contribute by creating new issues, sending pull requests on Github or you can send an email at: omar.elgabry.93@gmail.com

### Dependencies <a name="dependencies"></a>
+ [PHPMailer](https://github.com/PHPMailer/PHPMailer)
+ [Captcha](https://github.com/Gregwar/Captcha)
+ [Theme SB Admin 2](https://github.com/IronSummitMedia/startbootstrap-sb-admin-2)

### License <a name="license"></a>
Built under [MIT](http://www.opensource.org/licenses/mit-license.php) license.
