--TEST--
Parse using the parser generated by the parser generator for the grammar at http://en.wikipedia.org/wiki/Lr_parser
--FILE--
<?php
namespace sergiosgc;
require_once(__DIR__ . '/../vendor/autoload.php');

$grammar = new Structures_Grammar(true, false);
$grammar->addTerminal(Structures_Grammar_Symbol::create('0'));
$grammar->addTerminal(Structures_Grammar_Symbol::create('1'));
$grammar->addTerminal(Structures_Grammar_Symbol::create('+'));
$grammar->addTerminal(Structures_Grammar_Symbol::create('*'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('E'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('B'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('S'));

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('S'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('E'));
$rule->addReductionFunctionSymbolmap(0, '$e');
$rule->setReductionFunction('$result = $e->getValue();');
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('*'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('B'));
$rule->addReductionFunctionSymbolmap(0, '$e');
$rule->addReductionFunctionSymbolmap(2, '$b');
$rule->setReductionFunction('$result = ((int) $e->getValue()) * ((int) $b->getValue());');
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('+'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('B'));
$rule->addReductionFunctionSymbolmap(0, '$e');
$rule->addReductionFunctionSymbolmap(2, '$b');
$rule->setReductionFunction('$result = ((int) $e->getValue()) + ((int) $b->getValue());');
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('B'));
$rule->addReductionFunctionSymbolmap(0, '$b');
$rule->setReductionFunction('$result = $b->getValue();');
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('B'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('0'));
$rule->addReductionFunctionSymbolmap(0, '$zero');
$rule->setReductionFunction('$result = 0;');
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('B'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('1'));
$rule->addReductionFunctionSymbolmap(0, '$one');
$rule->setReductionFunction('$result = 1;');
$grammar->addRule($rule);

$generator = new Text_Parser_Generator_LR($grammar);
eval($generator->generate('SampleParser'));

class DummyTokenizer 
{
    protected $tokens = array();
    protected $i=0;
    public function __construct()
    {
        $this->tokens = array(
            new Text_Tokenizer_Token('1', '1'),
            new Text_Tokenizer_Token('+', '+'),
            new Text_Tokenizer_Token('1', '1'));
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
    $parser = new \SampleParser($tokenizer);
    $parser->setDebugLevel(10);
    var_dump($parser->parse());
} catch (Exception $e) { var_dump($e); print $e->getMessage(); }
?>
--EXPECT--
Read token 1(1) state []
Shifting to state 4
Read token +(+) state [4]
Reducing using reduce_rule_5 state [4]
Pushing state 2 Result state [2]
Reducing using reduce_rule_3 state [2]
Pushing state 1 Result state [1]
Shifting to state 6
Read token 1(1) state [1 6]
Shifting to state 4
Read token $($) state [1 6 4]
Reducing using reduce_rule_5 state [1 6 4]
Pushing state 8 Result state [1 6 8]
Reducing using reduce_rule_2 state [1 6 8]
Pushing state 1 Result state [1]
Accepting
object(sergiosgc\Text_Tokenizer_Token)#116 (2) {
  ["_id":protected]=>
  string(1) "E"
  ["_value":protected]=>
  int(2)
}
