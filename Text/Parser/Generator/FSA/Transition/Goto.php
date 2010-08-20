<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
require_once('Text/Parser/Generator/FSA/Transition.php');

/** 
 * Text_Parser_Generator_FSA_Transition_Goto represents a reduce transtion in a Text_Parser_Generator_FSA state graph
 */ 
class Text_Parser_Generator_FSA_Transition_Goto extends Text_Parser_Generator_FSA_Transition
{
    protected $targetState;
    /* getTargetState {{{ */
    public function getTargetState()
    {
        return $this->targetState;
    }
    /* }}} */
    /* Constructor  {{{ */
    public function __construct($origin, $target)
    {
        parent::__construct($origin);
        if (!$target instanceof Text_Parser_Generator_FSA_State) throw new Text_Parser_Generator_Exception('Target of transition must be a Text_Parser_Generator_FSA_State');
        $this->targetState = $target;
    }
    /* }}} */
    /* computeLookahead {{{ */
    public function computeLookahead($grammar)
    {
        $result = array();
        return $result;
        foreach ($this->getOriginItem()->followSet($this->grammar) as $symbol) {
            $result[] = array($symbol);
        }
        //if (count($result) != 1) throw new Text_Parser_Generator_Exception('Failed assert count($result) == 1');
        return $result;
    }
    /* }}} */
    /* __equals {{{ */
    public function __equals($other)
    {
        if (!parent::__equals($other)) return false;
        return $other->getTargetState() == $this->getTargetState();
    }
    /* }}} */
    /* __toString {{{ */
    public function __toString()
    {
        return sprintf('Goto on %s to %d because of %s' . "\n", 
            $this->getAdvanceSymbol()->getId(), 
            $this->getTargetState()->getFSA()->getStateIndex($this->getTargetState()),
            (string) $this->getOriginItem());
    }
    /* }}} */
    /* conflictsWith {{{ */
    public function conflictsWith($other)
    {
        if (!parent::conflictsWith($other)) return false;
        if ($other instanceof $this && $other->getTargetState()->__equals($this->getTargetState())) return false;
        return true;
    }
    /* }}} */
}
?>
