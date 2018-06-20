--TEST--
Parse using an LALR(1) parser for a grammar with a reduce-reduce conflict and parse input
--FILE--
<?php
require_once(__DIR__ . '/../vendor/autoload.php');

$grammar = new \sergiosgc\Structures_Grammar(true, false);
$grammar->addTerminal(\sergiosgc\Structures_Grammar_Symbol::create('a'));
$grammar->addTerminal(\sergiosgc\Structures_Grammar_Symbol::create('b'));
$grammar->addTerminal(\sergiosgc\Structures_Grammar_Symbol::create('c'));
$grammar->addNonTerminal(\sergiosgc\Structures_Grammar_Symbol::create('S'));
$grammar->addNonTerminal(\sergiosgc\Structures_Grammar_Symbol::create('B'));
$grammar->addNonTerminal(\sergiosgc\Structures_Grammar_Symbol::create('A'));

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('S'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('F'));
$grammar->addRule($rule);

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('F'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('B'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('b'));
$grammar->addRule($rule);

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('F'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('A'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('c'));
$grammar->addRule($rule);

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('B'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('a'));
$grammar->addRule($rule);

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('A'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('a'));
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
            new \sergiosgc\Text_Tokenizer_Token('a', 'a'),
            new \sergiosgc\Text_Tokenizer_Token('c', 'c'));
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
Read token a(a) state []
Shifting to state 4
Read token c(c) state [4]
Reducing using reduce_rule_4 state [4] Result state [3]
Shifting to state 6
Read token () state [3 6]
Reducing using reduce_rule_2 state [3 6] Result state [1]
Accepting
object(sergiosgc\Text_Tokenizer_Token)#54 (2) {
  ["_id":protected]=>
  string(1) "F"
  ["_value":protected]=>
  string(0) ""
}
