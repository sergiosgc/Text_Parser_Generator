--TEST--
Parse using an LALR(1) parser for a grammar with a shift-reduce conflict
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
$grammar->addTerminal(Structures_Grammar_Symbol::create('1'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('S'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('F'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('A'));

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('S'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('F'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('F'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('1'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('A'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('F'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('1'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('A'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('1'));
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
            new Text_Tokenizer_Token('1', '1'),
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
    $parser = new SampleParser(new DummyTokenizer());
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
object(Text_Tokenizer_Token)#52 (2) {
  ["_id":protected]=>
  string(1) "F"
  ["_value":protected]=>
  string(0) ""
}