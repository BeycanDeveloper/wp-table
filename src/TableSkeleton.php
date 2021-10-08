<?php 

namespace Beycan\WPTable;

class TableSkeleton extends \WP_List_Table
{
    /**
     * Item to show per page
     * @var int
     */
    private $perPage = 10;

    /**
     * Total item count
     * @var int
     */
    private $totalRow = 0;
    
    /**
     * To access the constructor of this table
     * @var Table
     */
    private $table;
    
    /**
     * Columns to be used for sorting
     * @var array
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
     * total number of data to be paged
     * 
     * @param int $totalRow
     * 
     * @return void
     */
    public function setTotalRow(int $totalRow): void
    {
        $this->totalRow = $totalRow;
    }
    

    /**
     * Sets the data the table displays per page.
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
     * Prepares and shows the table.
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

        if (!empty($this->table->headerElements)) {
            $headerElements = '';
            foreach ($this->table->headerElements as $func) {
                $headerElements .= call_user_func($func);
            }
            echo $headerElements;
        }

        $this->display();
    }

    /**
     * Makes our table ready to be shown.
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
        $totalRow = $this->totalRow > 0 ? $this->totalRow : count($this->table->dataList);
        
        $this->set_pagination_args([
            'total_items' => $totalRow,
            'per_page'    => $this->perPage
        ]);

        $this->items = array_slice($this->table->dataList, 0, $this->perPage);

        $this->_column_headers = array($this->table->columns, [], $this->sortableColumns);
    }

    /**
     * Table columns to be submitted by the user
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