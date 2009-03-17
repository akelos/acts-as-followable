<?php
/**
 * @ExtensionPoint BaseActiveRecord
 *
 */
class ActsAsFollowableExtensions
{
    function &follow(&$user) {
        $return = false;
        if (isset($this->followable) && method_exists($this->followable,"follow")) {
            $return=&$this->followable->follow(&$user);
        }
        return $return;
    }
    
    function &unfollow(&$user) {
        $return = false;
        if (isset($this->followable) && method_exists($this->followable,"follow")) {
            $return=&$this->followable->unfollow(&$user);
        }
        return $return;
    }
    
function isFollowing(&$user) {
        $return = false;
        if (isset($this->followable) && method_exists($this->followable,"isFollowing")) {
            $return=&$this->followable->isFollowing(&$user);
        }
        return $return;
    }
function isFollowedBy(&$user) {
        $return = false;
        if (isset($this->followable) && method_exists($this->followable,"isFollowedBy")) {
            $return=&$this->followable->isFollowedBy(&$user);
        }
        return $return;
    }
function isFriendOf(&$user) {
        $return = false;
        if (isset($this->followable) && method_exists($this->followable,"isFriendOf")) {
            $return=&$this->followable->isFriendOf(&$user);
        }
        return $return;
    }
}
?>