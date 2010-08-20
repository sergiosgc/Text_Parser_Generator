--TEST--
Generate LALR(1) parser for a grammar with a reduce-reduce conflict
--FILE--
<?php
ini_set('include_path', realpath(dirname(__FILE__) . '/../../Structures_Grammar/') . ':' .
                        realpath(dirname(__FILE__) . '/../') . ':' .
                        ini_get('include_path'));
require_once('Structures/Grammar/Symbol.php');
require_once('Structures/Grammar/Rule.php');
require_once('Structures/Grammar.php');
require_once('Text/Parser/Generator/LALR.php');

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
print($generator->generate('SampleParser'));
?>
--EXPECT--
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
require_once('Text/Parser/LALR.php');
/**
 *
 * This is an automatically generated parser for the following grammar:
 *
 * [0] S->F
 * [1] F->Bb
 * [2] F->Ac
 * [3] B->a
 * [4] A->a
 *
 * -- Finite State Automaton States --
 * ----------- 0 -----------
 *   --Itemset--
 *     S->•F
 *    +F->•Bb
 *    +F->•Ac
 *    +B->•a
 *    +A->•a
 *   --Transitions--
 *    Goto on F to 1 because of S->•F
 *    Goto on B to 2 because of F->•Bb
 *    Goto on A to 3 because of F->•Ac
 *    Shift on a to 4 because of B->•a 
 *    Shift on a to 4 because of A->•a 
 * ----------- 1 -----------
 *   --Itemset--
 *     S->F•
 *   --Transitions--
 *    Accept on  using S->F
 * ----------- 2 -----------
 *   --Itemset--
 *     F->B•b
 *   --Transitions--
 *    Shift on b to 5 because of F->B•b 
 * ----------- 3 -----------
 *   --Itemset--
 *     F->A•c
 *   --Transitions--
 *    Shift on c to 6 because of F->A•c 
 * ----------- 4 -----------
 *   --Itemset--
 *     B->a•
 *     A->a•
 *   --Transitions--
 *    Reduce on b using B->a 
 *    Reduce on c using A->a 
 * ----------- 5 -----------
 *   --Itemset--
 *     F->Bb•
 *   --Transitions--
 *    Reduce on  using F->Bb 
 * ----------- 6 -----------
 *   --Itemset--
 *     F->Ac•
 *   --Transitions--
 *    Reduce on  using F->Ac
 *
 */
class SampleParser extends Text_Parser_LALR
{
    /* Constructor {{{ */
    /**
     * Parser constructor
     * 
     * @param Text_Tokenizer Tokenizer that will feed this parser
     */
    public function __construct(&$tokenizer)
    {
        parent::__construct($tokenizer);
        $this->_gotoTable = unserialize('a:1:{i:0;a:3:{s:1:"F";i:1;s:1:"B";i:2;s:1:"A";i:3;}}');
        $this->_actionTable = unserialize('a:7:{i:1;a:1:{s:0:"";a:1:{s:6:"action";s:6:"accept";}}i:0;a:1:{s:1:"a";a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:4;}}i:2;a:1:{s:1:"b";a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:5;}}i:3;a:1:{s:1:"c";a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:6;}}i:4;a:2:{s:1:"b";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:0:"";}s:15:"leftNonTerminal";s:1:"B";s:8:"function";s:13:"reduce_rule_3";}s:1:"c";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:0:"";}s:15:"leftNonTerminal";s:1:"A";s:8:"function";s:13:"reduce_rule_4";}}i:5;a:1:{s:0:"";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:2:{i:0;s:0:"";i:1;s:0:"";}s:15:"leftNonTerminal";s:1:"F";s:8:"function";s:13:"reduce_rule_1";}}i:6;a:1:{s:0:"";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:2:{i:0;s:0:"";i:1;s:0:"";}s:15:"leftNonTerminal";s:1:"F";s:8:"function";s:13:"reduce_rule_2";}}}');
    }
    /* }}} */
    /* reduce_rule_3 {{{ */
    /**
     * Reduction function for rule 3 
     *
     * Rule 3 is:
     * B->a
     *
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'B' token
     */
    protected function &reduce_rule_3()
    {
        require_once('Text/Tokenizer/Token.php');
        $result = '';
        $result =& new Text_Tokenizer_Token('B', $result);
        return $result;
    }
    /* }}} */
    /* reduce_rule_4 {{{ */
    /**
     * Reduction function for rule 4 
     *
     * Rule 4 is:
     * A->a
     *
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'A' token
     */
    protected function &reduce_rule_4()
    {
        require_once('Text/Tokenizer/Token.php');
        $result = '';
        $result =& new Text_Tokenizer_Token('A', $result);
        return $result;
    }
    /* }}} */
    /* reduce_rule_1 {{{ */
    /**
     * Reduction function for rule 1 
     *
     * Rule 1 is:
     * F->Bb
     *
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'F' token
     */
    protected function &reduce_rule_1()
    {
        require_once('Text/Tokenizer/Token.php');
        $result = '';
        $result =& new Text_Tokenizer_Token('F', $result);
        return $result;
    }
    /* }}} */
    /* reduce_rule_2 {{{ */
    /**
     * Reduction function for rule 2 
     *
     * Rule 2 is:
     * F->Ac
     *
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'F' token
     */
    protected function &reduce_rule_2()
    {
        require_once('Text/Tokenizer/Token.php');
        $result = '';
        $result =& new Text_Tokenizer_Token('F', $result);
        return $result;
    }
    /* }}} */
}