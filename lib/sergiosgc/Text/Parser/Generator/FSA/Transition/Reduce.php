<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
namespace sergiosgc;

/** 
 * Text_Parser_Generator_FSA_Transition_Reduce represents a reduce transition in a Text_Parser_Generator_FSA state graph
 */ 
class Text_Parser_Generator_FSA_Transition_Reduce extends Text_Parser_Generator_FSA_Transition
{
    /* Constructor  {{{ */
    public function __construct($origin)
    {
        parent::__construct($origin);
    }
    /* }}} */
    /* matchesFirstSet {{{  */
    public function matchesFirstSet($grammar)
    {
        return $this->getOriginItem()->firstSet($grammar)->symbolExists($this->advanceSymbol);
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
    /* __toString {{{ */
    public function __toString()
    {
        $result =  sprintf('Reduce on %s using %s ', $this->getAdvanceSymbol()->getId(), (string) $this->getOriginItem()->getRule());
        foreach ($this->getLookahead() as $la) $result .= (string) $la;
        $result .= "\n";
        return $result;
    }
    /* }}} */
    /* conflictsWith {{{ */
    public function conflictsWith($other)
    {
        if (!parent::conflictsWith($other)) return false;
        if ($other instanceof $this && $other->getOriginItem() == $this->getOriginItem()) return false;
        return true;
    }
    /* }}} */
}
?>
