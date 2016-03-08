<?php

namespace Gcl\GclUsers\Contracts;

interface Validator
{
    /**
     * Custom validator rule
     *
     * @return boolean
     */
    public static function boot($request);

    /**
     * Declare rules
     *
     * @return array
     */
    public static function rules();
}
