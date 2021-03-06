<?php
class Bluetooth_model extends Model {

	function __construct($serial='')
	{
		parent::__construct('id', 'bluetooth'); //primary key, tablename
		$this->rs['id'] = '';
		$this->rs['serial_number'] = $serial; $this->rt['serial_number'] = 'VARCHAR(255) UNIQUE';
		//-1 means unknown
		$this->rs['bluetooth_status'] = '-1';
		$this->rs['keyboard_battery'] = '-1';
		$this->rs['mouse_battery'] = '-1';
		$this->rs['trackpad_battery'] = '-1';

		// Schema version, increment when creating a db migration
		$this->schema_version = 1;

		// Create table if it does not exist
		$this->create_table();

		if ($serial)
			$this->retrieve_record($serial);
		
		$this->serial = $serial;

	}
	
	/**
	 * Get devices with low battery
	 *
	 * Select devices with battery level below 15%
	 *
	 **/
	public function get_low()
	{
		$out = array();
		$sql = "SELECT bluetooth.serial_number, machine.computer_name,
							bluetooth.keyboard_battery, bluetooth.mouse_battery,
							bluetooth.trackpad_battery
						FROM bluetooth
						LEFT JOIN reportdata USING (serial_number)
						LEFT JOIN machine USING (serial_number)
						WHERE ((bluetooth.keyboard_battery BETWEEN 0 AND 14)
							OR (bluetooth.mouse_battery BETWEEN 0 AND 14)
							OR (bluetooth.trackpad_battery BETWEEN 0 AND 14))
						".get_machine_group_filter('AND');
		foreach($this->query($sql) as $obj)
		{
			$out[] = $obj;
		}
		
		return $out;
	}

	// ------------------------------------------------------------------------

	/**
	 * Process data sent by postflight
	 *
	 * @param string data
	 *
	 **/
	function process($data)
	{
		// Translate network strings to db fields
        $translate = array(
        	'Status = ' => 'bluetooth_status',
        	'Keyboard = ' => 'keyboard_battery',
        	'Mouse = ' => 'mouse_battery',
        	'Trackpad = ' => 'trackpad_battery');
			
		//clear any previous data we had
		foreach($translate as $search => $field) {
			$this->$field = -1;
		}
		// Parse data
		foreach(explode("\n", $data) as $line) {
		    // Translate standard entries
			foreach($translate as $search => $field) {

			    if(strpos($line, $search) === 0) {

				    $value = trim(substr($line, strlen($search)));
					
					// Legacy client module
					if($value == 'Disconnected')
					{
						$value = -1;
					}
					elseif(preg_match('/(\d+)% battery life remaining/', $value, $matches))
					{
						$value = $matches[1];
					}
					elseif(preg_match('/Bluetooth is (.+)/', $value, $matches))
					{
						$value = $matches[1] == 'on' ? 1 : 0;
					}

				    $this->$field = intval($value);
				    break;
			    }
			}

		} //end foreach explode lines
		$this->save();
	}
}
