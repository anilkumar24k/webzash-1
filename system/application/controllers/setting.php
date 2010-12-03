<?php
class Setting extends Controller {

	function Setting()
	{
		parent::Controller();
		$this->load->model('Setting_model');
		return;
	}

	function index()
	{
		$this->template->set('page_title', 'Settings');
		$this->template->load('template', 'setting/index');
		return;
	}

	function account()
	{
		$this->template->set('page_title', 'Account Settings');
		$account_data = $this->Setting_model->get_current();


		$default_start = '01/04/';
		$default_end = '31/03/';
		if (date('n') > 3)
		{
			$default_start .= date('Y');
			$default_end .= date('Y') + 1;
		} else {
			$default_start .= date('Y') - 1;
			$default_end .= date('Y');
		}

		/* Form fields */
		$data['account_name'] = array(
			'name' => 'account_name',
			'id' => 'account_name',
			'maxlength' => '100',
			'size' => '40',
			'value' => ($account_data) ? echo_value($account_data->name) : '',
		);
		$data['account_address'] = array(
			'name' => 'account_address',
			'id' => 'account_address',
			'rows' => '4',
			'cols' => '47',
			'value' => ($account_data) ? echo_value($account_data->address) : '',
		);
		$data['account_email'] = array(
			'name' => 'account_email',
			'id' => 'account_email',
			'maxlength' => '100',
			'size' => '40',
			'value' => ($account_data) ? echo_value($account_data->email) : '',
		);
		$data['assy_start'] = array(
			'name' => 'assy_start',
			'id' => 'assy_start',
			'maxlength' => '11',
			'size' => '11',
			'value' => ($account_data) ? date_mysql_to_php(echo_value($account_data->ay_start)) : $default_start,
		);
		$data['assy_end'] = array(
			'name' => 'assy_end',
			'id' => 'assy_end',
			'maxlength' => '11',
			'size' => '11',
			'value' => ($account_data) ? date_mysql_to_php(echo_value($account_data->ay_end)) : $default_end,
		);
		$data['account_currency'] = array(
			'name' => 'account_currency',
			'id' => 'account_currency',
			'maxlength' => '10',
			'size' => '10',
			'value' => ($account_data) ? echo_value($account_data->currency_symbol) : '',
		);
		$data['account_date'] = array(
			'name' => 'account_date',
			'id' => 'account_date',
			'maxlength' => '20',
			'size' => '10',
			'value' => ($account_data) ? echo_value($account_data->date_format) : '',
		);
		$data['account_timezone'] = ($account_data) ? echo_value($account_data->timezone) : 'UTC';

		/* Form validations */
		$this->form_validation->set_rules('account_name', 'Account Name', 'trim|required|min_length[2]|max_length[100]');
		$this->form_validation->set_rules('account_address', 'Account Address', 'trim|max_length[255]');
		$this->form_validation->set_rules('account_email', 'Account Email', 'trim|valid_email');
		$this->form_validation->set_rules('assy_start', 'Assessment Year Start', 'trim|required|is_date');
		$this->form_validation->set_rules('assy_end', 'Assessment Year End', 'trim|required|is_date');
		$this->form_validation->set_rules('account_currency', 'Currency', 'trim|max_length[10]');
		$this->form_validation->set_rules('account_date', 'Date', 'trim|max_length[30]');
		$this->form_validation->set_rules('account_timezone', 'Timezone', 'trim|max_length[6]');

		/* Repopulating form */
		if ($_POST)
		{
			$data['account_name']['value'] = $this->input->post('account_name', TRUE);
			$data['account_address']['value'] = $this->input->post('account_address', TRUE);
			$data['account_email']['value'] = $this->input->post('account_email', TRUE);
			$data['assy_start']['value'] = $this->input->post('assy_start', TRUE);
			$data['assy_end']['value'] = $this->input->post('assy_end', TRUE);
			$data['account_currency']['value'] = $this->input->post('account_currency', TRUE);
			$data['account_date']['value'] = $this->input->post('account_date', TRUE);
			$data['account_timezone'] = $this->input->post('account_timezone', TRUE);
		}

		/* Validating form */
		if ($this->form_validation->run() == FALSE)
		{
			$this->messages->add(validation_errors(), 'error');
			$this->template->load('template', 'setting/account', $data);
			return;
		}
		else
		{
			$data_account_name = $this->input->post('account_name', TRUE);
			$data_account_address = $this->input->post('account_address', TRUE);
			$data_account_email = $this->input->post('account_email', TRUE);
			$data_assy_start = date_php_to_mysql($this->input->post('assy_start', TRUE));
			$data_assy_end = date_php_to_mysql($this->input->post('assy_end', TRUE));
			$data_account_currency = $this->input->post('account_currency', TRUE);
			$data_account_date = $this->input->post('account_date', TRUE);
			$data_account_timezone = $this->input->post('timezones', TRUE);

			/* Verify if current settings exist. If not add new settings */
			$current = $this->Setting_model->get_current();
			if ( ! $current)
			{
				$this->messages->add('Current settings were not valid', 'message');
				if ( ! $this->db->query("INSERT INTO settings (id, name, address, email, ay_start, ay_end, currency_symbol, date_format, timezone) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)", array($data_account_name, $data_account_address, $data_account_email, $data_assy_start, $data_assy_end, $data_account_currency, $data_account_date, $data_account_timezone)))
				{
					$this->messages->add('Error adding new settings', 'error');
					$this->template->load('template', 'setting/account', $data);
					return;
				}
			}

			/* Update settings */
			if ( ! $this->db->query("UPDATE settings SET name = ?, address = ?, email = ?, ay_start = ?, ay_end = ?, currency_symbol = ?, date_format = ?, timezone = ? WHERE id = 1", array($data_account_name, $data_account_address, $data_account_email, $data_assy_start, $data_assy_end, $data_account_currency, $data_account_date, $data_account_timezone)))
			{
				$this->messages->add('Error updating settings', 'error');
				$this->template->load('template', 'setting/account', $data);
				return;
			}

			/* Success */
			$this->messages->add('Settings updated successfully', 'success');
			redirect('setting');
			return;
		}
		return;
	}

	function cf()
	{
		$this->load->helper('file');
		$this->template->set('page_title', 'Carry forward account');

		/* Form fields */
		$default_start_str = $this->config->item('account_ay_end');
		$default_start_year = date('Y', strtotime($default_start_str));
		$default_start = date('d/m/Y', strtotime($default_start_str));

		$default_end_year = $default_start_year + 1;
		$default_end = '31/03/' . $default_end_year;

		/* Form fields */
		$data['account_label'] = array(
			'name' => 'account_label',
			'id' => 'account_label',
			'maxlength' => '30',
			'size' => '30',
			'value' => '',
		);
		$data['account_name'] = array(
			'name' => 'account_name',
			'id' => 'account_name',
			'maxlength' => '100',
			'size' => '40',
			'value' => '',
		);
		$data['assy_start'] = array(
			'name' => 'assy_start',
			'id' => 'assy_start',
			'maxlength' => '11',
			'size' => '11',
			'value' => $default_start,
		);
		$data['assy_end'] = array(
			'name' => 'assy_end',
			'id' => 'assy_end',
			'maxlength' => '11',
			'size' => '11',
			'value' => $default_end,
		);

		$data['database_name'] = array(
			'name' => 'database_name',
			'id' => 'database_name',
			'maxlength' => '100',
			'size' => '40',
			'value' => '',
		);

		$data['database_username'] = array(
			'name' => 'database_username',
			'id' => 'database_username',
			'maxlength' => '100',
			'size' => '40',
			'value' => '',
		);

		$data['database_password'] = array(
			'name' => 'database_password',
			'id' => 'database_password',
			'maxlength' => '100',
			'size' => '40',
			'value' => '',
		);

		$data['database_host'] = array(
			'name' => 'database_host',
			'id' => 'database_host',
			'maxlength' => '100',
			'size' => '40',
			'value' => 'localhost',
		);

		$data['database_port'] = array(
			'name' => 'database_port',
			'id' => 'database_port',
			'maxlength' => '100',
			'size' => '40',
			'value' => '3306',
		);
		$data['create_database'] = FALSE;
		$data['account_name']['value'] = $this->config->item('account_name');

		/* Form validations */
		$this->form_validation->set_rules('account_label', 'C/F Label', 'trim|required|min_length[2]|max_length[30]|alpha_numeric');
		$this->form_validation->set_rules('account_name', 'C/F Account Name', 'trim|required|min_length[2]|max_length[100]');
		$this->form_validation->set_rules('assy_start', 'C/F Assessment Year Start', 'trim|required|is_date');
		$this->form_validation->set_rules('assy_end', 'C/F Assessment Year End', 'trim|required|is_date');

		$this->form_validation->set_rules('database_name', 'Database Name', 'trim|required');
		$this->form_validation->set_rules('database_username', 'Database Username', 'trim|required');

		/* Repopulating form */
		if ($_POST)
		{
			$data['account_label']['value'] = $this->input->post('account_label', TRUE);
			$data['account_name']['value'] = $this->input->post('account_name', TRUE);
			$data['assy_start']['value'] = $this->input->post('assy_start', TRUE);
			$data['assy_end']['value'] = $this->input->post('assy_end', TRUE);

			$data['create_database'] = $this->input->post('create_database', TRUE);
			$data['database_name']['value'] = $this->input->post('database_name', TRUE);
			$data['database_username']['value'] = $this->input->post('database_username', TRUE);
			$data['database_password']['value'] = $this->input->post('database_password', TRUE);
			$data['database_host']['value'] = $this->input->post('database_host', TRUE);
			$data['database_port']['value'] = $this->input->post('database_port', TRUE);
		}

		/* Validating form */
		if ($this->form_validation->run() == FALSE)
		{
			$this->messages->add(validation_errors(), 'error');
			$this->template->load('template', 'setting/cf', $data);
			return;
		}
		else
		{
			$data_account_label = $this->input->post('account_label', TRUE);
			$data_account_label = strtolower($data_account_label);
			$data_account_name = $this->input->post('account_name', TRUE);
			$data_assy_start = date_php_to_mysql($this->input->post('assy_start', TRUE));
			$data_assy_end = date_php_to_mysql($this->input->post('assy_end', TRUE));

			$data_database_host = $this->input->post('database_host', TRUE);
			$data_database_port = $this->input->post('database_port', TRUE);
			$data_database_name = $this->input->post('database_name', TRUE);
			$data_database_username = $this->input->post('database_username', TRUE);
			$data_database_password = $this->input->post('database_password', TRUE);

			$ini_file = "system/application/config/accounts/" . $data_account_label . ".ini";

			/* Check if database ini file exists */
			if (get_file_info($ini_file))
			{
				$this->messages->add("Account with same label already exists", 'error');
				$this->template->load('template', 'setting/cf', $data);
				return;
			}

			if ($data_database_host == "")
				$data_database_host = "localhost";
			if ($data_database_port == "")
				$data_database_port = "3306";

			/* Setting database */
			$dsn = "mysql://${data_database_username}:${data_database_password}@${data_database_host}:${data_database_port}/${data_database_name}";
			$newacc = $this->load->database($dsn, TRUE);
			$conn_error = $newacc->_error_message();

			/* Creating database if it does not exist */
			if ($this->input->post('create_database', TRUE) == "1")
			{
				if ((substr($conn_error, 0, 16) == "Unknown database"))
				{
					if ($newacc->query("CREATE DATABASE " . $data_database_name))
					{
						$this->messages->add("New database created", 'success');
						/* Retrying to connect to new database */
						$newacc = $this->load->database($dsn, TRUE);
						$conn_error = $newacc->_error_message();
					} else {
						$this->messages->add("Cannot create database", 'error');
						$this->template->load('template', 'setting/cf', $data);
						return;
					}
				}
			}

			if ( ! $newacc->conn_id)
			{
				$this->messages->add("Cannot connecting to database", 'error');
				$this->template->load('template', 'setting/cf', $data);
				return;
			}  else if ($conn_error != "") {
				$this->messages->add("Error connecting to database. " . $newacc->_error_message(), 'error');
				$this->template->load('template', 'setting/cf', $data);
				return;
			} else if ($newacc->query("SHOW TABLES")->num_rows() > 0) {
				$this->messages->add("Selected database in not empty", 'error');
				$this->template->load('template', 'setting/cf', $data);
				return;
			} else {
				/* Executing the database setup script */
				$setup_account = read_file('system/application/controllers/admin/database.sql');
				$setup_account_array = explode(";", $setup_account);
				foreach($setup_account_array as $row)
				{
					if (strlen($row) < 5)
						continue;
					$newacc->query($row);
					if ($newacc->_error_message() != "")
						$this->messages->add($newacc->_error_message(), 'error');
				}

				/* Adding the account settings */
				$newacc->query("INSERT INTO settings (id, label, name, address, email, ay_start, ay_end, currency_symbol, date_format, timezone, database_version) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array(1, "", $data_account_name, $data_account_address, $data_account_email, $data_assy_start, $data_assy_end, $data_account_currency, $data_account_date, $data_account_timezone, 1));
				$this->messages->add("Successfully created webzash account", 'success');

				/* Adding account settings to file. Code copied from manage controller */
				$con_details = '[database]\ndb_hostname = "' . $data_database_host . '"\ndb_port = "' . $data_database_port . '"\ndb_name = "' . $data_database_name . '"\ndb_username = "' . $data_database_username . '"\ndb_password = "' . $data_database_password . '"\n';

				$con_details_html = '[database]<br />db_hostname = "' . $data_database_host . '"<br />db_port = "' . $data_database_port . '"<br />db_name = "' . $data_database_name . '"<br />db_username = "' . $data_database_username . '"<br />db_password = "' . $data_database_password . '"<br />';

				/* Importing the C/F Values */

				/* Writing the connection string to end of file - writing in 'a' append mode */
				if ( ! write_file($ini_file, $con_details))
				{
					$this->messages->add("Failed to add account settings file. Please check if \"" . $ini_file . "\" file is writable", 'error');
					$this->messages->add("You can manually create a text file \"" . $ini_file . "\" with the following content :<br /><br />" . $con_details_html, 'error');
				} else {
					$this->messages->add("Successfully added webzash account settings file to list of active accounts", 'success');
				}

				redirect('settings');
				return;
			}
		}
		return;
	}
}
