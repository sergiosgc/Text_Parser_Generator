--TEST--
Parse using an LALR(1) parser for a grammar with a shift-reduce conflict
--FILE--
<?php
require_once(__DIR__ . '/../vendor/autoload.php');

$grammar = new \sergiosgc\Structures_Grammar(true, false);
$grammar->addTerminal(\sergiosgc\Structures_Grammar_Symbol::create('1'));
$grammar->addNonTerminal(\sergiosgc\Structures_Grammar_Symbol::create('S'));
$grammar->addNonTerminal(\sergiosgc\Structures_Grammar_Symbol::create('F'));
$grammar->addNonTerminal(\sergiosgc\Structures_Grammar_Symbol::create('A'));

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('S'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('F'));
$grammar->addRule($rule);

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('F'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('1'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('A'));
$grammar->addRule($rule);

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('F'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('1'));
$grammar->addRule($rule);

$rule = new \sergiosgc\Structures_Grammar_Rule();
$rule->addSymbolToLeft(\sergiosgc\Structures_Grammar_Symbol::create('A'));
$rule->addSymbolToRight(\sergiosgc\Structures_Grammar_Symbol::create('1'));
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
            new \sergiosgc\Text_Tokenizer_Token('1', '1'),
            new \sergiosgc\Text_Tokenizer_Token('1', '1'));
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
Read token 1(1) state []
Shifting to state 2
Read token 1(1) state [2]
Shifting to state 4
Read token () state [2 4]
Reducing using reduce_rule_3 state [2 4] Result state [2 3]
Reducing using reduce_rule_1 state [2 3] Result state [1]
Accepting
object(sergiosgc\Text_Tokenizer_Token)#53 (2) {
  ["_id":protected]=>
  string(1) "F"
  ["_value":protected]=>
  string(0) ""
}
