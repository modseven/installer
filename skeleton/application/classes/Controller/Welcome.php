<?php

namespace <AppName>\Controller;

use Modseven\Controller;

class Welcome extends Controller
{

    public function index()
    {
        $this->response->body('hello, world!');
    }

} // End Welcome
