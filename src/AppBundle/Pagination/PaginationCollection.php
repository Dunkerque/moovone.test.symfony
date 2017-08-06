<?php
/**
 * Created by PhpStorm.
 * User: danny
 * Date: 06/08/2017
 * Time: 14:59
 */

namespace AppBundle\Pagination;

use JMS\Serializer\Annotation\Type;

class PaginationCollection
{
    /**
     * @Type("array<AppBundle\Entity\Movies>")
     */
    public $data;
    public $total;
    public $count;
    public $link = [];

    /**
     * PaginationCollection constructor.
     *
     * @param object $data result of pagerfanta
     * @param $totalItems
     */
    public function __construct($data, $total)
    {
        if ($data instanceof \ArrayIterator) {
            foreach ($data as $item) {
                $this->data[] = $item;
            }
        } else {
            $this->data = $data;
        }
        $this->total = $total;
        $this->count = count($data);
    }

    /**
     * Create link for pagination
     *
     * @param string $ref name of link
     * @param string $url link
     */
    public function addLink($ref, $url)
    {

        $this->link[$ref] = $url;
    }


}