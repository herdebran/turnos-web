<?php 
/**
  * Benchmarking helping class 
  *
  * With this class it is possible to messure different checkpoints with 
  * a precision of milliseconds
  *
  * @package  poroto
  * @version  1.2
  * @access   public
  * @copyright 2015-2017 7dedos
  * @author Augusto Wloch <agosto@7dedos.com>
  */
if ( ! defined('POROTO')) exit('No direct script access allowed');

class Benchmark {
	private $Benchmarks;

	public function __construct() {
		$this->Benchmarks[] = array("poroto core constructor init",microtime());
	}

	public function addBenchmark($name) {
		$this->Benchmarks[] = array($name,microtime());
	}

	public function getBenchmarksArray() {
		$out = array();
		for ($i=1; $i<count($this->Benchmarks); $i++) {
			$dif = $this->getMicrotimeDiffInSeconds($this->Benchmarks[$i][1], $this->Benchmarks[$i-1][1]);
			$out[]=array("from"=>$this->Benchmarks[$i-1][0], "to"=>$this->Benchmarks[$i][0], "time"=>$dif);
		}
		return($out);
	}
	public function getTotalTime() {
		return ($this->getMicrotimeDiffInSeconds($this->Benchmarks[count($this->Benchmarks)-1][1], $this->Benchmarks[0][1]));
	}
	private function getMicrotimeDiffInSeconds($end, $start, $decimalPos = 4) {
		list($sm, $ss) = explode(' ', $start);
		list($em, $es) = explode(' ', $end);
		return ( number_format(($em + $es) - ($sm + $ss), $decimalPos) ); 
	}


}