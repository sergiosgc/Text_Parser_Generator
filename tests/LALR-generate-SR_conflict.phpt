--TEST--
Generate LALR(1) parser for a grammar with a shift-reduce conflict
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
print($generator->generate('SampleParser'));
?>
--EXPECT--
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 *
 * This is an automatically generated parser for the following grammar:
 *
 * [0] S->F
 * [1] F->1A
 * [2] F->1
 * [3] A->1
 *
 * -- Finite State Automaton States --
 * ----------- 0 -----------
 *   --Itemset--
 *     S->•F
 *    +F->•1A
 *    +F->•1
 *   --Transitions--
 *    Goto on F to 1 because of S->•F
 *    Shift on 1 to 2 because of F->•1A 
 *    Shift on 1 to 2 because of F->•1 
 * ----------- 1 -----------
 *   --Itemset--
 *     S->F•
 *   --Transitions--
 *    Accept on  using S->F
 * ----------- 2 -----------
 *   --Itemset--
 *     F->1•A
 *     F->1•
 *    +A->•1
 *   --Transitions--
 *    Goto on A to 3 because of F->1•A
 *    Reduce on  using F->1 
 *    Shift on 1 to 4 because of A->•1 
 * ----------- 3 -----------
 *   --Itemset--
 *     F->1A•
 *   --Transitions--
 *    Reduce on  using F->1A 
 * ----------- 4 -----------
 *   --Itemset--
 *     A->1•
 *   --Transitions--
 *    Reduce on  using A->1
 *
 */
class SampleParser extends \sergiosgc\Text_Parser_LALR
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
        $this->_gotoTable = unserialize('a:2:{i:0;a:1:{s:1:"F";i:1;}i:2;a:1:{s:1:"A";i:3;}}');
        $this->_actionTable = unserialize('a:5:{i:1;a:1:{s:0:"";a:1:{s:6:"action";s:6:"accept";}}i:0;a:1:{i:1;a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:2;}}i:2;a:2:{i:1;a:2:{s:6:"action";s:5:"shift";s:9:"nextState";i:4;}s:0:"";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:0:"";}s:15:"leftNonTerminal";s:1:"F";s:8:"function";s:13:"reduce_rule_2";}}i:3;a:1:{s:0:"";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:2:{i:0;s:0:"";i:1;s:0:"";}s:15:"leftNonTerminal";s:1:"F";s:8:"function";s:13:"reduce_rule_1";}}i:4;a:1:{s:0:"";a:4:{s:6:"action";s:6:"reduce";s:7:"symbols";a:1:{i:0;s:0:"";}s:15:"leftNonTerminal";s:1:"A";s:8:"function";s:13:"reduce_rule_3";}}}');
    }
    /* }}} */
    /* reduce_rule_2 {{{ */
    /**
     * Reduction function for rule 2 
     *
     * Rule 2 is:
     * F->1
     *
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'F' token
     */
    protected function &reduce_rule_2()
    {
        $result = '';
        $result = new \sergiosgc\Text_Tokenizer_Token('F', $result);
        return $result;
    }
    /* }}} */
    /* reduce_rule_1 {{{ */
    /**
     * Reduction function for rule 1 
     *
     * Rule 1 is:
     * F->1A
     *
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'F' token
     */
    protected function &reduce_rule_1()
    {
        $result = '';
        $result = new \sergiosgc\Text_Tokenizer_Token('F', $result);
        return $result;
    }
    /* }}} */
    /* reduce_rule_3 {{{ */
    /**
     * Reduction function for rule 3 
     *
     * Rule 3 is:
     * A->1
     *
     * @return Text_Tokenizer_Token Result token from reduction. It must be a 'A' token
     */
    protected function &reduce_rule_3()
    {
        $result = '';
        $result = new \sergiosgc\Text_Tokenizer_Token('A', $result);
        return $result;
    }
    /* }}} */
}
