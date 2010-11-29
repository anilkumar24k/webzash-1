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
}
