--TEST--
Parse using an LALR(1) parser for a grammar with an optionally empty rule
--FILE--
<?php
ini_set('include_path', realpath(dirname(__FILE__) . '/../../Structures_Grammar/') . ':' .
                        realpath(dirname(__FILE__) . '/../../Text_Parser/') . ':' .
                        realpath(dirname(__FILE__) . '/../../Text_Tokenizer/') . ':' .
                        realpath(dirname(__FILE__) . '/../') . ':' .
                        ini_get('include_path'));
require_once('Structures/Grammar/Symbol.php');
require_once('Structures/Grammar/Rule.php');
require_once('Structures/Grammar.php');
require_once('Text/Parser/Generator/LALR.php');
require_once('Text/Tokenizer.php');
require_once('Text/Tokenizer/Token.php');

$grammar = new Structures_Grammar(true, false);
$grammar->addTerminal(Structures_Grammar_Symbol::create(' '));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('<whitespace>'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('S'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('<opt-whitespace>'));

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('S'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('<opt-whitespace>'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('<opt-whitespace>'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('<opt-whitespace>'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('<opt-whitespace>'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create(' '));
$grammar->addRule($rule);

$generator = new Text_Parser_Generator_LALR($grammar);
eval($generator->generate('SampleParser'));
class DummyTokenizer implements Text_Tokenizer
{
    protected $tokens = array();
    protected $i=0;
    public function __construct()
    {
        $this->tokens = array(
            new Text_Tokenizer_Token(' ', ' '),
            new Text_Tokenizer_Token(' ', ' '),
            new Text_Tokenizer_Token(' ', ' '));
        reset($this->tokens);
    }
    public function getNextToken()
    {
        if ($this->i < count($this->tokens)) return $this->tokens[$this->i++];
        return false;
    }
}

try 
{
    $parser = new SampleParser(new DummyTokenizer());
    $parser->setDebugLevel(10);
    var_dump($parser->parse());
} catch (Exception $e) { var_dump($e); print $e->getMessage(); }
?>
--EXPECT--
Read token  ( ) state []
Reducing using reduce_rule_1 state [] Result state [1]
Shifting to state 2
Read token  ( ) state [1 2]
Reducing using reduce_rule_2 state [1 2] Result state [1]
Shifting to state 2
Read token  ( ) state [1 2]
Reducing using reduce_rule_2 state [1 2] Result state [1]
Shifting to state 2
Read token () state [1 2]
Reducing using reduce_rule_2 state [1 2] Result state [1]
Accepting
object(Text_Tokenizer_Token)#44 (2) {
  ["_id":protected]=>
  string(16) "<opt-whitespace>"
  ["_value":protected]=>
  string(0) ""
}