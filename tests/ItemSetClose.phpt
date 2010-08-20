--TEST--
Closure test for an itemset
--FILE--
<?php
ini_set('include_path', realpath(dirname(__FILE__) . '/../../Structures_Grammar/') . ':' .
                        realpath(dirname(__FILE__) . '/../') . ':' .
                        ini_get('include_path'));
require_once('Structures/Grammar/Symbol.php');
require_once('Structures/Grammar/Rule.php');
require_once('Structures/Grammar.php');
require_once('Text/Parser/Generator/Item.php');
require_once('Text/Parser/Generator/ItemSet.php');

$grammar = new Structures_Grammar(false, false);
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
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('*'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('B'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('+'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('B'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('B'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('0'));
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('1'));
$grammar->addRule($rule);

$itemSet = new Text_Parser_Generator_ItemSet(new Text_Parser_Generator_Item($grammar->getRule(0), 0));
$itemSet->close($grammar);

print($itemSet);
?>
--EXPECT--
S->•E
+E->•E*B
+E->•E+B
+E->•B
+E->•0
+E->•1
