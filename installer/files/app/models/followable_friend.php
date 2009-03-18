<?php
Ak::import('Follower');
class FollowableFriend extends Follower 
{
    function _getIdentifier()
    {
        return 'following_id';
    }
}
?>