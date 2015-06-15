<?php
namespace modules\apc\models;


use yii\base\Model;
use yii\db\QueryInterface;
use yii\base\InvalidCallException;


class APCu extends Model
{
	public $apcu_version, $php_version, $memory_type, $start_time, $uptime, $variables_size,
	$file_upload_progress,
	$hits, $misses, $inserts, $entries,
	$memory_segment_size, $memory_segments, $memory_total_size, $memory_available, $memory_used;
	
	
	public function attributeLabels()
	{
		return [
			'apcu_version' => 'APCu Version',
			'php_version' => 'PHP Version',
			'memory_type' => 'Memory Type',
			'start_time' => 'Start Time',
			'uptime' => 'Uptime',
			'file_upload_progress' => 'File Upload Support',
			'hits' => 'Hits',
			'misses' => 'Misses',
			'inserts' => 'Inserts',
			'entries' => 'Cached Variables',
			'memory_total_size' => 'Total Memory Size',	
		];
	}
	
	protected function getDuration($datetime) 
	{
		return (new \DateTime())->diff($datetime)->format('%Y y %M m %D d %H h %I m %S s');
	}
	
	//cache_list
	//deleted_list
	public function __construct()
	{		
		$apcu_cache = apcu_cache_info();
		$apcu_sma = apcu_sma_info();
		//var_dump($apcu_cache['cache_list']);
		
		$this->apcu_version = phpversion('apcu');
		$this->php_version = phpversion();
		$this->memory_type = $apcu_cache['memory_type'];
		$this->variables_size = $apcu_cache['mem_size'];
		$this->start_time = date('Y-m-d H:i:s', $apcu_cache['start_time']);
		$this->uptime = $this->getDuration((new \DateTime())->setTimestamp($apcu_cache['start_time']));
		$this->file_upload_progress = $apcu_cache['file_upload_progress'] == 1 ? 'Yes' : 'No';
		$this->hits = $apcu_cache['num_hits'];
		$this->misses = $apcu_cache['num_misses'];
		$this->inserts = $apcu_cache['num_inserts'];
		$this->entries = $apcu_cache['num_entries'];
		$this->memory_available = $apcu_sma['avail_mem'];
		$this->memory_segments = $apcu_sma['num_seg'];
		$this->memory_segment_size = $apcu_sma['seg_size'];
		$this->memory_total_size = $this->memory_segments * $this->memory_segment_size;
		$this->memory_used = $this->memory_total_size - $this->memory_available;
	}
	
	//for google charts
	static public function getVariables($cols = [])
	{
		return apcu_cache_info()['cache_list'];
		// 'info' => string 'mylib963454f612a8b5fb4a63ba1e97f028a1' (length=37)
		// 'ttl' => int 0
		// 'num_hits' => float 10
		// 'modification_time' => int 1423844226
		// 'creation_time' => int 1423844226
		// 'deletion_time' => int 0
		// 'access_time' => int 1423844515
		// 'ref_count' => int 0
		// 'mem_size' => int 3248
		
		//$cols = ['info','ttl'];
			/*$list = [];
		$list[] = $cols;
	
		foreach (apcu_cache_info()['cache_list'] as $i) {
			$ar = [];
			foreach ($cols as $c) {
				$ar[] = $i[$c];
			}
			$list[] = $ar;
		}
		
		return $list;*/
	}
	
	/**
	 * pretty printer for byte values in format " 1.0 Mbytes "
	 * @param int $s bytes
	 * @return string
	 */
	static public function bsize($s)
	{
		foreach (array('','K','M','G') as $i => $k) {
			if ($s < 1024) break;
			$s/=1024;
		}
		return sprintf("%0.1f %sbyte".($s <= 1 ? '':'s'), $s, $k);
	}
}

