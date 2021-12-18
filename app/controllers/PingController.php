<?php

class PingController extends \BaseController {

    /**
     * Construct the resource.
     *
     */
    public function __construct() {
        $this->user = Auth::user();
    }
}
