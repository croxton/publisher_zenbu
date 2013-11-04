<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Publisher - Zenbu Support extension
 * ======================================================
 * Enables display of Publisher translation/workflow 
 * status for entries in Zenbu
 *
 * @version     1.0.0 
 * @author      Mark Croxton
 * @copyright   Copyright (c) 2013, hallmarkdesign
 * @license     http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link        http://hallmark-design.co.uk
 * ------------------------------------------------------
 * 
 * Requirements
 * ======================================================
 *
 * Zenbu:
 * @link    http://devot-ee.com/add-ons/zenbu
 *
 * Zenbu hooks docs:
 * @see     http://zenbustudio.com/software/dev/zenbu
 *
 * Publisher:
 * @link    http://devot-ee.com/add-ons/publisher
 */

class Publisher_zenbu_ext {
    
    public $name                = 'Publisher - Zenbu Support';
    public $addon_short_name    = 'publisher_zenbu';
    public $version             = '1.0.0'; 
    public $description         = 'Enables display of Publisher translation / workflow status for entries in Zenbu';
    public $settings_exist      = 'n';
    public $docs_url            = '';
    public $settings            = array();
    private $hooks              = array(
                                    'zenbu_add_column', 
                                    'zenbu_entry_cell_data', 
                                    'zenbu_custom_order_sort'
                                );

    // ------------------------------------------------------

    /**
     * Constructor
     *
     * @param   mixed   Settings array or empty string if none exist
     */
    public function __construct($settings = '')
    {
        $this->settings = $settings;
        ee()->load->add_package_path(PATH_THIRD.'publisher/', TRUE);
        ee()->lang->loadfile('publisher_zenbu');
        ee()->load->model('publisher_entry');
        ee()->lang->loadfile('publisher');
    }

    // ------------------------------------------------------
    
    /**
     * HOOK: zenbu_add_column
     * 
     * Adds a row in Zenbu's Display settings section
     * The $output array must have the following keys:
     * column: Computer-readable used as identifier for settings. Keep it unique!
     * label: Human-readable label used in the Display settings row.
     *
     * @return array    $output     An array of data used by Zenbu
     */
    public function hook_zenbu_add_column()
    {
        // Get whatever was passed through this hook from previous add-ons
        $field = ee()->extensions->last_call;

        // Add to this array with this add-on's data
        $field[] = array(
            'column'    => 'show_publisher_status',     
            'label'     => ee()->lang->line('show_publisher_status'),
        );
            
        return $field;
    }

    // ------------------------------------------------------

    /**
     * HOOK: zenbu_entry_cell_data
     *
     * Adds data to a Zenbu entry row
     * The key must match the computer-readable identifier, minus the 'show_' part.
     *
     * @param int   $entry_id       The current Entry ID
     * @param array $entry_array    An array of all entries found by Zenbu
     * @param int   $channel_id     The current channel ID for the entry
     * @return array    $output     An array of data used by Zenbu
     */
    public function hook_zenbu_entry_cell_data($entry_id, $entry_array, $channel_id)
    {       
        // Get whatever was passed through this hook from previous add-ons
        $output = ee()->extensions->last_call;

        if (isset($entry_id)) 
        {   
            $output['publisher_status'] = ee()->publisher_entry->is_translated_formatted($entry_id, TRUE);
        }
        return $output;
    }

    // ------------------------------------------------------

    /**
     * HOOK: zenbu_custom_order_sort
     * 
     * Adds custom entry ordering/sorting
     * Build on top of main Active Record to retrieve Zenbu results
     * 
     * @param string    $sort       The sort order (asc/desc)
     * @return void 
     */
    public function hook_zenbu_custom_order_sort($sort)
    {
        // join the transcribe entries xref table and sort on the language column
        #ee()->db->join('transcribe_entries_languages', 'channel_titles.entry_id = transcribe_entries_languages.entry_id', 'left');
        ee()->db->order_by('status', $sort);
    }

    // ------------------------------------------------------

    /**
     * Activate extension
     *
     * @return void
     */
    public function activate_extension() 
    {
        foreach ($this->hooks AS $hook)
        {
            $this->_add_hook($hook);
        }
    }

    // ------------------------------------------------------
 
    /**
     * Disable extension
     *
     * @return void
     */     
    public function disable_extension() 
    {
      ee()->db->where('class', __CLASS__);
      ee()->db->delete('exp_extensions');
    } 

    // ------------------------------------------------------
      
    /**
     * Update Extension
     *
     * @param   string  String value of current version
     * @return  mixed   void on update / FALSE if none
     */
    public function update_extension($current = '')
    {
        if ($current == '' OR (version_compare($current, $this->version) === 0))
        {
            return FALSE; // up to date
        }

        // update table row with current version
        ee()->db->where('class', __CLASS__);
        ee()->db->update('extensions', array('version' => $this->version));
    }

    // ------------------------------------------------------

    /**
     * Add extension hook
     *
     * @param      string
     * @return     void
     */
    private function _add_hook($name)
    {
        ee()->db->insert('extensions',
            array(
                'class'    => __CLASS__,
                'method'   => 'hook_' . $name,
                'hook'     => $name,
                'settings' => '',
                'priority' => 110,
                'version'  => $this->version,
                'enabled'  => 'y'
            )
        );
    }
}
/* End of file ext.publisher_zenbu.php */
/* Location: ./system/publisher_zenbu/ext.publisher_zenbu.php */
