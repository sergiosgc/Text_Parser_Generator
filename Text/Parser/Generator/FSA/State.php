<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
require_once('Text/Parser/Generator/Exception.php');

/**
 * Text_Parser_Generator_FSA_State a state in the finite state automaton for a parser
 *
 * A parser state is represented by a Text_Parser_Generator_ItemSet coupled with a set of transitions
 * Each transition connects a specific item in the state's itemset with a target state (connecting, in fact, two states).
 */
class Text_Parser_Generator_FSA_State
{
    protected $itemset;
    /* getItemset {{{ */
    /**
     * itemset getter
     *
     * @return Text_Parser_Generator_Itemset This parser's itemset
     */
    public function getItemset()
    {
        return $this->itemset;
    }
    /* }}} */
    /** The automaton this state belongs to */
    protected $fsa;
    /* setFSA {{{ */
    /**
     * fsa setter
     *
     * @param Text_Parser_Generator_FSA New value
     */
    public function setFSA($fsa)
    {
        $this->fsa = $fsa;
    }
    /* }}} */
    /* getFSA {{{ */
    /**
     * fsa getter
     *
     * @param Text_Parser_Generator_FSA Automaton that contains this state
     */
    public function getFSA()
    {
        return $this->fsa;
    }
    /* }}} */
    protected $transitions = array();
    /* addTransition {{{ */
    /**
     * Transition adder
     *
     * @param Text_Parser_Generator_FSA_Transition Transition to add to state
     */
    public function addTransition($transition)
    {
        foreach ($this->transitions as $candidate) if ($candidate == $transition) return;
        $this->transitions[] = $transition;
    }
    /* }}} */
    /* removeTransition {{{ */
    /**
     * Remove transition
     *
     * @param int Transition index
     */
    public function removeTransition($i)
    {
        $newTrans = array();
        if ($i instanceof Text_Parser_Generator_FSA_Transition) {
            foreach ($this->transitions as $idx => $transition) if (!$i->__equals($transition)) $newTrans[] = $transition;
        } else {
            foreach ($this->transitions as $idx => $transition) if ($i != $idx) $newTrans[] = $transition;
        }
        $this->transitions = $newTrans;
    }
    /* }}} */

    /* getTransition {{{ */
    /**
     * Transition getter
     *
     * @param int Transition index
     * @return Text_Parser_Generator_FSA_Transition Transition at specified index
     * @throws Text_Parser_Generator_Exception If the requested transition does not exist
     */
    public function &getTransition($i)
    {
        if (!array_key_exists($i, $this->transitions)) throw new Text_Parser_Generator_Exception(sprintf('Transition %d does not exist', $i));
        return $this->transitions[$i];
    }
    /* }}} */
    /* getTransitions {{{ */
    /**
     * Transition getter
     *
     * @return array Array of Text_Parser_Generator_FSA_Transition instances 
     */
    public function getTransitions()
    {
        return $this->transitions;
    }
    /* }}} */
    /* getTransitionsByClass {{{ */
    /**
     * Given a transition class, retrieve all transitions in this state that are instances of that class
     *
     * @param string Class name to seek
     * @return array Array of Text_Parser_Generator_FSA_Transition instances that are of the given class
     */
    public function getTransitionsByClass($class)
    {
        $result = array();
        foreach ($this->transitions as $idx => $candidate) if ($candidate instanceof $class) $result[] =& $this->transitions[$idx];
        return $result;
    }
    /* }}} */
    /* getIndex {{{ */
    /** 
     * Return the index of this state in the FSA that contains it
     *
     * @return int State index
     */
    public function getIndex()
    {
        return $this->fsa->getStateIndex($this);
    }
    /* }}} */
    /* guaranteeConflictless {{{ */
    /**
     * Test FSA state for conflicts, throw an exception if state has a conflict
     */
    public function guaranteeConflictless()
    {
        for ($i=0; $i<count($this->transitions) - 1; $i++) for ($j=$i+1; $j<count($this->transitions); $j++) {
            if ($this->transitions[$i]->conflictsWith($this->transitions[$j])) throw Text_Parser_Generator_ConflictException::create(
                $this->transitions[$i],
                $this->transitions[$j]);
        }
    }
    /* }}} */
    /* getItemsByPreviousSymbol {{{ */
    public function getItemsByPreviousSymbol($symbol)
    {
        return $this->itemset->getItemsByPreviousSymbol($symbol);
    }
    /* }}} */
    /* getItemEqualTo {{{ */
    public function getItemEqualTo($right)
    {
        return $this->itemset->getItemEqualTo($right);
    }
    /* }}} */
    /* Constructor {{{ */
    public function __construct($itemset)
    {
        $this->itemset = $itemset;
        $itemset->setState($this);
    }
    /* }}} */
    /* __equals {{{ */
    public function __equals($other)
    {
        return $this->itemset->__equals($other->getItemset());
    }
    /* }}} */
    /* __toString {{{ */
    public function __toString()
    {
        $result = '--Itemset--'.  "\n";
        $result .= preg_replace('_^_m', ' ', (string) $this->itemset);
        $result .= '--Transitions--'.  "\n";
        foreach ($this->transitions as $transition) $result .= preg_replace('_^_m', ' ', (string) $transition);
        return $result;
    }
    /* }}} */
}
?>
