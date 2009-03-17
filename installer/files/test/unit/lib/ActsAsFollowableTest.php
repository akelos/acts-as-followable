<?php
require_once(AK_BASE_DIR.DS.'app'.DS.'vendor'.DS.'plugins'.DS.'acts_as_followable'.DS.'lib'.DS.'ActsAsFollowable.php');

class ActsAsFollowableTest extends AkUnitTest
{

    function test_start()
    {
        $this->uninstallAndInstallMigration('ActsAsFollowablePlugin');
        $this->installAndIncludeModels('Person');
        $this->includeAndInstatiateModels('Person');
        $this->followable = new ActsAsFollowable(&$this->Person);
        $this->populateTables('people');
    }
    function test_get_classification_for()
    {
        $obj = 'test';
        $expectedClassification = null;
        $classification = $this->followable->getClassificationFor($obj);
        $this->assertEqual($expectedClassification, $classification);
        
        $obj = new stdclass;
        $expectedClassification = 'stdclass';
        $classification = $this->followable->getClassificationFor($obj);
        $this->assertEqual($expectedClassification, $classification);
        
        $obj = new Person();
        $expectedClassification = 'Person';
        $classification = $this->followable->getClassificationFor($obj);
        $this->assertEqual($expectedClassification, $classification);
    }
    
    function test_get_identifier_for()
    {
        $obj = 'test';
        $expectedIdentifier = null;
        $identifier = $this->followable->getIdentifierFor($obj);
        $this->assertEqual($expectedIdentifier, $identifier);
        
        $obj = new stdclass;
        $obj->id = 1;
        $expectedIdentifier = 1;
        $identifier = $this->followable->getIdentifierFor($obj);
        $this->assertEqual($expectedIdentifier, $identifier);
        
        $obj = new stdclass;
        $expectedIdentifier = null;
        $identifier = $this->followable->getIdentifierFor($obj);
        $this->assertEqual($expectedIdentifier, $identifier);
    }
  
    
    function test_follow()
    {
        $bermi = $this->Person->find(1);
        $this->assertTrue($bermi);
        $arno = $this->Person->find(2);
        $this->assertTrue($arno);
        $ok = $bermi->follow($arno);
        $this->assertTrue($ok);
        $this->assertTrue($ok->id>0);
        
        $arno = $this->Person->find(2,array('include'=>'followers'));
        
        $this->assertEqual(1,count($arno->followers));
        
        $bermi = $this->Person->find(1,array('include'=>'followings'));
        
        $this->assertEqual(1,count($bermi->followings));
        $this->assertEqual('Arno',$bermi->followings[0]->name);
        
        $ok = $bermi->unfollow($arno);
        $this->assertTrue($ok);
        
        $arno = $this->Person->find(2,array('include'=>'followers'));
        $this->assertEqual(0,count($arno->followers));
    }
   
    function test_is_following_and_is_follower()
    {
        $bermi = $this->Person->find(1);
        $this->assertTrue($bermi);
        $arno = $this->Person->find(2);
        $this->assertTrue($arno);
        $ok = $bermi->follow($arno);
        $this->assertTrue($ok);
        $this->assertTrue($bermi->isFollowing($arno));
        $this->assertTrue($arno->isFollowedBy($bermi));
    }
    function test_is_already_following()
    {
        $bermi = $this->Person->find(1);
        $this->assertTrue($bermi);
        $arno = $this->Person->find(2);
        $this->assertTrue($arno);
        $ok = @$bermi->follow($arno);
        $this->assertFalse($ok);
        $bermi->unfollow($arno);
    }
    function test_is_friend()
    {
        $bermi = $this->Person->find(1);
        $this->assertTrue($bermi);
        
        $arno = $this->Person->find(2);
        $this->assertTrue($arno);
        $ok = $bermi->follow($arno);
        $this->assertTrue($ok);
        $jose = $this->Person->find(3);
        $this->assertTrue($jose);
        $ok = $bermi->follow($jose);
        $this->assertTrue($ok);
        $ok = $jose->follow($bermi);
        $this->assertTrue($ok);
        $ok = $arno->follow($bermi);
        $this->assertTrue($ok);
        $this->assertTrue($bermi->isFriendOf($arno));
        $this->assertTrue($arno->isFriendOf($bermi));
        
        $bermi = $this->Person->find(1,array('include'=>array('friends')));
        $this->assertEqual(2,count($bermi->friends));
        $arno = $this->Person->find(2,array('include'=>array('friends')));
        $this->assertEqual(1,count($arno->friends));
        $arno->unfollow($bermi);
        $bermi = $this->Person->find(1,array('include'=>array('friends')));
        $this->assertEqual(1,count($bermi->friends));
        $arno = $this->Person->find(2,array('include'=>array('friends')));
        $this->assertEqual(0,count($arno->friends));
    }
    function test_delete()
    {
        $bermi = $this->Person->find(1,array('include'=>'followers,friends,followings'));
        $bermi->destroy();
        $f = new Follower();
        $this->assertFalse($f->findBy('following_id',1));
    }
    function test_find_with_followers_friends_following()
    {
        $new = $this->Person->create(array('name'=>'NewGuy'));
        
        $arno = &$this->Person->find(2);
        $jose = &$this->Person->find(3);
        
        // new guy and arno are friends
        $arno->follow($new);
        $new->follow($arno);
        $jose->follow($new);
        
        $theNewGuy = $this->Person->findFirstBy('name','NewGuy',array('include'=>'followers,followings,friends'));

        $this->assertEqual(1,$theNewGuy->friend->count());
        $this->assertEqual(2,$theNewGuy->follower->count());
        $this->assertEqual(1,$theNewGuy->following->count());
    }
}
?>