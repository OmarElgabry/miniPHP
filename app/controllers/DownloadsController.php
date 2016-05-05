<?php

/**
 * Downloads controller
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */
class DownloadsController extends Controller {


    public function beforeAction(){

        parent::beforeAction();

        $actions = ['download', 'users'];
        $this->Security->requireGet($actions);

        // if you want to add csrf_token in the URL of file download
        // So, it will be something like this: http://localhost/miniPHP/downloads/download/f850749b62bf3badfb6c0?csrf_token=21eb0f2c6b4fddce8a7f3
        // $this->Security->config("validateCsrfToken", true);
    }

    /**
     * download a file provided by it's hashed name
     * the url should be something like: http://localhost/miniPHP/downloads/download/f850749b62bf3ba57b6380b67c6f3096bcdfb6c0
     *
     * @param string $hashedFileName
     */
    public function download($hashedFileName = ''){

        $fullPath = APP . "uploads/" ;
        $file = $this->file->getByHashedName($hashedFileName);

        if(empty($file)){
            return $this->error(404);
        }

        $fullPath .= $hashedFileName . "." . $file["extension"];
        $file["basename"] = $file["filename"] . "." . $file["extension"];

        if(!Uploader::isFileExists($fullPath)){
            return $this->error(404);
        }

        $this->response->download($fullPath, ["basename" => $file["basename"], "extension" => $file["extension"]]);
    }

    /**
     * download users data as csv file
     *
     */
    public function users(){

        $data = $this->admin->getUsersData();
        $this->response->csv(["cols" => $data["cols"], "rows" => $data["rows"]], ["filename" => $data["filename"]]);
    }

    public function isAuthorized(){

        $action = $this->request->param('action');
        $role = Session::getUserRole();
        $resource = "downloads";

        //only for admin
        Permission::allow('admin', $resource, "*");

        //only for normal users
        Permission::allow('user', $resource, "download");

        return Permission::check($role, $resource, $action);

    }
}
