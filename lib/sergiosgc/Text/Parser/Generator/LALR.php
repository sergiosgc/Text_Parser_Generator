<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
namespace sergiosgc;

/**
 * A Text_Parser_Generator_LALR is an LALR parser generator. 
 *
 * The parser generator requires a grammar as a Structures_Grammar instance, 
 * and will produce code for a Text_Parser_LR subclass for parsing the given 
 * grammar.  
 */
class Text_Parser_Generator_LALR extends Text_Parser_Generator_LR
{
    /* Constructor {{{ */
    /** 
     * Create a Text_Parser_Generator_LALR, for the given grammar
     *
     * @param Structures_Grammar The grammar to use for generating the parser
     */
    public function __construct($grammar)
    {
        $this->parserClass = '\sergiosgc\Text_Parser_LALR';
        parent::__construct($grammar);
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
        // Remove reduce transitions whose trigger symbol never occurs 
        // This is an optimization that reduces the parser table, so we can execute it
        // on all execute transitions, even if they don't participate in any conflict
        // 
        // This effectively removes all reduce-reduce conflicts on LALR(1) parsers
        foreach ($this->fsa->getStates() as $idx => $state) {
            $transitions = $state->getTransitions();
            for ($i = count($transitions) - 1; $i >= 0; $i--) {
                if ($transitions[$i] instanceof Text_Parser_Generator_FSA_Transition_Reduce && 
                    !$transitions[$i]->matchesFirstSet($this->grammar)) {
                    $state->removeTransition($i);
                }
            }
        }
        // Find shift-reduce conflicts and attempt resolution by computing lookaheads for participating
        // transactions
        foreach ($this->fsa->getStates() as $idx => $state) {
            $reduces = $state->getTransitionsByClass('Text_Parser_Generator_FSA_Transition_Reduce');
            $shifts = $state->getTransitionsByClass('Text_Parser_Generator_FSA_Transition_Shift');
            foreach ($reduces as $reduce) foreach ($shifts as $shift) {
                if ($reduce->conflictsWith($shift)) {
                    $shift->computeLookahead($this->grammar);
                    $reduce->computeLookahead($this->grammar);
                }
            }
        }
        // Find any remaining conflicts on transactions with different priorities. Solve by removing enough of the
        // lower priority transaction to remove the conflict
        foreach ($this->fsa->getStates() as $idx => $state) {
            $transitions = array_merge($state->getTransitionsByClass('Text_Parser_Generator_FSA_Transition_Reduce'), 
                                       $state->getTransitionsByClass('Text_Parser_Generator_FSA_Transition_Shift'));
            $toRemove = array();
            for ($i=0; $i < count($transitions); $i++) for ($j = $i+1; $j < count($transitions); $j++) if
                ($transitions[$i]->conflictsWith($transitions[$j]) &&
                 $transitions[$i]->getOriginItem()->getRule()->getPriority() != $transitions[$j]->getOriginItem()->getRule()->getPriority()) {
                if ($transitions[$i]->getOriginItem()->getRule()->getPriority() > $transitions[$j]->getOriginItem()->getRule()->getPriority()) {
                    $high = $i; 
                    $low = $j;
                } else {
                    $high = $j; 
                    $low = $i;
                    //$toRemove[$i] = true;
                }
                // Attempt removing common lookaheads
                $transitions[$low]->removeLookaheadCommonWith($transitions[$high], $this->grammar);
                // If they still conflict, remove the lower priority transition
                if ($transitions[$i]->conflictsWith($transitions[$j])) $toRemove[$low] = true;
            }
            krsort($toRemove);
            foreach(array_keys($toRemove) as $i) {
                $state->removeTransition($transitions[$i]);
            }
        }
        $this->fsa->guaranteeConflictless();
    }
    /* }}} */
}
?>
