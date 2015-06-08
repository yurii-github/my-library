<?php
namespace app\components;
use yii\helpers\FileHelper;


class AssetManager extends \yii\web\AssetManager
{ 
	/**
	 * (non-PHPdoc)
	 * EXTRA: 
	 * @see \yii\web\AssetManager::publishDirectory()
	 * 
	 *  [const-dir] contain directory name or empty if copy current asset directly to base assets' dir 
	 */
	public function publishDirectory($src, $options)
	{
		// default behavior with hashed dir
		if (!isset($options['const-dir'])) {
			return parent::publishDirectory($src, $options);
		}

		//
		// my custom : don't generate random dir, instead, use custom if set
		//
		$dstDir = $this->basePath . (!empty($options['const-dir']) ? '/'. $options['const-dir'] : '');
		//dont copy if already was copied
		// TODO: add datetime checks
		if (file_exists($dstDir)) {
			return [$dstDir, $this->baseUrl ];
		}
		// A. copy only subdirs if set
		if (!empty($options['sub-dirs']) && is_array($options['sub-dirs'])) {
			foreach ($options['sub-dirs'] as $subdir) {
				if (is_dir($src.'/'.$subdir)) {
					FileHelper::copyDirectory($src.'/'.$subdir, $dstDir.'/'.$subdir, [
						'dirMode' => $this->dirMode,
						'fileMode' => $this->fileMode,
						'beforeCopy' => @$options['beforeCopy'],
						'afterCopy' => @$options['afterCopy'],
						'forceCopy' => @$options['forceCopy']
					]);
				} //TODO: else write error log
			}
		} else { //copy whole dir
			FileHelper::copyDirectory($src, $dstDir, [
				'dirMode' => $this->dirMode,
				'fileMode' => $this->fileMode,
				'beforeCopy' => @$options['beforeCopy'],
				'afterCopy' => @$options['afterCopy'],
				'forceCopy' => @$options['forceCopy']
			]);
		}
		
		return [$dstDir, $this->baseUrl ];
	}
	
	
	
	protected function publishFile($src)
	{
		throw new \Exception('Not implemented!');
		//TODO: check custom behavior
		return parent::publishFile($src);
	}
}