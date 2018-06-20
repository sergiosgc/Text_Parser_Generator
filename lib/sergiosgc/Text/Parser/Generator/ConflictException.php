<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
namespace sergiosgc;

class Text_Parser_Generator_ConflictException extends Text_Parser_Generator_Exception
{
    protected $transitions;
    public function getTransitions()
    {
        return $this->transitions;
    }
    public function __construct($message, $leftTransition, $rightTransition)
    {
        parent::__construct(sprintf("%s\nState:\n%s\nLeft transition:\n%s\nRight transition:\n%s", 
                            $message, 
                            (string) $leftTransition->getOriginState()->getItemSet(),
                            (string) $leftTransition, 
                            (string) $rightTransition));
        $this->transitions = array($leftTransition, $rightTransition);
    }
    public static function create($leftTransition, $rightTransition)
    {
        if (
            !($leftTransition instanceof Text_Parser_Generator_FSA_Transition_Reduce || $leftTransition instanceof Text_Parser_Generator_FSA_Transition_Shift) ||
            !($rightTransition instanceof Text_Parser_Generator_FSA_Transition_Reduce || $rightTransition instanceof Text_Parser_Generator_FSA_Transition_Shift) ||
            ($rightTransition instanceof Text_Parser_Generator_FSA_Transition_Shift && $leftTransition instanceof Text_Parser_Generator_FSA_Transition_Shift)) {
            return new Text_Parser_Generator_ConflictException('Strange conflict', $leftTransition, $rightTransition);
        }
        if ($leftTransition instanceof Text_Parser_Generator_FSA_Transition_Reduce && 
              $rightTransition instanceof Text_Parser_Generator_FSA_Transition_Shift) {
            return self::create($rightTransition, $leftTransition);
        }
        if ($rightTransition instanceof Text_Parser_Generator_FSA_Transition_Reduce && 
              $leftTransition instanceof Text_Parser_Generator_FSA_Transition_Reduce) {
            return new Text_Parser_Generator_ReduceReduceConflictException('Reduce-reduce conflict', $leftTransition, $rightTransition);
        } else {
            return new Text_Parser_Generator_ShiftReduceConflictException('Shift-reduce conflict', $leftTransition, $rightTransition);
        }

    }
}
