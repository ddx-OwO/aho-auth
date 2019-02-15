<?php
/**
 * Name:    Aho Auth
 * Author:  Dewa Andhika Putra
 *          dewaandhika18@gmail.com
 *          @dwzzzl
 *
 * Created:  2019.01.25
 *
 * Requirements: PHP 7.1 or above
 *
 * @package    aho-auth
 * @author     Dewa Andhika Putra
 * @link       http://github.com/dwzzzl/aho-auth
 * @since      Version 0.1.0
 */
 
defined('BASEPATH') or exit('No direct access script allowed');

class Aho_group_model extends CI_Model {

    /**
     * Tables
     * 
     * @var array
     */
    public $tables = array();

    /**
     * User identity column
     * 
     * @var string
     */
    public $identity_column;

    /**
     * @var array
     */
    public $join = array();

    public function __construct()
    {
        parent::__construct();

        $this->load->library('message');

        $this->tables = $this->config->item('tables', 'aho_config');
        $this->identity_column = $this->config->item('identity_column', 'aho_config');
        $this->join = $this->config->item('join', 'aho_config');
    }

    /**
     * Groups
     *
     * @return  static
     */

    public function groups()
    {
        return $this->db->get($this->tables['groups']);
    }

    /**
     * Add group
     *
     * @param string $name Group name
     * @param string $desc Group description
     * @return mixed
     */
    public function add_group($name, $desc = '')
    {
        $insert = is_array('name') ? 'insert_batch' : 'insert';
        $data = is_array('name') ? $name : ['name' => $name, 'description' => $desc];

        return $this->db
                    ->{$insert}($this->tables['groups'], $data)
                    ->affected_rows() > 0;
    }

    /**
     * Remove group(s)
     *
     * @param   array|int   $group_id   Group IDs
     * @return  bool
     */

    public function remove_group($group_id)
    {
        $where = is_array($group_id) ? 'where_in' : 'where';
        
        return $this->db
                    ->{$where}('group_id', $group_id)
                    ->delete($this->tables['groups']);
    }

    /**
     * Add user to group
     *
     * @param   int $user_id    User ID
     * @param   int $group_id   Group ID
     * @return  bool
     */

    public function add_to_group($user_id, $group_id)
    {
        $this->db->set(
            [
                'user_id' => $user_id,
                'group_id' => $group_id
            ]
        );
        return (bool)$this->db->insert($this->tables['user_groups']);
    }

    /**
     * Move user to specific groups
     *
     * @param   int         $user_id    User ID
     * @param   array|int   $group_id  Group IDs. 
     * @return  bool
     */

    public function move_to_group($user_id, $group_id)
    {
        if ( ! is_array($group_id))
        {
            $group_id = array($group_id);
        }

        $this->remove_user_groups($user_id);

        $batch = array();
        foreach ($group_id as $gid) 
        {
            $data['user_id'] = $user_id;
            $data['group_id'] = $gid;
            array_push($batch, $data);
        }
        return (bool)$this->db->insert_batch($this->tables['user_groups'], $batch);
    }

    /**
     * Remove user from specific group
     *
     * @param   int     $user_id    User ID
     * @param   int     $group_id   Group ID. 
     * @return  bool
     */

    public function remove_from_group($user_id, $group_id)
    {
        $this->db->where([
            'user_id' => $user_id, 
            'group_id' => $group_id
        ]);
        return $this->db->delete($this->tables['user_groups']);
    }

    /**
     * Remove user from all groups
     *
     * @param   int     $user_id    User ID 
     * @return  bool
     */

    public function remove_user_groups($user_id)
    {
        return $this->db
                    ->where('user_id', $user_id)
                    ->delete($this->tables['user_groups']);
    }
}