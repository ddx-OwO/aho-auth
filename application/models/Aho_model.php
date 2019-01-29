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

class Aho_model extends CI_Model {

    /**
     * Time constants
     */
    const MINUTE_IN_SECONDS = 60;
    const HOUR_IN_SECONDS = 60 * MINUTE_IN_SECONDS;
    const DAY_IN_SECONDS = 24 * HOUR_IN_SECONDS;
    const WEEK_IN_SECONDS = 7 * DAY_IN_SECONDS;
    const MONTH_IN_SECONDS = 30 * DAY_IN_SECONDS;
    const YEAR_IN_SECONDS = 365 * DAY_IN_SECONDS;

    /**
     * Set
     * 
     * @var array
     */
    public $_auth_set = array();

    /**
     * Where
     * 
     * @var array
     */
    public $_auth_where = array();

    /**
     * Where in
     * 
     * @var array
     */
    public $_auth_where_in = array();

    /**
     * Select
     * 
     * @var array
     */
    public $_auth_select = array();

    /**
     * Limit
     * 
     * @var string
     */
    public $_auth_limit = NULL;

    /**
     * Offset
     * 
     * @var string
     */
    public $_auth_offset = NULL;

    /**
     * Order by
     * 
     * @var string
     */
    public $_auth_order_by = NULL;

    /**
     * Order
     * 
     * @var string
     */
    public $_auth_order = NULL;

    /**
     * Group by
     * 
     * @var array
     */
    public $_auth_group_by = array();

    /**
     * Response
     * 
     * @var string
     */
    protected $response = NULL;

    public function __construct()
    {
        parent::__construct();

        // Load the database
        $this->load->database();

        $this->load->library('message');

        // Initialize response for static return
        $this->response = new stdClass();
    }

    /**
     * __get
     *
     * Enables the use of CI super-global without having to define an extra variable.
     *
     * @param    string $var
     * @return    mixed
     */
    public function __get($var)
    {
        return get_instance()->$var;
    }

    /**
     * @param string $select
     * @return static
     */
    public function select($select)
    {
        $this->_auth_select[] = $select;
        return $this;
    }

    /**
     * @param array|string $where 
     * @param null|string $value 
     * @return static
     */
    public function where($where, $value = NULL)
    {
        if( ! is_array($where))
        {
            $where = array($where => $value);
        }
        array_push($this->_auth_where, $where);
        return $this;
    }

    /**
     * @param string $where 
     * @param array $value 
     * @return static
     */
    public function where_in($where, $value = array())
    {
        $this->_auth_where_in[] = array('key' => $where, 'value' => $value);
        return $this;
    }

    /**
     * @param array|string $column 
     * @param null|string $value 
     * @return static
     */
    public function set($column, $value = NULL)
    {
        if( ! is_array($column))
        {
            $column = array($column => $value);
        }
        array_push($this->_auth_set, $column);
        return $this;
    }

    /**
     * @param int $limit 
     * @return static
     */
    public function limit($limit)
    {
        $this->_auth_limit = $limit;
        return $this;
    }

    /**
     * @param int $offset 
     * @return static
     */
    public function offset($offset)
    {
        $this->_auth_offset = $offset;
        return $this;
    }

    /**
     * @param string $group_by
     * @return static
     */
    public function group_by($group_by)
    {
        $this->_auth_group_by[] = $group_by;
        return $this;
    }

    /**
     * @param string $column 
     * @param string $order 
     * @return static
     */
    public function order_by($column, $order = 'desc')
    {
        $this->_auth_order_by = $column;
        $this->_auth_order = $order;
        return $this;
    }

    /**
     * @return object|mixed
     */
    public function row()
    {
        return $this->response->row();
    }

    /**
     * @return array|mixed
     */
    public function row_array()
    {
        return $this->response->row_array();
    }

    /**
     * @return mixed
     */
    public function result()
    {
        return $this->response->result();
    }

    /**
     * @return array|mixed
     */
    public function result_array()
    {
        return $this->response->result_array();
    }

    /**
     * @return void
     */
    public function free_result()
    {
        $this->response->free_result();
    }

    /**
     * Get
     * @param string $table 
     * @param int|null $limit 
     * @param int|null $offset 
     * 
     * @return static
     */
    public function get($table, $limit = NULL, $offset = NULL)
    {
        if (isset($this->_auth_select) && ! empty($this->_auth_select))
        {
            foreach ($this->_auth_select as $select) 
            {
                $this->db->select($select);
            }

            // Reset the variable
            $this->_auth_select = array();
        }
        else
        {
            // default selects
            $this->db->select("{$table}.*");
        }

        if (isset($this->_auth_where) && ! empty($this->_auth_where))
        {
            foreach ($this->_auth_where as $where) 
            {
                $this->db->where($where);
            }
            $this->_auth_where = array();
        }

        if (isset($this->_auth_where_in) && ! empty($this->_auth_where_in))
        {
            foreach ($this->_auth_where_in as $where) 
            {
                $this->db->where_in($where['key'], $where['value']);
            }
            $this->_auth_where_in = array();
        }

        if ( ! empty($this->_auth_group_by))
        {
            $this->db->group_by($group_by);
            $this->_auth_group_by = array();
        }

        if (isset($limit))
        {
            $this->_auth_limit = $limit;
        }

        if (isset($offset))
        {
            $this->_auth_offset = $offset;
        }

        if (isset($this->_auth_limit) && isset($this->_auth_offset))
        {
            $this->db->limit($this->_auth_limit);
            $this->db->offset($this->_auth_offset);

            $this->_auth_limit  = NULL;
            $this->_auth_offset = NULL;
        }
        elseif (isset($this->_auth_limit)) 
        {
            $this->db->limit($this->_auth_limit);

            $this->_auth_limit = NULL;
        }

        if (isset($this->_auth_order_by) && isset($this->_auth_order))
        {
            $this->db->order_by($this->_auth_order_by, $this->_auth_order);

            $this->_auth_order    = NULL;
            $this->_auth_order_by = NULL;
        }

        $this->response = $this->db->get($this->tables[$table]);
        return $this;
    }

    /**
     * Add bulk data
     * 
     * @param string $table 
     * @param array $data 
     * 
     * @return int|bool Return the affected rows or FALSE on failure
     */
    public function safe_insert_batch($table, $data = array())
    {
        // Initialize batch variable
        $batch = array();

        if (isset($this->_auth_set) && ! empty($this->_auth_set))
        {
            foreach ($this->_auth_set as $set_data) 
            {
                $batch[] = $set_data;
            }
            $this->_auth_set = array();
        }

        // If there is value in $data, we use it
        if ( ! empty($data))
        {
            $batch = $data;
        }

        $this->db->trans_begin();

        $affected_rows = $this->db->insert_batch($table, $batch);

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        unset($batch);

        return $affected_rows;
    }

    /**
     * Add
     * 
     * @param string $table 
     * @param array  $data
     * @return int|bool
     */
    public function safe_insert($table)
    {
        if (isset($this->_auth_set) && ! empty($this->_auth_set))
        {
            foreach ($this->_auth_set as $set_data) 
            {
                $this->db->set($set_data);
            }
            $this->_auth_set = array();
        }

        // Begin the transaction
        $this->db->trans_begin();

        $this->db->insert($table);

        $id = $this->db->insert_id();

        // If there is no insert_id, then the return value will be affected_rows()
        if($id === 0)
        {
            $id = $this->db->affected_rows();
        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return $id;
    }

    /**
     * update
     * 
     * @param string $table 
     * @return bool
     */
    public function safe_update($table)
    {
        if (isset($this->_auth_set) && ! empty($this->_auth_set))
        {
            foreach ($this->_auth_set as $data) 
            {
                $this->db->set($data);
            }
            $this->_auth_set = array();
        }

        if (isset($this->_auth_where) && ! empty($this->_auth_where))
        {
            foreach ($this->_auth_where as $where) 
            {
                $this->db->where($where);
            }
            $this->_auth_where = array();
        }

        if (isset($this->_auth_where_in) && ! empty($this->_auth_where_in))
        {
            foreach ($this->_auth_where_in as $where) 
            {
                $this->db->where_in($where['key'], $where['value']);
            }
            $this->_auth_where_in = array();
        }

        $this->db->trans_begin();

        $this->db->update($table);

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    /**
     * Delete
     * 
     * @param string $table 
     * @return bool
     */
    public function safe_delete($table)
    {
        if (isset($this->_auth_where) && ! empty($this->_auth_where))
        {
            foreach ($this->_auth_where as $where) 
            {
                $this->db->where($where);
            }
            $this->_auth_where = array();
        }

        if (isset($this->_auth_where_in) && ! empty($this->_auth_where_in))
        {
            foreach ($this->_auth_where_in as $where) 
            {
                $this->db->where_in($where['key'], $where['value']);
            }
            $this->_auth_where_in = array();
        }

        $this->db->trans_begin();

        $this->db->delete($table);

        if($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }
}
