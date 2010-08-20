<?php
require_once('Text/Parser/Generator/Exception.php');
class Text_Parser_Generator_Exception_Conflict extends Text_Parser_Generator_Exception
{
    public function __construct($parser, $message = '', $parentException = null)
    {
        $message = sprintf(<<<EOS
%s

Parser generator debug information:
 - Grammar:
%s

 - Parser states:
%s

 - State transition table:
%s

 - Shift table:
%s

 - Goto table:
%s

 - Reduce table:
%s

 - Accept states:
%s

EOS
            , 
            $message,
            preg_replace('_^_m', '    ', (string) $parser->getGrammar()),
            preg_replace('_^_m', '    ', (string) $parser->debugInfoForStates()),
            preg_replace('_^_m', '    ', (string) $parser->debugInfoForStateTransitionTable()),
            preg_replace('_^_m', '    ', (string) $parser->debugInfoForShiftTable()),
            preg_replace('_^_m', '    ', (string) $parser->debugInfoForGotoTable()),
            preg_replace('_^_m', '    ', (string) $parser->debugInfoForReduceTable()),
            preg_replace('_^_m', '    ', (string) $parser->debugInfoForAccept()));
        parent::__construct($message, $parentException);            
    }
}
?>
