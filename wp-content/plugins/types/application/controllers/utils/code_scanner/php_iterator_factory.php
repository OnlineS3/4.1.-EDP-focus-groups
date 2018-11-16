<?php

namespace OTGS\Toolset\Types\Utils\CodeScanner;


class PhpIteratorFactory {

	public function create( $directory_iterator ) {
		$directory_iterator = new \RecursiveDirectoryIterator( $directory_iterator );
		$directory_iterator->setFlags( \FilesystemIterator::SKIP_DOTS );

		$dot_iterator = new RecursiveDotFilterIterator( $directory_iterator );

		$recursive_iterator = new \RecursiveIteratorIterator( $dot_iterator );
		$php_files_iterator = new \RegexIterator(
			$recursive_iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH
		);

		return $php_files_iterator;
	}

}


class RecursiveDotFilterIterator extends \RecursiveFilterIterator {

	public function accept()
	{
		return '.' !== substr($this->current()->getFilename(), 0, 1);
	}

}