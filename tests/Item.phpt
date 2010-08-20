--TEST--
Create a few Text_Parser_Generator_Item instances
--FILE--
<?php
ini_set('include_path', realpath(dirname(__FILE__) . '/../../Structures_Grammar') . ':' .
                        realpath(dirname(__FILE__) . '/../') . ':' .
                        ini_get('include_path'));
require_once('Structures/Grammar/Symbol.php');
require_once('Structures/Grammar/Rule.php');
require_once('Text/Parser/Generator/Item.php');
$rule = new Structures_Grammar_Rule();
$symbol = Structures_Grammar_Symbol::create('A');
$symbol->setTerminal(false);
$rule->addSymbolToLeft($symbol);
$symbol = Structures_Grammar_Symbol::create('b');
$symbol->setTerminal(true);
$rule->addSymbolToRight($symbol);
$symbol = Structures_Grammar_Symbol::create('B');
$symbol->setTerminal(false);
$rule->addSymbolToRight($symbol);

$item = new Text_Parser_Generator_Item($rule, 0);
print($item);
print("\n");
$item = new Text_Parser_Generator_Item($rule, 1);
print($item);
print("\n");
$item = new Text_Parser_Generator_Item($rule, 2);
print($item);
print("\n");
$item = new Text_Parser_Generator_Item($rule, 0);
print($item);
print("\n");
$item = $item->advance();
print($item);
print("\n");
$item = $item->advance();
print($item);
print("\n");

?>
--EXPECT--
A->•bB
A->b•B
A->bB•
A->•bB
A->b•B
A->bB•
