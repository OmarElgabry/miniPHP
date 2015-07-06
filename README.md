![miniPHP](https://raw.githubusercontent.com/OmarElGabry/miniPHP/master/public/img/backgrounds/background.png)

# miniPHP

A small, simple PHP MVC framework skeleton that encapsulates a lot of features surrounded with powerful security layers.

miniPHP is a very simple application, useful for small projects, helps to understand the PHP MVC skeleton, know how to authenticate and authorize, encrypt data and apply security concepts, sanitization and validation, make Ajax calls and more.

It's not a full framework, nor a very basic one but it's not complicated. You can easily install, understand, and use it in any of your projects.

It's indented to remove the complexity of the frameworks. I've been digging into the internals of some frameworks for a while. Things like authentication, and authorization that you will see here is not something I've invented from the scratch, Some of it, is aggregation of concepts applied already be frameworks, but, built in a much simpler way, So, you can understand it, and take it further.

If you need to build bigger application, and take the advantage of most of the features available in frameworks, you can see [CakePHP](http://cakephp.org/), [Laravel](http://laravel.com/), [Symphony](http://symfony.com/).

Either way, I believe it's important to understand the PHP MVC skeleton, and know how to authenticate and authorize, learn about security issues and how can you defeat against, and how to build you own features([News Feed, Posts](#newsfeed-posts-comments), [Files](#files), ...etc) and merge them with these core features.

## Index
+ [Installation](#installation)
+ [Components](#components)
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
+ [Login](#login)
	- [User Verification](#user-verification)
	- [Forgotten Password](#forgotten-password)
	- [Brute-Force attack](#brute-force)
	- [Captcha](#captcha)
	- [Block IP Addresses](#block-ip)
+ [Database](#database)
+ [Encryption](#encryption)
+ [Validation](#validation)
+ [Error](#error)
+ [Logger](#logger)
+ [Email](#email)
+ [News Feed & Posts & Comments](#newsfeed-posts-comments)
+ [Files](#files)
+ [Profile](#profile)
+ [Notifications](#notifications)
+ [Users](#users)
+ [Backups](#backups)
+ [Support](#support)
+ [Contribute](#contribute)
+ [Dependencies](#dependencies)
+ [License](#license)

### Installation <a name="installation"></a>
Steps:

1. Edit configuration file in _app/config/config.php_ with your credentials

2. Execute SQL queries in __installation_ directory

3. Install [Composer](https://getcomposer.org/doc/00-intro.md) for dependencies
```
	composer install
```

### Components <a name="components"></a>
Components are pretty much like backbone for controller. They provide reusable logic to be used as part of the controller. Things like Authentication, Authorization, Form Tampering, and Validate CSRF Tokens are implemented inside Components. 

It's better to pull these pieces of logic out of controller class, and keep all various tasks and validations inside these Components.

Every component inherits from the base/super class called ```Component```. Each has a defined task. There are two components, one for Authentication and Authorization, and another one for other Security Issues.

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
Do you have the right to access or to perform X action?. The AuthComponent takes care of authorization for each controller. Each controller must implement ``` isAuthorized() ``` method.

This method will be called by default at the end of controller constructor. What you need to do is to return ``` boolean ``` value.

So, for example:
```php
	//Inside AdminController

    public function isAuthorized(){

        $role = Session::getUserRole();
        if(isset($role) && $role === "admin"){
            return true;
        }
        return false;
    }

```

If you want to take it further and apply some permission rules, There is a powerful class called ``` Permission ``` responsible for defining permission rules. This class allows you to define "Who is allowed to perform specific action method on current controller".

Example on that:
```php
	//Inside FilesController

   public function isAuthorized(){

        $action = $this->request->param('action');
        $role = Session::getUserRole();
        $resource = "files";

        //only for admins
		//they are allowed to all actions on $resource
        Permission::allow('admin', $resource, ['*']);

		//for normal users, they can delete only if the current user is the owner
		//Permission class will then check if the current user is owner or not
        Permission::allow('user', $resource, ['delete'], 'owner');

        $fileId = $this->request->data("file_id");
        $config = [
            "user_id" => Session::getUserId(),
            "table" => "files",
            "id" => $fileId
        ];

		//providing the current user's role, $resource, action method, and some configuration data
		//Permission class will check based on rules defined above and return boolean value
        return Permission::check($role, $resource, $action, $config);
    }

```
Now, you can check authorization based on user's role, and for each action method.

### Security <a name="security"></a>
The SecurityComponent takes care of various security tasks and validation. 

#### HTTP Method<a name="http-method"></a>

It's important to restrict the request methods. As an example, if you have an action method that accepts form values, So, ONLY POST request will be accepted. The same idea for Ajax, GET, ..etc.

You can do this inside ```initialize()``` method, or personally I prefer to keep it inside ```beforeAction() ``` method. These methods are inherited from ```Controller``` Class.

```php
	//Inside FilesController

    public function beforeAction(){

        parent::beforeAction();

        $action = $this->request->param('action');
        $actions = ['create', 'delete'];

        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);

    }


```

Also if you require all requests to be through secured connection, you can configure whole controller, and specific actions to redirect all requests to HTTPS instead of HTTP.

```php
	//Inside FilesController

    public function beforeAction(){

        parent::beforeAction();

        $action = $this->request->param('action');
        $actions = ['create', 'delete'];

        $this->Security->requireSecure($actions);

    }

```
#### Domain Validation<a name="referer"></a>

Check & validate if request is coming from the same domain. Although they can be faked, It's good to keep them as part of our security layers.


#### Form Tampering<a name="form-tampering"></a>

validate submitted form coming from POST request. In case of Ajax request, you can append data along with form values, these values will be validated too.

+ Unknown fields cannot be added to the form.
+ Fields cannot be removed from the form.

The pitfall of this method is you need to define the expected form fields, or data that will be sent with POST request.


```php
	//Inside FilesController

    public function beforeAction(){

        parent::beforeAction();

        $action = $this->request->param('action');
        $actions = ['create', 'delete'];

        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);

        switch($action){
            case "create":
                $this->Security->config("form", [ 'fields' => ['file']]);
                break;
            case "delete":
                $this->Security->config("form", [ 'fields' => ['file_id']]);
                break;
        }
    }

```

#### CSRF Tokens<a name="csrf"></a>
CSRF Tokens are important to validate the submitted forms, and to make sure they aren't faked. A hacker can trick the user to make a request to a website, or click on a link, and so on.

They are valid for a certain duration(>= 1 day), then it will be regenerated and stored in user's session.

Here, CSRF tokens are generated per session. You can either add a hidden form field with ``` name = "csrf_token" value = "<?= Session::generateCsrfToken(); ?>" ```

But, Since all form requests here are made through Ajax calls, ```Session::generateCsrfToken()``` will be assigned to JS variable and will be sent with every ajax request, Instead of adding hidden form value to every form.


#### htacess<a name="htaccess"></a>

+ All requests will be redirected to ```index.php``` in public root folder. 
+ Block directory traversal/browsing
+ Deny access to app directory(Althought it's not needed if you setup the application correctly)

### Login<a name="login"></a>

**NOTE** If you don't have SSL, you would better want to encrypt data manually at Client Side, If So, read [this](http://stackoverflow.com/questions/3715920/about-password-hashing-system-on-client-side) and also [this](http://stackoverflow.com/questions/4121629/password-encryption-at-client-side?lq=1)

#### User Verification<a name="user-verification"></a>
Whenever the user registers, An email will be sent with token concatenated with encrypted user id. This token will be expired after 24 hour.

It's much better to expire these tokens, and re-use the registered email if they are expired.

**Passwords** are hashed using the latest algorithms in PHP v5.5
```php
$hashedPassword = password_hash($password, PASSWORD_DEFAULT, array('cost' => "10"));
```

#### Forgotten Password<a name="forgotten-password"></a>
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

#### Captcha<a name="captcha"></a>
CAPTCHAs are particularly effective in preventing automated logins. I am using [Captcha](https://github.com/Gregwar/Captcha) an awesome PHP Captcha library.

#### Block IP Address<a name="block-ip"></a>
Blocking IP Addresses is the last solution to think about. IP Address will be blocked if the same IP failed to login multiple times(>=10) using different credentials(emails).

### Database<a name="database"></a>
PHP Data Objects (PDO) is used for preparing and executing database queries. Inside ```Database``` Class, there are various methods that hides complexity and let's you instantiate database object, prepare, bind, and execute in few lines.

+ SQL Injection
	- Using prepared statements will prevent SQL Injection.
+ Limit Privileges
	- Don't use _root_ user, Create a new one instead.
	- Always assign limited privileges to current database user
	- ```SELECT, INSERT, UPDATE, DELETE ``` are enough for users
	- For backups, You need to use another database user with more privileges. These privileges needed for [mysqldump](https://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) are mentioned in ```Admin``` Class.
+ UTF-8
	- For complete UTF-8 support, you need to use ```utf8mb4 ```on database level.
	- MySQL’s ```utf8``` charset only store UTF-8 encoded symbols that consist of one to three bytes. But, It can't for symbols with four bytes. 
	- Here, I am using ```utf8```. But, if you want to upgrade to ```utf8mb4 ``` follow these links:
		- [Link 1](https://mathiasbynens.be/notes/mysql-utf8mb4)
		- [Link 2](https://dev.mysql.com/doc/refman/5.5/en/charset-unicode-upgrading.html)
		- Don't forget to change **charset** in _app/config/config.php_ to ```utf8mb4 ```

### Encryption<a name="encryption"></a>
``` Encryption ``` Class is responsible for encrypting and decryption of data. Encryption is applied to thinks like in cookies, Post Id, User Id, ..etc.

What you encrpyt will be different each time. Meaning if you encrypred string "ABC", suppose you will get "xD3msr4", next time you encrypt "ABC" you will get totally different output. Definitely, either way you will always get the original string when you decrypt.

### Validation<a name="validation"></a>
Validation is a small library for validating user inputs. All validation rules are inside ``` Validation ``` Class.

#### Usage
```php

$validation = new Validation();

//there are default error messages for each rule
//but, you still can define your custom error message
$validation->addRuleMessage("emailUnique", "The email you entered is already exists");

if(!$validation->validate([
  	"User Name" => [$name, "required|alphaNumWithSpaces|minLen(4)|maxLen(30)"],
    "Email" => [$email, "required|email|emailUnique|maxLen(50)"],
    'Password' => [$password,"required|equals(".$confirmPassword.")|minLen(6)|password"],
    'Password Confirmation' => [$confirmPassword, 'required']])) {

	var_dump($validation->errors());
}
```

### Error<a name="error"></a>
``` Error``` Class is responsible for handling all exceptions and errors. It will use [Logger](#logger) to log error. Error reporting is turned off by default, because every error will be logged and saved in  _app/logs/log.txt_.

If error encountered or exception was thrown, the application will show System Error(500).

#### Configurations(php.ini)
+ Turn Off display errors
+ Turn Off log errors if not needed

### Logger<a name="logger"></a>
A place where you can log, write any failures, errors, exceptions, or any other malicious actions or attacks.

```php
Logger::log("COOKIE", self::$userId . " is trying to login using invalid cookie", __FILE__, __LINE__);
```

### Email<a name="email"></a>
Emails are sent using [PHPMailer](https://github.com/PHPMailer/PHPMailer) via SMTP, another awesome library for sending emails. You shouldn't use ```mail()``` function of PHP.

**NOTE** You need to configure your SMTP account data in _app/config/config.php_.


### News Feed & Posts & Comments<a name="newsfeed-posts-comments"></a>

Think of News Feed as tweets in twitter, and in Posts like when you open an Issue in Github.

They are implemented to be merged with the core features mentioned above. Also apply some concepts like Pagination, How can you edit & delete in place(secured way), How can you manage permissions for who can create, edit, update and delete, and so forth.

You will see each newsfeed comes with and encrypted id like: ```feed-51b2cfa```.

### Files<a name="files"></a>
Nothing wired to explain, You can upload and download.
#### Upload
+ POST request, MIME, Size, Image dimension Validations
+ Setting file permission to avoid executable files
+ Sanitizing file names
+ Progress bar(no-plugins)

#### Download
+ Every file will have hashed version of it's name, this hashed name will be exposed to users. The hashed name = hash(original filename . extension). So, download link will look something like this: [http://localhost/miniPHP/downloads/download/b989f733f948e8a4b8b700e1](http://localhost/miniPHP/downloads/download/b989f733f948e8a4b8b700e1)

#### Configurations(php.ini)
+ Set ```file_uploads``` to true
+ Set ```upload_max_filesize, max_file_uploads, post_max_size```
	- Check [documentation](http://php.net/manual/en/ini.core.php#ini.post-max-size) to know how to assign proper values for each.

### Profile<a name="profile"></a>
Every user can change his name, email, password. Also upload profile picture, initially(default.png).

### Notifications<a name="notifications"></a>
Did you see the red notifications on facebook, or the blue one on twitter. The same idea is here. But, It's implemented using triggers instead. Triggers are defined in __installation/triggers.sql_.

So, whenever user creates a new newsfeed, post, or upload a file, this will increment the count for all other users, and will display a red notification in navigation bar.

### Users<a name="users"></a>
Only admins have access to see all registered users. They can delete, edit their info.

### Backups<a name="backups"></a>
In most of the situations, you will need to create backups for the system, and restore them whenever you want.

This is done by using [mysqldump](https://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) to create and restore backups. All backups will be stored in _app/backups_.

### Support <a name="support"></a>
# PLEASE 
I've written this script in my free time during my studies. This is for free, unpaid. I am saying this because I've seen many developers acts very rude towards any software, and their behavior is really frustrating. I don't know why?! Everyone tends to complain, and saying harsh words. I do accept the feedback, but, in a good and respectful manner.

There are many other scripts online for purchase that does the same thing(if not less), and their authors are earning good money from it, but, I choose to keep it public, available for everyone.

# SUPPORT
If you learnt something, or I saved your time, please support the project by spreading the word.

### Contribute <a name="contribute"></a>

Contribute by creating new issues, sending pull requests on Github or you can send an email at: omar_elgabry_93@gmail.com

### Dependencies <a name="dependencies"></a>
+ [PHPMailer](https://github.com/PHPMailer/PHPMailer)
+ [Captcha](https://github.com/Gregwar/Captcha)
+ [Theme SB Admin 2](https://github.com/IronSummitMedia/startbootstrap-sb-admin-2)

### License <a name="license"></a>
Built under [MIT](http://www.opensource.org/licenses/mit-license.php) license.
