<?php
class PersonInstaller extends AkInstaller
{
    function up_1()
    {
        $this->createTable('people','id,name');
        
    }
    
    function down_1()
    {
        $this->dropTable('people');
    }
}