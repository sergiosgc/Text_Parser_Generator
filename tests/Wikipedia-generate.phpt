--TEST--
Generate parser for the grammar at http://en.wikipedia.org/wiki/Lr_parser
--FILE--
<?php
ini_set('include_path', realpath(dirname(__FILE__) . '/../../Structures_Grammar/') . ':' .
                        realpath(dirname(__FILE__) . '/../') . ':' .
                        ini_get('include_path'));
require_once('Structures/Grammar/Symbol.php');
require_once('Structures/Grammar/Rule.php');
require_once('Structures/Grammar.php');
require_once('Text/Parser/Generator/LR.php');

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
$rule->addReductionFunctionSymbolmap(0, '$e');
$rule->addReductionFunctionSymbolmap(1, '$b');
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('E'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('B'));
$rule->addReductionFunctionSymbolmap(0, '$b');
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('B'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('0'));
$rule->addReductionFunctionSymbolmap(0, '$zero');
$grammar->addRule($rule);

$rule = new Structures_Grammar_Rule();
$rule->addSymbolToLeft(Structures_Grammar_Symbol::create('B'));
$rule->addSymbolToRight(Structures_Grammar_Symbol::create('1'));
$rule->addReductionFunctionSymbolmap(0, '$one');
$grammar->addRule($rule);

$generator = new Text_Parser_Generator_LR($grammar);
print($generator->generate('SampleParser'));
?>
--EXPECT--
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
require_once('Text/Parser/LR.php');
/**
 *
 * This is an automatically generated parser for the following grammar:
 *
 * [0] S->E
 * [1] E->E*B
 * [2] E->E+B
 * [3] E->B
 * [4] B->0
 * [5] B->1
 *
 * -- Finite State Automaton States --
 * ----------- 0 -----------
 *   --Itemset--
 *     S->•E
 *    +E->•E*B
 *    +E->•E+B
 *    +E->•B
 *    +B->•0
 *    +B->•1
 *   --Transitions--
 *    Goto on E to 1 because of S->•E
 *    Goto on E to 1 because of E->•E*B
 *    Goto on E to 1 because of E->•E+B
 *    Goto on B to 2 because of E->•B
 *    Shift on 0 to 3 because of B->•0 
 *    Shift on 1 to 4 because of B->•1 
 * ----------- 1 -----------
 *   --Itemset--
 *     S->E•
 *     E->E•*B
 *     E->E•+B
 *   --Transitions--
 *    Accept on  using S->E
 *    Shift on * to 5 because of E->E•*B 
 *    Shift on + to 6 because of E->E•+B 
 * ----------- 2 -----------
 *   --Itemset--
 *     E->B•
 *   --Transitions--
 *    Reduce on 0 using E->B 
 *    Reduce on 1 using E->B 
 *    Reduce on + using E->B 
 *    Reduce on * using E->B 
 *    Reduce on  using E->B 
 * ----------- 3 -----------
 *   --Itemset--
 *     B->0•
 *   --Transitions--
 *    Reduce on 0 using B->0 
 *    Reduce on 1 using B->0 
 *    Reduce on + using B->0 
 *    Reduce on * using B->0 
 *    Reduce on  using B->0 
 * ----------- 4 -----------
 *   --Itemset--
 *     B->1•
 *   --Transitions--
 *    Reduce on 0 using B->1 
 *    Reduce on 1 using B->1 
 *    Reduce on + using B->1 
 *    Reduce on * using B->1 
 *    Reduce on  using B->1 
 * ----------- 5 -----------
 *   --Itemset--
 *     E->E*•B
 *    +B->•0
 *    +B->•1
 *   --Transitions--
 *    Goto on B to 7 because of E->E*•B
 *    Shift on 0 to 3 because of B->•0 
 *    Shift on 1 to 4 because of B->•1 
 * ----------- 6 -----------
 *   --Itemset--
 *     E->E+•B
 *    +B->•0
 *    +B->•1
 *   --Transitions--
 *    Goto on B to 8 because of E->E+•B
 *    Shift on 0 to 3 because of B->•0 
 *    Shift on 1 to 4 because of B->•1 
 * ----------- 7 -----------
 *   --Itemset--
 *     E->E*B•
 *   --Transitions--
 *    Reduce on 0 using E->E*B 
 *    Reduce on 1 using E->E*B 
 *    Reduce on + using E->E*B 
 *    Reduce on * using E->E*B 
 *    Reduce on  using E->E*B 
 * ----------- 8 -----------
 *   --Itemset--
 *     E->E+B•
 *   --Transitions--
 *    Reduce on 0 using E->E+B 
 *    Reduce on 1 using E->E+B 
 *    Reduce on + using E->E+B 
 *    Reduce on * using E->E+B 
 *    Reduce on  using E->E+B
 *
 */
class SampleParser extends Text_Parser_LR
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
        $this->_gotoTable = unserialize('a:3:{i:0;a:2:{s:1:"E";i:1;s:1:"B";i:2;}i:5;a:1:{s:1:"B";i:7;}i:6;a:1:{s:1:"B";i:8;}}');
        $this->_actionTable = unserialize('a:9:{i:1;a:3:{s:0:"";a:1:{s:6:"action";s:6:"accept";}s:1:"*";a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:5;}s:1:"+";a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:6;}}i:0;a:2:{i:0;a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:3;}i:1;a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:4;}}i:5;a:2:{i:0;a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:3;}i:1;a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:4;}}i:6;a:2:{i:0;a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:3;}i:1;a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:4;}}i:2;a:5:{i:0;a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:2:"$b";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_3";}i:1;a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:2:"$b";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_3";}s:1:"+";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:2:"$b";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_3";}s:1:"*";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:2:"$b";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_3";}s:0:"";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:2:"$b";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_3";}}i:3;a:5:{i:0;a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:5:"$zero";}s:15:"leftNonTerminal";s:1:"B";s:8:"function";s:13:"reduce_rule_4";}i:1;a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:5:"$zero";}s:15:"leftNonTerminal";s:1:"B";s:8:"function";s:13:"reduce_rule_4";}s:1:"+";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:5:"$zero";}s:15:"leftNonTerminal";s:1:"B";s:8:"function";s:13:"reduce_rule_4";}s:1:"*";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:5:"$zero";}s:15:"leftNonTerminal";s:1:"B";s:8:"function";s:13:"reduce_rule_4";}s:0:"";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:5:"$zero";}s:15:"leftNonTerminal";s:1:"B";s:8:"function";s:13:"reduce_rule_4";}}i:4;a:5:{i:0;a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:4:"$one";}s:15:"leftNonTerminal";s:1:"B";s:8:"function";s:13:"reduce_rule_5";}i:1;a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:4:"$one";}s:15:"leftNonTerminal";s:1:"B";s:8:"function";s:13:"reduce_rule_5";}s:1:"+";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:4:"$one";}s:15:"leftNonTerminal";s:1:"B";s:8:"function";s:13:"reduce_rule_5";}s:1:"*";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:4:"$one";}s:15:"leftNonTerminal";s:1:"B";s:8:"function";s:13:"reduce_rule_5";}s:0:"";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:4:"$one";}s:15:"leftNonTerminal";s:1:"B";s:8:"function";s:13:"reduce_rule_5";}}i:7;a:5:{i:0;a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:3:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_1";}i:1;a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:3:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_1";}s:1:"+";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:3:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_1";}s:1:"*";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:3:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_1";}s:0:"";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:3:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_1";}}i:8;a:5:{i:0;a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:3:{i:0;s:2:"$e";i:1;s:2:"$b";i:2;s:0:"";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_2";}i:1;a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:3:{i:0;s:2:"$e";i:1;s:2:"$b";i:2;s:0:"";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_2";}s:1:"+";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:3:{i:0;s:2:"$e";i:1;s:2:"$b";i:2;s:0:"";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_2";}s:1:"*";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:3:{i:0;s:2:"$e";i:1;s:2:"$b";i:2;s:0:"";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_2";}s:0:"";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:3:{i:0;s:2:"$e";i:1;s:2:"$b";i:2;s:0:"";}s:15:"leftNonTerminal";s:1:"E";s:8:"function";s:13:"reduce_rule_2";}}}');
    }
    /* }}} */
    /* reduce_rule_3 {{{ */
    /**
     * Reduction function for rule 3 
     *
     * Rule 3 is:
     * E->B
     *
     * @param Text_Tokenizer_Token Token of type 'B'
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'E' token
     */
    protected function &reduce_rule_3(&$b)
    {
        require_once('Text/Tokenizer/Token.php');
        $result = '';
        $result =& new Text_Tokenizer_Token('E', $result);
        return $result;
    }
    /* }}} */
    /* reduce_rule_4 {{{ */
    /**
     * Reduction function for rule 4 
     *
     * Rule 4 is:
     * B->0
     *
     * @param Text_Tokenizer_Token Token of type '0'
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'B' token
     */
    protected function &reduce_rule_4(&$zero)
    {
        require_once('Text/Tokenizer/Token.php');
        $result = '';
        $result =& new Text_Tokenizer_Token('B', $result);
        return $result;
    }
    /* }}} */
    /* reduce_rule_5 {{{ */
    /**
     * Reduction function for rule 5 
     *
     * Rule 5 is:
     * B->1
     *
     * @param Text_Tokenizer_Token Token of type '1'
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'B' token
     */
    protected function &reduce_rule_5(&$one)
    {
        require_once('Text/Tokenizer/Token.php');
        $result = '';
        $result =& new Text_Tokenizer_Token('B', $result);
        return $result;
    }
    /* }}} */
    /* reduce_rule_1 {{{ */
    /**
     * Reduction function for rule 1 
     *
     * Rule 1 is:
     * E->E*B
     *
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'E' token
     */
    protected function &reduce_rule_1()
    {
        require_once('Text/Tokenizer/Token.php');
        $result = '';
        $result =& new Text_Tokenizer_Token('E', $result);
        return $result;
    }
    /* }}} */
    /* reduce_rule_2 {{{ */
    /**
     * Reduction function for rule 2 
     *
     * Rule 2 is:
     * E->E+B
     *
     * @param Text_Tokenizer_Token Token of type 'E'
     * @param Text_Tokenizer_Token Token of type '+'
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'E' token
     */
    protected function &reduce_rule_2(&$e,&$b)
    {
        require_once('Text/Tokenizer/Token.php');
        $result = '';
        $result =& new Text_Tokenizer_Token('E', $result);
        return $result;
    }
    /* }}} */
}