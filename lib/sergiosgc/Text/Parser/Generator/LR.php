<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
namespace sergiosgc;

/**
 * A Text_Parser_Generator_LR is an LR(0) parser generator. 
 *
 * The parser generator requires a grammar as a Structures_Grammar instance, 
 * and will produce code for a Text_Parser_LR subclass for parsing the given 
 * grammar.  
 */
class Text_Parser_Generator_LR
{
    protected $parserClass = '\sergiosgc\Text_Parser_LR';
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
}
?>
