<?php 

namespace Beycan\WPTable;

/**
 * Table skeleton
 * 
 * @package Beycan\WPTable\TableSkeleton
 * @version 1.0
 * @author halilbeycan0@gmail.com
 */

class TableSkeleton extends \WP_List_Table
{
    /**
     * Item to show per page
     * @var int
     * @since 1.0
     */
    private $perPage = 10;
    
    /**
     * To access the constructor of this table
     * @var Table
     * @since 1.1
     */
    private $table;
    
    /**
     * Columns to be used for sorting
     * @var array
     * @since 1.0
     */
    private $sortableColumns = [];

    /**
     * Set data required for table creation
     * 
     * @param Table $creator To access the constructor of this table
     * 
     * @return void
     */

    public function setTable(Table $table): void
    {
        $this->table = $table;
    }

    /**
     * Prepares and shows the table.
     * @since 1.0
     * 
     * @return void
     */
    public function render(): void
    {
        $this->prepare();

        if (isset($this->table->options['search'])) {
            ?>
                <form>
                    <?php if (!empty($_GET)) {
                        foreach ($_GET as $key => $value) { ?>
                            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>"/>
                        <?php }
                    } ?>
                    <?php $this->search_box(
                        $this->table->options['search']['title'], 
                        $this->table->options['search']['id']
                    ); ?>
                </form>
            <?php
        }

        $headerElements = '';
        if (!empty($this->table->headerElements)) {
            foreach ($this->table->headerElements as $func) {
                ob_start();
                call_user_func($func);
                $headerElements .= ob_get_clean();
            }
        }

        echo $headerElements;

        $this->display();
    }

    /**
     * Makes our table ready to be shown.
     * @since 1.0
     * 
     * @return void
     */
    public function prepare(): void
    {
        $this->setPerPage((isset($_GET['per-page']) ? intval($_GET['per-page']) : $this->perPage));

        // Prepare data list
        $this->table->prepareDataList();

        // Set pagination variables
        $currentPage = $this->get_pagenum();
        $totalRow = count($this->table->dataList);
        
        $this->set_pagination_args([
            'total_items' => $totalRow,
            'per_page'    => $this->perPage
        ]);

        $this->items = array_slice(
            $this->table->dataList, 
            (($currentPage - 1) * $this->perPage), 
            $this->perPage
        );

        $this->_column_headers = array(
            $this->table->columns, [], 
            $this->sortableColumns
        );
    }

    /**
     * Sets the data the table displays per page.
     * @since 1.0
     * 
     * @param int $perPage
     * 
     * @return void
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    /**
     * Set the columns with sorting feature in the table.
     * @since 1.0
     * 
     * @param array $sortableColumns
     * 
     * @return void
     */
    public function setSortableColumns(array $sortableColumns): void
    {
        array_map(function($column) {
            $this->sortableColumns[$column] = [$column, true];
        }, $sortableColumns);
    }

    /**
     * Table columns to be submitted by the user
     * @since 1.0
     * 
     * Mandatory and private for WordPress
     * 
     * @return array
     */
    public function get_columns(): array
    {
        return $this->table->columns;
    }

    /**
     * Columns to be used for sorting
     * @since 1.0
     * 
     * Mandatory and private for WordPress
     * 
     * @return array
     */
    public function get_sortable_columns(): array
    {
        return $this->sortableColumns;
    }

    /**
     * Define what data to show on each column of the table
     * @since 1.0
     * 
     * Mandatory and private for WordPress
     * 
     * @param array $itemList current row
     * @param string $columnName - Current column name
     *
     * @return mixed
     */
    public function column_default($itemList, $columnName)
    {
        if (in_array($columnName, array_keys($itemList))) {
            return $itemList[$columnName];
        } else {
            return esc_html__('Key not found!');
        }
    }

}