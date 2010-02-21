<?php

# This file is part of the Akelos Framework
# (Copyright) 2004-2010 Bermi Ferrer bermi a t bermilabs com
# See LICENSE and CREDITS for details

/**
 * This is the base class for all sort of models (Mailers or Active records)
 * It handles the naming conventions for models.
 *
 * See also <AkActiveRecord> and <AkActionMailer> as those are the ones you will usually inherit from
*/
class AkBaseModel extends AkLazyObject
{
    public $locale_namespace;
    public $_modelName;
    protected
    $_report_undefined_attributes = false;

    /**
    * Returns current model name
    */
    public function getModelName() {
        if(!isset($this->_modelName)){
            if(!$this->setModelName()){
                trigger_error(Ak::t('Unable to fetch current model name'),E_USER_NOTICE);
            }
        }
        return $this->_modelName;
    }

    /**
    * Sets current model name
    *
    * Use this option if the model name can't be guessed by the Active Record
    */
    public function setModelName($model_name = null) {
        if(!empty($model_name)){
            $this->_modelName = $model_name;
        }else{
            $this->_modelName = get_class($this);
        }
        return true;
    }

    public function getParentModelName() {
        if(!isset($this->_parentModelName)){
            if(!$this->setParentModelName()){
                return false;
            }
        }
        return $this->_parentModelName;
    }

    public function setParentModelName($model_name = null) {
        if(!empty($model_name)){
            $this->_parentModelName = $model_name;
        }else{
            $class_name = AkInflector::camelize(get_parent_class($this));
            if($class_name == 'AkActiveRecord'){
                trigger_error(Ak::t('The Akelos Framework could not automatically configure your model name.'.
                ' This might be caused because your model file is not located on %path. Please call $this->setParentModelName("YourParentModelName");'.
                ' in your model constructor in order to make this work.',array('%path'=>AK_MODELS_DIR.DS)), E_USER_ERROR);
                return false;
            }
            $this->_parentModelName = $class_name;
        }
        return true;
    }

    public function t($string, $array = null) {
        return Ak::t($string, $array, 
                AkConfig::getOption('locale_namespace', 
                    (!empty($this->locale_namespace) ? $this->locale_namespace : (
                        defined('AK_DEFAULT_LOCALE_NAMESPACE') ? AK_DEFAULT_LOCALE_NAMESPACE : 
                        AkInflector::underscore($this->getModelName())
                        )
                    )
                )
            );
    }


    public function getAttributeCondition($argument) {
        return is_array($argument) ? 'IN (?)' : (is_null($argument) ? 'IS ?' : '= ?');
    }


    protected function _enableObservers() {
        $this->extendClassLazily('AkModelObserver',
        array(
        'methods' => array (
        'notifyObservers',
        'setObservableState',
        'getObservableState',
        'addObserver',
        'getObservers',
        ),
        'autoload_path' => AK_ACTIVE_SUPPORT_DIR.DS.'models'.DS.'observer.php'
        ));
    }


    protected function _enableErrors() {
        $this->extendClassLazily('AkModelErrors',
        array(
        'methods' => array(
        'addError',
        'addErrorOnBlank',
        'addErrorOnBoundaryBreaking',
        'addErrorOnBoundryBreaking',
        'addErrorOnEmpty',
        'addErrorToBase',
        'clearErrors',
        'countErrors',
        'errorsToString',
        'getBaseErrors',
        'getDefaultErrorMessageFor',
        'getErrors',
        'getErrorsOn',
        'getFullErrorMessages',
        'hasErrors',
        'isInvalid',
        'yieldEachError',
        'yieldEachFullError',
        'yieldError',
        ),
        'autoload_path' => AK_ACTIVE_SUPPORT_DIR.DS.'models'.DS.'errors.php'
        ));
    }


    protected function _enableValidations() {
        $this->extendClassLazily('AkModelValidations',
        array(
        'methods' => array(
        'validate',
        'validateOnCreate',
        'validateOnUpdate',
        'needsValidation',
        'isBlank',
        'isValid',
        'validatesPresenceOf',
        'validatesUniquenessOf',
        'validatesLengthOf',
        'validatesInclusionOf',
        'validatesExclusionOf',
        'validatesNumericalityOf',
        'validatesFormatOf',
        'validatesAcceptanceOf',
        'validatesConfirmationOf',
        'validatesSizeOf',
        ),
        'autoload_path' => AK_ACTIVE_SUPPORT_DIR.DS.'models'.DS.'validations.php'
        ));
    }
    

    protected function _enableUtilities() {
        $this->extendClassLazily('AkModelUtilities',
        array(
        'methods' => array(
        'fromXml',
        'toJson',
        'fromJson',
        'toXml',
        'toYaml',
        ),
        'autoload_path' => AK_ACTIVE_SUPPORT_DIR.DS.'models'.DS.'utilities.php'
        ));
    }
    protected function _enableDebug() {
        $this->extendClassLazily('AkModelDebug',
        array(
        'methods' => array(
        'dbug',
        'toString',
        'dbugging',
        'debug'
        ),
        'autoload_path' => AK_ACTIVE_SUPPORT_DIR.DS.'models'.DS.'debug.php'
        ));
    }

}

class AkModelExtenssion
{
    protected $_Model;
    public function setExtendedBy(&$Model) {
        $this->_Model = $Model;
    }
}

