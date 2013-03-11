<?php // $Id: settingslib.php 919 2013-03-07 02:08:18Z malu $

defined('MOODLE_INTERNAL') || die;

/**
 * Multiple checkboxes with icons for each label
 */
class admin_setting_configmulticheckboxwithicon extends admin_setting_configmulticheckbox {
    /** @var array Array of icons value=>icon */
    protected $icons;

    /**
     * Constructor: uses parent::__construct
     *
     * @param string $name unique ascii name, either 'mysetting' for settings that in config, or 'myplugin/mysetting' for ones in config_plugins.
     * @param string $visiblename localised
     * @param string $description long localised info
     * @param array $defaultsetting array of selected
     * @param array $choices array of $value=>$label for each checkbox
     * @param array $icons array of $value=>$icon for each checkbox
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, array $choices, array $icons) {
        $this->icons = $icons;
        parent::__construct($name, $visiblename, $description, $defaultsetting, $choices);
    }

    /**
     * Returns XHTML field(s) as required by choices
     *
     * Relies on data being an array should data ever be another valid vartype with
     * acceptable value this may cause a warning/error
     * if (!is_array($data)) would fix the problem
     *
     * @todo Add vartype handling to ensure $data is an array
     *
     * @param array $data An array of checked values
     * @param string $query
     * @return string XHTML field
     */
    public function output_html($data, $query='') {
        if (!$this->load_choices() or empty($this->choices)) {
            return '';
        }
        $default = $this->get_defaultsetting();
        if (is_null($default)) {
            $default = array();
        }
        if (is_null($data)) {
            $data = array();
        }
        $options = array();
        $defaults = array();
        foreach ($this->choices as $key=>$description) {
            if (!empty($data[$key])) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            if (!empty($default[$key])) {
                $defaults[] = $description;
            }

//            $options[] = '<input type="checkbox" id="'.$this->get_id().'_'.$key.'" name="'.$this->get_full_name().'['.$key.']" value="1" '.$checked.' />'
//                .'<label for="'.$this->get_id().'_'.$key.'">'.highlightfast($query, $description).'</label>';
            $options[] = '<input type="checkbox" id="'.$this->get_id().'_'.$key.'" name="'.$this->get_full_name().'['.$key.']" value="1" '.$checked.' />'
                .'<label for="'.$this->get_id().'_'.$key.'">'.$this->icons[$key].highlightfast($query, $description).'</label>';
        }

        if (is_null($default)) {
            $defaultinfo = NULL;
        } elseif (!empty($defaults)) {
            $defaultinfo = implode(', ', $defaults);
        } else {
            $defaultinfo = get_string('none');
        }

        $return = '<div class="form-multicheckbox">';
        $return .= '<input type="hidden" name="'.$this->get_full_name().'[xxxxx]" value="1" />'; // something must be submitted even if nothing selected
        if ($options) {
            $return .= '<ul>';
            foreach ($options as $option) {
                $return .= '<li>'.$option.'</li>';
            }
            $return .= '</ul>';
        }
        $return .= '</div>';

        return format_admin_setting($this, $this->visiblename, $return, $this->description, false, '', $defaultinfo, $query);
    }
}

/**
 * Multiple checkboxes for module types
 */
class admin_setting_configmulticheckboxmodtypes extends admin_setting_configmulticheckboxwithicon {
    /**
     * @global moodle_database $DB
     * @global core_renderer $OUTPUT
     * @param string $name
     * @param string $visiblename
     * @param string $description
     * @param array $defaultsetting
     */
    public function __construct($name, $visiblename, $description, $defaultsetting = null) {
        global $DB, $OUTPUT;
        $choices = array();
        $icons = array();
        foreach ($DB->get_records('modules', array(), 'name ASC') as $module) {
            $choices[$module->name] = get_string('modulename', $module->name);
            $icons[$module->name] = ' ' . $OUTPUT->pix_icon('icon', '', $module->name, array('class' => 'icon'));
        }
        parent::__construct($name, $visiblename, $description, $defaultsetting, $choices, $icons);
    }
}

/**
 * Multiple checkboxes for question types
 */
class admin_setting_configmulticheckboxqtypes extends admin_setting_configmulticheckboxwithicon {
    /**
     * @global core_renderer $OUTPUT
     * @param string $name
     * @param string $visiblename
     * @param string $description
     * @param array $defaultsetting
     */
    public function __construct($name, $visiblename, $description, $defaultsetting = null) {
        global $OUTPUT;
        $choices = array();
        $icons = array();
        $qtypes = question_bank::get_all_qtypes();
        // some qtypes do not need workaround
        unset($qtypes['missingtype']);
        unset($qtypes['random']);
        // question_bank::sort_qtype_array() expects array(name => local_name)
        $qtypenames = array_map(function ($qtype) { return $qtype->local_name(); }, $qtypes);
        foreach (question_bank::sort_qtype_array($qtypenames) as $name => $label) {
            $choices[$name] = $label;
            $icons[$name] = ' ' . $OUTPUT->pix_icon('icon', '', $qtypes[$name]->plugin_name()) . ' ';
        }
        parent::__construct($name, $visiblename, $description, $defaultsetting, $choices, $icons);
    }
}