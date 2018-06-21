<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
namespace sergiosgc;

/** 
 * Text_Parser_Generator_FSA_Transition_Shift represents a reduce transtion in a Text_Parser_Generator_FSA state graph
 */ 
class Text_Parser_Generator_FSA_Transition_Shift extends Text_Parser_Generator_FSA_Transition
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
        $result = new Structures_Grammar_Symbol_Set();
        foreach ($this->getOriginItem()->followSet($grammar) as $symbol) {
            $result->addSymbol($symbol);
        }
        $this->lookahead = array($result);
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
        $result =  sprintf('Shift on %s to %d because of %s ', 
            $this->getAdvanceSymbol()->getId(), 
            $this->getTargetState()->getFSA()->getStateIndex($this->getTargetState()),
            (string) $this->getOriginItem());

        $result .= "Lookahead = ";
        foreach ($this->getLookahead() as $la) $result .= (string) $la;
        $result .= "\n";
        return $result;
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
