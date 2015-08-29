<?php

/**
 * Pagination Class
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Pagination {

    /**
     * @access public
     * @var integer Current Page
     */
    public $currentPage;

    /**
     * @access public
     * @var integer Number of items(newsfeed, posts, ..etc.) to be displayed per page
     */
    public $perPage;

    /**
     * @access public
     * @var integer Total count of items(newsfeed, posts, ..etc.)
     */
    public $totalCount;

    /**
     * This is the constructor for Pagination Object.
     *
     * @access  public
     * @param   integer  $currentPage
     * @param   integer  $totalCount
     * @param   integer  $perPage Number of items per page
     */
    public function __construct($currentPage = 1, $totalCount = 0, $perPage = 0){
        $this->currentPage = (empty($currentPage))? 1: (int)$currentPage;
        $this->totalCount = (empty($totalCount))? 0: (int)$totalCount;
        $this->perPage = (empty($perPage))? Config::get('PAGINATION_DEFAULT_LIMIT'): (int)$perPage;
    }

    /**
     * get pagination object by executing COUNT(*) query.
     *
     * @access public
     * @param  string  $table
     * @param  string  $options
     * @param  array   $values  array of data
     * @param  integer $pageNum
     * @param  integer $extraOffset check comment class
     * @return Pagination
     */
    public static function pagination($table, $options, $values, $pageNum, $extraOffset = 0){

        $database = Database::openConnection();
        $query  = "SELECT COUNT(*) AS count FROM {$table}  ";
        $query .= $options;

        $database->prepare($query);
        $database->execute($values);
        $totalCount = $database->fetchAssociative()["count"];
        $extraOffset = ((int)$extraOffset > $totalCount)? 0: (int)$extraOffset;

        return new Pagination((int)$pageNum, $totalCount - $extraOffset);
    }

    /**
     * Get the offset.
     *
     * @access public
     * @return integer
     */
    public function getOffset() {
        return ($this->currentPage - 1) * $this->perPage;
    }

    /**
     * Get number of total pages.
     *
     * @access public
     * @return integer
     */
    public function totalPages() {
        return ceil($this->totalCount/$this->perPage);
    }

    /**
     * Get the number of the previous page.
     *
     * @access public
     * @return integer  Number of previous page
     */
    public function previousPage() {
        return $this->currentPage - 1;
    }

    /**
     * Get the number of the next page.
     *
     * @access public
     * @return integer  Number of next page
     */
    public function nextPage() {
        return $this->currentPage + 1;
    }

    /**
     * checks if there is a previous page or not
     *
     * @access public
     * @return boolean
     */
    public function hasPreviousPage() {
        return $this->previousPage() >= 1 ? true : false;
    }

    /**
     * checks if there is a next page or not
     *
     * @access public
     * @return boolean
     */
    public function hasNextPage() {
        return $this->totalPages() >= $this->nextPage()? true : false;
    }

}
