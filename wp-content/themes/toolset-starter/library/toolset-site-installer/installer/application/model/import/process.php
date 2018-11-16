<?php

class TT_Import_Process {

	private $field_todos = 'toolset-based-themes-import-process-todos';
	private $field_todos_done = 'toolset-based-themes-import-process-todos-done';

	private function increaseCount( $field ) {
		$count = get_option( $field, 0 );
		update_option( $field, $count + 1 );
	}

	public function increaseTodo() {
		$this->increaseCount( $this->field_todos );
	}

	public function increaseTodoDone() {
		$this->increaseCount( $this->field_todos_done );
	}

	public function getProcessInPercentage() {
		$todos = get_option( $this->field_todos );
		$todos_done = get_option( $this->field_todos_done );

		if( ! $todos ) {
			return false;
		}

		return round( 100 / intval( $todos ) * intval( $todos_done ) );
	}

	public function clearProcess() {
		delete_option( $this->field_todos );
		delete_option( $this->field_todos_done );
	}
}