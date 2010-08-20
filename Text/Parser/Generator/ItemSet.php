<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
require_once('Text/Parser/Generator/Item.php');
/**
 * A Text_Parser_Generator_ItemSet is a set of Text_Parser_Generator_Item instances
 */
class Text_Parser_Generator_ItemSet
{
    /** The FSA state we are part of */
    protected $state = null;
    /** The items in this set */
    protected $items = array();
    /** How many items compose the item set kernel (non-kernel items are closure items) */
    protected $kernelWatermark = 0;
    /* getState {{{ */
    public function getState()
    {
        return $this->state;
    }
    /* }}} */
    /* getFSA {{{ */
    public function getFSA()
    {
        return $this->getState()->getFSA();
    }
    /* }}} */
    /* setState {{{ */
    public function setState($to)
    {
        $this->state = $to;
    }
    /* }}} */
    /* setKernelWatermark {{{ */
    /**
     * Set the kernel watermark
     *
     * @param int New watermark value. Defaults to the current item count
     */
    public function setKernelWatermark($value = null)
    {
        if (is_null($value)) $value = count($this->items) - 1;
        $this->kernelWatermark = $value;
    }
    /* }}} */
    /* isItemInKernel {{{ */
    public function isItemInKernel($i)
    {
        if ($i instanceof Text_Parser_Generator_Item) $i = $this->getItemIndex($i);
        return $i <= $this->kernelWatermark;
    }
    /* }}} */
    /* count {{{ */
    /** 
     * Return the item set item count
     *
     * @return int Item count
     */
    public function count()
    {
        return count($this->items);
    }
    /* }}} */
    /* getItem {{{ */
    /** 
     * Item getter
     *
     * @param int Item index
     * @return Text_Parser_Generator_Item Item at the provided index, or null if non-existant
     */
    public function getItem($index)
    {
        if ($index >= $this->count()) return null;
        return $this->items[$index];
    }
    /* }}} */
    /* addItem {{{ */
    /**
     * Item adder
     *
     * @param Text_Parser_Generator_Item Item to add
     */
    public function addItem($item)
    {
        foreach ($this->items as $current) if ($current->__equals($item)) return;
        $this->items[] = $item;
        $item->setItemSet($this);
    }
    /* }}} */
    /* getItemIndex {{{ */
    /**
     * Item search function
     *
     * @param Text_Parser_Generator_Item Item to search for
     * @return int Item index, or null if not found
     */
    public function getItemIndex($item)
    {
        foreach ($this->items as $index => $current) if ($current->__equals($item)) return $index;
        return null;
    }
    /* }}} */
    /* getItems {{{ */
    /**
     * Item getter
     *
     * @return array Array of Text_Parser_Generator_Item instances. The items in this set
     */
    public function getItems()
    {
        return $this->items;
    }
    /* }}} */
    /* close {{{ */
    /**
     * Perform item set closure
     *
     * Closing an item set is an operation that guarantees, for a given grammar, and a given item set kernel 
     * (contained in this set) that this item set completely describes one parser state. Closing is 
     * performed like this:
     *  - Observe items in the set whose current symbol (the one after the dot) is non-terminal
     *  - For the non-terminal symbols, fetch from the grammar rules whose production left side is the non-terminal 
     *  - Add these rules
     *  - Repeat the procedure until no more rules are added
     *
     * @param Structures_Grammar The grammar to use during closure
     */
    public function close($grammar)
    {
        $this->setKernelWatermark();
        for ($i=0; $i<count($this->items); $i++) {
            if (!is_null($this->items[$i]->getSymbol()) && $this->items[$i]->getSymbol()->isNonTerminal()) {
                foreach ($grammar->getRulesByLeftSymbol($this->items[$i]->getSymbol()) as $rule) $this->addItem(new Text_Parser_Generator_Item($rule, 0));
            }
        }
    }
    /* }}} */
    /* getItemEqualTo {{{ */
    public function getItemEqualTo($right)
    {
        foreach($this->items as $i => $item) if ($item->__equals($right)) return $this->items[$i];
        return null;
    }
    /* }}} */
    /* getItemsByPreviousSymbol {{{ */
    public function getItemsByPreviousSymbol($symbol)
    {
        $result = array();
        foreach($this->items as $i => $item) if (!is_null($item->getPreviousSymbol()) && $item->getPreviousSymbol()->__equals($symbol)) $result[] = $this->items[$i];
        return $result;
    }
    /* }}} */
    /* Constructor {{{ */
    public function __construct($item = null)
    {
        if (!is_null($item)) $this->addItem($item);
    }
    /* }}} */
    /* __equals {{{ */
    public function __equals($other)
    {
        if (!($other instanceof Text_Parser_Generator_ItemSet)) return false;

        $otherItems = $other->getItems();
        if (count($otherItems) != count($this->items)) return false;

        foreach ($this->items as $left) {
            $found = false;
            foreach ($otherItems as $right) if ($left->__equals($right)) {
                $found = true;
                break;
            }
            if (!$found) return false;
        }
        return true;
    }
    /* }}} */
    /* __toString {{{ */
    public function __toString()
    {
        $result = '';
        foreach ($this->items as $i => $item) {
            $result .= ($i <= $this->kernelWatermark ? ' ' : '+') . $item . "\n";
        }
        return $result;
    }
    /* }}} */

    /* isReduceable {{{ */
    public function isReduceable($grammar)
    {
        foreach ($this->items as $current) if ($current->isReduceable($grammar)) return true;
        return false;
    }
    /* }}} */
    /* getReduceItem {{{ */
    public function getReduceItem($grammar)
    {
        if (!$this->isReduceable($grammar)) return false;
        $result = null;
        foreach ($this->items as $current) if ($current->isReduceable($grammar)) {
            if (!is_null($result)) throw new Exception('Reduce-reduce conflict'); // TODO Proper exception
            $result = $current;
        }
        return $result;
    }
    /* }}} */
}
?>
