<?php
class soft_protect
{
	private $elements = array();
	public function add($elements, $regex)
	{
		array_push($this->elements, array($elements, $regex));
		return $this;
	}

	public function write()
	{
		$lines = array();
		array_push($lines, "<script type='text/javascript'>");
		foreach($this->elements as $element)
		{
			$elems = array();
			foreach($element[0] as $protectkey)
			{
				array_push($elems, "'".addslashes($protectkey)."'");
			}
			array_push($lines, "soft_protect([".implode(",", $elems)."], ".$element[1].");");
		}
		array_push($lines, "</script>");
		return implode("\r\n", $lines);
	}
}
?>
