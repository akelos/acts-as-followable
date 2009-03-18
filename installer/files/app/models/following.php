<?php
Ak::import('Follower');
class Following extends Follower 
{
    
    function _getIdentifier()
    {
        return 'following_id';
    }
}
?>