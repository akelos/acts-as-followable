<?php

class Follower extends ActiveRecord 
{
    static $identifier = 'follower_id';
    static $belongsTo = 'related';
    function __construct()
    {
        $attributes = (array)func_get_args();
        return $this->init($attributes);
    }
    function init($attributes = array())
    {
        if(isset($attributes[0]) && count($attributes) === 1 && is_string($attributes[0])){
            $name = $attributes[0];
            $this->belongsToClass = $name;
            Ak::import($name);
            $identifier = self::$identifier;
            if (method_exists($this,'_getIdentifier')) {
                $identifier = $this->_getIdentifier();
            }
            if (class_exists($name)) {
                    $this->belongsTo = array(strtolower($name) => array('class_name'=>$name,'primary_key_name'=>$identifier,'conditions'=>'following_class = ? AND follower_class = ?','bind'=>array($this->belongsToClass, $this->belongsToClass)));
            } else {
                trigger_error(Ak::t('Cannot instantiate model %modelName',array('modelName'=>$name)));
            }
        }
        return parent::init($attributes);
    }
    
    function &instantiate($record, $set_as_new = true)
    {
       $inheritance_column = $this->getInheritanceColumn();
        if(!empty($record[$inheritance_column])){
            $inheritance_column = $record[$inheritance_column];
            $inheritance_model_name = AkInflector::camelize($inheritance_column);
            @require_once(AkInflector::toModelFilename($inheritance_model_name));
            if(!class_exists($inheritance_model_name)){
                trigger_error($this->t("The single-table inheritance mechanism failed to locate the subclass: '%class_name'. ".
                "This error is raised because the column '%column' is reserved for storing the class in case of inheritance. ".
                "Please rename this column if you didn't intend it to be used for storing the inheritance class ".
                "or overwrite #{self.to_s}.inheritance_column to use another column for that information.",
                array('%class_name'=>$inheritance_model_name, '%column'=>$this->getInheritanceColumn())),E_USER_ERROR);
            }
        }

        $model_name = isset($inheritance_model_name) ? $inheritance_model_name : $this->getModelName();
        if (!isset($this->belongsToClass)) {
            $object =& new $model_name('attributes', $record);
        }else {
            $object =& new $model_name($this->belongsToClass);
            $object->setAttributes($record);
        }
        $object->_newRecord = $set_as_new;
        
        $object->afterInstantiate();
        $object->notifyObservers('afterInstantiate');
        
        (AK_CLI && AK_ENVIRONMENT == 'development') ? $object ->toString() : null;

        return $object;
    }
}
?>