<?php

class Collection extends ArrayObject {
  private $data;
  public $count;
    
  function __construct() {
    $this->data = new ArrayObject();
    $this->count = 0;
  }
 
  function addObject($_id, $_object) {
    $_thisItem = new CollectionObj($_id, $_object);
    $this->data->offSetSet($_id, $_thisItem);
    $this->count++;
  }
  function deleteObject($_id) {
    $this->data->offsetUnset($_id);
    $this->count--;
  }
  function getObject($_id) {
		if ($_id >= $this->count) {
			return false;
		}
		else {
      $_thisObject = $this->data->offSetGet($_id);
      return $_thisObject->getObject();
		}
  }
  function printCollection() {
    print_r($this->data);
  }
}

class CollectionObj {
  private $id;
  private $object;
  
  function __construct($_id, $_object) {
    $this->id = $_id;
    $this->object = $_object;
  }
  function getObject() {
    return $this->object;
  }
  function printObject() {
    print_r($this);
  }
}