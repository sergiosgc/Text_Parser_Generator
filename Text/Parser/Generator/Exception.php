<?php
class Text_Parser_Generator_Exception extends Exception
{
}

class Text_Parser_Generator_InvalidItemException extends Text_Parser_Generator_Exception
{
}

class Text_Parser_Generator_InvalidStateException extends Text_Parser_Generator_Exception
{
}

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
        require_once('Text/Parser/Generator/FSA/Transition/Reduce.php');
        require_once('Text/Parser/Generator/FSA/Transition/Shift.php');
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
class Text_Parser_Generator_ShiftReduceConflictException extends Text_Parser_Generator_ConflictException
{
}
class Text_Parser_Generator_ReduceReduceConflictException extends Text_Parser_Generator_ConflictException
{
}
?>
