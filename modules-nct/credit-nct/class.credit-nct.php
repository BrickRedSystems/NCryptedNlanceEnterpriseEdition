<?php
class Credit {
	function __construct($module = "", $id = 0, $token = "", $reffToken = "") {
		foreach ($GLOBALS as $key => $values) {
			$this -> $key = $values;
		}
		$this -> module = $module;
		$this -> id = $id;
		$this -> userData = getUserData();
		

	}

	public function getPageContent() {
		$results = $this -> db -> select('tbl_credit_plans', '*', array('isActive' => 'y')) -> results();
		
		$html = null;
		foreach ($results as $k => $v) {
			$replace = array(
				'%planName%' => $v['planName'],				
				'%credits%' => $v['credits'],
				'%price%' => CURRENCY_SYMBOL.$v['price'],
				'%id%' => $v['id'],
				'%href%' => SITE_BUY_CREDIT.$v['id'],
				'%buy_button%' => 'Buy'
			);
			$html .= get_view(DIR_TMPL . $this -> module . "/credit-row-nct.tpl.php",$replace);
		}

		return get_view(DIR_TMPL . $this -> module . "/" . $this -> module . ".tpl.php", array('%plans%' => $html));
	}

}
?>
