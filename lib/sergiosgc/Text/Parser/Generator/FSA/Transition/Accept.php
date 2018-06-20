<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
namespace sergiosgc;

/** 
 * Text_Parser_Generator_FSA_Transition_Accept represents an accept transition in a Text_Parser_Generator_FSA state graph
 */ 
class Text_Parser_Generator_FSA_Transition_Accept extends Text_Parser_Generator_FSA_Transition
{
    /* Constructor  {{{ */
    public function __construct($origin)
    {
        parent::__construct($origin);
    }
    /* }}} */
    /* computeLookahead {{{ */
    public function computeLookahead($grammar)
    {
        return array();
    }
    /* }}} */
    /* __toString {{{ */
    public function __toString()
    {
        return sprintf('Accept on %s using %s' . "\n", $this->getAdvanceSymbol()->getId(), (string) $this->getOriginItem()->getRule());
    }
    /* }}} */
    /* conflictsWith {{{ */
    public function conflictsWith($other)
    {
        if (!parent::conflictsWith($other)) return false;
        if ($other instanceof $this) return false;
        return true;
    }
    /* }}} */
}
?>
