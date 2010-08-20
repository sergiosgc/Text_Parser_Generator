--TEST--
Test detection of shift-reduce conflicts
--FILE--
<?php
ini_set('include_path', realpath(dirname(__FILE__) . '/../../Structures_Grammar/') . ':' .
                        realpath(dirname(__FILE__) . '/../') . ':' .
                        ini_get('include_path'));
require_once('Structures/Grammar/Symbol.php');
require_once('Structures/Grammar/Rule.php');
require_once('Structures/Grammar.php');
require_once('Text/Parser/Generator/LR.php');
require_once('Text/Parser/Generator/Exception.php');

$grammar = new Structures_Grammar(true, false);
$grammar->addTerminal(Structures_Grammar_Symbol::create('1'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('E'));
$grammar->addNonTerminal(Structures_Grammar_Symbol::create('S'));

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('S'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('E'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('1'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('E'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('1'));
$grammar->addRule($rule);

$generator = new Text_Parser_Generator_LR($grammar);
try {
    print($generator->generate('SampleParser'));
} catch (Text_Parser_Generator_ShiftReduceConflictException $e) {
    print('Caught shift-reduce-exception\n');
}
?>
--EXPECT--
Caught shift-reduce-exception\n
