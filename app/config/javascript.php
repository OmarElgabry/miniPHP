<?php

 /**
  * This file contains javascript configuration for the application.
  * It will be used by app/core/Config.php
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */

return array(

    'root' => PUBLIC_ROOT,          /* public root used in ajax calls and redirection from client-side */
    'fileSizeOverflow' => 10485760  /* max file size, this is important to avoid overflow in files with big size */
);
