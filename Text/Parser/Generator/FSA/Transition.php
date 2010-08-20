<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
require_once('Text/Parser/Generator/Exception.php');
require_once('Text/Parser/Generator/Item.php');
require_once('Text/Parser/Generator/FSA/State.php');

/**
 * Text_Parser_Generator_FSA_Transition represents an arc in a Text_Parser_Generator_FSA state graph
 *
 * A transition connects a specific item in the state's itemset with a target state (connecting, in fact, two states).
 * This class is abstract, as only Text_Parser_Generator_FSA_Transition_Reduce and Text_Parser_Generator_FSA_Transition_Shift
 * should be instantiated
 */
abstract class Text_Parser_Generator_FSA_Transition
{
    protected $advanceSymbol;
    /* getAdvanceSymbol {{{ */
    public function getAdvanceSymbol()
    {
        return $this->advanceSymbol;
    }
    /* }}} */
    /* setAdvanceSymbol {{{ */
    public function setAdvanceSymbol($symbol)
    {
        $this->advanceSymbol = $symbol;
    }
    /* }}} */
    protected $originItem;
    /* getOriginItem {{{ */
    public function getOriginItem()
    {
        return $this->originItem;
    }
    /* }}} */
    /* getOriginState {{{ */
    public function getOriginState()
    {
        return $this->originItem->getItemSet()->getState();
    }
    /* }}} */
    protected $lookahead = array();
    /* getLookahead {{{ */
    /**
     * Lookahead getter
     *
     * @return array Array of symbol sets specifying the lookahead that triggers this transition
     */
    public function getLookahead()
    {
        return $this->lookahead;
    }
    /* }}} */
    /* setLookahead {{{ */
    /**
     * Lookahead setter
     *
     * @param array Array of symbol sets specifying the lookahead for this transition
     */
    public function setLookahead($la)
    {
        $this->lookahead = $la;
    }
    /* }}} */

    /* removeLookaheadCommonWith {{{ */
    /**
     * Remove, from this transition's lookahead, symbols that would cause it to conflict with the argument transition
     *
     * Please note that this function will only remove lookahead symbols from the transition lookahead
     *
     * @param Text_Parser_Generator_FSA_Transition
     * @param Structures_Grammar Grammar to whose symbols the lookahead sets refer to
     */
    public function removeLookaheadCommonWith($other, $grammar)
    {
        foreach($other->getLookahead() as $i => $otherLookahead) {
            if (!array_key_exists($i, $this->lookahead)) $this->lookahead[$i] = $grammar->getNonTerminalSymbolSet();
            if ($this->lookahead[$i]->isDisjoint($otherLookahead)) break;
            $this->lookahead[$i]->complement($otherLookahead);
        }
    }
    /* }}} */
    
    /* Constructor  {{{ */
    public function __construct($origin)
    {
        if (!$origin instanceof Text_Parser_Generator_Item) throw new Text_Parser_Generator_Exception('Origin of transition must be a Text_Parser_Generator_Item');
        $this->originItem = $origin;
        $this->getOriginState()->addTransition($this);
    }
    /* }}} */
    /* __equals {{{ */
    public function __equals($other)
    {
        return $other->getOriginItem() == $this->getOriginItem();
    }
    /* }}} */

    /**
     * Compute the lookahead for this transition
     *
     * The result is an array of symbols, at most N large, for an LR(N) parser (or LALR(N)).
     * 
     * For an LALR(n) or LR(n) parser, the result contains at most N symbols. Naturally, 
     * for an LR(0) parser, the result is an empty array.
     *
     * If, for an LALR(n) parser, a result row contains less than n symbols, it can be assumed that the 
     * rightmost lookaheads (the ones closer to the end of the parsed document) are don't-cares/wildcards.
     *
     * @return array Array of symbol sets specifying the lookahead that triggers this transition
     */
    abstract public function computeLookahead($grammar);
    /* conflictsWith {{{ */
    /**
     * Test whether there is a conflict with another transition
     *
     * There's a conflict between two transitions originating from the same state if there is some 
     * input sequence (expected input symbol + lookahead symbols) that triggers both transitions
     * and the two transitions produce different parser state evolutions
     *
     * @return boolean true iff the transitions conflict
     */
    public function conflictsWith($other)
    {
        if ($this->getOriginState() != $other->getOriginState()) return false;
        if ($this->getAdvanceSymbol() != $other->getAdvanceSymbol()) return false;

        $left = $this->getLookahead();
        $right = $other->getLookahead();
        for ($i=min(count($left), count($right)) - 1; $i>=0; $i--) if ($left[$i]->isDisjoint($right[$i])) return false;

        return true;
    }
    /* }}} */
}
?>
