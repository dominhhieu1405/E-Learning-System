<?php

namespace Services;


class CommandBuilder {

    public function __construct()
    {

    }

    function runCommand($cmd){

        chdir('../backend');

        return shell_exec($cmd);
    }
}