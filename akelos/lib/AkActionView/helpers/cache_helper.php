<?php

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+

/**
 * @package ActionPack
 * @subpackage Helpers
 * @author Bermi Ferrer <bermi a.t bermilabs c.om>
 */


/**
 * Cache Helpers lets you cache fragments of templates
*
* == Caching a block into a fragment
*
*   <b>Hello {name}</b>
*   <?php if (!$cache_helper->begin()) { ?>
*     All the topics in the system:
*     <?= $controller->renderPartial("topic", $Topic->findAll()); ?>
*   <?= $cache_helper->end();} ?>
*  
*
*
*   Normal view text
*/
class CacheHelper extends AkActionViewHelper 
{
    
    public function begin($key = array(), $options = array())
    {
        return $this->_controller->cacheTplFragmentStart($key, $options);
    }

    public function end($key = array(), $options = array())
    {
        return $this->_controller->cacheTplFragmentEnd($key, $options);
    }
}
