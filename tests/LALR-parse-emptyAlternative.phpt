--TEST--
Parse using an LALR(1) parser for a grammar with an optionally empty rule
--FILE--
<?php
require_once(__DIR__ . '/../vendor/autoload.php');

$grammar = new \sergiosgc\Structures_Grammar(true, false);
$grammar->addTerminal(\sergiosgc\Structures_Grammar_Symbol::create(' '));
$grammar->addNonTerminal(\sergiosgc\Structures_Grammar_Symbol::create('<whitespace>'));
$grammar->addNonTerminal(\sergiosgc\Structures_Grammar_Symbol::create('S'));
$grammar->addNonTerminal(\sergiosgc\Structures_Grammar_Symbol::create('<opt-whitespace>'));

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('S'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('<opt-whitespace>'));
$grammar->addRule($rule);

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('<opt-whitespace>'));
$grammar->addRule($rule);

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('<opt-whitespace>'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('<opt-whitespace>'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create(' '));
$grammar->addRule($rule);

$generator = new \sergiosgc\Text_Parser_Generator_LALR($grammar);
eval($generator->generate('SampleParser'));
class DummyTokenizer implements \sergiosgc\Text_Tokenizer
{
    protected $tokens = array();
    protected $i=0;
    public function __construct()
    {
        $this->tokens = array(
            new \sergiosgc\Text_Tokenizer_Token(' ', ' '),
            new \sergiosgc\Text_Tokenizer_Token(' ', ' '),
            new \sergiosgc\Text_Tokenizer_Token(' ', ' '));
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
    $tokenizer = new DummyTokenizer();
    $parser = new SampleParser($tokenizer);
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
object(sergiosgc\Text_Tokenizer_Token)#45 (2) {
  ["_id":protected]=>
  string(16) "<opt-whitespace>"
  ["_value":protected]=>
  string(0) ""
}
