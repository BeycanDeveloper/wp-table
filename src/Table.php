<?php 

namespace Beycan\WPTable;

defined('ABSPATH') || die('You can use the WordPress Table Creator package only one WordPress in!');

// WP_List_Table is not loaded automatically so we need to load it in our application
if(!class_exists('WP_List_Table')){
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Table
{
    /**
     * Example of the created table
     * @var Table
     */
    private $table;

    public $hooks = [];
    public $options = [];
    public $headerElements = [];

    /**
     * Table columns to be submitted by the user
     * @var array
     */
    public $columns = [];

    /**
     * Table data to be submitted by the user
     * @var array
     */
    public $dataList = [];

    /**
     * Actions are taken to create a new table instance
     * 
     * @param array $columns Table columns to be submitted by the user
     * @param array $dataList Table data to be submitted by the user
     * 
     */
    public function __construct(array $columns, array $dataList)
    {
        // Create new table instance
        $this->table = new TableSkeleton();

        // Set variables
        $this->columns = $columns;
        $this->dataList = $dataList;

        // Prepare table
        $this->table->setTable($this);
    }

    /**
     * Prepare table data list
     * 
     * @return void
     */
    public function prepareDataList(): void
    {
        $add = true;

        $dataList = $this->dataList;
        $this->dataList = [];
        $columnsKeys = array_keys($this->columns);

        foreach ($dataList as $item) {
            if (!is_array($item)) {
                $item = (array) $item;
            }

            if (!empty($this->hooks)) {
                array_map(function($hooks) use (&$item) {
                    foreach($hooks as $key => $func) {
                        $item[$key] = call_user_func($func, (object) $item);
                    }
                }, $this->hooks);
            }
            
            if (isset($this->options['search']) && isset($_GET['s']) && !empty($_GET['s'])) {
                $add = preg_grep('~' . preg_quote($_GET['s'], '~') . '~', $item) ? true : false;
            }
            
            $item = array_intersect_key($item, array_flip($columnsKeys));

            if (true === $add) {
                $this->dataList[] = $item;
            }
        }
    }

    public function addHooks(array $hooks) 
    {
        $this->hooks[] = $hooks;
    }
    
    public function addHeaderElements(\Closure $callback) 
    {
        $this->headerElements[] = $callback;
    }

    public function setOptions(array $options = []) 
    {
        $this->options = $options;
    }

    public function __call($methodName, $args)
    {
        if (method_exists($this->table, $methodName)) {
            call_user_func_array([$this->table, $methodName], $args);
        }
    }

}