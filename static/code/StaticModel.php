<?php

class StaticModel extends ViewableData {

	/**
	 * $fields holds a (possibly nested) structure of fields. These can either be bare names, in which case they are dynamically typed, or
	 * key => value pairs, in which case the key is the name of the field, and value is:
	 *   - A DBField type
	 *   - A StaticModel derivative
	 *   - An array of nested fields in the same format
	 *
	 * Fields need not be declared here - they can be set and got dyamically. However, currently dynamic fields have no type - when gotten, they are always returned
	 * as strings, base StaticModel instances or ArrayList sequences, depending on the set content.
	 */
	static public $fields = array();

	/**
	 * $fields is a per-class-level static. For each class we need to build a complete merge of the stat from the entire class hierarchy, then convert any items
	 * with numeric keys into value=>value pairs before it can be used. This caches those values.
	 */
	static protected $built_fields = array();

	/**
	 * Although $fields specifies a specific type for each field, that's actually the eldest parent class of a class hierarchy. We might actually
	 * have some child class instead. We detect that here.
	 *
	 * To determine a candidiate type
	 * - Check to make sure every field specified in a particular classes fields is in $data
	 * - If there is a static method "type_check", check that returns true
	 *
	 * For each candidate type, check it's depth. Starting at the deepest level:
	 * - If there is only one candidate at this level, use that
	 * - If there is more than one candidate at this level, reduce the level
	 *
	 * @static
	 * @param $klass
	 * @param $data
	 * @return string
	 */
	static function refine_type($klass, $data) {
		$candidates = array();

		foreach(ClassInfo::subclassesFor($klass) as $child) {
			$fields = singleton($child)->fields();
			$type_check = array($child, 'type_check');

			if (is_callable($type_check)) {
				if (!call_user_func($type_check, $data)) continue;
			}
			else {
				foreach ($fields as $field) {
					if (!isset($data->$field)) continue 2;
				}
			}

			$level = count(ClassInfo::ancestry($child));
			if (!isset($candidates[$level])) $candidates[$level] = array();

			$candidates[$level][] = $child;
		}

		while ($candidates) {
			$level = array_pop($candidates);
			if (count($level) == 1) return $level[0];
		}

		return $klass;
	}

	public $parent;
	public $customFields;

	public $store;

	/**
	 * Create a new instance of a StaticModel. Both developer-declared subclasses and direct instances of StaticModel are constructed - the later
	 * for use with anonymous nested arrays in field declarations (and other situations where we don't have a more specific type available).
	 *
	 * @param $data array | stdClass | StaticModel - the data to load into this object on construction, either as an array, a stdClass or a StaticModel instance. The data
	 * is shallow-copied into this instance (how shallow depends on input type, and the types of the values).
	 * @param $parent StaticModel - when this object reflects a sub-portion of a document, parent points the containing portion of the document (internal)
	 * @param $store stdClass - the stdClass to use as the data store (internal)
	 * @param $fields array - a $fields array to use instead of the static variable when creating an base level StaticModel for anonymous nested array use (internal)
	 */
	function __construct($data = null, $parent = null, $store = null, $fields = null) {
		parent::__construct();

		// Save any passed in parent & connection values;
		$this->parent = $parent;

		// Create the data store
		$this->store = $store ? $store : new stdClass();

		// If a custom $fields variable passed in, we use that in _instead of_ (not merged with) the static self::$fields.
		if ($fields) $this->customFields = self::canonicalize_fields($fields);

		// When initial data is passed in, load it (this has to happen last, so everything else is alread set up)
		if ($data) $this->update($data);
	}

	/**
	 * Cast this instance to a different StaticModel subclass.
	 * @param $type string | array - the name of the class to cast to, or an array of fields to use with StaticModel to cast this as an anonymous block
	 * @param $determineSubclass boolean - when true, the type_field will be used to find the exact subclass of $class to cast to. When false, $class itself will always be used
	 * @return StaticModel - this object, cast to $class or a subclass of $class
	 */
	function castTo($type, $determineSubclass = true) {
		if (is_array($type)) {
			return new StaticModel(null, $this->parent, $this->store, $type);
		}
		else {
			if ($determineSubclass) $type = self::refine_type($type, $this->store);
			return new $type(null, $this->parent, $this->store);
		}
	}

	/**
	 * Update the fields of this instance with the values passed in as $data
	 * @param $data array | stdClass | StaticModel - the data to load into this object, either as an array, a stdClass or a StaticModel instance. The data
	 * is shallow-copied into this instance (how shallow depends on input type, and the types of the values).
	 * @return null
	 */
	function update($data) {
		if (!$data) {
			// NOP
		}
		else if (is_array($data)) {
			foreach ($data as $k => $v) {
				$this->setField($k, $v);
			}
		}
		else if ($data instanceof StaticModel) {
			$this->update($data->store);
		}
		else {
			$dataMeta = new ReflectionObject($data);
			foreach($dataMeta->getProperties(ReflectionProperty::IS_PUBLIC) as $propMeta) {
				$prop = $propMeta->getName();
				$this->setField($prop, $data->$prop);
			}
		}

		return $this;
	}

	/**
	 * The $fields static variable allows values of the form 'field_name' => 'field_type', and 'field_name' (which is actually some_number => 'field_name').
	 * This function takes a mixed array in that form and canonicalizes it so all values are of the 'field_name' => 'field_type' form.
	 * @param $fields
	 * @return unknown_type
	 */
	static function canonicalize_fields($fields) {
		$retval = array();

		foreach ($fields as $k => $v) {
			if (is_numeric($k)) $retval[$v] = null;
			else $retval[$k] = $v;
		}

		return $retval;
	}

	/**
	 * Returns the fields for this instance. This is post merge + key conversion, so will be a complete list of all the fields of this class and all parent classes,
	 * canonicalized (see canonicalize_fields)
	 * @return unknown_type
	 */
	function fields() {
		// If this instance has custom fields, use them
		if ($this->customFields) return $this->customFields;

		$class = $this->class;

		// Otherwise, if we haven't yet, merge and canonicalize
		if (!isset(self::$built_fields[$class])) {
			$fields = array();

			while ($class != 'StaticModel') {
				$fields = array_merge($fields, (array)Object::uninherited_static($class, 'fields'));
				$class  = get_parent_class($class);
			}

			self::$built_fields[$class] = self::canonicalize_fields($fields);
		}

		return self::$built_fields[$class];
	}

	public function castingHelper($field) {
		$fields = $this->fields();

		$spec = @$fields[$field];
		if (!$spec) $spec = 'Text';

		return $spec;
	}

	function hasField($field) {
		$fields = $this->fields();
		return array_key_exists($field, $fields) || isset($this->store->$field);
	}

	function getField($field) {
		$fields = $this->fields();

		$spec = @$fields[$field];
		if (!$spec) $spec = 'Text';

		$val = null;
		if (isset($this->store->$field)) $val = $this->store->$field;

		if (is_string($spec) && $spec[0] == '[') {
			if (!$val) $this->store->$field = array();
			return new StaticModel_Sequence($this, $this->store->$field, substr($spec, 1, -1));
		}

		if (is_array($spec)) {
			if (!$val) $this->store->$field = new stdClass();
			return new StaticModel(null, $this, $this->store->$field, $spec);
		}

		if (class_exists($spec) && (is_subclass_of($spec,'StaticModel') || is_object($val))) {
			$type = is_subclass_of($spec,'StaticModel') ? $spec : 'StaticModel';

			// If there is something stored for this field, see if we can find a more acurate type
			if ($val) $type = self::refine_type($type, $val);
			// Otherwise, make sure there is something to store values in
			else $this->store->$field = new stdClass();

			return new $type(null, $this, $this->store->$field);
		}

		return ($this->store && property_exists($this->store, $field)) ? $this->store->$field : null;
	}

	/**
	 * Set a field to a particular value.
	 *
	 * In general, we don't cast the passed value, with these exceptions:
	 *
	 *  If the passed value is an associative array (it has keys that are non-numeric), we convert to a structure like CouchDB returns by a json_encode / decode pair
	 *  If the passed value is numeric, we make sure it's a numeric type, not a string
	 *  If the field we are storing into is array-speced (that is '[Type]'), we make sure we're storing an array
	 *
	 * @see sapphire/core/ViewableData#setField($field, $value)
	 */
	function setField($field, $val) {
		$fields = $this->fields();

		$spec = @$fields[$field];

		if (is_array($val)) {
			foreach ($val as $k => $v) {
				if (!is_numeric($k)) {
					$val = json_decode(json_encode($val));
					break;
				}
			}
		}
		else if ($val instanceof StaticModel) {
			$val->parent = $this;
			$val = $val->store;
		}
		else if (is_numeric($val)) {
			$val = ((int)$val == (float)$val) ? (int)$val : (float)$val;
		}

		if (is_string($spec) && $spec[0] == '[') {
			if (!is_array($val)) $val = array($val);
		}

		$this->store->$field = $val;

		return $this;
	}

	function toJson() {
		return json_encode($this->store);
	}

	function fromJson($data) {
		$this->update(json_decode($data));
	}
}

class StaticModel_Sequence extends ViewableData implements IteratorAggregate, Countable, ArrayAccess {

	public $store;
	public $objectClass;

	function __construct($parent, &$store, $objectClass) {
		$this->parent = $parent;
		$this->store =& $store;
		$this->objectClass = $objectClass;
		$this->idx = 0;
	}

	// DataObjectSet equivalent functions

	public function Count() {
		return sizeof($this->store);
	}

	function push($obj) {
		$class = $this->objectClass;

		if (!($obj instanceof $class)) $obj = new $class($obj);
		array_push($this->store, $obj->store);
	}

	// ArrayAccess

	function prepareItem($val, $idx) {
		$type = StaticModel::refine_type($this->objectClass, $val);

		$obj = new $type(null, $this->parent, $val);
		$obj->iteratorProperties($idx, sizeof($this->store));
		return $obj;
	}

	function offsetExists($idx) {
		return isset($this->store[$idx]);
	}

	function offsetGet($idx) {
		if (!isset($this->store[$idx])) return null;
		return $this->prepareItem($this->store[$idx], $idx);
	}

	function offsetSet($idx, $val) {
		if (is_array($val)) {
			$val = json_decode(json_encode($val));
		}
		else if ($val instanceof StaticModel) {
			$val = $val->store;
		}

		return $this->store[$idx] = $val;
	}

	function offsetUnset($idx) {
		unset($this->store[$idx]);
	}

	// IteratorAggregate

	public function getIterator() {
		return new StaticModel_Sequence_Iterator($this);
	}
}

class StaticModel_Sequence_Iterator implements Iterator {
	function __construct($seq) {
		$this->seq = $seq;
	}

	// Iterator

	function current() {
		return $this->seq->prepareItem(current($this->seq->store), key($this->seq->store));
	}

	function key() {
		return key($this->seq->store);
	}

	function next() {
		next($this->seq->store);
		return $this->seq->prepareItem(current($this->seq->store), key($this->seq->store));
	}

	function rewind() {
		reset($this->seq->store);
		return $this->seq->prepareItem(current($this->seq->store), key($this->seq->store));
	}

	function valid() {
		return current($this->seq->store) !== false;
	}
}
