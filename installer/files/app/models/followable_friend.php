<?php
Ak::import('Follower');
class FollowableFriend extends Follower 
{
    function __construct()
    {
        $this->setTableName('followable_friends', true, true);
        $attributes = (array)func_get_args();
        return $this->init($attributes);
    }
    
    function _getIdentifier()
    {
        return 'following_id';
    }
}
?>