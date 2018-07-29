<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class settings extends CI_Controller
{
    public $data;
    public $file_dir;
    public $user_info;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form'));
        $this->load->model('user', '', true);
        $this->load->library('form_validation');

        if ($this->session->userdata('logged_in')) {
            $this->data = $this->session->userdata;
            $this->file_dir = $this->data['file_dir'];
        } else {
            redirect('home', 'refresh');
        }
    }

    public function index()
    {
        if ($this->session->userdata('logged_in')) {
            $data['projects'] = $this->projects->get_projects($this->session->userdata('id'));
            $data['current_project'] = $this->projects->get_project($this->session->userdata('project_id'));

            $this->load->view('settings', $data);
        }
    }

    public function save_use_freq($bool)
    {
        if ($bool == "on") {
            $bool = true;
        } else {
            $bool = false;
        }
        $id = $this->session->userdata('id');
        $data = array(
            'use_freq' => $bool,
        );

        $this->db->where('id', $id);
        $this->db->update('users', $data);
        $this->session->set_userdata('use_freq', $bool);
    }

    public function save_low_freq($low_freq, $high_freq)
    {
        $id = $this->session->userdata('id');
        if ($low_freq <= 100 && $low_freq > $high_freq) {
            $data = array(
                'freq_lower_bound' => $low_freq,
            );

            $this->db->where('id', $id);
            $this->db->update('users', $data);
            $this->session->set_userdata('freq_lower_bound', $low_freq);
        }
    }

    public function save_high_freq($high_freq, $low_freq)
    {
        $id = $this->session->userdata('id');
        if ($high_freq < $low_freq && $high_freq >= 0) {
            $data = array(
                'freq_upper_bound' => $high_freq,
            );

            $this->db->where('id', $id);
            $this->db->update('users', $data);
            $this->session->set_userdata('freq_upper_bound', $high_freq);
        }
    }

    public function save_layout($layout)
    {
        $id = $this->session->userdata('id');
        $data = array(
            'layout' => $layout,
        );

        $this->db->where('id', $id);
        $this->db->update('users', $data);
        $this->session->set_userdata('layout', $layout);
    }

    public function save_mod($mod_resolution)
    {
        $id = $this->session->userdata('id');
        if ($mod_resolution > 0 && $mod_resolution <= 1) {
            $data = array(
                'mod_resolution' => $mod_resolution,
            );

            $this->db->where('id', $id);
            $this->db->update('users', $data);
            $this->session->set_userdata('mod_resolution', $mod_resolution);
        }
    }

    public function save_net_vis($date_range)
    {
        $id = $this->session->userdata('id');
        $data = array(
            'date_range' => $date_range,
        );

        $this->db->where('id', $id);
        $this->db->update('users', $data);
        $this->session->set_userdata('date_range', $date_range);
    }

    public function save_skew_x($skew_x)
    {
        $id = $this->session->userdata('id');
        $data = array(
            'skew_x' => $skew_x,
        );

        $this->db->where('id', $id);
        $this->db->update('users', $data);
        $this->session->set_userdata('skew_x', $skew_x);
    }

    public function save_skew_y($skew_y)
    {
        $id = $this->session->userdata('id');
        $data = array(
            'skew_y' => $skew_y,
        );

        $this->db->where('id', $id);
        $this->db->update('users', $data);
        $this->session->set_userdata('skew_y', $skew_y);
    }

    public function save_skew_z($skew_z)
    {
        $id = $this->session->userdata('id');
        $data = array(
            'skew_z' => $skew_z,
        );

        $this->db->where('id', $id);
        $this->db->update('users', $data);
        $this->session->set_userdata('skew_z', $skew_z);
    }

    public function save_shape($shape)
    {
        $id = $this->session->userdata('id');
        $data = array(
            'shape' => $shape,
        );

        $this->db->where('id', $id);
        $this->db->update('users', $data);
        $this->session->set_userdata('shape', $shape);
    }

    public function save_settings()
    {
        if ($this->input->post('file_action') == "nlp_set") {
            $this->delete_files($this->input->post('checkbox'));
        } elseif ($this->input->post('file_action') == "net_gen_set") {
            $this->save_use_freq($this->input->post('useFreq'));
            $this->save_low_freq($this->input->post('freq_lower'), $this->input->post('freq_upper'));
            $this->save_high_freq($this->input->post('freq_upper'), $this->input->post('freq_lower'));
        } elseif ($this->input->post('file_action') == "net_ana_set") {
            $this->save_layout($this->input->post('Layout'));
            $this->save_mod($this->input->post('mod_resolution'));
        } elseif ($this->input->post('file_action') == "net_vis_set") {
            $this->save_net_vis($this->input->post('date_range'));
            $this->save_skew_x($this->input->post('skew_x'));
            $this->save_skew_y($this->input->post('skew_y'));
            $this->save_skew_z($this->input->post('skew_z'));
            $this->save_shape($this->input->post('shape'));
        } elseif ($this->input->post('file_action') == "current_project") {
            $idOut = $this->session->userdata['id'];
            $new_file_dir = $this->config->item('user_directory') . $this->session->userdata('email');
            $new_file_dir = $new_file_dir . '/' . $this->projects->get_project($this->input->post('project'))->name;
            $new_project = array(
                'project_id' => $this->input->post('project'),
                'file_dir' => $new_file_dir,
            );
            $this->user->update_user($new_project, $idOut);
            $this->session->set_userdata('project_id', $this->input->post('project'));
            $this->session->set_userdata('file_dir', $new_file_dir);
        } elseif ($this->input->post('file_action') == "delete") {
            $this->delete_project($this->input->post('project'));
        }
        redirect('settings', 'refresh');
    }

    public function delete_project($id)
    {
        $this->projects->delete_project($id);
        // TODO - delete directory - This is now a feature. "Deleted" projects can be recovered.
    }

    public function create_directory()
    {
        $dir = $this->input->post('directory_name');

        $projectIn = array(
            'name' => $dir,
            'user_id' => $this->session->userdata('id'),
        );
        $this->projects->createProject($projectIn);

        $dir_path = $this->config->item('user_directory') . $this->session->userdata('email');
        $dir = $dir_path . '/' . $dir;

        $oldmask = umask(0);
        if (!@mkdir($dir, 0777)) {
            $error = error_get_last();
            echo $error['message'];
        }
        if (!@mkdir($dir . '/raw', 0777)) {
            $error = error_get_last();
            echo $error['message'];
        }
        if (!@mkdir($dir . '/preprocessed', 0777)) {
            $error = error_get_last();
            echo $error['message'];
        }
        if (!@mkdir($dir . '/semantic_networks', 0777)) {
            $error = error_get_last();
            echo $error['message'];
        }
        if (!@mkdir($dir . '/partiview_generator', 0777)) {
            $error = error_get_last();
            echo $error['message'];
        }
        if (!@mkdir($dir . '/partiview_generator/individual_gexfs', 0777)) {
            $error = error_get_last();
            echo $error['message'];
        }
        if (!@mkdir($dir . '/parti_output', 0777)) {
            $error = error_get_last();
            echo $error['message'];
        }
        umask($oldmask);
        redirect('settings', 'refresh');
    }
}
