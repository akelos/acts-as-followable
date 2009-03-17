<?php

class ActsAsFollowablePlugin extends AkPlugin
{
    function load()
    {
        require_once($this->getPath().DS.'lib'.DS.'ActsAsFollowable.php');
    }
}

?>