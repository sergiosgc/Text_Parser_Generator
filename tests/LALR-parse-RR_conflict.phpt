--TEST--
Parse using an LALR(1) parser for a grammar with a reduce-reduce conflict and parse input
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
$grammar->addTerminal(Structures_Grammar_Symbol::create('a'));
$grammar->addTerminal(Structures_Grammar_Symbol::create('b'));
$grammar->addTerminal(Structures_Grammar_Symbol::create('c'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('S'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('B'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('A'));

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('S'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('F'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('F'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('B'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('b'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('F'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('A'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('c'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('B'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('a'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('A'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('a'));
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
            new Text_Tokenizer_Token('a', 'a'),
            new Text_Tokenizer_Token('c', 'c'));
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
Read token a(a) state []
Shifting to state 4
Read token c(c) state [4]
Reducing using reduce_rule_4 state [4] Result state [3]
Shifting to state 6
Read token () state [3 6]
Reducing using reduce_rule_2 state [3 6] Result state [1]
Accepting
object(Text_Tokenizer_Token)#53 (2) {
  ["_id":protected]=>
  string(1) "F"
  ["_value":protected]=>
  string(0) ""
}