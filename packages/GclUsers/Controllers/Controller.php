<?php

namespace Gcl\GclUsers\Controllers;

use Auth;
use Validator;
use App\Http\Controllers\Controller as AppController;

class Controller extends AppController
{
    /**
     * Check authentication
     *
     * @return boolean
     */
    public function checkAuth()
    {
        return !empty(Auth::user());
    }
}
