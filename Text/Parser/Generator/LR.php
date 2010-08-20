<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
require_once('Text/Parser/Generator/ItemSet.php');
/**
 * A Text_Parser_Generator_LR is an LR(0) parser generator. 
 *
 * The parser generator requires a grammar as a Structures_Grammar instance, 
 * and will produce code for a Text_Parser_LR subclass for parsing the given 
 * grammar.  
 */
class Text_Parser_Generator_LR
{
    protected $parserClass = 'Text_Parser_LR';
    protected $grammar;
    protected $fsa;
    /* Constructor {{{ */
    /** 
     * Create a Text_Parser_Generator_LR, for the given grammar
     *
     * @param Structures_Grammar The grammar to use for generating the parser
     */
    public function __construct($grammar)
    {
        $this->grammar = $grammar;
    }
    /* }}} */
    /* initFSA {{{ */
    /**
     * Create the finite state automaton and add the first state to it
     */
    protected function initFSA()
    {
        require_once('Text/Parser/Generator/FSA.php');
        $itemSet = new Text_Parser_Generator_ItemSet();
        foreach($this->grammar->getRules() as $rule) if ($rule->getLeftSymbol(0) == $this->grammar->getStartSymbol()) {
            $itemSet->addItem(new Text_Parser_Generator_Item($rule));
        }
        $itemSet->close($this->grammar);
        $this->fsa = new Text_Parser_Generator_FSA();
        $this->fsa->addState($itemSet);
    }
    /* }}} */
    /* populateFSA {{{ */
    /**
     * Using the states already in the FSA, populate it with reachable states and state transitions
     */
    protected function populateFSA()
    {
        require_once('Text/Parser/Generator/FSA/State.php');
        require_once('Text/Parser/Generator/FSA/Transition.php');
        require_once('Text/Parser/Generator/FSA/Transition/Reduce.php');
        require_once('Text/Parser/Generator/FSA/Transition/Shift.php');
        require_once('Text/Parser/Generator/FSA/Transition/Goto.php');
        require_once('Text/Parser/Generator/FSA/Transition/Accept.php');
        require_once('Structures/Grammar/Symbol.php');
        for ($i=0; $i<$this->fsa->stateCount(); $i++) {
            $newStates = array();
            foreach ($this->fsa->getState($i)->getItemSet()->getItems() as $item) {
                // Accept transition
                if ($item->getIndex() >= $item->getRule()->rightCount() && 
                    $item->getRule()->getLeftSymbol(0) == $this->grammar->getStartSymbol()) {
                        $transition = new Text_Parser_Generator_FSA_Transition_Accept($item);
                        $transition->setAdvanceSymbol(Structures_Grammar_Symbol::create(''));
                }
                // Shift and goto transitions
                $newItem = $item->advance();
                if (is_null($newItem)) {
                    if ($this->grammar->getRuleIndex($item->getRule()) > 0) {
                        foreach ($this->grammar->getTerminals() as $terminal) {
                            $transition = new Text_Parser_Generator_FSA_Transition_Reduce($item);
                            $transition->setAdvanceSymbol($terminal);
                        }
                        $transition = new Text_Parser_Generator_FSA_Transition_Reduce($item);
                        $transition->setAdvanceSymbol(Structures_Grammar_Symbol::create(''));
                    }
                } else {
                    if (!array_key_exists($item->getSymbol()->getId(), $newStates)) $newStates[$item->getSymbol()->getId()] = 
                        new Text_Parser_Generator_FSA_State(new Text_Parser_Generator_ItemSet());
                    $newStates[$item->getSymbol()->getId()]->getItemSet()->addItem($newItem);
                    if ($item->getSymbol()->isTerminal()) {
                        $transition = new Text_Parser_Generator_FSA_Transition_Shift($item, $newStates[$item->getSymbol()->getId()]);
                    } else {
                        $transition = new Text_Parser_Generator_FSA_Transition_Goto($item, $newStates[$item->getSymbol()->getId()]);
                    }
                    $transition->setAdvanceSymbol($item->getSymbol());
                }
            }
            foreach($newStates as $state) {
                $state->getItemSet()->close($this->grammar);
                $this->fsa->addState($state);
            }
        }
    }
    /* }}} */
    /* guaranteeConflictless {{{ */
    /**
     * Guarantee the FSA has no conflicts.
     * 
     * This function must check the FSA for transaction conflicts and, if possible, fix any existing
     * conflicts. Upon normal return, the FSA can be assumed to be conflictless.
     *
     * If it is not possible to remove all conflicts from the FSA, this function must throw a
     * Text_Parser_Generator_ConflictException
     *
     * @throws Text_Parser_Generator_ConflictException
     */
    public function guaranteeConflictless()
    {
        $this->fsa->guaranteeConflictless();
    }
    /* }}} */
    /* generate {{{ */
    /** 
     * Generate code for a parser for the grammar.
     *
     * This method generates the php code for a parser that handles the grammar in this generator.
     * @return string Parser class PHP code
     */
    public function generate($className, $verbosity = 1)
    {
        $this->initFSA();
        $this->populateFSA();
        $this->guaranteeConflictless();
        return sprintf(<<<EOS
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
require_once('%s');
/**
 *
 * This is an automatically generated parser for the following grammar:
 *
%s
 *
%s
 *
 */
class %s extends %s
{
    /* Constructor {{{ */
    /**
     * Parser constructor
     * 
     * @param Text_Tokenizer Tokenizer that will feed this parser
     */
    public function __construct(&\$tokenizer)
    {
        parent::__construct(\$tokenizer);
        \$this->_gotoTable = unserialize('%s');
        \$this->_actionTable = unserialize('%s');
    }
    /* }}} */
%s
}
EOS
            , strtr($this->parserClass, array('_' => '/')) . '.php'
            , chop(preg_replace('_^_m', ' * ', (string) $this->grammar))
            , chop($verbosity == 0 ? " *\n" : preg_replace('_^_m', ' * ', (string) $this->fsa))
            , $className
            , $this->parserClass
            , strtr(serialize($this->fsa->gotoTable()), array('\\' => '\\\\', '\'' => '\\\''))
            , strtr(serialize($this->fsa->actionTable($this->grammar)), array('\\' => '\\\\', '\'' => '\\\''))
            , chop(preg_replace('_^_m', '    ', $this->fsa->reductionFunctionCode($this->grammar)))
        );
    }
    /* }}} */
    
//    protected $states = array();
//    protected $stateTransitions = array();
//
//    /* getGrammar {{{ */
//    public function getGrammar()
//    {
//        return $this->grammar;
//    }
//    /* }}} */
//    /* addState {{{ */
//    /**
//     * Add a new state to the parser, if it does not exist. Return the state index.
//     *
//     * This method adds a new state to the parser, represented as a Text_Parser_Generator_ItemSet. 
//     * If the state already exists, the method will not add it. It will always return the index
//     * of the state in the parser.
//     *
//     * @param Text_Parser_Generator_ItemSet itemset represeting the new state
//     * @return int State index
//     */
//    protected function addState(Text_Parser_Generator_ItemSet $state)
//    {
//        $index = $this->stateIndex($state); 
//        if (!is_null($index)) return $index;
//
//        $this->states[] = $state;
//        return count($this->states) - 1;
//    }
//    /* }}} */
//    /* stateIndex {{{ */
//    /**
//     * Return the index of a state in the parser, or null if non-existant.
//     *
//     * @param Text_Parser_Generator_ItemSet Itemset representing the state to search for
//     * @return int|null State index, or null if non-existant
//     */
//    protected function stateIndex(Text_Parser_Generator_ItemSet $state)
//    {
//        foreach ($this->states as $index => $candidate) if ($candidate == $state) return $index;
//        return null;
//    }
//    /* }}} */
//    /* initializeStateTable {{{ */
//    /**
//     * Create the first parser state.
//     *
//     * This method finds grammar rules whose production left side is the grammar start symbol. With 
//     * these rules, it creates the first itemset, closes it, and creates the first parser state.
//     */
//    protected function initializeStateTable()
//    {
//        $itemSet = new Text_Parser_Generator_ItemSet($this);
//        foreach($this->grammar->getRules() as $rule) if ($rule->getLeftSymbol(0) == $this->grammar->getStartSymbol()) {
//            $itemSet->addItem(new Text_Parser_Generator_Item($rule));
//        }
//        $itemSet->close($this->grammar);
//        $this->states = array($itemSet);
//        $this->stateTransitions = array();
//    }
//    /* }}} */
//    /* quote {{{ */
//    /**
//     * Quote a literal, php-code-style
//     *
//     * @param mixed Literal to quote
//     * @return string Quoted string
//     */
//    protected static function quote($value)
//    {
//        if (is_string($value)) return '\'' . strtr($value, array('\\' => '\\\\', '\'' => '\\\'')) . '\'';
//        return $value;
//    }
//    /* }}} */
//    /* arrayToPhpString {{{ */
//    /**
//     * Convert an array into a php expression that builds the array
//     *
//     * @param array Array to convert
//     * @return string Array as PHP array expression
//     */
//    protected static function arrayToPhpString($arr)
//    {
//        $result = '';
//        $separator = "\n";
//        foreach ($arr as $key => $value) {
//            $result .= $separator;
//            if (is_array($value)) {
//                $result .= sprintf("%s => \n%s", 
//                 self::quote($key),
//                 preg_replace('_^_m', ' ', self::arrayToPhpString($value)));
//            } else {
//                $result .= sprintf('%s => %s', 
//                 self::quote($key),
//                 self::quote($value));
//            }
//            $separator = ",\n";
//        }
//        if ($result == '') return 'array()';
//        return sprintf("array(%s\n)", preg_replace('_^_m', ' ', $result));
//
//    }
//    /* }}} */
//    /* gotoTable {{{ */
//    /** 
//     * Generate the parser goto table
//     *
//     * @return array Parser goto table
//     */
//    protected function gotoTable()
//    {
//        $table = array();
//        foreach ($this->stateTransitions as $state => $nextStates) {
//            $row = array();
//            foreach ($nextStates as $symbolId => $nextState) {
//                if ($this->grammar->isNonTerminal($symbolId)) $row[$symbolId] = $nextState;
//            }
//            if (count($row)) $table[$state] = $row;
//        }
//        return $table;
//    }
//    /* }}} */
//    /* generateGotoTable {{{ */
//    /** 
//     * Generate code for the parser goto table
//     *
//     * @return string Parser goto table as a PHP array definition
//     */
//    protected function generateGotoTable()
//    {
//        return self::arrayToPhpString($this->gotoTable());
//    }
//    /* }}} */
//    /* shiftTable {{{ */
//    /**
//     * Compute the table of shift actions.
//     *
//     * @return array Table of shift actions
//     */
//    public function shiftTable()
//    {
//        $table = array();
//        foreach ($this->stateTransitions as $state => $nextStates) {
//            $row = array();
//            foreach ($nextStates as $symbolId => $nextState) if ($this->grammar->isTerminal($symbolId)) $row[(string) $symbolId] = $nextState;
//            if (count($row)) $table[$state] = $row;
//        }
//
//        $this->_shiftTable = $table;
//        return $table;
//    }
//    /* }}} */
//    /* reduceTable {{{ */
//    /**
//     * Compute the table of reduce actions.
//     *
//     * @return array Table of reduce actions each item is a Structure_Grammar_Rule for reduction in that state(x)input
//     */
//    public function reduceTable()
//    {
//        $table = array();
//        foreach ($this->states as $stateId => $state) {
//            $reduceItem = $state->getReduceItem($this->grammar);
//            if ($reduceItem) {
//                $table[$stateId] = array();
//                foreach ($this->grammar->getTerminals() as $symbol) $table[$stateId][(string) $symbol] = $reduceItem;
//                $table[$stateId][''] = $reduceItem;
//            }
//        }
//        $this->_reduceTable = $table;
//        return $table;
//    }
//    /* }}} */
//    /* acceptTable {{{ */
//    public function acceptTable() 
//    {
//        $result = array();
//        foreach ($this->states as $stateId => $state) {
//            $accept = false;
//            foreach ($state->getItems() as $item) {
//                if (($item->getIndex() >= $item->getRule()->rightCount()) && 
//                    ($item->getRule()->getLeftSymbol(0) == $this->grammar->getStartSymbol())) {
//                    $accept = true;
//                    break;
//                }
//            }
//            if ($accept) {
//                $result[] = $stateId;
//            }
//        }
//        return $result;
//    }
//    /* }}} */
//    /* generateActionTable {{{ */
//    /** 
//     * Generate code for the parser action table
//     *
//     * @return string Parser action table as a PHP array definition
//     */
//    protected function generateActionTable()
//    {
//        $actionTable = $this->shiftTable();
//        foreach ($actionTable as $state => $actions) {
//            foreach ($actions as $symbol => $action) {
//                if (is_int($action)) $actionTable[$state][(string) $symbol] = array(
//                    'action' => 'shift',
//                    'nextState' => $action);
//            }
//        }
//
//        $reduceTable = $this->reduceTable();
//        foreach (array_keys($reduceTable) as $state) if (array_key_exists($state, $actionTable)) {
//            require_once('Text/Parser/Generator/Exception/Conflict.php');
//            throw new Text_Parser_Generator_Exception_Conflict($this, 
//                sprintf('Shift-reduce conflict on state %d',
//                    $state));
//        }
//
//        foreach ($reduceTable as $state => $symbols) foreach($symbols as $symbol => $item) {
//            $rule = $item->getRule();
//            $actionTable[$state][(string) $symbol] = array(
//                'action' => 'reduce',
//                'symbols' => $rule->getReductionFunctionSymbolmap(),
//                'leftNonTerminal' => $rule->getLeftSymbol(0)->getId(),
//                'function' => sprintf('reduce_rule_%d', $this->grammar->getRuleIndex($rule)));
//        }
//
//        foreach ($this->acceptTable() as $state) $actionTable[$state][''] = array(
//                'action' => 'accept'
//            );
//
//        return self::arrayToPhpString($actionTable);
//    }
//    /* }}} */
//    /* generateReductionFunctions {{{ */
//    /** 
//     * Generate the code for reduction functions in the parser
//     *
//     * @return string PHP code for the reduction functions
//     */
//    protected function generateReductionFunctions()
//    {
//        $reduceTable = $this->reduceTable();
//
//        $result = '';
//        foreach ($reduceTable as $state => $symbols) {
//            $code = $symbols[array_pop(array_keys($symbols))]->getRule()->getReductionFunction();
//            $args = $symbols[array_pop(array_keys($symbols))]->getRule()->getReductionFunctionSymbolmap();
//            $signature='';
//            foreach($args as $arg) if ($arg != '') {
//                $signature .= (strlen($signature) == 0 ? '' : ',') . $arg;
//            }
//            if (is_null($code) || $code == '') $code = '$result = \'\';';
//            $result .= sprintf(<<<EOS
//protected function %s(%s)
//{
//    require_once('Text/Tokenizer/Token.php');
//
//    %s
//    
//    return new Text_Tokenizer_Token('%s', \$result);
//}
//
//EOS
//            ,sprintf('reduce_rule_%d', $this->grammar->getRuleIndex($symbols[array_pop(array_keys($symbols))]->getRule())), 
//            $signature,
//            $code,
//            $symbols[array_pop(array_keys($symbols))]->getRule()->getLeftSymbol(0)->getId());
//        }
//        return $result;
//    }
//    /* }}} */
//    /* generateStateTransitions {{{ */
//    /**
//     * Compute the next possible states in the parser finite-state-machine and fill out the stateTransitions field
//     *
//     * @param Text_Parser_Generator_ItemSet Base parser state to compute transitions from
//     */
//    protected function generateStateTransitions(Text_Parser_Generator_ItemSet $fromState)
//    {
//        $transitions = array();
//        $reachableSets = $fromState->reachableItemSets($this->grammar);
//        foreach ($reachableSets as $symbolId => $set) {
//            $state = $this->addState($set);
//            $transitions[$symbolId] = $state;
//        }
//        $this->stateTransitions[$this->stateIndex($fromState)] = $transitions;
//    }
//    /* }}} */
//    /* generate {{{ */
//    /** 
//     * Generate code for a parser for the grammar.
//     *
//     * This method generates the php code for a parser that handles the grammar in this generator.
//     * @return string Parser class PHP code
//     */
//    public function generate($className)
//    {
//        $this->initializeStateTable();
//        for ($i=0; $i<count($this->states); $i++) {
//            $this->generateStateTransitions($this->states[$i]);
//        }
//        return sprintf(<<<EOS
//require_once('Text/Parser/LR.php');
///**
// *
// * This is an automatically generated parser for the following grammar:
// *
//%s *
// * The parser states are as follows:
// * (• is the position marker, and + marks kernel items)
// *
//%s *
// * The parser state transition table is as follows:
// *
// * <pre>
//%s * </pre>
// * Shift table:
//%s
// *
// * Goto table:
//%s
// *
// * Reduce table:
//%s
// *
// * Accept states:
//%s
// */
//class %s extends Text_Parser_LR
//{
//    public function __construct(&\$tokenizer)
//    {
//        parent::__construct(\$tokenizer);
//        \$this->_gotoTable = 
//%s;
//        \$this->_actionTable = 
//%s;
//    }
//%s
//}
//EOS
//        , preg_replace('_^_m', ' * ', (string) $this->grammar),
//          preg_replace('_^_m', ' * ', $this->debugInfoForStates()),
//          preg_replace('_^_m', ' * ', $this->debugInfoForStateTransitionTable()),
//          preg_replace('_^_m', ' *  ', (string) $this->debugInfoForShiftTable()),
//          preg_replace('_^_m', ' *  ', (string) $this->debugInfoForGotoTable()),
//          preg_replace('_^_m', ' *  ', (string) $this->debugInfoForReduceTable()),
//          preg_replace('_^_m', ' *  ', (string) $this->debugInfoForAccept()),
//          $className, 
//          preg_replace('_^_m', '            ', $this->generateGotoTable()), 
//          preg_replace('_^_m', '            ', $this->generateActionTable()), 
//          preg_replace('_^_m', '    ', $this->generateReductionFunctions())); 
//    }
//    /* }}} */
//    /* getItemsByPreviousSymbol {{{ */
//    public function getItemsByPreviousSymbol($symbol)
//    {
//        $result = array();
//        foreach($this->states as $state) $result = array_merge($result, $state->getItemsByPreviousSymbol($symbol));
//        return $result;
//    }
//    /* }}} */
//    /* getItemEqualTo {{{ */
//    public function getItemEqualTo($right)
//    {
//        foreach($this->states as $i => $state) {
//            $candidate = $this->states[$i]->getItemEqualTo($right);
//            if (!is_null($candidate)) return $candidate;
//        }
//        return null;
//    }
//    /* }}} */
//    /* debugInfoForStateTransitionTable {{{ */
//    /** 
//     * Return a human-readable string representation of the state transition table, for debugging purposes
//     *
//     * @return string The state transition table, human-readable
//     */
//    public function debugInfoForStateTransitionTable()
//    {
//        $result = 'state';
//        foreach ($this->grammar->getTerminals() as $symbol) $result .= sprintf('%5s', (string) $symbol);
//        foreach ($this->grammar->getNonTerminals() as $symbol) $result .= sprintf('%5s', (string) $symbol);
//        $result .= "\n";
//        foreach($this->stateTransitions as $id => $transitions) {
//            $result .= sprintf('%5d', $id);
//            foreach(array_merge($this->grammar->getTerminals(), $this->grammar->getNonTerminals()) as $symbol) {
//                $result .= sprintf('%5s', array_key_exists((string) $symbol, $transitions) ? $transitions[(string) $symbol] : '');
//            }
//            $result .= "\n";
//        }
//        return $result;
//    }
//    /* }}} */
//    /* debugInfoForStates {{{ */
//    /** 
//     * Return a human-readable string representation of the parser states, for debugging purposes
//     *
//     * @return string The parser states, human-readable
//     */
//    public function debugInfoForStates()
//    {
//        $result = '';
//        foreach ($this->states as $index => $state) {
//            $result .= sprintf("[%d]\n%s",
//                $index,
//                preg_replace('_^_m', ' | ', (string) $state));
//        }
//        return $result;
//    }
//    /* }}} */
//    /* debugInfoForShiftTable {{{ */
//    /** 
//     * Return a human-readable string representation of the shift table, for debugging purposes
//     * 
//     * The table has one row per parser state, and one column per terminal symbol. Each cell contains 
//     * the next state the parser will be in, after shifting. Empty rows are pruned before returning.
//     *
//     * @return string The parser shift table, human-readable
//     */
//    public function debugInfoForShiftTable()
//    {
//        $shiftTable = $this->shiftTable();
//        $colWidth = 0;
//        foreach ($shiftTable as $state => $actions) {
//            $colWidth = max($colWidth, strlen((string) $state));
//            foreach ($actions as $symbol => $action) $colWidth = max($colWidth, strlen((string) $action));
//        }
//        foreach ($this->grammar->getTerminals() as $symbol) $colWidth = max($colWidth, strlen((string) $symbol));
//        $colWidth++;
//        $result = sprintf("%${colWidth}s", '');
//        foreach ($this->grammar->getTerminals() as $symbol) $result .= sprintf("%${colWidth}s", (string) $symbol);
//        $result .= "\n";
//        
//        foreach ($shiftTable as $state => $actions) {
//            $result .= sprintf("%${colWidth}s", (string) $state);
//            foreach($this->grammar->getTerminals() as $symbol) {
//                $result .= sprintf("%${colWidth}s", array_key_exists((string) $symbol, $actions) ? $actions[(string) $symbol] : '');
//            }
//            $result .= "\n";
//        }
//
//        return $result;
//    }
//    /* }}} */
//    /* debugInfoForGotoTable {{{ */
//    /** 
//     * Return a human-readable string representation of the goto table, for debugging purposes
//     * 
//     * The table has one row per parser state, and one column per nonterminal symbol. Each cell contains 
//     * the next state the parser will be in. Empty rows are pruned before returning.
//     *
//     * @return string The parser goto table, human-readable
//     */
//    public function debugInfoForGotoTable()
//    {
//        $gotoTable = $this->gotoTable();
//        $colWidth = 0;
//        foreach ($gotoTable as $state => $actions) {
//            $colWidth = max($colWidth, strlen((string) $state));
//            foreach ($actions as $symbol => $action) $colWidth = max($colWidth, strlen((string) $action));
//        }
//        foreach ($this->grammar->getNonTerminals() as $symbol) $colWidth = max($colWidth, strlen((string) $symbol));
//        $colWidth++;
//        $result = sprintf("%${colWidth}s", '');
//        foreach ($this->grammar->getNonTerminals() as $symbol) $result .= sprintf("%${colWidth}s", (string) $symbol);
//        $result .= "\n";
//        
//        foreach ($gotoTable as $state => $actions) {
//            $result .= sprintf("%${colWidth}s", (string) $state);
//            foreach($this->grammar->getNonTerminals() as $symbol) {
//                $result .= sprintf("%${colWidth}s", array_key_exists((string) $symbol, $actions) ? $actions[(string) $symbol] : '');
//            }
//            $result .= "\n";
//        }
//
//        return $result;
//    }
//    /* }}} */
//    /* debugInfoForReduceTable {{{ */
//    /** 
//     * Return a human-readable string representation of the reduce table, for debugging purposes
//     * 
//     * The table has one row per parser state, and one column per terminal symbol. Each cell contains 
//     * the index of the grammar rule used for the reduction.
//     *
//     * @return string The parser reduce table, human-readable
//     */
//    public function debugInfoForReduceTable()
//    {
//        $reduceTable = $this->reduceTable();
//        $colWidth = 0;
//        foreach ($reduceTable as $state => $actions) {
//            $colWidth = max($colWidth, strlen((string) $state));
//            foreach ($actions as $symbol => $item) $colWidth = max($colWidth, strlen((string) $this->grammar->getRuleIndex($item->getRule())));
//        }
//        foreach ($this->grammar->getTerminals() as $symbol) $colWidth = max($colWidth, strlen((string) $symbol));
//        $colWidth++;
//        $result = sprintf("%${colWidth}s", '');
//        foreach ($this->grammar->getTerminals() as $symbol) $result .= sprintf("%${colWidth}s", (string) $symbol);
//        $result .= "\n";
//        
//        foreach ($reduceTable as $state => $actions) {
//            $result .= sprintf("%${colWidth}s", (string) $state);
//            foreach($this->grammar->getTerminals() as $symbol) {
//                $result .= sprintf("%${colWidth}s", array_key_exists((string) $symbol, $actions) ? 
//                    $this->grammar->getRuleIndex($actions[(string) $symbol]->getRule())
//                    : '');
//            }
//            $result .= "\n";
//        }
//
//        return $result;
//    }
//    /* }}} */
//    /* debugInfoForAccept {{{ */
//    /** 
//     * Return a human-readable string of the states where end-of-input causes an accept
//     * 
//     * @return string The parser reduce table, human-readable
//     */
//    public function debugInfoForAccept()
//    {
//        $result = '';
//        foreach ($this->acceptTable() as $state) $result .= ($result == '' ? '' : ' ') . $state;
//        return $result;
//    }
//    /* }}} */
}
?>
