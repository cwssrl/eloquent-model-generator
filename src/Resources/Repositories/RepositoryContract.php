<?php

namespace App\Repositories;

/**
 * Interface RepositoryContract
 * @package App\Repositories\Backend
 */
interface RepositoryContract
{
    /**
     * Return Query Builder Object for custom queries on repository model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery();

    /**
     * Get element by id
     * If not found throw exception
     *
     * @param integer $id Id of element researched
     * @return mixed element with id = $id
     * @throws \App\Exceptions\GenericException if element not found
     */
    public function findOrThrowException($id);

    /**
     * Get element with by id including trashed element
     * If not found throw exception
     *
     * @param integer $id Id of element researched
     * @return mixed element with id = $id
     * @throws \App\Exceptions\GenericException if element not found
     */
    public function findWithTrashedOrThrowException($id);

    /**
     * Get first element on table
     *
     * @return mixed element
     * @throws \App\Exceptions\GenericException if table is empty
     */
    public function firstOrThrowException();

    /**
     * Get all element on table
     * Order by column passed on first argument
     * Sort ( asc | desc ) by second argument
     *
     * @param string $order_by column for order by
     * @param string $sort sorting method (asc | desc)
     * @return mixed
     */
    public function getAll($order_by = 'id', $sort = 'asc');

    /**
     * Get element paginated
     *
     * @param integer $per_page number of elements on any page
     * @param string $order_by order by column
     * @param string $sort sorting method ( asc | desc )
     * @return mixed return element
     *
     */
    public function getPaginated($per_page, $order_by = 'id', $sort = 'asc');

    /**
     * Get a collection of elements with requested fields
     *
     * @param mixed $fields fields to get
     * @return \Illuminate\Database\Eloquent\Collection of items with requested fields
     */
    public function getFields($fields);
}