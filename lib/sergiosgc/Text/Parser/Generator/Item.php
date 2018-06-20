<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
namespace sergiosgc;

/**
 * A Text_Parser_Generator_Item is a Structures_Grammar rule extended with
 * the parser state (i.e. how much of the rule was consumed and how much is left).
 *
 * This parser state is represented by a position in the rule. The position represents
 * the index of the next symbol to be consumed (in the righthand side of the rule).
 */
class Text_Parser_Generator_Item
{
    protected $rule;
    protected $index;
    protected $itemSet;
    protected $firstSet = null;
    /* setItemSet {{{ */
    public function setItemSet($itemSet)
    {
        $this->itemSet = $itemSet;
    }
    /* }}} */
    /* getItemSet {{{ */
    public function getItemSet()
    {
        return $this->itemSet;
    }
    /* }}} */
    /* getRule {{{ */
    public function getRule() 
    {
        return $this->rule;
    }
    /* }}} */
    /* getIndex {{{ */
    public function getIndex()
    {
        return $this->index;
    }
    /* }}} */
    /* getSymbol {{{ */
    public function getSymbol()
    {
        return $this->rule->getRightSymbol($this->index);
    }
    /* }}} */
    /* getPreviousSymbol {{{ */
    public function getPreviousSymbol()
    {
        if ($this->index == 0) return null;
        return $this->rule->getRightSymbol($this->index - 1);
    }
    /* }}} */
    /* advance {{{ */
    public function advance()
    {
        if ($this->index >= $this->rule->rightCount()) return null;
        return new Text_Parser_Generator_Item($this->rule, $this->index + 1);
    }
    /* }}} */
    /* isReduceable {{{ */
    public function isReduceable($grammar)
    {
        if ($grammar->getRuleIndex($this->getRule()) === 0) return false;
        return $this->index >= $this->rule->rightCount();
    }
    /* }}} */
    /* Constructor {{{ */
    public function __construct(Structures_Grammar_Rule $rule, $index = 0)
    {
        if ($index > $rule->rightCount()) throw new Text_Parser_Generator_InvalidItemException(sprintf('Unable to create item at symbol %d for rule with %d symbols', $index, $rule->rightCount()));

        $this->rule = $rule;
        $this->index = $index;
    }
    /* }}} */
    /* __equals {{{ */
    public function __equals($other)
    {
        if (!($other instanceof Text_Parser_Generator_Item)) return false;
        if ($other->getIndex() != $this->getIndex()) return false;
        if (!$other->getRule()->__equals($this->rule)) return false;
        return true;
    }
    /* }}} */
    /* __toString {{{ */
    public function __toString()
    {
        $left = $this->rule->getLeft();
        $right = $this->rule->getRight();
        $result = '';
        foreach ($left as $symbol) $result .= (string) $symbol;
        $result .= '->';
        foreach ($right as $i => $symbol) {
            if ($i == $this->index) $result .= '•';
            $result .= $symbol;
        }
        if ($this->index == count($right)) $result .= '•';
        if ($this->getRule()->getPriority() != 0) $result .= sprintf('(prio:%d)', $this->getRule()->getPriority());

        return $result;
    }
    /* }}} */
    /* followSet {{{ */
    /**
     * Calculates the follow set (a set of terminal symbols) for this item in the context of its item set
     *
     * The follow set is the set of symbols (terminal) that can occur after this item's current symbol, in the context of the parser state
     * represented by this itemset.
     * 
     * The follow set is calculated as follows:
     *  - If the item is of the form A->. (i.e. empty rule) or A->.BCD (i.e. dot is at the front)
     *    - The follow set of the item B->C.AB that exists in this item's item set
     *  - If the item is of the form A->BCD. (i.e. the dot is at the end, non-empty rule):
     *    - The union of the first sets for all items of the form E->FGA.HI or E->FGA. (i.e. the dot is after A) in this item's item set's generator
     *  - If the item is of the form A->B.CD (i.e. the dot is not at the end)
     *    - The first set of A->BC.D in this item's item set's generator
     *
     * @return Structures_Grammar_Symbol_Set The follow set
     */
    public function followSet($grammar)
    {
        if (is_null($this->itemSet)) throw new Text_Parser_Generator_Exception('followSet requires that the item be part of an item set');
        if ($this->index == 0 && !$this->itemSet->isItemInKernel($this)) {
            foreach ($this->itemSet->getItems() as $item) if ($item->getSymbol() == $this->getRule()->getLeftSymbol(0)) return $item->followSet($grammar);
            throw new Text_Parser_Generator_Exception('Panic! I should never have gotten to this point in the code!!! This itemset contains a starting empty item that is not a closure of another item in the set! Is the grammar\'s start symbol nullable by any chance???');
        }
        if ($this->index == $this->rule->rightCount()) {
            $result = new Structures_Grammar_Symbol_Set();
            foreach($this->itemSet->getFSA()->getItemsByPreviousSymbol($this->getRule()->getLeftSymbol(0)) as $item) {
                $result->union($item->firstSet($grammar));
            }
            return $result;
        }
        return $this->itemSet->getFSA()->getItemEqualTo($this->advance())->firstSet($grammar);
    }
    /* }}} */
    /* firstSet {{{ */
    /**
     * Calculates the first set (a set of terminal symbols) for this item in the context of its item set
     *
     * The first set is the set of symbols (terminal) that can occur in this item's current symbol position, in the context of the parser state
     * represented by this itemset.
     *
     * The first set for an item of the form A->BCD.EFG is calculated as the union of:
     *  - The union of the grammatical first sets of E,F,G in sequence until one of the symbols after the dot is non-nullable (i.e. if F is not 
     *    nullable, the grammatical first set of G is not included)
     *  - The set { $ } if all items after the dot are nullable and A is the gramatic's start symbol
     *  - The union of the the first sets for all items of the form H->IJA.KL in this item's itemset generator, if all items are nullable and A 
     *    is not the gramatic's start symbol
     *
     * @return Structures_Grammar_Symbol_Set The item's first set
     */
    public function firstSet($grammar)
    {
        if (!is_null($this->firstSet)) return $this->firstSet;
        $this->firstSet = new Structures_Grammar_Symbol_Set();

        $result = new Structures_Grammar_Symbol_Set();
        for ($i=$this->index; $i<$this->getRule()->rightCount(); $i++) {
            $result->union($grammar->symbolFirstSet($this->getRule()->getRightSymbol($i)));
            if (!$grammar->isSymbolNullable($this->getRule()->getRightSymbol($i))) break;
        }
        if ($i==$this->getRule()->rightCount()) {
            if ($grammar->getStartSymbol()->__equals($this->getRule()->getLeftSymbol(0))) {
                $result->addSymbol(Structures_Grammar_Symbol::create(''));
            } else {
                foreach($this->itemSet->getFSA()->getItemsByPreviousSymbol($this->getRule()->getLeftSymbol(0)) as $item) $result->union($item->firstSet($grammar));
            }
        }
        $this->firstSet = $result;
        return $result;
    }
    /* }}} */
}

?>
