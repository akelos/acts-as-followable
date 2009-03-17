<?php

class ActsAsFollowablePluginInstaller extends AkInstaller
{
    function down_1()
    {
        $this->dropTable('followers');
        $this->execute('DROP VIEW followable_friends');
    }
    
     function up_1()
    {

        $this->createTable('followers','id,
                                        follower_class,
                                        follower_id,
                                        following_class,
                                        following_id,
                                        created_at,
                                        updated_at');
        
        $this->addIndex('followers','follower_class');
        $this->addIndex('followers','following_class');
        $this->addIndex('followers','UNIQUE following_id,follower_id,follower_class,following_class','UNQ_FOLLOWS');
        
        $this->execute('CREATE VIEW followable_friends AS SELECT *
FROM followers a
WHERE a.follower_id
IN (

SELECT following_id
FROM followers
WHERE follower_id = a.following_id  AND following_class = a.follower_class
)');
    }
}
?>