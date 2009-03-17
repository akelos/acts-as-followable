<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2007, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @package ActiveRecord
 * @subpackage Behaviours
 * @author Arno Schneider <arno a.t. bermilabs dot com>
 * @copyright Copyright (c) 2002-2007, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

require_once(AK_LIB_DIR.DS.'AkActiveRecord'.DS.'AkObserver.php');
require_once(AK_APP_DIR.DS.'models'.DS.'activity.php');

/**
 * This plugin allows you to follow models and become friends.
 *
 * Following models:
 *
 * $paul->follow($frank);
 * 
 * Selecting with followers:
 * 
 * $paul = $this->User->findFirstBy('name','paul',array('include'=>'followers'));
 * 
 * var_dump($paul->followers[0]->getAttributes());
 * 
 * Selecting with the models someone is following:
 * 
 * $paul = $this->User->findFirstBy('name','paul',array('include'=>'followings'));
 *
 * var_dump($paul->followings[0]->getAttributes());
 * 
 * == Checking relations
 * 
 * $paul->isFollowing($frank);
 * $paul->isFollowedBy($frank);
 * $paul->isFriendOf($frank);
 * 
 * == Configuration Options
 * none so far
 * 
 */
class ActsAsFollowable extends AkObserver
{
    var $_instance;
    var $_taggableType;
    var $_tagList;
    var $_loaded = false;
    var $_cached_tag_column;
    var $_activities = array();

    function ActsAsFollowable(&$ActiveRecordInstance, $options = array())
    {
        $this->_instance = &$ActiveRecordInstance;
        $this->observe(&$this->_instance);
        $this->init($options);
        $this->_setHasMany($ActiveRecordInstance);
    }

    function _setHasMany(&$instance)
    {
        $value = array();
        $aliases = array('habtm','hasAndBelongsToMany');
        foreach($aliases as $alias) {
            if (isset($instance->$alias)) {
                if (!is_array($instance->$alias)) {
                    $value[]=$instance->$alias;
                } else {
                    $value = array_merge($value,$instance->$alias);
                }
                unset($instance->$alias);
            }
        }
        
        $instance->hasAndBelongsToMany = $value;
        
        $instance->hasAndBelongsToMany ['followers'] = 
         array ('unique' => 'true', 
         'dependent'=>'destroy',
                'join_table'=>'followers',
                'join_class_name'=>'Follower',
                'table_name'=> $instance->getTableName(),
                'class_name' => $this->getClassificationFor($instance),
                'foreign_key' => 'following_id', 
                'association_foreign_key'=>'follower_id',
                'condition' => 'follower_class = "' . $this->getClassificationFor($instance) . '" AND following_class="'.$this->getClassificationFor($instance).'"' );
        $instance->hasAndBelongsToMany['followings']=
         array ('unique' => 'true', 
         'dependent'=>'destroy',
                'join_table'=>'followers',
                'join_class_name'=>'Following',
                'table_name'=>$instance->getTableName(),
                'class_name' => $this->getClassificationFor($instance),
                'foreign_key' => 'follower_id', 
                'association_foreign_key'=>'following_id',
                'condition' => 'follower_class = "' . $this->getClassificationFor($instance) . '" AND following_class="'.$this->getClassificationFor($instance).'"' );
         $instance->hasAndBelongsToMany['friends']=
         array ('unique' => 'true', 
                'dependent'=>'destroy',
                'join_table'=>'followable_friends',
                'join_class_name'=>'FollowableFriend',
                'table_name'=>$instance->getTableName(),
                'class_name' => $this->getClassificationFor($instance),
                'foreign_key' => 'follower_id', 
                'association_foreign_key'=>'following_id',
                'condition' => 'follower_class = "' . $this->getClassificationFor($instance) . '" AND following_class="'.$this->getClassificationFor($instance).'"' );
         
         //var_dump($instance->hasAndBelongsToMany);
    }

    function init($options = array())
    {
        

    }
    function xafterDestroy(&$record)
    {
        $f = new Follower();
        $f->destroyAll('following_id OR follower_id',$record->getId(),$record->getId());
        return true;
    }
    function getClassificationFor(&$obj)
    {
        if (is_scalar($obj)) {
            return null;
        } else if (is_a($obj,'AkActiveRecord')) {
            return $obj->getModelName();
        } else {
            return strtolower(get_class($obj));
        }
    }
    function getIdentifierFor(&$obj)
    {
        if (isset($obj->id)) {
            return $obj->id;
        } else {
            return null;
        }
    }
    
    function &follow(&$record)
    {
        $false = false;
        if ($record->getId()<1) return $false;
        $f = new Follower();
        $f->set('follower_id',$this->_instance->getId());
        $f->set('follower_class', $this->getClassificationFor($this->_instance));
        $f->set('following_id',$record->getId());
        $f->set('following_class', $this->getClassificationFor($record));
        $res=($res=$f->save())?$f:$res;
        return $res;
    }
    function &unfollow(&$record)
    {
        $false = false;
        if ($record->getId()<1) return $false;
        if (isset($this->_instance->followers[$record->getId()])) {
            $res=$this->_instance->followers[$record->getId()]->destroy();
        } else {
            $f = new Follower();
            $res = $f->findFirstBy('follower_id AND follower_class AND following_id AND following_class',
                            $this->_instance->getId(),$this->getClassificationFor($this->_instance), $record->getId(),$this->getClassificationFor($record));
            if ($res) {
                $res = $res->destroy();
            }
        }
        return $res;
    }
    
    function isFollowing(&$record)
    {
        if (isset($this->_instance->followings) && is_array($this->_instance->followings) && !empty($this->_instance->followings) && $this->getClassificationFor($this->_instance) == $this->getClassificationFor($record)) {
            foreach($this->_instance->followings as $f) {
                if ($f->getId() == $record->getId()) return true;
            }
            
        }
            $f = new Follower();
            $res = $f->findFirstBy('follower_id AND follower_class AND following_id AND following_class',
                            $this->_instance->getId(),$this->getClassificationFor($this->_instance), $record->getId(),$this->getClassificationFor($record));
            if ($res) {
                return true;
            }
            return false;
        
    }
    
    function isFollowedBy(&$record)
    {
        if (isset($this->_instance->followers) && is_array($this->_instance->followers) && !empty($this->_instance->followers) && $this->getClassificationFor($this->_instance) == $this->getClassificationFor($record)) {
            foreach($this->_instance->followers as $f) {
                if ($f->getId() == $record->getId()) return true;
            }
            return false;
        } 
            $f = new Follower();
            $res = $f->findFirstBy('following_id AND following_class AND follower_id AND follower_class',
                            $this->_instance->getId(),$this->getClassificationFor($this->_instance), $record->getId(),$this->getClassificationFor($record));
            if ($res) {
                return true;
            }
            return false;
        
    }
    
    
    function isFriendOf(&$record) {
        return $this->isFollowing(&$record) && $this->isFollowedBy(&$record);
    }
}
?>