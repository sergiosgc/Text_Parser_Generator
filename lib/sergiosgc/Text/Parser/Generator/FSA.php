<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
namespace sergiosgc;

/**
 * Text_Parser_Generator_FSA represents the finite state automaton for a parser
 *
 * A parser finite state automaton is composed of a set of states, connected by a set of transitions.
 * States contain ItemSets, representing the parser state. Transitions come in two flavours: reduce and shift.
 * Each transition connects a specific item in a state's itemset with a target state (connecting, in fact, two states).
 *
 * This class, and the structurally contained classes Text_Parser_Generator_FSA_State and Text_Parser_Generator_FSA_Transition
 * do not contain the logic needed to derive the actual FSA from the grammar. That function is delegated on Text_Parser_Generator 
 * subclasses.
 * 
 * This class does contain the logic to generate the LR parser action and goto tables representing this FSA.
 */
class Text_Parser_Generator_FSA
{
    protected $states = array();
    /* getStates {{{ */
    /**
     * States array getter
     *
     * @return array Array of states in this FSA
     */
    public function getStates()
    {
        return $this->states;
    }
    /* }}} */
    /* getState {{{ */
    /**
     * States instance getter
     *
     * @param int State index
     * @return Text_Parser_Generator_FSA_State State at the provided index
     * @throws Text_Parser_Generator_InvalidStateException if state does not exist
     */
    public function getState($i)
    {
        if (!array_key_exists($i, $this->states)) throw new Text_Parser_Generator_InvalidStateException(sprintf('State %d does not exist', $i));
        return $this->states[$i];
    }
    /* }}} */
    /* getStateIndex {{{ */
    /**
     * Search state and return its index
     *
     * @param Text_Parser_Generator_FSA_State State to search for
     * @return int State index
     * @throws Text_Parser_Generator_InvalidStateException if state does not exist
     */
    public function getStateIndex($state)
    {
        foreach ($this->states as $i => $candidate) if ($candidate->__equals($state)) return $i;
        throw new Text_Parser_Generator_InvalidStateException('State not found');
    }
    /* }}} */
    /* stateExists {{{ */
    /**
     * Test if state is part of this FSA
     * 
     * @param Text_Parser_Generator_FSA_State State to search for
     * @return boolean true iff state exists in this FSA
     */
    public function stateExists($state)
    {
        foreach ($this->states as $i => $candidate) if ($candidate->__equals($state)) return true;
        return false;
    }
    /* }}} */
    /* stateCount {{{ */
    /**
     * Return the cardinality of the state set
     * 
     * @return int Number of states in this FSA
     */
    public function stateCount()
    {
        return count($this->states);
    }
    /* }}} */
    /* addState {{{ */
    /**
     * Add state to FSA
     * 
     * If the state is already part of the FSA, no operation is executed and no error is returned
     *
     * @param Text_Parser_Generator_FSA_State State to add
     */
    public function addState($state)
    {
        if ($state instanceof Text_Parser_Generator_ItemSet) $state = new Text_Parser_Generator_FSA_State($state);
        $state->setFSA($this);
        if ($this->stateExists($state)) return;
        $this->states[] = $state;
    }
    /* }}} */
    /* removeState {{{ */
    /**
     * Remove state from FSA
     *
     * If the state is not part of the FSA, no operation is executed and no error is returned
     *
     * @param Text_Parser_Generator_FSA_State State to remove
     */
    public function removeState($state)
    {
        if (!$this->stateExists($state)) return;
        unset($this->states[$this->getStateIndex($state)]);
        $this->states = array_values($this->states);
    }
    /* }}} */
    /* getTransitionsByClass {{{ */
    /**
     * Given a transition class, retrieve all transitions in this FSA that are instances of that class
     *
     * @param string Class name to seek
     * @return array Array of Text_Parser_Generator_FSA_Transition instances that are of the given class
     */
    public function getTransitionsByClass($class)
    {
        $result = array();
        foreach ($this->states as $state) $result = array_merge($result, $state->getTransitionsByClass($class));
        return $result;
    }
    /* }}} */
    /* getItemsByPreviousSymbol {{{ */
    public function getItemsByPreviousSymbol($symbol)
    {
        $result = array();
        foreach($this->states as $state) $result = array_merge($result, $state->getItemsByPreviousSymbol($symbol));
        return $result;
    }
    /* }}} */
    /* getItemEqualTo {{{ */
    public function getItemEqualTo($right)
    {
        foreach($this->states as $i => $state) {
            $candidate = $this->states[$i]->getItemEqualTo($right);
            if (!is_null($candidate)) return $candidate;
        }
        throw new Text_Parser_Generator_Exception('Could not find item equal to ' . (string) $right);
    }
    /* }}} */
    /* addActionToTableRow {{{ */
    protected static function addActionToTableRow(&$row, $lookahead, $symbolset, &$action)
    {
        foreach ($symbolset as $symbol) {
            $symbol = (string) $symbol;
            if (count($lookahead) == 0) {
                $row[$symbol] = &$action;
            } else {
                if (!array_key_exists($symbol, $row)) $row[$symbol] = array(
                    'action' => 'lookahead',
                    'actionTable' => array(),
                    'wildcardActionTable' => array());
                self::addActionToTableRow($row[$symbol]['actionTable'], array_slice($lookahead, 1), $lookahead[0], $action);
            }
        }
    }
    /* }}} */
    /* addActionToTable {{{ */
    protected static function addActionToTable(&$table, $lookahead, $state, $symbol, $action)
    {

        if (!array_key_exists($state, $table)) $table[$state] = array();
        self::addActionToTableRow($table[$state], $lookahead, array($symbol), $action);
    }
    /* }}} */
    /* gotoTable {{{ */
    /** 
     * Generate the parser goto table
     *
     * The parser goto table is a bidimensional array. Each row is indexed by a parser state index. Each column is indexed
     * by the symbol triggering the goto. Each cell contains the next state the parser should goto.
     *
     * @return array LR Parser goto table
     */
    public function gotoTable()
    {
        $table = array();
        foreach ($this->getTransitionsByClass('\sergiosgc\Text_Parser_Generator_FSA_Transition_Goto') as $transition) {
            self::addActionToTable($table, 
                                   array(),
                                   $transition->getOriginState()->getIndex(), 
                                   $transition->getAdvanceSymbol()->getId(), 
                                   $transition->getTargetState()->getIndex());
        }
        return $table;
    }
    /* }}} */
    /* actionTable {{{ */
    /** 
     * Generate the parser action table
     *
     * The parser action table is a bidimensional array. Each row is indexed by a parser state index. Each column is indexed
     * by the symbol triggering the action. Each cell contains the action to be taken. The action is an associative array:
     *  - For an accept action, it contains an element 'action' containing the string 'accept'
     *  - For a shift action, it contains an element 'action' containing the string 'shift' and an element 'nextState' containing the next state (an int)
     *  - For a reduce action, it contains:
     *    - An element 'action' containing the string 'reduce'
     *    - An element 'function' containing the function name that will execute the reduction
     *    - An element 'symbols' which is a numerically indexed array containing the names assigned to the symbols in the grammar rule being 
     *      reduced (non-assigned symbols should contain an empty string
     *    - An element 'rule' containing the human-readable representation of the grammar rule (for debugging purposes)
     *  - For a lookahead action, it contains
     *    - An element 'action' containing the string 'lookahead'
     *    - An element 'actionTable' containing one action table row, indexed by lookahead token IDs. 
     *    - An element 'wildcardActionTable' containing an action table row to be used if no match can be found in 'actionTable' above.
     *
     * @return array LR Parser goto table
     */
    public function actionTable($grammar)
    {
        $table = array();
        foreach ($this->getTransitionsByClass('\sergiosgc\Text_Parser_Generator_FSA_Transition_Accept') as $transition) {
            self::addActionToTable($table, 
                                   $transition->getLookahead(),
                                   $transition->getOriginState()->getIndex(), 
                                   $transition->getAdvanceSymbol()->getId(), 
                                   array(
                                    'action' => 'accept'));
        }
        foreach ($this->getTransitionsByClass('\sergiosgc\Text_Parser_Generator_FSA_Transition_Shift') as $transition) {
            self::addActionToTable($table, 
                                   $transition->getLookahead(),
                                   $transition->getOriginState()->getIndex(), 
                                   $transition->getAdvanceSymbol()->getId(), 
                                   array(
                                    'action' => 'shift',
                                    'nextState' => $transition->getTargetState()->getIndex()));
        }
        foreach ($this->getTransitionsByClass('\sergiosgc\Text_Parser_Generator_FSA_Transition_Reduce') as $transition) {
            self::addActionToTable($table, 
                                   $transition->getLookahead(),
                                   $transition->getOriginState()->getIndex(), 
                                   $transition->getAdvanceSymbol()->getId(), 
                                   array(
                                    'action' => 'reduce',
                                    'symbols' => $transition->getOriginItem()->getRule()->getReductionFunctionSymbolmap(),
                                    'leftNonTerminal' => $transition->getOriginItem()->getRule()->getLeftSymbol(0)->getId(),
                                    'function' => sprintf('reduce_rule_%d', $grammar->getRuleIndex($transition->getOriginItem()->getRule()))));
        }
        return $table;
    }
    /* }}} */
    /* reductionFunctionCode {{{ */
    /** 
     * Generate the code for reduction functions in the parser
     *
     * @param Structures_Grammar Grammar for which to generate the reduction functions
     * @return string PHP code defining the reduction functions
     */
    public function reductionFunctionCode($grammar)
    {
        $done = array();
        $result = '';
        foreach ($this->getTransitionsByClass('\sergiosgc\Text_Parser_Generator_FSA_Transition_Reduce') as $transition) {
            if (array_key_exists($grammar->getRuleIndex($transition->getOriginItem()->getRule()), $done)) continue;
            $done[$grammar->getRuleIndex($transition->getOriginItem()->getRule())] = true;
            $code = $transition->getOriginItem()->getRule()->getReductionFunction();
            $args = $transition->getOriginItem()->getRule()->getReductionFunctionSymbolmap();
            $signature = '';
            $argPHPDoc = " *\n";
            foreach($args as $idx => $arg) if ($arg != '') {
                $signature .= (strlen($signature) == 0 ? '' : ',') . '&' . $arg;
                $argPHPDoc .= sprintf(<<<EOS
 * @param Text_Tokenizer_Token Token of type '%s'

EOS
                    , $transition->getOriginItem()->getRule()->getRightSymbol($idx)->getId());
            }
            if (is_null($code) || $code == '') $code = '$result = \'\';';
            $result .= sprintf(<<<EOS
/* %s {{{ */
/**
 * Reduction function for rule %d 
 *
 * Rule %d is:
 * %s
%s
 * @return Text_Tokenizer_Token Result token from reduction. It must be a '%s' token
 */
protected function &%s(%s)
{
    %s
    \$result = new \sergiosgc\Text_Tokenizer_Token('%s', \$result);
    return \$result;
}
/* }}} */

EOS
            , sprintf('reduce_rule_%d', $grammar->getRuleIndex($transition->getOriginItem()->getRule()))
            , $grammar->getRuleIndex($transition->getOriginItem()->getRule())
            , $grammar->getRuleIndex($transition->getOriginItem()->getRule())
            , (string) $transition->getOriginItem()->getRule()
            , chop($argPHPDoc)
            , (string) $transition->getOriginItem()->getRule()->getLeftSymbol(0)->getId()
            , sprintf('reduce_rule_%d', $grammar->getRuleIndex($transition->getOriginItem()->getRule()))
            , $signature
            , $code
            , $transition->getOriginItem()->getRule()->getLeftSymbol(0)->getId());
        }
        return $result;
    }
    /* }}} */
    
    /* guaranteeConflictless {{{ */
    /**
     * Test FSA for conflicts, throw an exception if FSA has a conflict
     */
    public function guaranteeConflictless()
    {
        foreach($this->states as $state) $state->guaranteeConflictless();
    }
    /* }}} */

    /* __toString {{{ */
    public function __toString()
    {
        $result = '-- Finite State Automaton States --' . "\n";
        foreach($this->states as $i => $state) {
            $result .= sprintf('----------- %d -----------' . "\n", $i);
            $result .= preg_replace('_^_m', '  ', (string) $state);
        }
        return $result;
    }
    /* }}} */
}

?>
